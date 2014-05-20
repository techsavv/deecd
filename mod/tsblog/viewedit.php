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
 * This page prints information about edits to a blog post.
 *
 * @author Matt Clarkson <mattc@catalyst.net.nz>
 * @author Sam Marshall <s.marshall@open.ac.uk>
 * @package tsblog
 */

require_once("../../config.php");
require_once("locallib.php");

$editid = required_param('edit', PARAM_INT);       // Blog post edit ID.

if (!$edit = $DB->get_record('tsblog_edits', array('id'=>$editid))) {
    print_error('invalidedit', 'tsblog');
}

if (!$post = tsblog_get_post($edit->postid)) {
    print_error('invalidpost', 'tsblog');
}

if (!$cm = get_coursemodule_from_instance('tsblog', $post->tsblogid)) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record("course", array("id"=>$cm->course))) {
    print_error('coursemisconf');
}

if (!$tsblog = $DB->get_record("tsblog", array("id"=>$cm->instance))) {
    print_error('invalidcoursemodule');
}

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
tsblog_check_view_permissions($tsblog, $context, $cm);

$url = new moodle_url('/mod/tsblog/viewedit.php', array('edit'=>$editid));
$PAGE->set_url($url);

// Check security.
$canpost            = tsblog_can_post($tsblog, $post->userid, $cm);
$canmanageposts     = has_capability('mod/tsblog:manageposts', $context);
$canmanagecomments  = has_capability('mod/tsblog:managecomments', $context);
$canaudit           = has_capability('mod/tsblog:audit', $context);

// Get strings.
$strtsblogs     = get_string('modulenameplural', 'tsblog');
$strtsblog      = get_string('modulename', 'tsblog');
$strtags        = get_string('tags', 'tsblog');
$strviewedit    = get_string('viewedit', 'tsblog');

// Set-up groups.
$currentgroup = tsblog_get_activity_group($cm, true);
$groupmode = tsblog_get_activity_groupmode($cm, $course);


// Print the header.
if ($tsblog->global) {
    if (!$tsbloginstance = $DB->get_record('tsblog_instances', array('id'=>$post->tsbloginstancesid))) {
        print_error('invalidblog', 'tsblog');
    }
    if (!$tsbloguser = $DB->get_record('user', array('id'=>$tsbloginstance->userid))) {
        print_error('invaliduserid');
    }

    $PAGE->navbar->add(fullname($tsbloguser), new moodle_url('/user/view.php', array('id'=>$tsbloguser->id)));
    $PAGE->navbar->add(format_string($tsblog->name), new moodle_url('/mod/tsblog/view.php', array('user'=>$tsbloguser->id)));
}

if (!empty($post->title)) {
    $PAGE->navbar->add(format_string($post->title), new moodle_url('/mod/tsblog/viewpost.php', array('post'=>$post->id)));
} else {
    $PAGE->navbar->add(shorten_text(format_string($post->message, 30)),
            new moodle_url('/mod/tsblog/viewpost.php', array('post'=>$post->id)));
}

$PAGE->navbar->add($strviewedit);
$PAGE->set_title(format_string($tsblog->name));
$PAGE->set_heading(format_string($course->fullname));
echo $OUTPUT->header();

// Print the main part of the page.
echo '<div class="tsblog-topofpage"></div>';

// Print blog posts.
?>
<div id="middle-column">
    <div class="tsblog-post">
        <h3><?php print format_string($edit->oldtitle) ?></h3>
        <?php
        $fs = get_file_storage();
        if ($files = $fs->get_area_files($context->id, 'mod_tsblog', 'edit', $edit->id, "timemodified", false)) {
            echo '<div class="tsblog-post-attachments">';
            foreach ($files as $file) {
                $filename = $file->get_filename();
                $mimetype = $file->get_mimetype();
                $iconimage = '<img src="'.$OUTPUT->pix_url(file_mimetype_icon($mimetype)).'" class="icon" alt="'.
                        $mimetype.'" />';
                $path = file_encode_url($CFG->wwwroot.'/pluginfile.php', '/'.$context->id.'/mod_tsblog/edit/'.
                        $edit->id.'/'.$filename);
                echo "<a href=\"$path\">$iconimage</a> ";
                echo "<a href=\"$path\">".s($filename)."</a><br />";
            }
            echo '</div>';
        }
        ?>
        <div class="tsblog-post-date">
            <?php print tsblog_date($edit->timeupdated) ?>
        </div>
        <p>
<?php
$text = file_rewrite_pluginfile_urls($edit->oldmessage, 'pluginfile.php', $context->id, 'mod_tsblog',
        'message', $edit->postid);
print format_text($text, FORMAT_HTML);
?>
        </p>
    </div>
</div>
<?php

// Finish the page.
echo '<div class="clearfix"></div>';
echo $OUTPUT->footer();