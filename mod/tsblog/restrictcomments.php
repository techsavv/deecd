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

// You can restrict comments (=change your posts so that they only permit
// comments from signed-in users) on either a post or a blog. A confirmation
// screen displays first.
require_once('../../config.php');
require_once('locallib.php');

// Get post or blog details
$postid = optional_param('post', 0, PARAM_INT);
if ($postid) {
    $isblog = false;
    if (!$tsblog = tsblog_get_blog_from_postid($postid)) {
        print_error('invalidrequest');
    }
} else {
    $blogid = required_param('blog', PARAM_INT);
    $isblog = true;
    if (!$tsblog = $DB->get_record('tsblog', array('id'=>$blogid))) {
        print_error('invalidrequest');
    }
}

// Get other details and check access
if (!$cm = get_coursemodule_from_instance('tsblog', $tsblog->id)) {
    print_error('invalidcoursemodule');
}
if (!$course = $DB->get_record("course", array("id"=>$cm->course))) {
    print_error('coursemisconf');
}

// Require login and access to blog
require_login($course, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
tsblog_check_view_permissions($tsblog, $context, $cm);

// You must be able to post to blog (if blog = site blog, then your one)
if (!tsblog_can_post($tsblog, $USER->id, $cm)) {
    print_error('accessdenied', 'tsblog');
}

// If there was a specified post, it must be yours
if (!$isblog) {
    $userid = $DB->get_field_sql("
SELECT
    bi.userid
FROM
    {tsblog_posts} bp
    INNER JOIN {tsblog_instances} bi ON bi.id=bp.tsbloginstancesid
WHERE
    bp.id = ?", array($postid));
    if ($userid !== $USER->id) {
        print_error('accessdenied', 'tsblog');
    }
}

// Is this the actual change or just the confirm?
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_sesskey();

    // Apply actual change
    $rparam = array();
    if ($isblog) {
        $restriction = 'b.id = ? ';
        $rparam[] = $blogid;
    } else {
        $restriction = 'bp.id = ? ';
        $rparam[] = $postid;
    }
    if (!$DB->execute("
UPDATE {tsblog_posts} SET allowcomments = " . TS_BLOG_COMMENTS_ALLOW ."
WHERE id IN (
SELECT bp.id FROM
    {tsblog_posts} bp
    INNER JOIN {tsblog_instances} bi ON bi.id = bp.tsbloginstancesid
    INNER JOIN {tsblog} b ON b.id = bi.tsblogid
WHERE
    bi.userid = ?
    AND bp.allowcomments >= " . TS_BLOG_COMMENTS_ALLOWPUBLIC . "
    AND $restriction)", array_merge(array($USER->id), $rparam))) {
        print_error('error_unspecified', 'tsblog', 'RC3');
    }

    // Redirect
    if ($isblog) {
        if ($tsblog->global) {
            redirect('view.php?user=' . $USER->id);
        } else {
            redirect('view.php?id=' . $cm->id);
        }
    } else {
        redirect('viewpost.php?post=' . $postid);
    }
}

// This is the confirm screen. Do navigation first...
$tsbloginstance = $DB->get_record('tsblog_instances', array('tsblogid'=>$tsblog->id, 'userid'=>$USER->id));
tsblog_build_navigation($tsblog, $tsbloginstance, $USER);
if (!$isblog) {
    $post = $DB->get_record('tsblog_posts', array('id'=>$postid));
    tsblog_get_post_extranav($post);
}

$PAGE->navbar->add(get_string('moderated_restrictpage', 'tsblog'));
$PAGE->set_title(format_string($tsblog->name));
$PAGE->set_heading(format_string($course->fullname));
echo $OUTPUT->header();


if ($isblog) {
    if ($tsblog->global) {
        $nourl = 'view.php';
        $noparams = array('user' => $USER->id);
    } else {
        $nourl = 'view.php';
        $noparams = array('id' => $cm->id);
    }
    $yesparams = array('blog' => $blogid);
} else {
    $nourl ='viewpost.php';
    $noparams = array('post' => $postid);
    $yesparams = array('post' => $postid);
}
$yesparams['sesskey'] = sesskey();

// Display the query
echo $OUTPUT->confirm(get_string('moderated_restrict' . ($isblog ? 'blog' : 'post') . '_info',
            'tsblog'),
                     new moodle_url('/mod/tsblog/restrictcomments.php', $yesparams),
                     new moodle_url('mod/tsblog/'.$nourl, $noparams));


// Page footer
echo $OUTPUT->footer();
