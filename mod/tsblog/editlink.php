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
 * This page allows a user to add and edit related blog links
 *
 * @author Matt Clarkson <mattc@catalyst.net.nz>
 * @package tsblog
 */

require_once("../../config.php");
require_once("locallib.php");
require_once('link_form.php');

$blog = required_param('blog', PARAM_INT);                          // Blog ID
$bloginstancesid = optional_param('bloginstance', 0, PARAM_INT);     // Blog instances ID
$linkid = optional_param('link', 0, PARAM_INT);                     // Comment ID for editing

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
// TODO: If statement didn't look right! CC-Inline control structures not allowed.
if ($linkid) {
    if (!$link = $DB->get_record('tsblog_links', array('id'=>$linkid))) {
        $link = false;
    }
}

$url = new moodle_url('/mod/tsblog/editlink.php', array('blog'=>$blog, 'bloginstance'=>$bloginstancesid, 'link'=>$linkid));
$PAGE->set_url($url);

// Check security.
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
tsblog_check_view_permissions($tsblog, $context, $cm);

if ($linkid) {
    $bloginstancesid=$link->tsbloginstancesid;
}
$tsbloginstance = $bloginstancesid ? $DB->get_record('tsblog_instances', array('id'=>$bloginstancesid)) : null;
    tsblog_require_userblog_permission('mod/tsblog:managelinks', $tsblog, $tsbloginstance, $context);

if ($tsblog->global) {
    $blogtype = 'personal';
    $tsbloguser = $USER;
    $viewurl = 'view.php?user='.$tsbloginstance->userid;
} else {
    $blogtype = 'course';
    $viewurl = 'view.php?id='.$cm->id;
}

// Get strings.
$strtsblogs  = get_string('modulenameplural', 'tsblog');
$strtsblog   = get_string('modulename', 'tsblog');
$straddlink  = get_string('addlink', 'tsblog');
$streditlink = get_string('editlink', 'tsblog');

$mform = new mod_tsblog_link_form('editlink.php', array('edit' => !empty($linkid)));

if ($mform->is_cancelled()) {
    redirect($viewurl);
    exit;
}

if (!$frmlink = $mform->get_data()) {

    if (!isset($link)) {
        $link = new stdClass;
        $link->general = $straddlink;
    } else {
        $link->link = $link->id;
    }

    $link->blog = $blog;
    $link->bloginstance = $bloginstancesid;

    $mform->set_data($link);


    // Print the header

    if ($blogtype == 'personal') {
        $PAGE->navbar->add(fullname($tsbloguser), new moodle_url('/user/view.php', array('id'=>$tsbloguser->id)));
        $PAGE->navbar->add(format_string($tsblog->name), new moodle_url('/mod/tsblog/view.php', array('blog'=>$blog)));
    } else {
        $PAGE->navbar->add(($linkid ? $streditlink : $straddlink));
    }
    $PAGE->set_title(format_string($tsblog->name));
    $PAGE->set_heading(format_string($course->fullname));
    echo $OUTPUT->header();

    echo '<br />';
    $mform->display();

    echo $OUTPUT->footer();

} else {
    if ($frmlink->link) {
        $frmlink->id = $frmlink->link;
        $frmlink->tsblogid = $tsblog->id;

        if (!tsblog_edit_link($frmlink)) {
            print_error('couldnotaddlink', 'tsblog');
        }

    } else {
        unset($frmlink->id);
        $frmlink->tsblogid = $tsblog->id;
        $frmlink->tsbloginstancesid = $bloginstancesid;

        if (!tsblog_add_link($frmlink)) {
            print_error('couldnotaddlink', 'tsblog');
        }
    }

    redirect($viewurl);
}
