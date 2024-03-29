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
 * This page allows a user to delete a blog comments
 *
 * @author Matt Clarkson <mattc@catalyst.net.nz>
 * @package tsblog
 */
require_once("../../config.php");
require_once("locallib.php");
require_once($CFG->libdir . '/completionlib.php');

$commentid  = required_param('comment', PARAM_INT);    // Comment ID to delete
$confirm = optional_param('confirm', 0, PARAM_INT);    // Confirm that it is ok to delete comment

if (!$comment = $DB->get_record('tsblog_comments', array('id'=>$commentid))) {
    print_error('invalidcomment',  'tsblog');
}

if (!$post = tsblog_get_post($comment->postid)) {
    print_error("invalidpost", 'tsblog');
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
$url = new moodle_url('/mod/tsblog/deletepost.php', array('comment'=>$commentid, 'confirm'=>$confirm));
$PAGE->set_url($url);

// Check security.
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
tsblog_check_view_permissions($tsblog, $context, $cm);

// You can always delete your own comments, or any comment on your own
// personal blog
if (!($comment->userid==$USER->id ||
    ($tsblog->global && $post->userid == $USER->id))) {
    require_capability('mod/tsblog:managecomments', $context);
}

if ($tsblog->global) {
    $blogtype = 'personal';
    // Get blog user from the tsblog_get_post result (to save making an
    // extra query); this is only used to display their name anyhow
    $tsbloguser = (object)array('id'=>$post->userid,
        'firstname'=>$post->firstname, 'lastname'=>$post->lastname);
} else {
    $blogtype = 'course';
}
$viewurl = new moodle_url('/mod/tsblog/viewpost.php', array('post'=>$post->id));

if (!empty($commentid) && !empty($confirm)) {
    $updatecomment = (object)array(
        'id' => $commentid,
        'deletedby' => $USER->id,
        'timedeleted' => time());
    $DB->update_record('tsblog_comments', $updatecomment);

    // Inform completion system, if available
    $completion = new completion_info($course);
    if ($completion->is_enabled($cm) && ($tsblog->completioncomments)) {
        $completion->update_state($cm, COMPLETION_INCOMPLETE, $comment->userid);
    }

    redirect($viewurl);
    exit;
}

// Get Strings.
$strtsblogs  = get_string('modulenameplural', 'tsblog');
$strtsblog   = get_string('modulename', 'tsblog');

// Print the header.
$PAGE->set_title(format_string($tsblog->name));
$PAGE->set_heading(format_string($course->fullname));
if ($blogtype == 'personal') {
    $PAGE->navbar->add(fullname($tsbloguser), new moodle_url('/user/view.php', array('id'=>$tsbloguser->id)));
    $PAGE->navbar->add(format_string($tsblog->name));
}
echo $OUTPUT->header();
echo $OUTPUT->confirm(get_string('confirmdeletecomment', 'tsblog'),
                 new moodle_url('/mod/tsblog/deletecomment.php', array('comment'=>$commentid, 'confirm'=>'1')),
                 $viewurl);
