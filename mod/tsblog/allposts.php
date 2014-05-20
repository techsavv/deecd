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
 * This page prints all non-private personal tsblog posts
 *
 * @author Jenny Gray <j.m.gray@open.ac.uk>
 * @package tsblog
 */

require_once('../../config.php');
require_once('locallib.php');

$offset = optional_param('offset', 0, PARAM_INT);   // Offset for paging.
$tag    = optional_param('tag', null, PARAM_TAG);   // Tag to display.

if (!$tsblog = $DB->get_record("tsblog", array("global"=>1))) { // The personal blogs module.
    print_error('personalblognotsetup', 'tsblog');
}

if (!$cm = get_coursemodule_from_instance('tsblog', $tsblog->id)) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record("course", array("id" => $cm->course))) {
    print_error('coursemisconf');
}

$url = new moodle_url('/mod/tsblog/allposts.php', array('offset' => $offset, 'tag'=>$tag));
$PAGE->set_url($url);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
if (!empty($CFG->tsblogallpostslogin) && $tsblog->maxvisibility == TS_BLOG_VISIBILITY_PUBLIC) {
    // Set blog visibility temporarily to loggedin user to force login to this page.
    $tsblog->maxvisibility = TS_BLOG_VISIBILITY_LOGGEDINUSER;
    tsblog_check_view_permissions($tsblog, $context, $cm);
    $tsblog->maxvisibility = TS_BLOG_VISIBILITY_PUBLIC;
} else {
    tsblog_check_view_permissions($tsblog, $context, $cm);
}

$tsblogoutput = $PAGE->get_renderer('mod_tsblog');

// Check security.
$blogtype = 'personal';
$returnurl = 'allposts.php?';

if ($tag) {
    $returnurl .= '&amp;tag='.urlencode($tag);
}

$canmanageposts = has_capability('mod/tsblog:manageposts', $context);
$canaudit       = has_capability('mod/tsblog:audit', $context);

// Log visit.
add_to_log($course->id, "tsblog", "allposts", $returnurl, $tsblog->id, $cm->id);

// Get strings.
$strtsblog      = get_string('modulename', 'tsblog');
$strnewposts    = get_string('newerposts', 'tsblog');
$strolderposts  = get_string('olderposts', 'tsblog');
$strfeeds       = get_string('feeds', 'tsblog');

$strblogsearch  = get_string('searchblogs', 'tsblog');

// Get Posts.
list($posts, $recordcount) = tsblog_get_posts($tsblog, $context, $offset, $cm, null, -1, null,
        $tag, $canaudit, true);

$PAGE->set_title(format_string($tsblog->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->navbar->add(format_string($tsblog->name), new moodle_url('/mod/tsblog/allposts.php'));
$CFG->additionalhtmlhead .= tsblog_get_meta_tags($tsblog, 'all', '', $cm);

// Generate extra navigation.
if ($offset) {
    $a = new stdClass();
    $a->from = ($offset+1);
    $a->to   = (($recordcount - $offset) > TS_BLOG_POSTS_PER_PAGE) ? $offset +
            TS_BLOG_POSTS_PER_PAGE : $recordcount;
    $PAGE->navbar->add(get_string('extranavolderposts', 'tsblog', $a));
} else if (!empty($tag)) {
    $PAGE->navbar->add(get_string('extranavtag', 'tsblog', $tag));
}

if (tsblog_search_installed()) {
    $buttontext=<<<EOF
<form action="search.php" method="get"><div>
  <input type="text" name="query" value=""/>
  <input type="hidden" name="id" value="{$cm->id}"/>
  <input type="submit" value="{$strblogsearch}"/>
</div></form>
EOF;
} else {
    $buttontext='';
}
$url = new moodle_url("$CFG->wwwroot/course/mod.php",
        array('update' => $cm->id, 'return' => true, 'sesskey' => sesskey()));
$PAGE->set_button($buttontext);

$PAGEWILLCALLSKIPMAINDESTINATION = true; // OU accessibility feature.

// The right column, BEFORE the middle-column.
if (isloggedin() and !isguestuser()) {
    list($tsblog, $tsbloginstance) = tsblog_get_personal_blog($USER->id);
    $blogeditlink = "<br /><a href=\"view.php\" class=\"tsblog-links\">$tsbloginstance->name</a>";
    $bc = new block_contents();
    $bc->attributes['id'] = 'tsblog-links';
    $bc->attributes['class'] = 'tsblog-sideblock block';
    $bc->title = format_string($tsblog->name);
    $bc->content = $blogeditlink;
    $PAGE->blocks->add_fake_block($bc, BLOCK_POS_RIGHT);
}

if ($tsblog->statblockon) {
    // 'Discovery' block.
    $stats = array();
    $stats[] = tsblog_stats_output_commentpoststats($tsblog, $cm, $tsblogoutput, false, true);
    $stats[] = tsblog_stats_output_visitstats($tsblog, $cm, $tsblogoutput);
    $stats[] = tsblog_stats_output_poststats($tsblog, $cm, $tsblogoutput);
    $stats[] = tsblog_stats_output_commentstats($tsblog, $cm, $tsblogoutput);
    $stats = $tsblogoutput->render_stats_container('allposts', $stats);
    $bc = new block_contents();
    $bc->attributes['id'] = 'tsblog-discover';
    $bc->attributes['class'] = 'tsblog-sideblock block';
    $bc->title = get_string('discovery', 'tsblog', tsblog_get_displayname($tsblog, true));
    $bc->content = $stats;
    if (!empty($stats)) {
        $PAGE->blocks->add_fake_block($bc, BLOCK_POS_RIGHT);
    }
}

if ($feeds = tsblog_get_feedblock($tsblog, 'all', '', false, $cm)) {
    $bc = new block_contents();
    $bc->attributes['id'] = 'tsblog-feeds';
    $bc->attributes['class'] = 'tsblog-sideblock block';
    $bc->title = $strfeeds;
    $bc->content = $feeds;
    $PAGE->blocks->add_fake_block($bc, BLOCK_POS_RIGHT);
}
// Must be called after add_fake_blocks.
echo $OUTPUT->header();
// Start main column.
print '<div id="middle-column" class="has-right-column">';

print skip_main_destination();

// Renderer hook so extra info can be added to global blog pages in theme.
echo $tsblogoutput->render_viewpage_prepost();

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
    if ($offset > 0) {
        if ($offset-TS_BLOG_POSTS_PER_PAGE == 0) {
            print "<div class='tsblog-newerposts'><a href=\"$returnurl\">$strnewposts</a></div>";
        } else {
            print "<div class='tsblog-newerposts'><a href=\"$returnurl&amp;offset=" .
            ($offset-TS_BLOG_POSTS_PER_PAGE) . "\">$strnewposts</a></div>";
        }
    }
    if ($recordcount - $offset > TS_BLOG_POSTS_PER_PAGE) {
        echo "<a href=\"$returnurl&amp;offset=" . ($offset+TS_BLOG_POSTS_PER_PAGE) .
                "\">$strolderposts</a>";
    }
    echo '</div>';
}

// Print information allowing the user to log in if necessary, or letting
// them know if there are no posts in the blog.
if (!isloggedin() || isguestuser()) {
    print '<p class="tsblog_loginnote">' . get_string('maybehiddenposts', 'tsblog',
            (object) array('link' => 'bloglogin.php', 'name' => tsblog_get_displayname($tsblog))) . '</p>';
} else if (!$posts) {
    print '<p class="tsblog_noposts">' . get_string('noposts', 'tsblog', tsblog_get_displayname($tsblog)) . '</p>';
}
print '</div>';
// Finish the page.
echo $OUTPUT->footer();
