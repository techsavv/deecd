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

$linkid  = required_param('link', PARAM_INT);          // Link ID to delete
$confirm = optional_param('confirm', 0, PARAM_INT);    // Confirm that it is ok to delete link

if (!$link = $DB->get_record('tsblog_links', array('id'=> $linkid))) {
    print_error('invalidlink', 'tsblog');
}

if (!$cm = get_coursemodule_from_instance('tsblog', $link->tsblogid)) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record("course", array("id"=> $cm->course))) {
    print_error('coursemisconf');
}

if (!$tsblog = $DB->get_record("tsblog", array("id"=> $cm->instance))) {
    print_error('invalidcoursemodule');
}

$url = new moodle_url('/mod/tsblog/deletelink.php', array('link'=>$linkid, 'confirm'=>$confirm));
$PAGE->set_url($url);

// Check security.
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
tsblog_check_view_permissions($tsblog, $context, $cm);

$tsbloginstance = $link->tsbloginstancesid ? $DB->get_record('tsblog_instances', array('id'=>$link->tsbloginstancesid)) : null;
tsblog_require_userblog_permission('mod/tsblog:managelinks', $tsblog, $tsbloginstance, $context);

if ($tsblog->global) {
    $blogtype = 'personal';
    $tsbloguser = $USER;
} else {
    $blogtype = 'course';
}

$viewurl = new moodle_url('/mod/tsblog/view.php', array('id'=>$cm->id));

if (!empty($linkid) && !empty($confirm)) {
    tsblog_delete_link($tsblog, $link);
    redirect($viewurl);
    exit;
}

// Get Strings.
$strtsblogs  = get_string('modulenameplural', 'tsblog');
$strtsblog   = get_string('modulename', 'tsblog');

// Print the header.
if ($blogtype == 'personal') {
        $PAGE->navbar->add(fullname($tsbloguser), new moodle_url('/user/view.php', array('id'=>$tsbloguser->id)));
        $PAGE->navbar->add(format_string($tsblog->name));
}
$PAGE->set_title(format_string($tsblog->name));
$PAGE->set_heading(format_string($course->fullname));
echo $OUTPUT->header();
echo $OUTPUT->confirm(get_string('confirmdeletelink', 'tsblog'),
                 new moodle_url('/mod/tsblog/deletelink.php', array('link'=>$linkid, 'confirm'=>'1')),
                 $viewurl);