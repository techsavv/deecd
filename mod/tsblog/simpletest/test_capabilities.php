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
 * Unit tests for (some of) mod/tsblog participation features
 *
 * @package mod
 * @subpackage tsblog
 * @copyright 2011 The Open University
 * @author Stacey Walker <stacey@catalyst-eu.net>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot . '/mod/tsblog/locallib.php');

class tsblog_capabilities_test extends UnitTestCaseUsingDatabase {
    public static $includecoverage = array('mod/tsblog/locallib.php');
    public $tsblog_tables = array('lib' => array(
                                      'role',
                                      'context',
                                      'capabilities',
                                      'role_capabilities',
                                      'user',
                                      'role_assignments',
                                      'course_categories',
                                      'course_sections',
                                      'course',
                                      'files',
                                      'modules',
                                      'course_modules',
                                      'user_enrolments',
                                      'enrol',
                                      'groups',
                                      'groups_members',
                                      'scale',
                                      'grade_grades',
                                      'grade_categories',
                                      'grade_settings',
                                      'grade_items',
                                      'grade_grades_history',
                                      'log'
                                      ),
                                  'mod/tsblog' => array(
                                      'tsblog',
                                      'tsblog_instances',
                                      'tsblog_posts',
                                      'tsblog_edits',
                                      'tsblog_comments',
                                      'tsblog_tags',
                                      'tsblog_taginstances',
                                      'tsblog_links')
                             );

    public function test_can_view_participation() {
        global $CFG, $USER;

        foreach ($this->tsblog_tables as $dir => $tables) {
            $this->create_test_tables($tables, $dir); // Create tables
        }

        accesslib_clear_all_caches_for_unit_testing();
        $this->switch_to_test_db();
        $this->switch_to_test_cfg();

        $course = new stdClass();
        $course->category = 0;
        $course->id = $this->testdb->insert_record('course', $course);
        $syscontext = get_system_context(false);

        $adminrole  = create_role(get_string('admins'), 'admin',
            get_string('adminsdescription'), 'admin');
        $teacherrole = create_role(get_string('defaultcourseteacher'), 'editingteacher',
            get_string('defaultcourseteacherdescription'), 'editingteacher');
        $studentrole = create_role(get_string('defaultcoursestudent'), 'student',
            get_string('defaultcoursestudentdescription'), 'student');
        $guestrole = create_role(get_string('guest'), 'guest',
            get_string('guestdescription'), 'guest');
        $userrole = create_role(get_string('authenticateduser'), 'user',
            get_string('authenticateduserdescription'), 'user');

        update_capabilities('moodle');
        update_capabilities('mod_tsblog');

        // Now make some test users.
        $users = $this->load_test_data('user',
            array('username', 'confirmed', 'deleted'),
            array(
                'user'      => array('user',    1, 0),
                'teacher'   => array('teacher', 1, 0),
                'student'   => array('student', 1, 0),
            )
        );

        $adminrole   = $this->testdb->get_record('role', array('shortname' => 'admin'));
        $teacherrole = $this->testdb->get_record('role', array('shortname' => 'editingteacher'));
        $studentrole = $this->testdb->get_record('role', array('shortname' => 'student'));
        $guestrole   = $this->testdb->get_record('role', array('shortname' => 'guest'));
        $userrole    = $this->testdb->get_record('role', array('shortname' => 'user'));

        // set some CFG values to get to the real crux of has_capability check
        $CFG->siteguest = $guestrole->id;
        $CFG->rolesactive = 1;

        $tsblog = $this->get_new_tsblog_whole_course($course->id);
        $this->load_course_module($course, $tsblog);
        $cm = get_coursemodule_from_instance('tsblog', $tsblog->id, $course->id);
        $context = get_context_instance(CONTEXT_MODULE, $cm->id);

        // And some role assignments.
        $ras = $this->load_test_data('role_assignments',
            array('userid', 'roleid', 'contextid'),
            array(
                'user'   => array($users['user']->id,    $userrole->id,   $context->id),
                'teacher' => array($users['teacher']->id, $teacherrole->id, $context->id),
                'student' => array($users['student']->id, $studentrole->id, $context->id),
            )
        );

        // Now add some role overrides.
        $rcs = $this->load_test_data('role_capabilities',
            array('capability',                       'roleid',         'contextid',  'permission'),
            array(
                array('mod/tsblog:viewparticipation', $adminrole->id,   $context->id, CAP_ALLOW),
                array('mod/tsblog:viewindividual',    $adminrole->id,   $context->id, CAP_ALLOW),
                array('mod/tsblog:post',              $adminrole->id,   $context->id, CAP_ALLOW),
                array('mod/tsblog:comment',           $adminrole->id,   $context->id, CAP_ALLOW),
                array('mod/tsblog:viewparticipation', $teacherrole->id, $context->id, CAP_ALLOW),
                array('mod/tsblog:post',              $teacherrole->id, $context->id, CAP_ALLOW),
                array('mod/tsblog:comment',           $teacherrole->id, $context->id, CAP_ALLOW),
                array('mod/tsblog:post',              $studentrole->id, $context->id, CAP_ALLOW),
                array('mod/tsblog:comment',           $studentrole->id, $context->id, CAP_ALLOW),
            )
        );

        // initially our general has no capabilities
        $this->switch_global_user_id($users['user']->id);
        accesslib_clear_all_caches_for_unit_testing();
        $canview = tsblog_can_view_participation($course, $tsblog, $cm, 0);
        $this->assertEqual($canview, TS_BLOG_NO_PARTICIPATION);

        // siteadmin user must have TS_BLOG_USER_PARTICIPATION
        // so we add our general user as a siteadmin
        $CFG->siteadmins = $users['user']->id;
        $canview = tsblog_can_view_participation($course, $tsblog, $cm, 0);
        $this->assertEqual($canview, TS_BLOG_USER_PARTICIPATION);

        $this->revert_global_user_id();
        $this->switch_global_user_id($users['teacher']->id);
        accesslib_clear_all_caches_for_unit_testing();
        $canview = tsblog_can_view_participation($course, $tsblog, $cm, 0);
        $this->assertEqual($canview, TS_BLOG_USER_PARTICIPATION);

        $this->revert_global_user_id();
        $this->switch_global_user_id($users['student']->id);
        accesslib_clear_all_caches_for_unit_testing();
        $canview = tsblog_can_view_participation($course, $tsblog, $cm, 0);
        $this->assertEqual($canview, TS_BLOG_MY_PARTICIPATION);

        // test some group membership
        $group1 = new StdClass();
        $group1->name = 'G1';
        $group1->courseid = $course->id;
        $group1->id = $this->testdb->insert_record('groups', $group1);

        $group2 = new StdClass();
        $group2->name = 'G2';
        $group2->courseid = $course->id;
        $group2->id = $this->testdb->insert_record('groups', $group2);

        // separate groups, and NOT member
        // student only sees own participation
        $cm->groupmode = SEPARATEGROUPS;
        $this->testdb->update_record('course_modules', $cm);
        $canview = tsblog_can_view_participation($course, $tsblog, $cm, $group1->id);
        $this->assertEqual($canview, TS_BLOG_NO_PARTICIPATION);

        // separate groups, and IS member
        // student only sees own participation
        $gms = $this->load_test_data('groups_members',
            array('userid', 'groupid'),
            array(
                array($users['student']->id, $group1->id),
            )
        );
        $canview = tsblog_can_view_participation($course, $tsblog, $cm, $group1->id);
        $this->assertEqual($canview, TS_BLOG_MY_PARTICIPATION);

        // visible groups and NOT member
        // student only sees own participation
        $cm->groupmode = VISIBLEGROUPS;
        $this->testdb->update_record('course_modules', $cm);
        $canview = tsblog_can_view_participation($course, $tsblog, $cm, $group2->id);
        $this->assertEqual($canview, TS_BLOG_MY_PARTICIPATION);

        // Teacher has accessallgroups can see groups
        $this->revert_global_user_id();
        $this->switch_global_user_id($users['teacher']->id);
        accesslib_clear_all_caches_for_unit_testing();
        $canview = tsblog_can_view_participation($course, $tsblog, $cm, $group2->id);
        $this->assertEqual($canview, TS_BLOG_USER_PARTICIPATION);

        $cm->groupmode = SEPARATEGROUPS;
        $this->testdb->update_record('course_modules', $cm);
        accesslib_clear_all_caches_for_unit_testing();
        $canview = tsblog_can_view_participation($course, $tsblog, $cm, $group2->id);
        $this->assertEqual($canview, TS_BLOG_USER_PARTICIPATION);

        // reset global USER
        $this->revert_global_user_id();
    }

    public function load_course_module($course, $tsblog) {
        $module = new StdClass;
        $module->name = 'tsblog';
        $module->visible = 1;
        $module->version = '2011110800';
        $module->id = $this->testdb->insert_record('modules', $module);

        $cm = new StdClass;
        $cm->instance = $tsblog->id;
        $cm->course = $course->id;
        $cm->module = $module->id;
        $cm->visible = 1;
        $cm->groupmode = 0;
        $cm->id = $this->testdb->insert_record('course_modules', $cm);
    }

    /* Returns a whole course blog */
    public function get_new_tsblog_whole_course($courseid) {
        $tsblog = new StdClass();
        $tsblog->course = $courseid;
        $tsblog->name = 'Whole Course';
        $tsblog->intro = '';
        $tsblog->introformat = FORMAT_HTML;
        $tsblog->global = 0;
        $tsblog->views = 0;
        $tsblog->allowcomments = 0;
        $tsblog->maxvisibility = 100;
        $tsblog->individual = 0;
        $tsblog->grade = 0;
        $tsblog->id = $this->testdb->insert_record('tsblog', $tsblog);
        return $tsblog;
    }
}
