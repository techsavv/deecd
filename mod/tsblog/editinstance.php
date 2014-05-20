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
 * This page allows a user to edit their personal blog
 *
 * @author Matt Clarkson <mattc@catalyst.net.nz>
 * @author Sam Marshall <s.marshall@open.ac.uk>
 * @package tsblog
 */
define('TS_BLOG_EDIT_INSTANCE', true);

require_once('../../config.php');
require_once('locallib.php');
require_once('lib.php');
require_once('mod_form.php');

$bloginstancesid = required_param('instance', PARAM_INT);        // Bloginstance
$postid = optional_param('post', 0, PARAM_INT);   // Post ID for editing

if (!$tsbloginstance = $DB->get_record('tsblog_instances', array('id'=>$bloginstancesid))) {
    print_error('invalidblog', 'tsblog');
}
if (!$tsblog = $DB->get_record("tsblog", array("id"=>$tsbloginstance->tsblogid))) {
    print_error('invalidblog', 'tsblog');
}
if (!$tsblog->global) {
    print_error('invalidblog', 'tsblog');
}
if (!$cm = get_coursemodule_from_instance('tsblog', $tsblog->id)) {
    print_error('invalidcoursemodule');
}
if (!$course = $DB->get_record("course", array("id"=>$tsblog->course))) {
    print_error('invalidcoursemodule');
}

// Check security.
if (!$tsblog->global) {
    print_error('onlyworkspersonal', 'tsblog');
}
$url = new moodle_url('/mod/tsblog/editinstance.php', array('instance'=>$bloginstancesid, 'post'=>$postid));
$PAGE->set_url($url);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
tsblog_check_view_permissions($tsblog, $context, $cm);
$tsbloguser = $DB->get_record('user', array('id'=>$tsbloginstance->userid));
$viewurl = 'view.php?user='.$tsbloginstance->userid;

if ($USER->id != $tsbloginstance->userid && !has_capability('mod/tsblog:manageposts', $context)) {
    print_error('accessdenied', 'tsblog');
}

// Get strings.
$strtsblogs     = get_string('modulenameplural', 'tsblog');
$strtsblog      = get_string('modulename', 'tsblog');
$streditpost    = get_string('editpost', 'tsblog');
$strblogoptions = get_string('blogoptions', 'tsblog');

// Set-up groups.
$currentgroup = tsblog_get_activity_group($cm, true);
$groupmode = tsblog_get_activity_groupmode($cm, $course);

$mform = new mod_tsblog_mod_form('editinstance.php', array('maxvisibility' => $tsblog->maxvisibility, 'edit' => !empty($postid)));

if ($mform->is_cancelled()) {
    redirect($viewurl);
    exit;
}

$textfieldoptions = array(
        'maxfiles' => EDITOR_UNLIMITED_FILES,
        'maxbytes' => $CFG->maxbytes,
        'context' => $context,
        );

if (!$frmtsbloginstance = $mform->get_data()) {

    $tsbloginstance->instance = $tsbloginstance->id;
    $tsbloginstance->summaryformat = FORMAT_HTML;
    $tsbloginstance = file_prepare_standard_editor($tsbloginstance, 'summary', $textfieldoptions, $context,
            'mod_tsblog', 'summary', $tsbloginstance->id);
    $mform->set_data($tsbloginstance);

    // Print the header.
    tsblog_build_navigation($tsblog, $tsbloginstance, $tsbloguser);
    $PAGE->navbar->add($strblogoptions);
    $PAGE->set_title(format_string($tsblog->name));
    echo $OUTPUT->header();

    echo '<br />';
    $mform->display();

    echo $OUTPUT->footer();

} else {
    // Handle form submission.
    $frmtsbloginstance->id = $frmtsbloginstance->instance;
    $frmtsbloginstance->summaryformat = FORMAT_HTML;
    $frmtsbloginstance = file_postupdate_standard_editor($frmtsbloginstance, 'summary', $textfieldoptions, $context,
            'mod_tsblog', 'summary', $frmtsbloginstance->id);
    $DB->update_record('tsblog_instances', $frmtsbloginstance);

    redirect($viewurl);
}