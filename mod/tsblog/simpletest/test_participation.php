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
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->dirroot . '/mod/tsblog/locallib.php');
require_once($CFG->libdir.'/gradelib.php');

class tsblog_participation_test extends UnitTestCaseUsingDatabase {
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
                                      'grade_categories_history',
                                      'grade_items_history',
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

    /**
     * Unit tests cover:
     *
     * - tsblog_get_participation($tsblog, $context, $groupid=0, $course,
     *                                  $sort='u.firstname,u.lastname')
     * - tsblog_get_user_participation($tsblog, $context, $userid,
     *                                  $groupid=0, $course)
     * - tsblog_update_grades($newgrades, $oldgrades, $cm, $tsblog, $course)
     *
     */
    public function test_participation() {

        // All operations until end of test method will happen in test DB
        accesslib_clear_all_caches_for_unit_testing();
        $this->switch_to_test_db();

        foreach ($this->tsblog_tables as $dir => $tables) {
            $this->create_test_tables($tables, $dir); // Create tables
        }

        $course = new stdClass();
        $course->category = 0;
        $course->id = $this->testdb->insert_record('course', $course);
        $systemcontext = get_system_context(false);

        $adminrole  = create_role(get_string('admin'), 'admin',
            'admindescription', 'admin');
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
        // usera is a control user; never assigned a role
        $users = $this->load_test_data('user',
            array('username', 'confirmed', 'deleted'),
            array(
                'usera' => array('usera', 1, 0),
                'userb' => array('userb', 1, 0),
                'userc' => array('userc', 1, 0),
            )
        );

        $studentrole = $this->testdb->get_record('role', array('shortname' => 'student'));
        $guestrole = $this->testdb->get_record('role', array('shortname' => 'guest'));

        $tsblog = $this->get_new_tsblog_whole_course($course->id);
        $this->load_course_module($course, $tsblog);
        $cm = get_coursemodule_from_instance('tsblog', $tsblog->id, $course->id);
        $context = get_context_instance(CONTEXT_MODULE, $cm->id);

        // And some role assignments.
        // User A has no role assignment
        $ras = $this->load_test_data('role_assignments',
            array('userid', 'roleid', 'contextid'),
            array(
                'userb' => array($users['userb']->id, $studentrole->id, $context->id),
                'userc' => array($users['userc']->id, $studentrole->id, $context->id),
            )
        );

        // check initial tsblog_get_participation call
        $participation = tsblog_get_participation($tsblog, $context, 0, $cm, $course);
        $this->assertEqual(count($participation), 2);
        foreach ($participation as $participant) {
            // so far no posts or comments
            $this->assertEqual(isset($participant->posts), false);
            $this->assertEqual(isset($participant->comments), false);

            // confirm usernames match
            if ($participant->id == $users['userb']->id) {
                $this->assertEqual($participant->username, 'userb');
            } else if ($participant->id == $users['userc']->id) {
                $this->assertEqual($participant->username, 'userc');
            }
        }

        // set some user posts
        $post = $this->get_post_hash($tsblog->id, $users['usera']->id, '', '<p>A post</p>');
        $postida = tsblog_add_post($post, $cm, $tsblog, $course);
        $this->assertIsA($postida, 'integer');

        $post = $this->get_post_hash($tsblog->id, $users['userb']->id, '', '<p>B post</p>');
        $postidb = tsblog_add_post($post, $cm, $tsblog, $course);
        $this->assertIsA($postidb, 'integer');

        $post = $this->get_post_hash($tsblog->id, $users['userb']->id, '', '<p>B post 2</p>');
        $postidb2 = tsblog_add_post($post, $cm, $tsblog, $course);
        $this->assertIsA($postidb2, 'integer');

        // set some user comments
        $comment = $this->get_comment_hash($postida, $users['userb']->id, '', '<p>B comment</p>');
        $commentidb = tsblog_add_comment($course, $cm, $tsblog, $comment);
        $this->assertIsA($commentidb, 'integer');

        $comment = $this->get_comment_hash($postidb, $users['userc']->id, '', '<p>C comment</p>');
        $commentidc = tsblog_add_comment($course, $cm, $tsblog, $comment);
        $this->assertIsA($commentidc, 'integer');

        $comment = $this->get_comment_hash($postida, $users['userc']->id, '', '<p>C comment 2</p>');
        $commentidc2 = tsblog_add_comment($course, $cm, $tsblog, $comment);
        $this->assertIsA($commentidc2, 'integer');

        // Test again with posts and comments
        $participation = tsblog_get_participation($tsblog, $context, 0, $cm, $course);
        $this->assertEqual(count($participation), 2);
        foreach ($participation as $participant) {
            if ($participant->id == $users['userb']->id) {
                $this->assertEqual(isset($participant->posts), true);
                $this->assertEqual(isset($participant->comments), true);
                $this->assertEqual((int)$participant->posts, 2);
                $this->assertEqual((int)$participant->comments, 1);
            } else if ($participant->id == $users['userc']->id) {
                $this->assertEqual(isset($participant->posts), false);
                $this->assertEqual(isset($participant->comments), true);
                $this->assertEqual((int)$participant->comments, 2);
            }
        }

        // mark a User C comment as deleted
        $comment = $this->testdb->get_record('tsblog_comments', array('id' => $commentidc));
        $comment->timedeleted = time();
        $this->testdb->update_record('tsblog_comments', $comment);
        $userparticipation = tsblog_get_user_participation($tsblog, $context,
            $users['userc']->id, 0, $cm, $course);
        $this->assertEqual(count($userparticipation->comments), 1);

        // setup some groups
        $group1 = new StdClass();
        $group1->name = 'G1';
        $group1->courseid = $course->id;
        $group1->id = $this->testdb->insert_record('groups', $group1);

        $group2 = new StdClass();
        $group2->name = 'G2';
        $group2->courseid = $course->id;
        $group2->id = $this->testdb->insert_record('groups', $group2);
        $gms = $this->load_test_data('groups_members',
            array('userid', 'groupid'),
            array(
                array($users['userb']->id, $group1->id),
                array($users['userc']->id, $group2->id),
            )
        );

        // Change to visible groups and add members to groups
        $cm->groupmode = VISIBLEGROUPS;
        $this->testdb->update_record('course_modules', $cm);

        // test single group participation
        $groupparticipation = tsblog_get_participation($tsblog, $context,
            $group1->id, $cm, $course);
        $this->assertEqual(count($groupparticipation), 1);
        $member = array_shift($groupparticipation);
        $this->assertEqual($member->id, $users['userb']->id);

        // Change one of userB posts to be in G1
        $postb1 = $this->testdb->get_record('tsblog_posts', array('id' => $postidb2));
        $postb1->groupid = $group1->id;
        $this->testdb->update_record('tsblog_posts', $postb1);
        $groupparticipation = tsblog_get_participation($tsblog, $context,
            $group1->id, $cm, $course);
        $userbparticipation = array_shift($groupparticipation);
        $this->assertEqual($userbparticipation->id, $users['userb']->id);
        $this->assertEqual(count($userbparticipation), 1);

        /* get_user_participation function */

        // since one User B blog post was changed to a group post there should only be one post
        // and one comment for B remaining as standalone participation
        $userparticipation = tsblog_get_user_participation($tsblog, $context,
            $users['userb']->id, 0, $cm, $course);
        $this->assertEqual($userparticipation->user->id, $users['userb']->id);
        $this->assertEqual(count($userparticipation->posts), 1);
        $this->assertEqual(count($userparticipation->comments), 1);

        /* Save Grades */

        // set up the initial scale and grade_item
        $scale = new StdClass;
        $scale->courseid = 0;
        $scale->userid = $users['usera']->id;
        $scale->name = '1-10';
        $scale->scale = '1,2,3,4,5,6,7,8,9,10';
        $scale->description = '1-10';
        $scale->descriptionformat = 1;
        $scale->timemodified = time();
        $scale->id = $this->testdb->insert_record('scale', $scale);

        $tsblog->grade = -1;
        $tsblog->instance = $tsblog->id;
        tsblog_update_instance($tsblog);
        $groupparticipation = tsblog_get_participation($tsblog, $context,
            $group1->id, $cm, $course);
        $userbgradeobj = $groupparticipation[$users['userb']->id]->gradeobj;
        $this->assertEqual(count($groupparticipation), 1);
        $this->assertNotNull($userbgradeobj);

        // update grades and check results
        $newgrades = array($users['userb']->id => 5, $users['userc']->id => 10);
        $oldgrades = tsblog_get_participation($tsblog, $context, $group1->id, $cm, $course);
        tsblog_update_grades($newgrades, $oldgrades, $cm, $tsblog, $course);
        $allgrades = tsblog_get_participation($tsblog, $context, 0, $cm, $course);
        $this->assertEqual($allgrades[$users['userb']->id]->gradeobj->grade, 5);

        // userc should NOT have been updated as not in the group
        $this->assertNull($allgrades[$users['userc']->id]->gradeobj->grade);
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

    public function get_post_hash($tsblogid, $userid, $title, $content) {
        $post = new StdClass();
        $post->tsblogid = $tsblogid;
        $post->userid = $userid;
        $post->groupid = 0;
        $post->title = $title;
        $post->message['itemid'] = 1;
        $post->message['text'] = $content;
        $post->allowcomments = 1;
        $post->visibility = 100;
        $post->attachments = '';
        return $post;
    }

    public function get_comment_hash($postid, $userid, $title, $content) {
        $comment = new stdClass();
        $comment->title = $title;
        $comment->message = $content;
        $comment->userid = $userid;
        $comment->postid = $postid;
        return $comment;
    }
}
