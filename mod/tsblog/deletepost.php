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
 * This page allows a user to delete a blog posts
 *
 * @author Matt Clarkson <mattc@catalyst.net.nz>
 * @package tsblog
 */
require_once("../../config.php");
require_once($CFG->dirroot . '/mod/tsblog/locallib.php');
require_once($CFG->libdir . '/completionlib.php');

$blog = required_param('blog', PARAM_INT);         // Blog ID.
$postid = required_param('post', PARAM_INT);       // Post ID for editing.
$confirm = optional_param('confirm', 0, PARAM_INT);// Confirm that it is ok to delete post.
$delete = optional_param('delete', 0, PARAM_INT);
$email = optional_param('email', 0, PARAM_INT);    // Email author.

if (!$tsblog = $DB->get_record("tsblog", array("id"=>$blog))) {
    print_error('invalidblog', 'tsblog');
}
if (!$post = tsblog_get_post($postid, false)) {
    print_error('invalidpost', 'tsblog');
}
if (!$cm = get_coursemodule_from_instance('tsblog', $blog)) {
    print_error('invalidcoursemodule');
}
if (!$course = $DB->get_record("course", array("id"=>$tsblog->course))) {
    print_error('coursemisconf');
}
$url = new moodle_url('/mod/tsblog/deletepost.php',
        array('blog' => $blog, 'post' => $postid, 'confirm' => $confirm));
$PAGE->set_url($url);

// Check security.
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
tsblog_check_view_permissions($tsblog, $context, $cm);

$postauthor=$DB->get_field_sql("
SELECT
    i.userid
FROM
    {tsblog_posts} p
    INNER JOIN {tsblog_instances} i on p.tsbloginstancesid=i.id
WHERE p.id = ?", array($postid));
if ($postauthor!=$USER->id) {
    require_capability('mod/tsblog:manageposts', $context);
}

$tsblogoutput = $PAGE->get_renderer('mod_tsblog');

if ($tsblog->global) {
    $blogtype = 'personal';
    $tsbloguser = $USER;
    $viewurl = new moodle_url('/mod/tsblog/view.php', array('user' => $USER->id));
    // Print the header.
    $PAGE->navbar->add(fullname($tsbloguser), new moodle_url('/user/view.php',
            array('id' => $tsbloguser->id)));
    $PAGE->navbar->add(format_string($tsblog->name));
} else {
    $blogtype = 'course';
    $viewurl = new moodle_url('/mod/tsblog/view.php', array('id' => $cm->id));
}

if ($email) {
    // Then open and process the form.
    require_once($CFG->dirroot . '/mod/tsblog/deletepost_form.php');
    $customdata = (object)array('blog' => $blog, 'post' => $postid,
            'delete' => $delete, 'email' => $email, 'url' => $viewurl);
    $mform = new mod_tsblog_deletepost_form('deletepost.php', $customdata);
    if ($mform->is_cancelled()) {
        // Form is cancelled, redirect back to the blog.
        redirect($viewurl);
    } else if ($submitted = $mform->get_data()) {
        // Mark the post as deleted.
        tsblog_do_delete($course, $cm, $tsblog, $post);
        // We need these for the call to render post.
        $canaudit = $canmanageposts = false;

        // Store copy of the post for the author.
        // If subject is set in this post, use it.
        if (!isset($post->title) || empty($post->title)) {
            $post->title = get_string('deletedblogpost', 'tsblog');
        }
        $messagepost = $tsblogoutput->render_post($cm, $tsblog, $post, $viewurl, $blogtype,
                $canmanageposts, $canaudit, false, false, false, true);

        // Set up the email message detail.
        $messagetext = $submitted->message['text'];
        $copyself = (isset($submitted->copyself)) ? true : false;
        $includepost = (isset($submitted->includepost)) ? true : false;
        $from = $SITE->fullname;

        // Always send HTML version.
        $user = (object)array(
                'email' => $post->email,
                'mailformat' => 1,
                'id' => $post->userid
        );

        $messagehtml = text_to_html($messagetext);

        // Include the copy of the post in the email to the author.
        if ($includepost) {
            $messagehtml .= $messagepost;
        }
        // Send an email to the author of the post.
        if (!email_to_user($user, $from, $post->title, '', $messagehtml)) {
            print_error(get_string('emailerror', 'tsblog'));
        }
        // Prepare for copies.
        $emails = $selfmail = array();
        if ($copyself) {
            $selfmail[] = $USER->email;
        }
        // Addition of 'Email address of other recipients'.
        if (!empty($submitted->emailadd)) {
            $emails = preg_split('~[; ]+~', $submitted->emailadd);
        }
        $emails = array_merge($emails, $selfmail);

        // If there are any recipients listed send them a copy.
        if (!empty($emails[0])) {
            $subject = strtoupper(get_string('copy')) . ' - '. $post->title;
            foreach ($emails as $email) {
                $fakeuser = (object)array(
                        'email' => $email,
                        'mailformat' => 1,
                        'id' => 0
                );
                if (!email_to_user($fakeuser, $from, $subject, '', $messagehtml)) {
                    print_error(get_string('emailerror', 'tsblog'));
                }
            }
        }
        redirect($viewurl);
    } else if (($delete && $email) ) {
        // If subject is set in this post, use it.
        if (!isset($post->title) || empty($post->title)) {
            $post->title = get_string('deletedblogpost', 'tsblog');
        }
        $displayname = tsblog_get_displayname($tsblog, true);
        // Prepare the object for the emailcontenthtml get_string.
        $emailmessage = new stdClass;
        $emailmessage->subject = $post->title;
        $emailmessage->blog = $tsblog->name;
        $emailmessage->activityname = $displayname;
        $emailmessage->firstname = $USER->firstname;
        $emailmessage->lastname = $USER->lastname;
        $emailmessage->course = $COURSE->fullname;
        $emailmessage->deleteurl = $CFG->wwwroot . '/mod/tsblog/viewpost.php?&post=' . $post->id;
        $formdata = new stdClass;
        $messagetext = get_string('emailcontenthtml', 'tsblog', $emailmessage);
        $formdata->message['text'] = $messagetext;
        // Display the form.
        echo $OUTPUT->header();
        $mform->set_data($formdata);
        $mform->display();
    }
} else {
    if (!$confirm) {
        $PAGE->set_title(format_string($tsblog->name));
        $PAGE->set_heading(format_string($course->fullname));
        echo $OUTPUT->header();
        $confirmdeletestring = get_string('confirmdeletepost', 'tsblog');
        $confirmstring = get_string('deleteemailpostdescription', 'tsblog');

        $deletebutton = new single_button(new moodle_url('/mod/tsblog/deletepost.php',
                array('blog' => $blog, 'post' => $postid, 'delete' => '1',
                        'confirm' => '1')), get_string('delete'), 'post');
        $cancelbutton = new single_button($viewurl, get_string('cancel'), 'get');

        if ($USER->id == $post->userid) {
            print $OUTPUT->confirm($confirmdeletestring, $deletebutton, $cancelbutton);
        } else {
            // Delete - Delete and email || Cancel.
            $deleteemailbutton = new single_button(new moodle_url('/mod/tsblog/deletepost.php',
                    array('blog' => $blog, 'post' => $postid, 'email' => '1', 'delete' => '1')),
                    get_string('deleteemailpostbutton', 'tsblog'), 'post');
            print tsblog_three_button($confirmstring,
                    $deletebutton,
                    $deleteemailbutton,
                    $cancelbutton);
        }
    } else {
        // Mark the post as deleted.
        tsblog_do_delete($course, $cm, $tsblog, $post);
        redirect($viewurl);
    }
}

echo $OUTPUT->footer();

function tsblog_do_delete($course, $cm, $tsblog, $post) {
    global $DB, $USER;
    $updatepost = (object)array(
            'id' => $post->id,
            'deletedby' => $USER->id,
            'timedeleted' => time()
    );

    $transaction = $DB->start_delegated_transaction();
    $DB->update_record('tsblog_posts', $updatepost);
    if (!tsblog_update_item_tags($post->tsbloginstancesid, $post->id,
            array(), $post->visibility)) {
        print_error('tagupdatefailed', 'tsblog');
    }
    if (tsblog_search_installed()) {
        $doc = tsblog_get_search_document($updatepost, $cm);
        $doc->delete();
    }
    // Inform completion system, if available.
    $completion = new completion_info($course);
    if ($completion->is_enabled($cm) && ($tsblog->completionposts)) {
        $completion->update_state($cm, COMPLETION_INCOMPLETE, $post->userid);
    }
    $transaction->allow_commit();
}

/**
 * Print a message along with three buttons buttoneone/buttontwo/Cancel
 *
 * If a string or moodle_url is given instead of a single_button, method defaults to post.
 *
 * @param string $message The question to ask the user.
 * @param single_button $buttonone The single_button component representing the buttontwo response.
 * @param single_button $buttontwo The single_button component representing the buttontwo response.
 * @param single_button $cancel The single_button component representing the Cancel response.
 * @return string HTML fragment
 */
function tsblog_three_button($message, $buttonone, $buttontwo, $cancel) {
    global $OUTPUT;
    if (!($buttonone instanceof single_button)) {
        throw new coding_exception('The buttonone param must be an instance of a single_button.');
    }

    if (!($buttontwo instanceof single_button)) {
        throw new coding_exception('The buttontwo param must be an instance of a single_button.');
    }

    if (!($cancel instanceof single_button)) {
        throw new coding_exception('The cancel param must be an instance of a single_button.');
    }

    $output = $OUTPUT->box_start('generalbox', 'notice');
    $output .= html_writer::tag('p', $message);
    $buttons = $OUTPUT->render($buttonone) . $OUTPUT->render($buttontwo) . $OUTPUT->render($cancel);
    $output .= html_writer::tag('div', $buttons, array('class' => 'buttons'));
    $output .= $OUTPUT->box_end();
    return $output;
}
