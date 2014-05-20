<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Import migrated personal blogs from a 1.9 system
 * These reside in dataroot  - provides means to delete imported data files
 *
 * @package    mod
 * @subpackage tsblog
 * @copyright  2012 The open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/auth/sams_soap/auth.php');
require_once($CFG->dirroot . '/mod/tsblog/locallib.php');
require_once($CFG->dirroot . '/mod/tsblog/lib.php');

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$datadir = $CFG->dataroot . '/tsblog/migration';

$action = optional_param('action', 'NONE', PARAM_ALPHA);

$PAGE->set_context($context);
$PAGE->set_url('/mod/tsblog/migratepersonal.php', array('action' => $action));

// Setup form for uploading zip files.
require_once("$CFG->libdir/formslib.php");
require_once("$CFG->libdir/uploadlib.php");
class importpersonal extends moodleform {
    public function definition() {
        $mform =& $this->_form;
        $mform->addElement('filepicker', 'attachment', 'Import 1.9 data zip', null, array('accepted_types' => '*.zip'));
        $this->add_action_buttons(false, 'Import');
    }
}
$form = new importpersonal();

if ($data = $form->get_data()) {
    // Upload file.
    @raise_memory_limit(MEMORY_EXTRA);
    @set_time_limit(0);
    $file = $form->save_temp_file('attachment');
    output('', true);
    $fp = get_file_packer();
    // Check file is vaild.
    $files = $fp->list_files($file);
    output('', true);
    if (!isset($files[0]->pathname) || strpos($files[0]->pathname, 'pb') !== 0) {
        print_error('Invalid migration zip file.');
    }
    $fp->extract_to_pathname($file, $datadir);
    unlink($file);
    output('Unzipped file ready for importing');
    echo $OUTPUT->continue_button($PAGE->url);
    exit;
}

print $OUTPUT->header();

print $OUTPUT->heading('Import migrated personal blog data');

if ($action == 'DELETE') {
    // Delete an attempt folder.
    require_sesskey();
    $name = required_param('name', PARAM_PATH);
    $dirname = $datadir . '/' . $name;
    if (!file_exists($dirname) || $name == '' || strpos($name, 'pb') === false) {
        print_error('Directory does not exist.');
    }
    if (optional_param('confirm', false, PARAM_BOOL)) {
        if (fulldelete($dirname)) {
            print 'Folder deleted.';
        }
    } else {
        $link = $CFG->wwwroot . '/mod/tsblog/migratepersonal.php';
        $params = "?action=DELETE&name=$name&confirm=1&sesskey=" . sesskey();
        print $OUTPUT->confirm('Are you sure you want to delete?', $link . $params, $link);
    }
} else if ($action == 'GO') {
    require_sesskey();
    $name = required_param('name', PARAM_PATH);
    $dirname = $datadir . '/' . $name;
    if (!file_exists($dirname) || $name == '' || strpos($name, 'pb') === false) {
        print_error('Directory does not exist.');
    }
    if (optional_param('confirm', false, PARAM_BOOL)) {
        // Process the folder.
        @raise_memory_limit(MEMORY_EXTRA);
        @set_time_limit(0);
        migrate_backup($dirname);
    } else {
        if (!file_exists($dirname . '/success.txt') || !file_exists($dirname . '/tsblog.xml')) {
            print_error('Data no good. Missing key files.');
        }
        // File checks/info.
        $doc = new DOMDocument();
        $doc->load($dirname . '/tsblog.xml');
        $instances = $doc->getElementsByTagName('INSTANCE');
        print '<p>' . $instances->length . ' instances in dataset.</p>';
        if (file_exists($dirname . '/imported.xml')) {
            $doc = new DOMDocument();
            $doc->load($dirname . '/imported.xml');
            $tags = $doc->getElementsByTagName('instance');
            print '<p>Have processed ' . $tags->length . ' previously.</p>';
        }
        $link = $CFG->wwwroot . '/mod/tsblog/migratepersonal.php';
        $params = "?action=GO&name=$name&confirm=1&sesskey=" . sesskey();
        $form = $OUTPUT->confirm('You cannot undo this (it will attempt to rollback DB on error). Continue?',
                $link . $params, $link);
        // Add in server image/link match switching.
        $form = str_replace('<input type="submit" value="Continue" />',
                '<input id="acct" name="acct" type="checkbox">Images point to Acct?</input><br />
                <input name="kill" type="checkbox" checked="checked">Trial (rollback transactions)?</input><br />
                <label for="total">Instances to process in this run:</label>
                <select id="total" name="total">
                <option value="0">All available</option>
                <option value="500">500</option>
                <option value="1000">1000</option>
                <option value="5000">5000</option>
                </select><br />
                <input name="ignore" type="checkbox">Re-process instances already added? (Really shouldn\'t select this)</input><br />
                <input type="submit" value="Continue" />', $form);
        $form .= '<p>Select checkbox when images and urls in data set are pointing to learnacct not live server.</p>
                    <p>Once successfully added an instance will not be processed again and will be skipped.</p>';
        echo $form;
    }
}

if ($action == 'NONE') {
    // SHOW MAIN INTERFACE.
    // Show number of personal blog instances.
    if (!$blog = $DB->get_record('tsblog', array('global' => 1))) {
        print_error('globalblogmissing', 'tsblog');
    }
    $bloginsts = $DB->get_record_sql('SELECT COUNT(id) as count FROM {tsblog_instances} WHERE tsblogid = ?',
            array($blog->id));
    print "<p>There are <strong>$bloginsts->count</strong> personal blog instances on the system at present";
    print '<br />Imported instances will not override their settings - posts will be added to existing instances</p>';
    $form->display();
    // Display list of previous attempts and provide some management of these.
    $attemps = array();
    // Get folder info.
    if (file_exists($datadir)) {
        $folders = glob($datadir . '/pb*', GLOB_ONLYDIR);
        foreach ($folders as $folder) {
            $info = new stdClass();
            $info->path = $folder;
            $info->folder = substr($folder, strrpos($folder, '/') + 1);
            $info->size = 0;
            $info->size = @exec("du -b $folder | awk '{print $1}'");
            $info->date = str_replace('pb', '', $info->folder);
            $info->ok = file_exists($folder . '/success.txt') ? 'OK' : 'Failed';
            $attemps[] = $info;
        }
    }
    // Now show.
    print '<div id="existing"><p>Previous backups:</p>';
    foreach ($attemps as $attempt) {
        print '<p>' . userdate($attempt->date) . ' : ' . formatbytes($attempt->size) . ' (' . $attempt->ok . ') ';
        // Delete.
        print '<a href="' . $CFG->wwwroot . '/mod/tsblog/migratepersonal.php?action=DELETE&name=' . $attempt->folder . '&sesskey=' . sesskey() . '">';
        print $OUTPUT->pix_icon('t/delete', 'Delete') . '</a> ';
        // Zip.
        print '<a href="' . $CFG->wwwroot . '/mod/tsblog/migratepersonal.php?action=GO&name=' . $attempt->folder . '&sesskey=' . sesskey() . '">';
        print $OUTPUT->pix_icon('i/restore', 'Import data') . '</a>';
        print '</p>';
    }
    print '</div>';
}

print $OUTPUT->footer();
exit;

/**
 * Start the import of personal blog data
 * @param string $dir path of directory containing backup data
 */
function migrate_backup($dir) {
    global $DB, $CFG, $SOURCESERVER;
    $SOURCESERVER = 'http://learn.open.ac.uk';
    if (optional_param('acct', false, PARAM_BOOL)) {
        $SOURCESERVER = 'http://learnacct.open.ac.uk';
    }
    if ($kill = optional_param('kill', false, PARAM_BOOL)) {
        output('<b>Trial run</b>, will perform rollback on each instance.');
    }
    // Load in XML.
    $doc = new DOMDocument();
    $doc->load($dir . '/tsblog.xml');
    $instancetags = $doc->getElementsByTagName('INSTANCE');
    output('Total = ' . $instancetags->length . ' instances ');

    $total = required_param('total', PARAM_INT);
    $max = $total == 0 ? $instancetags->length + 1 : $total;
    output('Max instances to process: ' . $max);

    // Get xml that stores old instance ID's of those instances already processed.
    $donedoc = new DOMDocument();
    $done = array();
    $donetag = null;
    if (!optional_param('ignore', false, PARAM_BOOL) && file_exists($dir . '/imported.xml')) {
        $donedoc->load($dir . '/imported.xml');
        $donestr = $donedoc->saveXML();
        $donetag = $donedoc->getElementsByTagName('imported')->item(0);
        $tags = $donetag->childNodes->item(0);
        while ($tags != null) {
            // Store ids in array using fast looping.
            $done[$tags->nodeValue] = '';
            $tags->parentNode->removeChild($tags);
            $tags = $donetag->childNodes->item(0);
        }
        // Reinstate childnodes.
        $donedoc->loadXML($donestr);
        $donestr = null;
        output('Have already processed ' . count($done) . ' instances');
    } else {
        $donedoc->appendChild($donedoc->createElement('imported'));
    }
    $donetag = $donedoc->getElementsByTagName('imported')->item(0);
    $count = 0;
    $instances = array();// Stores data (as object) about each instance in array.
    $tags = $instancetags->item(0);
    // Fast looping of node list by always accessing first tag (faster than foreach).
    while ($tags != null && $count < $max) {
        $info = childnodes_to_object($tags);
        // Remove tag we just processed.
        $tags->parentNode->removeChild($tags);
        // Get the next tag to parse.
        $tags = $instancetags->item(0);
        // Have we processed this instance before? If so, skip.
        if (isset($done[$info->id])) {
            $info = null;
            continue;
        }
        $instances[$info->id] = $info;
        $info = null;
        $count++;
    }
    // Throw away xml as no longer need.
    $doc = null;
    $instancetags = null;
    $tags = null;
    output("Ready to process $count instances");

    // Process each blog instance - checking for existing user instance.
    $blog = $DB->get_record('tsblog', array('global' => 1), 'id', MUST_EXIST);
    $blog->global = 1;
    output('Got global blog record');
    if (!$cm = get_coursemodule_from_instance('tsblog', $blog->id)) {
        print_error('invalidcoursemodule');
    }
    $blogcontext = context_module::instance($cm->id);

    // Vars used to store blog user content xml data.
    $xmlcontent = new DOMDocument();
    $xmlfilenum = -1;
    $xpath = null;// Xpath for current content xml file.

    foreach ($instances as &$instance) {
        // transaction per instance.
        $trans = $DB->start_delegated_transaction();
        try {
            // Find/create user.
            if (!$instance->newuserid = tsblog_search_create_user($instance)) {
                output("<b>Error: Skipping blog for $instance->username</b>");
                continue;
            }

            // Work out new instance number (either from new or existing record).
            if ($bloginst = $DB->get_record('tsblog_instances',
                    array('tsblogid' => $blog->id, 'userid' => $instance->newuserid), 'id')) {
                $instance->newid = $bloginst->id;
                $bloginst = null;
            } else {
                // Create instance.
                $newbloginst = new stdClass();
                $newbloginst->tsblogid = $blog->id;
                $newbloginst->userid = $instance->newuserid;
                $newbloginst->name = $instance->name;
                $newbloginst->summary = blogxml_isempty($instance->summary) ? '' : $instance->summary;
                $newbloginst->summary = tsblog_decode_perbloglinks($newbloginst->summary, $xpath);
                $newbloginst->accesstoken = $instance->accesstoken;
                $newbloginst->views = $instance->views;
                if (!($instance->newid = $DB->insert_record('tsblog_instances', $newbloginst))) {
                    output("<b>Failed to add blog instance $instance->name</b>");
                    continue;
                }
                $newbloginst = null;
                // Migrate any mystuff images in summary.
                if (strpos($instance->summary, '@@PLUGINFILE@@')
                        || stripos($instance->summary, $SOURCESERVER . '/pix/smartpix.php/ou/s/')) {
                    $updaterec = new stdClass();
                    $updaterec->id = $instance->newid;
                    $updaterec->summary = tsblog_add_files($instance->summary, $dir, $blogcontext->id,
                            'summary', $instance->newid);
                    $DB->update_record('tsblog_instances', $updaterec);
                    $updaterec = null;
                }
            }

            output('Finished setup blog instance for ' . $instance->username . ' :new id=' . $instance->newid);

            // What is XML file for current instance, load if not current one.
            $instfilenum = get_filenumber($instance->id);
            if ($instfilenum != $xmlfilenum) {
                // Load our XML into current var.
                if (!$xmlcontent->load($dir . '/i' . $instfilenum . '.xml')) {
                    output('<b>Error: Failed to load user data</b> file number ' . $instfilenum);
                    continue;
                }
                $xmlfilenum = $instfilenum;
                $xpath = new DOMXPath($xmlcontent);
                // Get data from XML into vars for quick access.
                $alllinks = array();
                $linktags = $xmlcontent->getElementsByTagName('LINK');
                foreach ($linktags as $l) {
                    $linkob = childnodes_to_object($l);
                    if (!isset($alllinks[$linkob->tsbloginstancesid])) {
                        $alllinks[$linkob->tsbloginstancesid] = array();
                    }
                    $alllinks[$linkob->tsbloginstancesid][] = $linkob;
                    $linkob = null;
                }
                $linktags = null;
                $allusers = array();
                $usertags = $xmlcontent->getElementsByTagName('USER');
                foreach ($usertags as $t) {
                    $allusers[$t->getAttribute('id')] = childnodes_to_object($t);
                }
                $usertags = null;
                $allposts = array();
                $posttags = $xmlcontent->getElementsByTagName('POST');
                $tag = $posttags->item(0);
                // Fast looping for posts as probably the most tags.
                while ($tag != null) {
                    $linkob = childnodes_to_object($tag);
                    $tag->parentNode->removeChild($tag);
                    $tag = $posttags->item(0);
                    if (!isset($linkob->tsbloginstancesid)) {
                        continue;
                    }
                    if (!isset($allposts[$linkob->tsbloginstancesid])) {
                        $allposts[$linkob->tsbloginstancesid] = array();
                    }
                    $allposts[$linkob->tsbloginstancesid][] = $linkob;
                    $linkob = null;
                }
                $posttags = null;
                $alltags = array();
                $tagtags = $xmlcontent->getElementsByTagName('TAG');
                foreach ($tagtags as $l) {
                    $linkob = childnodes_to_object($l);
                    if (!isset($linkob->postid)) {
                        continue;
                    }
                    if (!isset($alltags[$linkob->postid])) {
                        $alltags[$linkob->postid] = array();
                    }
                    $alltags[$linkob->postid][] = $linkob;
                    $linkob = null;
                }
                $tagtags = null;
                $alledits = array();
                $edittags = $xmlcontent->getElementsByTagName('EDIT');
                foreach ($edittags as $l) {
                    $linkob = childnodes_to_object($l);
                    if (!isset($linkob->postid)) {
                        continue;
                    }
                    if (!isset($alledits[$linkob->postid])) {
                        $alledits[$linkob->postid] = array();
                    }
                    $alledits[$linkob->postid][] = $linkob;
                    $linkob = null;
                }
                $edittags = null;
                $allcomments = array();
                $commenttags = $xmlcontent->getElementsByTagName('COMMENT');
                foreach ($commenttags as $l) {
                    $linkob = childnodes_to_object($l);
                    if (!isset($linkob->postid)) {
                        continue;
                    }
                    if (!isset($allcomments[$linkob->postid])) {
                        $allcomments[$linkob->postid] = array();
                    }
                    $allcomments[$linkob->postid][] = $linkob;
                    $linkob = null;
                }
                $commenttags = null;
                output('Loaded user data file ' . $xmlfilenum);
            }
            // Store posts info for this instance (used in link checks).
            $postinfo = array();
            if (isset($allposts[$instance->id])) {
                // Create a clone of the array as it contains objects...
                $postinfo = serialize($allposts[$instance->id]);
                $postinfo = unserialize($postinfo);
            }

            // Create instance links.
            // $links = $xpath->query("/DATA/LINKS/LINK[TS_BLOGINSTANCESID=$instance->id]");
            $links = array();
            if (isset($alllinks[$instance->id])) {
                $links = $alllinks[$instance->id];
            }
            $linksa = array();
            // Sort by SORTORDER - so lowest first.
            foreach ($links as $newlink) {
                // Create new link object and add to our new array.
                // $newlink = childnodes_to_object($link);
                $newlink->tsbloginstancesid = $instance->newid;
                $newlink->tsblogid = $blog->id;
                unset($newlink->id);
                if (!isset($newlink->sortorder)) {
                    $newlink->sortorder = 0;
                }
                $linksa[] = $newlink;
            }
            $links = null;
            // Sort by SORTORDER - so lowest first.
            usort($linksa, 'tsblog_sort_links');
            foreach ($linksa as $link) {
                // Create link.
                $link->url = tsblog_decode_perbloglinks($link->url, $xpath, $postinfo, $instance->userid);
                if (!tsblog_add_link($link)) {
                    output("Error: Failed to create link for blog:$instance->newid");
                }
            }
            $linksa = null;
            // End of link creation code.
            output('Finished links');
            output('Processing posts');
            // PROCESS Blog posts.
            $instance->postmapping = array();// Store old + new ids.
            // $posts = $xpath->query("/DATA/POSTS/POST[TS_BLOGINSTANCESID=$instance->id]");
            $posts = array();
            if (isset($allposts[$instance->id])) {
                $posts = $allposts[$instance->id];
            }
            foreach ($posts as $newpost) {
                // Sort out post object ready to save.
                // $newpost = childnodes_to_object($post);
                $oldid = $newpost->id;
                unset($newpost->id);
                $newpost->groupid = 0;// Just in case!
                $newpost->tsbloginstancesid = $instance->newid;
                if (!is_null($newpost->deletedby)) {
                    if ($newpost->deletedby == $instance->userid) {
                        // Most of the time will be blog user.
                        $newpost->deletedby = $instance->newuserid;
                    } else {
                        // Find mapped user id in this system.
                        // $users = $xpath->query("/DATA/USERS/USER[@id=$newpost->deletedby]");
                        $users = isset($allusers[$newpost->deletedby]) ? $allusers[$newpost->deletedby] : false;
                        if (!$users) {
                            $newpost->deletedby = false;
                        } else {
                            $newpost->deletedby = tsblog_search_create_user($users);
                        }
                        if ($newpost->deletedby == false) {
                            output("Error: Failed to get/make user id for deletedby, old post id: $oldid");
                            $newpost->deletedby = $instance->newuserid;
                        }
                    }
                }
                if (!is_null($newpost->lasteditedby)) {
                    if ($newpost->lasteditedby == $instance->userid) {
                        // Most of the time will be blog user.
                        $newpost->lasteditedby = $instance->newuserid;
                    } else {
                        // Find mapped user id in this system.
                        // $users = $xpath->query("/DATA/USERS/USER[@id=$newpost->lasteditedby]");
                        $users = isset($allusers[$newpost->lasteditedby]) ? $allusers[$newpost->lasteditedby] : false;
                        if (!$users) {
                            $newpost->lasteditedby = false;
                        } else {
                            $newpost->lasteditedby = tsblog_search_create_user($users);
                        }
                        if ($newpost->lasteditedby == false) {
                            output("Error: Failed to get/make user id for edited by, old post id: $oldid");
                            $newpost->lasteditedby = $instance->newuserid;
                        }
                    }
                }
                $newpost->message = tsblog_decode_perbloglinks($newpost->message, $xpath, $postinfo, $instance->userid);
                if (!$postid = $DB->insert_record('tsblog_posts', $newpost)) {
                    output("<b>Error: Failed to create post, old id = $oldid</b>");
                    continue;
                }
                $instance->postmapping[$oldid] = $postid;// Record old/new id mapping.
                output('', true);// Output dots.
                // Migrate any mystuff images in message.
                if (strpos($newpost->message, '@@PLUGINFILE@@')
                        || stripos($newpost->message, $SOURCESERVER . '/pix/smartpix.php/ou/s/')) {
                    $updaterec = new stdClass();
                    $updaterec->id = $postid;
                    $updaterec->message = tsblog_add_files($newpost->message, $dir, $blogcontext->id,
                            'message', $postid);
                    $DB->update_record('tsblog_posts', $updaterec);
                    $updaterec = null;
                }
                // Add post tags (also used in search to save DB query).
                $newpost->tags = array();
                // $tags = $xpath->query("/DATA/TAGS/TAG[POSTID=$oldid]");
                $tagnames = array();
                $tags = array();
                if (isset($alltags[$oldid])) {
                    $tags = $alltags[$oldid];
                }
                foreach ($tags as $tag) {
                    $tagnames[] = $tag->tagname;
                }
                if (!empty($tagnames)) {
                    tsblog_update_item_tags($instance->newid, $postid, $tagnames);
                    $newpost->tags = $tagnames;
                    $tagnames = null;
                }
                if (is_null($newpost->deletedby)) {
                    // Update search.
                    $newpost->id = $postid;
                    $newpost->userid = $instance->newuserid;
                    tsblog_search_update($newpost, $cm);
                    output('', true);
                }
                $newpost = null;
            }
            $posts = null;
            // End Blog post processing.
            output('Finished Posts');
            // From now on all data is got from looping through postmapping array each time.

            // PROCESS Blog EDITS.
            foreach ($instance->postmapping as $oldpostid => $newpostid) {
                // $edits = $xpath->query("/DATA/EDITS/EDIT[POSTID=$oldpostid]");
                $edits = array();
                if (isset($alledits[$oldpostid])) {
                    $edits = $alledits[$oldpostid];
                }
                foreach ($edits as $newedit) {
                    // Sort out edit object ready to save.
                    // $newedit = childnodes_to_object($edit);
                    unset($newedit->id);
                    $newedit->postid = $newpostid;
                    if ($newedit->userid == $instance->userid) {
                        // Most of the time will be blog user.
                        $newedit->userid = $instance->newuserid;
                    } else {
                        // Find mapped user id in this system.
                        // $users = $xpath->query("/DATA/USERS/USER[@id=$newedit->userid]");
                        $users = isset($allusers[$newedit->userid]) ? $allusers[$newedit->userid] : false;
                        if (!$users) {
                            $newedit->userid = false;
                        } else {
                            $newedit->userid = tsblog_search_create_user($users);
                        }
                        if ($newedit->userid == false) {
                            output("Error: Failed to get/make user id for edit rec, old post id: $oldpostid");
                            $newedit->userid = $instance->newuserid;
                        }
                    }
                    $newedit->oldmessage = tsblog_decode_perbloglinks($newedit->oldmessage, $xpath, $postinfo, $instance->userid);
                    output('', true);// Output dots.
                    // Migrate any mystuff images in message.
                    if (strpos($newedit->oldmessage, '@@PLUGINFILE@@')
                            || stripos($newedit->oldmessage, $SOURCESERVER . '/pix/smartpix.php/ou/s/')) {
                        $newedit->oldmessage = tsblog_add_files($newedit->oldmessage, $dir, $blogcontext->id,
                                'message', $newpostid);
                    }
                    if (!$newid = $DB->insert_record('tsblog_edits', $newedit)) {
                        output("Error: Failed to create edit for old post id: $oldpostid");
                        continue;
                    }
                    $newedit = null;
                }
                $edits = null;
            }
            output('Finished edits');
            // End Blog EDITS processing.

            // Process blog comments (Standard + Approved only).
            foreach ($instance->postmapping as $oldpostid => $newpostid) {
                // $comments = $xpath->query("/DATA/COMMENTS/COMMENT[POSTID=$oldpostid]");
                $comments = array();
                if (isset($allcomments[$oldpostid])) {
                    $comments = $allcomments[$oldpostid];
                }
                foreach ($comments as $newcomment) {
                    // Sort out edit object ready to save.
                    // $newcomment = childnodes_to_object($comment);
                    $newcomment->postid = $newpostid;
                    if (!blogxml_isempty($newcomment->userid)) {
                        if ($newcomment->userid == $instance->userid) {
                            // Most of the time will be blog user.
                            $newcomment->userid = $instance->newuserid;
                        } else {
                            // Find mapped user id in this system.
                            // $users = $xpath->query("/DATA/USERS/USER[@id='$newcomment->userid']");
                            $users = isset($allusers[$newcomment->userid]) ? $allusers[$newcomment->userid] : false;
                            if (!$users || empty($users)) {
                                $newcomment->userid = false;
                            } else {
                                $newcomment->userid = tsblog_search_create_user($users);
                            }
                            if ($newcomment->userid == false) {
                                output("Error: Failed to get/make user id for comment, old id: $newcomment->id file:$xmlfilenum");
                                continue;// Skip this comment.
                            }
                        }
                    }
                    if (!blogxml_isempty($newcomment->deletedby)) {
                        if ($newcomment->deletedby == $instance->userid) {
                            // Most of the time will be blog user.
                            $newcomment->deletedby = $instance->newuserid;
                        } else {
                            // Find mapped user id in this system.
                            // $users = $xpath->query("/DATA/USERS/USER[@id=$newcomment->deletedby]");
                            $users = isset($allusers[$newcomment->deletedby]) ? $allusers[$newcomment->deletedby] : false;
                            if (!$users) {
                                $newcomment->deletedby = false;
                            } else {
                                $newcomment->deletedby = tsblog_search_create_user($users);
                            }
                            if ($newcomment->deletedby == false) {
                                output("Error: Failed to get/make user id for comment deletedby, old id: $newcomment->id");
                                $newcomment->deletedby = $instance->newuserid;
                            }
                        }
                    }
                    $newcomment->message = tsblog_decode_perbloglinks($newcomment->message, $xpath, $postinfo, $instance->userid);
                    unset($newcomment->id);
                    if (!$newid = $DB->insert_record('tsblog_comments', $newcomment)) {
                        output("Error: Failed to create comment for old post id: $oldpostid");
                        continue;
                    }
                    output('', true);// Output dots.
                    // Migrate any mystuff images in message.
                    if (strpos($newcomment->message, '@@PLUGINFILE@@')
                            || stripos($newcomment->message, $SOURCESERVER . '/pix/smartpix.php/ou/s/')) {
                        $updaterec = new stdClass();
                        $updaterec->id = $newid;
                        $updaterec->message = tsblog_add_files($newcomment->message, $dir, $blogcontext->id,
                                'messagecomment', $newid);
                        $DB->update_record('tsblog_comments', $updaterec);
                        $updaterec = null;
                    }
                    $newcomment = null;
                }
                $comments = null;
            }
            output('Finished comments');
            // End comments.

            // Commit everything in this instance.
            if ($kill) {
                throw new moodle_exception('kill');
            } else {
                // Commit and save that instance has been imported.
                $trans->allow_commit();
                $donetag->appendChild($donedoc->createElement('instance', $instance->id));
                if (!$donedoc->save($dir . '/imported.xml')) {
                    output('<b>Error saving xml record of instance, will be picked up next save</b>');
                }
            }
        } catch (moodle_exception $e) {
            try {
                $trans->rollback($e);
            } catch (Exception $e) {
                continue;
            }
        }
        $trans = null;
    }

    output('<b>Finished</b>');
}

/**
 * Searches for user based on username.
 * Creates a new user if not exist, based on info sent.
 * Mirrors SAMS SOAP user creation.
 * @param object $info
 * @return int new ID or false
 */
function tsblog_search_create_user($info) {
    global $DB, $CFG;
    if (empty($info->username)) {
        return false;
    }
    // Check our local static cache of users already processed.
    static $users;
    if (!isset($users)) {
        $users = array();
    } else if (!empty($users[$info->username])) {
        return $users[$info->username];
    }
    // Does user exist in system?
    if ($user = $DB->get_record('user', array('username' => $info->username), 'id')) {
        $info->newuserid = $user->id;
    } else {
        // Create user record with min info - expecting it to be updated when user logs in.
        $user = new stdClass();
        $user->username = $info->username;
        $user->firstname = !blogxml_isempty($info->firstname)
        ? $info->firstname : 'New';
        $user->lastname = !blogxml_isempty($info->lastname)
        ? $info->lastname : 'User';
        $user->idnumber = isset($info->idnumber) ? $info->idnumber : '';
        $user->email = !blogxml_isempty($info->email)
        ? $info->email : 'lts-vle-noreply@open.ac.uk';
        if ($user->email == 'lts-vle-noreply@open.ac.uk') {
            $user->emailstop = 1;
        }
        $user->country = 'GB';
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->lang = $CFG->lang;
        $user->confirmed = 1;
        $user->auth = 'sams_soap';
        $user->firstaccess = time();
        $user->picture = 0;
        $user->maildisplay = 0;
        // Set a dummy password (we won't ever check it).
        $user->password = hash_internal_user_password('SAMSsupplied');
        $user->trackforums = 1;
        try {
            $user->id = $DB->insert_record('user', $user, true);
        } catch (moodle_exception $e) {
            output("<strong>Failed to add user $user->username</strong>");
            return false;
        }
        // Add user to role.
        try {
            $auth = new auth_plugin_sams_soap();
            $auth->assign_role($user);
        } catch (moodle_exception $e) {
            output("<strong>Failed to assign user role for $user->username<strong>");
        }
        $info->newuserid = $user->id;
        $user = null;
    }
    $users[$info->username] = $info->newuserid;
    return $info->newuserid;
}

/**
 * Finds refs to images in text and adds files to DB and rewrite text content
 * @param string $text - text to search/replace
 * @param string $dir - dir to get images from
 * @param int $contextid
 * @param string $filearea - name of file area to add to
 * @param int $itemid - number of item id to use in files table
 */
function tsblog_add_files($text, $dir, $contextid, $filearea, $itemid) {
    global $CFG, $OUTPUT, $SOURCESERVER;
    if (strpos($text, '@@PLUGINFILE@@')) {
        require_once($CFG->dirroot . '/lib/filestorage/file_storage.php');
        static $knownfiles;
        if (!isset($knownfiles)) {
            $knownfiles = array();
        }
        $pattern = '#<img.*?src="(@@PLUGINFILE@@(.*?))"#';
        preg_match_all($pattern, $text, $matches);
        $filerecord = new stdClass();
        $filerecord->filearea = $filearea;
        $filerecord->itemid = $itemid;
        $filerecord->contextid = $contextid;
        $filerecord->component = 'mod_tsblog';
        $filerecord->filepath = '/';
        $fs = get_file_storage();
        for ($a = 0, $len = count($matches[2]); $a < $len; $a++) {
            // Add MyStuff images in a folder based on user name.
            $match = $matches[2][$a];
            try {
                $paths = explode('/', $match);
                $filerecord->filename = array_pop($paths);
                if (strpos($filerecord->filename, '.')) {
                    // Looks like we got filename OK so try and store, check if already done first.
                    if (!isset($knownfiles[$contextid . $filearea . $itemid . $filerecord->filename])) {
                        // Import file into system.
                        if (!$fs->file_exists($contextid, 'mod_tsblog', $filearea, $itemid, '/', $filerecord->filename)) {
                            $fs->create_file_from_pathname($filerecord, $dir . $match);
                            // Store we've processed so no need to import again (saves 1 query).
                            $knownfiles[$contextid . $filearea . $itemid . $filerecord->filename] = 0;
                        }
                    }
                    // Replace full path to just filename for rewrite plugin urls.
                    $text = str_replace($match, '/' . $filerecord->filename, $text);
                }
            } catch (moodle_exception $e) {
                // TODO clean up match if fail?
                output('Failed to import file ' . $match . ' : ' . $e->getMessage());
            }
        }
        $matches = null;
    }
    if (stripos($text, $SOURCESERVER . '/pix/smartpix.php/ou/s/')) {
        // Search for hard-coded Emoticons and convert to local versions.
        $pattern = '#<img.*?src="(' . $SOURCESERVER . '/pix/smartpix.php/ou/s/(.*?))"#';
        preg_match_all($pattern, $text, $matches);
        for ($a = 0, $len = count($matches[2]); $a < $len; $a++) {
            // Add MyStuff images in a folder based on user name.
            $match = $matches[2][$a];
            $match = str_ireplace('.gif', '', $match);
            $newurl = $OUTPUT->pix_url('/s/' . $match);
            $text = str_replace($matches[1][$a], $newurl, $text);
        }
    }
    return $text;
}

/**
 * Switches any encode backup links to 'proper' form.
 * Will search for user info and add as needed.
 * @param string $content
 * @param xpath $xpath
 */
function tsblog_decode_perbloglinks($content, $xpath, $userposts = null, $olduserid = null) {
    global $CFG, $SOURCESERVER;
    $result = $content;
    if (strpos($content, '$@TS_BLOGVIEWUSER') !== false) {
        // Process blog links.
        $searchstring = '/\$@(TS_BLOGVIEWUSER)\*([0-9]+)@\$/';
        preg_match_all($searchstring, $result, $foundset);
        if ($foundset[0]) {
            // Iterate over foundset[2]. They are the old_ids.
            foreach ($foundset[2] as $old_id) {
                // We get the needed variables here (stored in users in xml)
                $users = $xpath->query("/DATA/USERS/USER[@id=$old_id]");
                $newid = false;
                if ($users->length != 0) {
                    $user = childnodes_to_object($users->item(0));
                    if (!empty($user->username)) {
                        $newid = urlencode($user->username);
                    }
                }
                // Update the link to its new location.
                if (!empty($newid)) {
                    // Now replace it.
                    $result = str_replace("$@TS_BLOGVIEWUSER*$old_id@$", $CFG->wwwroot.'/mod/tsblog/view.php?u=' . $newid, $result);
                } else {
                    // Can't find, leave it as original.
                    $result = str_replace("$@TS_BLOGVIEWUSER*$old_id@$", $SOURCESERVER . '/mod/tsblog/view.php?user=' . $old_id, $result);
                }
            }
        }
    }
    if (strpos($content, '$@TS_BLOGVIEWPOST') !== false) {
        // Porcess post links.
        $searchstring = '/\$@(TS_BLOGVIEWPOST)\*([0-9]+)@\$/';
        preg_match_all($searchstring, $result, $foundset);
        if ($foundset[0]) {
            // Iterate over foundset[2]. They are the old_ids.
            foreach ($foundset[2] as $old_id) {
                $newusername = false;
                $newposttime = false;
                //See if this user made the post refered to, if so use their details.
                if ($userposts && $olduserid) {
                    foreach ($userposts as $post) {
                        if (isset($post->id) && $post->id == $old_id && !empty($post->timeposted)) {
                            $newposttime = $post->timeposted;
                            // We get the needed variables here (stored in users in xml).
                            $users = $xpath->query("/DATA/USERS/USER[@id={$olduserid}]");
                            if ($users->length != 0) {
                                $user = childnodes_to_object($users->item(0));
                                if (!empty($user->username)) {
                                    $newusername = urlencode($user->username);
                                }
                            }
                            break;;
                        }
                    }
                }
                // Update the link to its new location.
                if (!empty($newusername) && !empty($newposttime)) {
                    // Now replace it.
                    $posturl = new moodle_url('/mod/tsblog/viewpost.php', array('u' => $newusername, 'time' => $newposttime, 'post' => 0));
                    $result = str_replace("$@TS_BLOGVIEWPOST*$old_id@$", $posturl->out(false), $result);
                } else {
                    // Can't find, leave it as original.
                    $result = str_replace("$@TS_BLOGVIEWPOST*$old_id@$", $SOURCESERVER . '/mod/tsblog/viewpost.php?post=' . $old_id, $result);
                }
            }
        }
    }
    return $result;
}

// Used by link usort.
function tsblog_sort_links($a, $b) {
    if ($a->sortorder == $b->sortorder) {
        return 0;
    }
    return ($a->sortorder < $b->sortorder) ? -1 : 1;
}

function childnodes_to_object(DOMElement $node) {
    $node = clone $node;// Must clone or else original will have nodes removed.
    // Add all child elements as properties.
    $info = new stdClass();
    // Fast looping of node list by always accessing first tag (faster than foreach).
    $el = $node->childNodes->item(0);
    while ($el != null) {
        $elname = strtolower($el->nodeName);
        if ($el->hasChildNodes() && !($el->childNodes->length == 1 && $el->childNodes->item(0)->nodeType == XML_TEXT_NODE)) {
            $info->{$elname} = childnodes_to_object($el);
        } else if ($el->nodeType != XML_TEXT_NODE) {
            if ($el->nodeValue == '$@NULL@$') {
                $info->{$elname} = null;
            } else {
                $info->{$elname} = $el->nodeValue;
            }
        }
        $el->parentNode->removeChild($el);
        $el = $node->childNodes->item(0);
    }
    return $info;
}

function formatbytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Returns the appropriate file number per every 1000 - numbers are rounded down.
function get_filenumber($number) {
    return round($number, -3, PHP_ROUND_HALF_DOWN);
}

/**
 * Test if value from xml is empty/null
 * @param string $value
 */
function blogxml_isempty($value) {
    if (empty($value) || $value == '$@NULL@$' || is_null($value)) {
        return true;
    }
    return false;
}

function output($string, $nolbr = false) {
    $linebreak = $nolbr ? '' : '<br />';
    print ("$linebreak$string .");
    flush();
}
