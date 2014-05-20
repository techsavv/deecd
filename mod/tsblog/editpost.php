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
 * This page allows a user to add and edit blog posts
 *
 * @author Matt Clarkson <mattc@catalyst.net.nz>
 * @author Sam Marshall <s.marshall@open.ac.uk>
 * @package tsblog
 */

require_once("../../config.php");
require_once("locallib.php");
require_once('post_form.php');

$blog = required_param('blog', PARAM_INT);        // Blog ID
$postid = optional_param('post', 0, PARAM_INT);   // Post ID for editing

if ($blog) {
    if (!$tsblog = $DB->get_record("tsblog", array("id"=>$blog))) {
        print_error('invalidblog', 'tsblog');
    }
    if (!$cm = get_coursemodule_from_instance('tsblog', $blog)) {
        print_error('invalidcoursemodule');
    }
    if (!$course = $DB->get_record("course", array("id"=>$tsblog->course))) {
        print_error('coursemisconf');
    }
}
if ($postid) {
    if (!$post = $DB->get_record('tsblog_posts', array('id'=>$postid))) {
        print_error('invalidpost', 'tsblog');
    }
    if (!$tsbloginstance = $DB->get_record('tsblog_instances', array('id'=>$post->tsbloginstancesid))) {
        print_error('invalidblog', 'tsblog');
    }
}

$url = new moodle_url('/mod/tsblog/editpost.php', array('blog'=>$blog, 'post'=>$postid));
$PAGE->set_url($url);

// Check security.
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
tsblog_check_view_permissions($tsblog, $context, $cm);

$PAGE->requires->js_init_call('M.mod_tsblog.init', null, true);

if ($tsblog->global) {
    $blogtype = 'personal';

    // New posts point to current user
    if (!isset($tsbloginstance)) {
        $tsbloguser = $USER;
        if (!$tsbloginstance = $DB->get_record('tsblog_instances', array('tsblogid'=>$tsblog->id, 'userid'=>$USER->id))) {
            print_error('invalidblog', 'tsblog');
        }
    } else {
        $tsbloguser = $DB->get_record('user', array('id'=>$tsbloginstance->userid));
    }
    $viewurl = new moodle_url('/mod/tsblog/view.php', array('user'=>$tsbloguser->id));

} else {
    $blogtype = 'course';
    $viewurl = new moodle_url('/mod/tsblog/view.php', array('id'=>$cm->id));
}

// If editing a post, must be your post or you have manageposts
$canmanage=has_capability('mod/tsblog:manageposts', $context);
if (isset($post) && $USER->id != $tsbloginstance->userid && !$canmanage) {
    print_error('accessdenied', 'tsblog');
}

// Must be able to post in order to post OR edit a post. This is so that if
// somebody is blocked from posting, they can't just edit an existing post.
// Exception is that admin is allowed to edit posts even though they aren't
// allowed to post to the blog.
if (!(
    tsblog_can_post($tsblog, isset($tsbloginstance) ? $tsbloginstance->userid : 0, $cm) ||
    (isset($post) && $canmanage))) {
    print_error('accessdenied', 'tsblog');
}

// Get strings.
$strtsblogs  = get_string('modulenameplural', 'tsblog');
$strtsblog   = get_string('modulename', 'tsblog');
$straddpost  = get_string('newpost', 'tsblog', tsblog_get_displayname($tsblog));
$streditpost = get_string('editpost', 'tsblog');


// Set-up groups.
$currentgroup = tsblog_get_activity_group($cm, true);
$groupmode = tsblog_get_activity_groupmode($cm, $course);
if ($groupmode==VISIBLEGROUPS && !groups_is_member($currentgroup) && !$tsblog->individual) {
    require_capability('moodle/site:accessallgroups', $context);
}

$mform = new mod_tsblog_post_form('editpost.php', array(
    'individual' => $tsblog->individual,
    'maxvisibility' => $tsblog->maxvisibility,
    'allowcomments' => $tsblog->allowcomments,
    'edit' => !empty($postid),
    'personal' => $tsblog->global,
    'maxbytes' => $tsblog->maxbytes,
    'maxattachments' => $tsblog->maxattachments));
if ($mform->is_cancelled()) {
    redirect($viewurl);
    exit;
}


if (!$frmpost = $mform->get_data()) {

    if ($postid) {
        $post->post  = $post->id;
        $post->general = $streditpost;
        $post->tags = tsblog_get_tags_csv($post->id);
    } else {
        $post = new stdClass;
        $post->general = $straddpost;
    }

    $post->blog = $tsblog->id;

    $draftitemid = file_get_submitted_draft_itemid('attachments');
    file_prepare_draft_area($draftitemid, $context->id, 'mod_tsblog', 'attachment',
            empty($post->id) ? null : $post->id);

    $draftid_editor = file_get_submitted_draft_itemid('message');
    $currenttext = file_prepare_draft_area($draftid_editor, $context->id, 'mod_tsblog',
            'message', empty($post->id) ? null : $post->id,
            array('subdirs'=>0), empty($post->message) ? '' : $post->message);

    $post->attachments = $draftitemid;
    $post->message = array('text'=>$currenttext,
                           'format'=>empty($post->messageformat) ? editors_get_preferred_format() : $post->messageformat,
                           'itemid'=>$draftid_editor);

    $mform->set_data($post);


    // Print the header

    if ($blogtype == 'personal') {
        $PAGE->navbar->add(fullname($tsbloguser), new moodle_url('/user/view.php', array('id'=>$tsbloguser->id)));
        $PAGE->navbar->add(format_string($tsbloginstance->name), $viewurl);
    }
    $PAGE->navbar->add($post->general);
    /*SG - Instructions at the top of the New blog post page - can edit language string in theme*/
    $PAGE->set_pagelayout('newblogpost');
    /*End change*/
    $PAGE->set_title(format_string($tsblog->name));
    $PAGE->set_heading(format_string($course->fullname));
    echo $OUTPUT->header();
    $renderer = $PAGE->get_renderer('mod_tsblog');
    echo $renderer->render_pre_postform($tsblog, $cm);
    $mform->display();

    echo $OUTPUT->footer();

} else {

    $post = $frmpost;
    // Handle form submission.
    if (!empty($post->post)) {
        // update the post
        $post->id = $post->post;
        $post->tsblogid = $tsblog->id;
        $post->userid = $tsbloginstance->userid;

        tsblog_edit_post($post, $cm);
        add_to_log($course->id, "tsblog", "edit post", $viewurl, $tsblog->id, $cm->id);
        redirect($viewurl);

    } else {
        // insert the post
        unset($post->id);
        $post->tsblogid = $tsblog->id;
        $post->userid = $USER->id;

        // Consider groups only when it is not an individual blog.
        if ($tsblog->individual) {
            $post->groupid = 0;
        } else {
            if (!$currentgroup && $groupmode) {
                print_error('notaddpostnogroup', 'tsblog');
            }
            $post->groupid = $currentgroup;
        }

        if (!tsblog_add_post($post, $cm, $tsblog, $course)) {
            print_error('notaddpost', 'tsblog');
        }
        add_to_log($course->id, "tsblog", "add post", $viewurl, $tsblog->id, $cm->id);
        redirect($viewurl);
    }

}
