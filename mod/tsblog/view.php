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
 * This page prints a particular instance of tsblog
 *
 * @author Matt Clarkson <mattc@catalyst.net.nz>
 * @author Sam Marshall <s.marshall@open.ac.uk>
 * @package tsblog
 */

require_once('../../config.php');
require_once('locallib.php');

$id     = optional_param('id', 0, PARAM_INT);       // Course Module ID.
$user   = optional_param('user', 0, PARAM_INT);     // User ID.
$username = optional_param('u', '', PARAM_USERNAME);// User login name.
$offset = optional_param('offset', 0, PARAM_INT);   // Offset fo paging.
$tag    = optional_param('tag', null, PARAM_TAG);   // Tag to display.

// Set user value if u (username) set.
if ($username != '') {
    if (!$tsbloguser = $DB->get_record('user', array('username' => $username))) {
        print_error('invaliduser');
    }
    $user = $tsbloguser->id;
}

$url = new moodle_url('/mod/tsblog/view.php', array('id'=>$id, 'user'=>$user, 'offset'=>$offset,
        'tag'=>$tag));
$PAGE->set_url($url);

if ($id) {
    // Load efficiently (and with full $cm data) using get_fast_modinfo.
    $course = $DB->get_record_select('course',
            'id = (SELECT course FROM {course_modules} WHERE id = ?)', array($id),
            '*', MUST_EXIST);
    $modinfo = get_fast_modinfo($course);
    $cm = $modinfo->get_cm($id);
    if ($cm->modname !== 'tsblog') {
        print_error('invalidcoursemodule');
    }

    if (!$tsblog = $DB->get_record("tsblog", array("id"=>$cm->instance))) {
        print_error('invalidcoursemodule');
    }
    $tsbloguser = (object) array('id' => null);
    $tsbloginstance = null;
    $tsbloginstanceid = null;

} else if ($user) {
    if (!isset($tsbloguser)) {
        if (!$tsbloguser = $DB->get_record('user', array('id' => $user))) {
            print_error('invaliduserid');
        }
    }
    if (!list($tsblog, $tsbloginstance) = tsblog_get_personal_blog($tsbloguser->id)) {
        print_error('invalidcoursemodule');
    }
    if (!$cm = get_coursemodule_from_instance('tsblog', $tsblog->id)) {
        print_error('invalidcoursemodule');
    }
    if (!$course = $DB->get_record("course", array("id"=>$tsblog->course))) {
        print_error('coursemisconf');
    }
    $tsbloginstanceid = $tsbloginstance->id;
} else if (isloggedin()) {
    redirect('view.php?user='.$USER->id);
} else {
    redirect('bloglogin.php');
}

// The mod_edit page gets it wrong when redirecting to a personal blog.
// Since there's no way to know what personal blog was being updated
// this redirects to the users own blog.
if ($tsblog->global && empty($user)) {
    redirect('view.php?user='.$USER->id);
    exit;
}

// If viewing a course blog that requires login, but you're not logged in,
// this causes odd behaviour in OU systems, so redirect to bloglogin.php.
if ($tsblog->maxvisibility != TS_BLOG_VISIBILITY_PUBLIC && !isloggedin()) {
    redirect('bloglogin.php?returnurl=' .
            substr($FULLME, strpos($FULLME, 'view.php')));
}

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
tsblog_check_view_permissions($tsblog, $context, $cm);
$tsblogoutput = $PAGE->get_renderer('mod_tsblog');

// Check security.
$canpost        = tsblog_can_post($tsblog, $user, $cm);
$canmanageposts = has_capability('mod/tsblog:manageposts', $context);
$canaudit       = has_capability('mod/tsblog:audit', $context);

// Get strings.
$strtsblogs     = get_string('modulenameplural', 'tsblog');
$strtsblog      = get_string('modulename', 'tsblog');
$straddpost = get_string('newpost', 'tsblog', tsblog_get_displayname($tsblog));
$strexportposts = get_string('tsblog:exportposts', 'tsblog');
$strtags        = get_string('tags', 'tsblog');
$stredit        = get_string('edit', 'tsblog');
$strdelete      = get_string('delete', 'tsblog');
$strnewposts    = get_string('newerposts', 'tsblog');
$strolderposts  = get_string('olderposts', 'tsblog');
$strcomment     = get_string('comment', 'tsblog');
$strviews = get_string('views', 'tsblog', tsblog_get_displayname($tsblog));
$strlinks       = get_string('links', 'tsblog');
$strfeeds       = get_string('feeds', 'tsblog');
$strblogsearch = get_string('searchthisblog', 'tsblog', tsblog_get_displayname($tsblog));

// Set-up groups.
$groupmode = tsblog_get_activity_groupmode($cm, $course);
$currentgroup = tsblog_get_activity_group($cm, true);

if (!tsblog_is_writable_group($cm)) {
    $canpost=false;
    $canmanageposts=false;
    $cancomment=false;
    $canaudit=false;
}

if (isset($cm)) {
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

// Print the header.
$PAGEWILLCALLSKIPMAINDESTINATION = true;
$hideunusedblog=false;

if ($tsblog->global) {
    $blogtype = 'personal';
    $returnurl = $CFG->wwwroot . '/mod/tsblog/view.php?user='.$user;

    $name = $tsbloginstance->name;

    $buttontext = tsblog_get_search_form('user', $tsbloguser->id, $strblogsearch);
} else {
    $blogtype = 'course';
    $returnurl = $CFG->wwwroot . '/mod/tsblog/view.php?id='.$id;

    $name = $tsblog->name;

    $buttontext = tsblog_get_search_form('id', $cm->id, $strblogsearch);
}

if ($tag) {
    $returnurl .= '&amp;tag='.urlencode($tag);
}

// Set-up individual.
$currentindividual = -1;
$individualdetails = 0;

// Set up whether the group selector should display.
$showgroupselector = true;
if ($tsblog->individual) {
    // If separate individual and visible group, do not show groupselector
    // unless the current user has permission.
    if ($tsblog->individual == TS_BLOG_SEPARATE_INDIVIDUAL_BLOGS
        && !has_capability('mod/tsblog:viewindividual', $context)) {
        $showgroupselector = false;
    }

    $canpost=true;
    $individualdetails = tsblog_individual_get_activity_details($cm, $returnurl, $tsblog,
            $currentgroup, $context);
    if ($individualdetails) {
        $currentindividual = $individualdetails->activeindividual;
        if (!$individualdetails->newblogpost) {
            $canpost=false;
        }
    }
}

// Get Posts.
list($posts, $recordcount) = tsblog_get_posts($tsblog, $context, $offset, $cm, $currentgroup,
        $currentindividual, $tsbloguser->id, $tag, $canaudit);



$hideunusedblog=!$posts && !$canpost && !$canaudit;

if ($tsblog->global && !$hideunusedblog) {
    // Bit about hidden with if global then $posts
    // In order to prevent people from looping through numbers to get the
    // name of every user in the site (in case these names are considered
    // private), don't display the header when not displaying posts, except
    // to users who can post.
    tsblog_build_navigation($tsblog, $tsbloginstance, $tsbloguser);
} else {
    tsblog_build_navigation($tsblog, $tsbloginstance, null);

}
if (!$hideunusedblog) {
    // Generate extra navigation.
    $CFG->additionalhtmlhead .= tsblog_get_meta_tags($tsblog, $tsbloginstance, $currentgroup, $cm);
    $PAGE->set_button($buttontext);
    if ($offset) {
        $a = new stdClass();
        $a->from = ($offset+1);
        $a->to   = (($recordcount - $offset) > TS_BLOG_POSTS_PER_PAGE) ? $offset +
                TS_BLOG_POSTS_PER_PAGE : $recordcount;
        $PAGE->navbar->add(get_string('extranavolderposts', 'tsblog', $a));
    }
    if ($tag) {
        $PAGE->navbar->add(get_string('extranavtag', 'tsblog', $tag));
    }
}
$PAGE->set_title(format_string($tsblog->name));
$PAGE->set_heading(format_string($tsblog->name));


// Initialize $PAGE, compute blocks.
$editing = $PAGE->user_is_editing();

// The left column ...
$hasleft = !empty($CFG->showblocksonmodpages);
// The right column, BEFORE the middle-column.
if (!$hideunusedblog) {
    global $USER, $CFG;
    $links = '';
    if ($tsblog->global) {
        $title = $tsbloginstance->name;
        $summary = $tsbloginstance->summary;
        if (($tsbloginstance->userid == $USER->id) || $canmanageposts ) {
            $params = array('instance' => $tsbloginstance->id);
            $editinstanceurl = new moodle_url('/mod/tsblog/editinstance.php', $params);
            $streditinstance = get_string('blogoptions', 'tsblog');
            $links .= html_writer::start_tag('div', array('class' => 'tsblog-links'));
            $links .= html_writer::link($editinstanceurl, $streditinstance);
            $links .= html_writer::end_tag('div');
        }
        if (empty($CFG->tsblogallpostslogin) || isloggedin()) {
            $allpostsurl = new moodle_url('/mod/tsblog/allposts.php');
            $strallposts = get_string('siteentries', 'tsblog');
            $links .= html_writer::start_tag('div', array('class' => 'tsblog-links'));
            $links .= html_writer::link($allpostsurl, $strallposts);
            $links .= html_writer::end_tag('div');
        }



        $format = FORMAT_HTML;
    } else {
        $summary = $tsblog->intro;
        $title = $tsblog->name;
        $format = $tsblog->introformat;
    }

    // Name, summary, related links.
    $bc = new block_contents();
    $bc->attributes['class'] = 'tsblog-sideblock block';
    /*$bc->attributes['id'] = 'tsblog_info_block';*/
    $bc->title = format_string($title);
    $bc->content = format_text($summary, $format) . $links;
    if ($tsblog->global) {
        $bc->content = file_rewrite_pluginfile_urls($bc->content, 'mod/tsblog/pluginfile.php',
                $context->id, 'mod_tsblog', 'summary', $tsbloginstance->id);
    } else {
        $bc->content = file_rewrite_pluginfile_urls($bc->content, 'pluginfile.php',
                $context->id, 'mod_tsblog', 'intro', null);
    }
    $PAGE->blocks->add_fake_block($bc, BLOCK_POS_RIGHT);

    // Tag Cloud.
    if ($tags = tsblog_get_tag_cloud($returnurl, $tsblog, $currentgroup, $cm, $tsbloginstanceid, $currentindividual)) {
        $bc = new block_contents();
        $bc->attributes['id'] = 'tsblog-tags';
        $bc->attributes['class'] = 'tsblog-sideblock block';
        $bc->title = $strtags;
        $bc->content = $tags;
        $PAGE->blocks->add_fake_block($bc, BLOCK_POS_RIGHT);
    }

    // Links.
    $links = tsblog_get_links($tsblog, $tsbloginstance, $context);
    if ($links) {
        $bc = new block_contents();
        $bc->attributes['id'] = 'tsblog-links';
        $bc->attributes['class'] = 'tsblog-sideblock block';
        $bc->title = $strlinks;
        $bc->content = $links;
        $PAGE->blocks->add_fake_block($bc, BLOCK_POS_RIGHT);
    }

    /* 'Discovery' block.
    $stats = array();
    $stats[] = tsblog_stats_output_myparticipation($tsblog, $cm, $tsblogoutput, $course, $currentindividual, $tsbloguser->id);
    $stats[] = tsblog_stats_output_commentpoststats($tsblog, $cm, $tsblogoutput, false, false, $currentindividual, $tsbloguser->id);
    if ($tsblog->statblockon) {
        // Add to 'Discovery' block when enabled only.
        $stats[] = tsblog_stats_output_visitstats($tsblog, $cm, $tsblogoutput);
        $stats[] = tsblog_stats_output_poststats($tsblog, $cm, $tsblogoutput);
        $stats[] = tsblog_stats_output_commentstats($tsblog, $cm, $tsblogoutput);
    }
    $stats = array_filter($stats);
    if (!empty($stats)) {
        $stats = $tsblogoutput->render_stats_container('view', $stats, count($stats));
        $bc = new block_contents();
        $bc->attributes['id'] = 'tsblog-discover';
        $bc->attributes['class'] = 'tsblog-sideblock block';
        $bc->title = get_string('discovery', 'tsblog', tsblog_get_displayname($tsblog, true));
        $bc->content = $stats;
        $PAGE->blocks->add_fake_block($bc, BLOCK_POS_RIGHT);
    }
    */

    // Feeds.
    if ($feeds = tsblog_get_feedblock($tsblog, $tsbloginstance, $currentgroup, false, $cm, $currentindividual)) {
        $feedicon = ' <img src="'.$OUTPUT->pix_url('i/rss').'" alt="'.get_string('blogfeed', 'tsblog').'"  class="feedicon" />';
        $bc = new block_contents();
        $bc->attributes['class'] = 'tsblog-sideblock block';
        $bc->title = $strfeeds;
        $bc->content = $feeds;
        $PAGE->blocks->add_fake_block($bc, BLOCK_POS_RIGHT);
    }
}
// Must be called after add_fake_blocks.
echo $OUTPUT->header();

// Start main column.
print '<div id="middle-column" class="has-right-column">';

echo $OUTPUT->skip_link_target();

// Print Groups and individual drop-down menu.
echo '<div class="tsblog-groups-individual-selectors">';

// Print Groups.
if ($showgroupselector) {
    groups_print_activity_menu($cm, $returnurl);
}
// Print Individual.
if ($tsblog->individual) {
    if ($individualdetails) {
        echo $individualdetails->display;
        $individualmode = $individualdetails->mode;
        $currentindividual = $individualdetails->activeindividual;
    }
}
echo '</div>';
if (!$hideunusedblog && $tsblog->global) {
    // Renderer hook so extra info can be added to global blog pages in theme.
    echo $tsblogoutput->render_viewpage_prepost();
}
// Print the main part of the page.

echo '<div id="tsblogbuttons">';

// New post button - in group blog, you can only post if a group is selected.
if ($tsblog->individual && $individualdetails) {
    $showpostbutton = $canpost;
} else {
    $showpostbutton = $canpost && ($currentgroup || !$groupmode );
}
if ($showpostbutton) {
    echo '<div id="addpostbutton">';
    echo $OUTPUT->single_button(new moodle_url('/mod/tsblog/editpost.php', array('blog' =>
            $cm->instance)), $straddpost, 'get');
    echo '</div>';
    if ($tsblog->allowimport && ($tsblog->global ||
            $tsblog->individual != TS_BLOG_NO_INDIVIDUAL_BLOGS)) {
        echo '<div class="tsblog_importpostbutton">';
        echo $OUTPUT->single_button(new moodle_url('/mod/tsblog/import.php', array('id' =>
                $cm->id)), get_string('import', 'tsblog'), 'get');
        echo '</div>';
    }
}

// View participation button.
$canview = tsblog_can_view_participation($course, $tsblog, $cm, $currentgroup);
if ($canview) {
        
        /*Brought in 'My blog posts' button from previous version*/
        if ($canview == TS_BLOG_MY_PARTICIPATION) {
        if (groups_is_member($currentgroup, $USER->id) || !$currentgroup) {
            $strparticipation = get_string('myparticipation', 'tsblog');
            $participationurl = new moodle_url('userparticipation.php', array('id' => $cm->id,
                    'group' => $currentgroup, 'user' => $USER->id));
        }
    } else {

        $strparticipation = get_string('participationbyuser', 'tsblog');
        $participationurl = new moodle_url('participation.php', array('id' => $cm->id,
                'group' => $currentgroup));
    }
    if (isset($participationurl)) {
         /*end change*/
        echo '<div class="participationbutton">';
        echo $OUTPUT->single_button($participationurl, $strparticipation, 'get');
        echo '</div>';
    }
}

echo '</div>';

// Print blog posts.
if ($posts) {
    echo '<div id="tsblog-posts">';
    $rowcounter = 1;
    foreach ($posts as $post) {
        $post->row = $rowcounter;
        echo $tsblogoutput->render_post($cm, $tsblog, $post, $returnurl, $blogtype,
                $canmanageposts, $canaudit, true, false);
        $rowcounter++;
    }
    echo "<div class='tsblog-paging'>";
    if ($offset > 0) {
        if ($offset-TS_BLOG_POSTS_PER_PAGE == 0) {
            print "<div class='tsblog-newerposts'><a href=\"$returnurl\">$strnewposts</a></div>";
        } else {
            print "<div class='tsblog-newerposts'><a href=\"$returnurl&amp;offset=" .
                    ($offset-TS_BLOG_POSTS_PER_PAGE) . "\">$strnewposts</a></div>";
        }
    }

    if ($recordcount - $offset > TS_BLOG_POSTS_PER_PAGE) {
        print "<div class='tsblog-olderposts'><a href=\"$returnurl&amp;offset=" .
                ($offset+TS_BLOG_POSTS_PER_PAGE) . "\">$strolderposts</a></div>";
    }
    echo '</div></div>';
    echo '<div id="addexportpostsbutton">';
    // Show portfolio export link.
    // Will need to be passed enough details on the blog so it can accurately work out what
    // posts are displayed (as tsblog_get_posts above).
    if (!empty($CFG->enableportfolios) &&
            (has_capability('mod/tsblog:exportpost', $context))) {
        require_once($CFG->libdir . '/portfoliolib.php');
        if ($canaudit) {
            $canaudit = 1;
        } else {
            $canaudit = 0;
        }
        if (empty($tsbloguser->id)) {
            $tsbloguser->id = 0;
        }
        $tagid = null;
        if (!is_null($tag)) {
            // Make tag work with portfolio param cleaning by looking up id.
            if ($tagrec = $DB->get_record('tsblog_tags', array('tag' => $tag), 'id')) {
                $tagid = $tagrec->id;
            }
        }
        $button = new portfolio_add_button();
        $button->set_callback_options('tsblog_all_portfolio_caller',
                array('postid' => $post->id,
                        'tsblogid' => $tsblog->id,
                        'offset' => $offset,
                        'currentgroup' => $currentgroup,
                        'currentindividual' => $currentindividual,
                        'tsbloguserid' => $tsbloguser->id,
                        'canaudit' => $canaudit,
                        'tag' =>  $tagid,
                        'cmid' => $cm->id, ), 'mod_tsblog');
        echo $button->to_html(PORTFOLIO_ADD_TEXT_LINK) .
        get_string('exportpostscomments', 'tsblog');
    }
    echo '</div>';
}
// Print information allowing the user to log in if necessary, or letting
// them know if there are no posts in the blog.
if (isguestuser() && $USER->id==$user) {
    print '<p class="tsblog_loginnote">'.
            get_string('guestblog', 'tsblog',
                    'bloglogin.php?returnurl='.urlencode($returnurl)).'</p>';
} else if (!isloggedin() || isguestuser()) {
    print '<p class="tsblog_loginnote">'.
            get_string('maybehiddenposts', 'tsblog',
                    (object) array('link' => 'bloglogin.php?returnurl='.urlencode($returnurl),
                            'name' => tsblog_get_displayname($tsblog))).'</p>';
} else if (!$posts) {
    print '<p class="tsblog_noposts">'.
            get_string('noposts', 'tsblog', tsblog_get_displayname($tsblog)).'</p>';
}

// Log visit and bump view count.
add_to_log($course->id, "tsblog", "view", 'view.php?id='.$cm->id, $tsblog->id, $cm->id);
$views = tsblog_update_views($tsblog, $tsbloginstance);

// Finish the page.
echo "<div class=\"clearer\"></div><div class=\"tsblog-views\">$strviews $views</div></div>";

echo $OUTPUT->footer();
