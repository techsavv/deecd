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
 * Called via ajax when updating blog stats.
 * cmid (id param) must be sent unless a personal blog page.
 *
 * @package    mod
 * @subpackage tsblog
 * @copyright  2013 The open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
header('Content-Type: application/json');
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/mod/tsblog/locallib.php');

require_sesskey();
$type = required_param('type', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);

if ($id) {
    // Load efficiently (and with full $cm data) using get_fast_modinfo.
    $course = $DB->get_record_select('course',
            'id = (SELECT course FROM {course_modules} WHERE id = ?)', array($id),
            '*', MUST_EXIST);

    $modinfo = get_fast_modinfo($course);
    $cm = $modinfo->get_cm($id);
    if ($cm->modname !== 'tsblog') {
        print_error('invalidcoursemodule');
    }

    if (!$tsblog = $DB->get_record('tsblog', array('id' => $cm->instance))) {
        print_error('invalidcoursemodule');
    }
} else {
    // Global personal blog.
    if (!$tsblog = $DB->get_record('tsblog', array('global' => 1))) {
        print_error('personalblognotsetup', 'tsblog');
    }

    if (!$cm = get_coursemodule_from_instance('tsblog', $tsblog->id)) {
        print_error('invalidcoursemodule');
    }
}

tsblog_check_view_permissions($tsblog, context_module::instance($cm->id), $cm);

$func = "tsblog_stats_output_$type";

if (function_exists($func)) {
    echo json_encode($func($tsblog, $cm, null, true));
}
