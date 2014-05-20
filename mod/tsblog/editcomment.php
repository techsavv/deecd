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
 * This page allows a user to add and edit blog comments
 *
 * @author Matt Clarkson <mattc@catalyst.net.nz>
 * @author Sam Marshall <s.marshall@open.ac.uk>
 * @package tsblog
 */
require_once("../../config.php");
require_once("locallib.php");
require_once('comment_form.php');

define('TS_BLOG_CONFIRMED_COOKIE', 'TS_BLOG_REALPERSON');

$blog = required_param('blog', PARAM_INT);              // Blog ID
$postid = required_param('post', PARAM_INT);            // Post ID for editing
$commentid = optional_param('comment', 0, PARAM_INT);   // Comment ID for editing

if (!$tsblog = $DB->get_record("tsblog", array("id"=>$blog))) {
    print_error('invalidblog', 'tsblog');
}
if (!$cm = get_coursemodule_from_instance('tsblog', $blog)) {
    print_error('invalidcoursemodule');
}
if (!$course = $DB->get_record("course", array("id"=>$tsblog->course))) {
    print_error('coursemisconf');
}
if (!$post = $DB->get_record('tsblog_posts', array('id'=>$postid))) {
    print_error('invalidpost', 'tsblog');
}
if (!$tsbloginstance = $DB->get_record('tsblog_instances', array('id'=>$post->tsbloginstancesid))) {
    print_error('invalidblog', 'tsblog');
}
$url = new moodle_url('/mod/tsblog/editcomment.php', array('blog'=>$blog, 'post'=>$postid, 'comment'=>$commentid));
$PAGE->set_url($url);

// Check security.
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

tsblog_check_view_permissions($tsblog, $context, $cm);
$post->userid=$tsbloginstance->userid; // tsblog_can_view_post needs this
if (!tsblog_can_view_post($post, $USER, $context, $tsblog->global)) {
    print_error('accessdenied', 'tsblog');
}

tsblog_get_activity_groupmode($cm, $course);
if (!tsblog_can_comment($cm, $tsblog, $post)) {
    print_error('accessdenied', 'tsblog');
}

if ($tsblog->allowcomments == TS_BLOG_COMMENTS_PREVENT || $post->allowcomments == TS_BLOG_COMMENTS_PREVENT) {
    print_error('commentsnotallowed', 'tsblog');
}

$viewurl = 'viewpost.php?post='.$post->id;
if ($tsblog->global) {
    $blogtype = 'personal';
    if (!$tsbloguser = $DB->get_record('user', array('id'=>$tsbloginstance->userid))) {
        print_error('invaliduserid');
    }
} else {
    $blogtype = 'course';
}

// Get strings.
$strtsblogs  = get_string('modulenameplural', 'tsblog');
$strtsblog   = get_string('modulename', 'tsblog');
$straddcomment  = get_string('newcomment', 'tsblog');

$moderated = !(isloggedin() && !isguestuser());
$confirmed = isset($_COOKIE[TS_BLOG_CONFIRMED_COOKIE]) &&
        $_COOKIE[TS_BLOG_CONFIRMED_COOKIE] == get_string(
            'moderated_confirmvalue', 'tsblog');
$mform = new mod_tsblog_comment_form('editcomment.php', array(
        'maxvisibility' => $tsblog->maxvisibility,
        'edit' => !empty($commentid),
        'blogid' => $blog,
        'postid' => $postid,
        'moderated' => $moderated,
        'confirmed' => $confirmed,
        'maxbytes' => $tsblog->maxbytes
        ));

if ($mform->is_cancelled()) {
    redirect($viewurl);
    exit;
}
$PAGE->set_title(format_string($tsblog->name));
$PAGE->set_heading(format_string($course->fullname));

if (!$comment = $mform->get_data()) {

    $comment = new stdClass;
    $comment->general = $straddcomment;
    $comment->blog = $blog;
    $comment->post = $postid;
    $mform->set_data($comment);

    // Print the header

    if ($blogtype == 'personal') {
        tsblog_build_navigation($tsblog, $tsbloginstance, $tsbloguser);
    } else {
        tsblog_build_navigation($tsblog, $tsbloginstance, null);
        $url = new moodle_url("$CFG->wwwroot/course/mod.php", array('update' => $cm->id, 'return' => true, 'sesskey' => sesskey()));
    }

    tsblog_get_post_extranav($post);
    $PAGE->navbar->add($comment->general);
    echo $OUTPUT->header();


    echo '<br />';
    $mform->display();

    echo $OUTPUT->footer();

} else {
    // Prepare comment for database
    unset($comment->id);
    $comment->userid = $USER->id;
    $comment->postid = $postid;

    // Special behaviour for moderated users
    if ($moderated) {
        // Check IP address
        if (tsblog_too_many_comments_from_ip()) {
            print_error('error_toomanycomments', 'tsblog');
        }

        // Set the confirmed cookie if they haven't got it yet
        if (!$confirmed) {
            setcookie(TS_BLOG_CONFIRMED_COOKIE, $comment->confirm,
                    time() + 365 * 24 * 3600); // Expire in 1 year
        }

        if (!tsblog_add_comment_moderated($tsblog, $tsbloginstance, $post, $comment)) {
            print_error('couldnotaddcomment', 'tsblog');
        }
        $approvaltime = tsblog_get_typical_approval_time($post->userid);

        tsblog_build_navigation($tsblog, $tsbloginstance, isset($tsbloguser) ? $tsbloguser : null);
        tsblog_get_post_extranav($post);
        $PAGE->navbar->add(get_string('moderated_submitted', 'tsblog'));
        echo $OUTPUT->header();
        notice(get_string('moderated_addedcomment', 'tsblog') .
                ($approvaltime ? ' ' .
                    get_string('moderated_typicaltime', 'tsblog', $approvaltime)
                : ''), 'viewpost.php?post=' . $postid, $course);
        // does not continue
    }

    $comment->userid = $USER->id;

    if (!tsblog_add_comment($course, $cm, $tsblog, $comment)) {
        print_error('couldnotaddcomment', 'tsblog');
    }
    add_to_log($course->id, "tsblog", "add comment", $viewurl, $tsblog->id, $cm->id);
    redirect($viewurl);
}
