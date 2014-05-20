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
 * Page for saving grades for all or one user participation
 *
 * @package mod
 * @subpackage tsblog
 * @copyright 2011 The Open University
 * @author Stacey Walker <stacey@catalyst-eu.net>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/mod/tsblog/locallib.php');

$id         = required_param('id', PARAM_INT); // Course Module ID
$groupid    = optional_param('group', 0, PARAM_INT);
$userid     = optional_param('user', 0, PARAM_INT);

$params = array();
$params['id'] = $id;
$params['group'] = $groupid;
$url = new moodle_url('/mod/tsblog/savegrades.php');
if ($id) {
    $cm = get_coursemodule_from_id('tsblog', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $tsblog = $DB->get_record('tsblog', array('id' => $cm->instance), '*', MUST_EXIST);

    $PAGE->set_cm($cm);
}
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_course_login($course, true, $cm);
require_sesskey();

// participation capability check
$canview = tsblog_can_view_participation($course, $tsblog, $cm, $groupid);
if ($canview != TS_BLOG_USER_PARTICIPATION) {
    print_error('nopermissiontoshow');
}

// grading capability check
if (!tsblog_can_grade($course, $tsblog, $cm, $groupid)) {
    print_error('nopermissiontoshow');
}

$mode = '';
if (!empty($_POST['menu'])) {
    $gradeinfo = $_POST['menu'];
    $oldgrades = tsblog_get_participation($tsblog, $context, $groupid, $cm, $course);
} else if ($userid && !empty($_POST['grade'])) {
    $gradeinfo[$userid] = $_POST['grade'];
    $user = tsblog_get_user_participation($tsblog, $context, $userid, $groupid, $cm, $course);
    $oldgrades = array($userid => $user);
}

// update grades
if (!empty($gradeinfo)) {
    tsblog_update_grades($gradeinfo, $oldgrades, $cm, $tsblog, $course);
}

// redirect
redirect('participation.php?id=' . $id . '&group=' . $groupid);
