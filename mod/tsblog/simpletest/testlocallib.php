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
// Script used to approve moderated comments. This can be called in two ways;
// from email (which is a GET request) and from the web (which is POST).

/**
 * Unit tests for (some of) mod/tsblog/locallib.php.
 *
 * @author dan@danmarsden.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package tsblog
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->dirroot . '/mod/tsblog/locallib.php');

class tsblog_locallib_test extends UnitTestCaseUsingDatabase {
    public static $includecoverage = array('mod/tsblog/locallib.php');
    public $tsblog_tables = array('lib' => array(
                                      'course_categories',
                                      'course_sections',
                                      'course',
                                      'files',
                                      'modules',
                                      'context',
                                      'course_modules',
                                      'user',
                                      'capabilities',
                                      'role_assignments',
                                      'role_capabilities',
                                      'grade_items',
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
    public $courseid = 1;
    public $course = array();
    public $userid;
    public $modules = array();
    public $usercount = 0;


     /**
      * Create temporary test tables and entries in the database for these tests.
      * These tests have to work on a brand new site.
      */
    public function setUp() {
        global $CFG;

        parent::setup();

        $this->switch_to_test_db(); // All operations until end of test method will happen in test DB

        if (tsblog_search_installed()) {
            $this->tsblog_tables['local/ousearch'] = array(
                'local_ousearch_documents',
                'local_ousearch_words',
                'local_ousearch_occurrences');
        }

        foreach ($this->tsblog_tables as $dir => $tables) {
            $this->create_test_tables($tables, $dir); // Create tables
            foreach ($tables as $table) { // Fill them if load_xxx method is available
                $function = "load_$table";
                if (method_exists($this, $function)) {
                    $this->$function();
                }
            }
        }

    }

    public function tearDown() {
        parent::tearDown(); // All the test tables created in setUp will be dropped by this
    }

    public function load_user() {
        $user = new stdClass();
        $user->username = 'testuser';
        $user->firstname = 'Test';
        $user->lastname = 'User';
        $this->userid = $this->testdb->insert_record('user', $user);
    }

    public function load_course_categories() {
        $cat = new stdClass();
        $cat->name = 'misc';
        $cat->depth = 1;
        $cat->path = '/1';
        $this->testdb->insert_record('course_categories', $cat);
    }

    /**
     * Load module entries in modules table
     */
    public function load_modules() {
        $module = new stdClass();
        $module->name = 'subpage';
        $module->id = $this->testdb->insert_record('modules', $module);
        $this->modules[] = $module;
    }

    public function load_capabilities() {
        $cap = new stdClass();
        $cap->name = 'mod/tsblog:view';
        $cap->id = $this->testdb->insert_record('capabilities', $cap);
        $this->capabilities[] = $cap;
    }

    /*

     Unit tests cover:
         * Adding a blog
         * Deleting a blog
         * Adding posts
         * Adding comments
         * Getting a single post
         * Getting a list of posts

     Unit tests do NOT cover:
         * Deleting a Post - there is no back end function for this, the code is inline

    */

    // tests for adding and getting blog
    public function test_tsblog_add_blog() {

        $course = $this->get_new_course();

        // test adding
        $tsblog = new stdClass();
        $tsblog->course = $course->id;
        $tsblog->name = 'Test';
        $tsblog->intro = '';
        $tsblog->introformat = FORMAT_HTML;
        $tsblog->global = 0;
        $tsblog->views = 0;
        $tsblog->grade = 0;
        $tsblog->id = tsblog_add_instance($tsblog);
        $this->assertIsA($tsblog->id, 'integer');

        // add the course module records
        $coursesection = $this->get_new_course_section($course->id);
        $cm = $this->get_new_course_module($course->id, $tsblog->id, $coursesection->id);
        $tsblog->instance = $cm->instance;

        // test updating
        $tsblog->name = 'Test Update';
        $this->assertTrue(tsblog_update_instance($tsblog));
    }

    public function test_tsblog_add_post() {

        // whole course
        $course        = $this->get_new_course();
        $coursesection = $this->get_new_course_section($course->id);
        $tsblog        = $this->get_new_tsblog_whole_course($course->id);
        $cm            = $this->get_new_course_module($course->id, $tsblog->id, $coursesection->id);

        // whole course - basic post
        $post = $this->get_post_hash($tsblog->id);
        $postid = tsblog_add_post($post, $cm, $tsblog, $course);
        $this->assertIsA($postid, 'integer');

        // personal blog
        $course = $this->get_new_course();
        $coursesection = $this->get_new_course_section($course->id);
        $tsblog = $this->get_new_tsblog_personal($course->id);
        $cm     = $this->get_new_course_module($course->id, $tsblog->id, $coursesection->id);

        // personal - basic post
        $post = $this->get_post_hash($tsblog->id);
        $postid = tsblog_add_post($post, $cm, $tsblog, $course);
        $this->assertIsA($postid, 'integer');

    }

    /* test_tsblog_add_comment */
    public function test_tsblog_add_comment() {

        // personal blog
        $course = $this->get_new_course();
        $coursesection = $this->get_new_course_section($course->id);
        $tsblog = $this->get_new_tsblog_personal($course->id);
        $cm     = $this->get_new_course_module($course->id, $tsblog->id, $coursesection->id);

        $post = $this->get_post_hash($tsblog->id);
        $postid = tsblog_add_post($post, $cm, $tsblog, $course);

        $comment = new stdClass();
        $comment->title = 'Test Comment';
        $comment->message = 'Message for test comment';
        $comment->authorname = 'Tester';
        $comment->postid = $postid;

        $commentid = tsblog_add_comment($course, $cm, $tsblog, $comment);
        $this->assertIsA($commentid, 'integer');

        // whole course
        $tsblog = $this->get_new_tsblog_whole_course($course->id);
        $cm     = $this->get_new_course_module($course->id, $tsblog->id, $coursesection->id);

        $post = $this->get_post_hash($tsblog->id);
        $postid = tsblog_add_post($post, $cm, $tsblog, $course);

        // only reset what we need to
        $comment->postid = $postid;

        $commentid = tsblog_add_comment($course, $cm, $tsblog, $comment);
        $this->assertIsA($commentid, 'integer');
    }

    // edit posts

    /*
     Test getting a single post
    */
    public function test_tsblog_add_and_get_post() {

        $course = $this->get_new_course();
        $coursesection = $this->get_new_course_section($course->id);
        $tsblog = $this->get_new_tsblog_personal($course->id);
        $cm     = $this->get_new_course_module($course->id, $tsblog->id, $coursesection->id);

        // first make sure we have a post to use
        $post_hash = $this->get_post_hash($tsblog->id);

        // set some custom things to check
        $title_check   = "test_tsblog_get_post";
        $message_check = "test_tsblog_get_post";
        $post_hash->title   = $title_check;
        $post_hash->message['text'] = $message_check;

        // create the post - assumes tsblog_add_post is working
        $postid = tsblog_add_post($post_hash, $cm, $tsblog,      $course);

        // get the actual post - what we're really testing
        $post = tsblog_get_post($postid);

        // do some basic checks - does it match our test post created above?
        $this->assertIsA($post, "stdClass");
        $this->assertEqual($post->title, $title_check);
        $this->assertEqual($post->message, $message_check);
    }

    /*
     Test getting mulitple posts
    */
    public function test_tsblog_add_and_get_posts() {   // disabled this as it's calling has_capability, which is failing, need to figure out how to implement that

        $course = $this->get_new_course();
        $coursesection = $this->get_new_course_section($course->id);
        $tsblog = $this->get_new_tsblog_whole_course($course->id);
        $cm     = $this->get_new_course_module($course->id, $tsblog->id, $coursesection->id);

        $postcount = 3; // number of posts to test

        $title_check   = "test_tsblog_get_posts";

        // first make sure we have some posts to use
        $post_hashes = array();
        for ($i = 1; $i <= $postcount; $i++) {
            $post_hashes[$i] = $this->get_post_hash($tsblog->id);
            $post_hashes[$i]->title   = $title_check . '_' . $i;
        }

        // create the posts - assumes tsblog_add_post is working
        $postids = array();
        foreach ($post_hashes as $post_hash) {
            $postids[] = tsblog_add_post($post_hash, $cm, $tsblog, $course);
        }

        // get a list of the posts
        $context      = get_context_instance(CONTEXT_MODULE, $cm->instance);
        $currentgroup = tsblog_get_activity_group($cm, true);

        list($posts, $recordcount) = tsblog_get_posts($tsblog, $context, 0, $cm, $currentgroup);

        // same name of records returned that were added?
        $this->assertEqual($recordcount, $postcount);

        // first post returned should match the last one added
        $this->assertEqual($posts[$postcount]->id,  $postcount);
        $this->assertEqual($posts[$postcount]->title,  $title_check . '_' . $postcount);

    }

    /* test_tsblog_get_last_modified */
    public function test_tsblog_get_last_modified() {

        $course = $this->get_new_course();
        $coursesection = $this->get_new_course_section($course->id);
        $tsblog = $this->get_new_tsblog_whole_course($course->id);
        $cm     = $this->get_new_course_module($course->id, $tsblog->id, $coursesection->id);

        $post = $this->get_post_hash($tsblog->id);
        $postid = tsblog_add_post($post, $cm, $tsblog, $course);

        $lastmodified = tsblog_get_last_modified($cm, $course, $this->userid);
        $this->assertNotNull($lastmodified, 'integer');

    }

    /*
     These functions require us to create database entries and/or grab objects to make it possible to test the
     many permuations required for TS Blogs.

    */

    public function get_new_user() {

        $this->usercount++;

        $user = new stdClass();
        $user->username = 'testuser' . $this->usercount;
        $user->firstname = 'Test';
        $user->lastname = 'User';
        $user->id = $this->testdb->insert_record('user', $user);
        return $user;
    }

    public function get_new_course() {
        $course = new stdClass();
        $course->category = 1;
        $course->fullname = 'Anonymous test course';
        $course->shortname = 'ANON';
        $course->summary = '';
        $course->modinfo = null;
        $course->id = $this->testdb->insert_record('course', $course);
        return $course;
    }

    public function get_new_course_section($courseid, $sectionid=1) {
        $section = new stdClass();
        $section->course = $courseid;
        $section->section = $sectionid;
        $section->name = 'Test Section';
        $section->id = $this->testdb->insert_record('course_sections', $section);
        return $section;
    }

    public function get_new_course_module($courseid, $subpageid, $section, $groupmode=0) {
        $cm = new stdClass();
        $cm->course = $courseid;
        $cm->module = $this->modules[0]->id;
        $cm->instance = $subpageid;
        $cm->section = $section;
        $cm->groupmode = $groupmode;
        $cm->groupingid = 0;
        $cm->id = $this->testdb->insert_record('course_modules', $cm);
        return $cm;
    }

    /* Returns a global AKA personal blog */
    public function get_new_tsblog_personal($courseid) {
        $tsblog = new stdClass();
        $tsblog->course = $courseid;
        $tsblog->name = 'Personal Blog';
        $tsblog->intro = '';
        $tsblog->introformat = FORMAT_HTML;
        $tsblog->global = 1;
        $tsblog->views = 0;
        $tsblog->allowcomments = 0;
        $tsblog->maxvisibility = 100;
        $tsblog->id = $this->testdb->insert_record('tsblog', $tsblog);
        return $tsblog;
    }

    /* Returns a whole course blog */
    public function get_new_tsblog_whole_course($courseid) {
        $tsblog = new stdClass();
        $tsblog->course = $courseid;
        $tsblog->name = 'Whole Course';
        $tsblog->intro = '';
        $tsblog->introformat = FORMAT_HTML;
        $tsblog->global = 0;
        $tsblog->views = 0;
        $tsblog->allowcomments = 0;
        $tsblog->maxvisibility = 100;
        $tsblog->grade = 0;
        $tsblog->id = $this->testdb->insert_record('tsblog', $tsblog);
        return $tsblog;
    }

    public function get_post_hash($tsblogid) {
        $post = new stdClass();
        $post->tsblogid = $tsblogid;
        $post->userid = $this->userid;
        $post->groupid = 0;
        $post->title = "testpost";
        $post->message['itemid'] = 1;
        $post->message['text'] = "<p>newpost</p>";
        $post->allowcomments = 1;
        $post->visibility = 100;
        $post->attachments = '';
        return $post;
    }
}
