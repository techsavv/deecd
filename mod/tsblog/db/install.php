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
 * Creates personal blog instance (on site front page) after install
 *
 * @package    mod
 * @subpackage tsblog
 * @copyright  2013 The open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_tsblog_install() {
    global $DB, $CFG;

    require_once($CFG->dirroot . '/course/lib.php');

    // Setup the global blog.
    $tsblog = new stdClass;
    $tsblog->course = SITEID;
    $tsblog->name = 'Personal Blogs';
    $tsblog->intro = '';
    $tsblog->introformat = FORMAT_HTML;
    $tsblog->accesstoken = md5(uniqid(rand(), true));
    $tsblog->maxvisibility = 300;// TS_BLOG_VISIBILITY_PUBLIC.
    $tsblog->global = 1;
    $tsblog->allowcomments = 2;// TS_BLOG_COMMENTS_ALLOWPUBLIC.

    if (!$tsblog->id = $DB->insert_record('tsblog', $tsblog)) {
        return false;
    }

    $mod = new stdClass;
    $mod->course = SITEID;
    $mod->module = $DB->get_field('modules', 'id', array('name'=>'tsblog'));
    $mod->instance = $tsblog->id;
    $mod->visible = 1;
    $mod->visibleold = 0;
    $mod->section = 1;

    if (!$cm = add_course_module($mod)) {
        return true;
    }
    set_config('tsblogsetup', null);

    return true;
}
