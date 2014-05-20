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
 * Search results page.
 *
 * @copyright &copy; 2007 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package tsblog
 *//** */
require_once('../../config.php');
require_once('locallib.php');
require_once($CFG->dirroot.'/local/ousearch/searchlib.php');

$id     = optional_param('id', 0, PARAM_INT);       // Course Module ID
$user   = optional_param('user', 0, PARAM_INT);     // User ID
$querytext = required_param('query', PARAM_RAW);
$querytexthtml = htmlspecialchars($querytext);

if ($id) {
    if (!$cm = get_coursemodule_from_id('tsblog', $id)) {
        print_error('invalidcoursemodule');
    }

    if (!$course = $DB->get_record("course", array("id"=>$cm->course))) {
        print_error('coursemisconf');
    }

    if (!$tsblog = $DB->get_record("tsblog", array("id"=>$cm->instance))) {
        print_error('invalidcoursemodule');
    }
    $tsbloguser = (object) array('id' => null);
    $tsbloginstance = null;
    $tsbloginstanceid = null;

} else if ($user) {
    if (!$tsbloguser = $DB->get_record('user', array('id'=>$user))) {
        print_error('invaliduserid');
    }
    if (!list($tsblog, $tsbloginstance) = tsblog_get_personal_blog($tsbloguser->id)) {
        print_error('invalidcoursemodule');
    }
    if (!$cm = get_coursemodule_from_instance('tsblog', $tsblog->id)) {
        print_error('invalidcoursemodule');
    }
    if (!$course = $DB->get_record("course", array("id"=>$tsblog->course))) {
        print_error('coursemisconf');
    }
    $tsbloginstanceid = $tsbloginstance->id;
} else {
    print_error('missingrequiredfield');
}

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
$PAGE->set_context($context);

$url = new moodle_url('/mod/tsblog/search.php', array('id'=>$id, 'user'=>$user, 'query'=>$querytext));
$PAGE->set_url($url);
$PAGE->set_cm($cm);
$PAGE->set_title(format_string($tsblog->name));
tsblog_check_view_permissions($tsblog, $context, $cm);

if ($tsblog->global) {
    // Check this user is allowed to view the user's blog
    if ($tsblog->maxvisibility != TS_BLOG_VISIBILITY_PUBLIC && isset($tsbloguser)) {
        $usercontext = get_context_instance(CONTEXT_USER, $tsbloguser->id);
        require_capability('mod/tsblog:view', $usercontext);
    }
    $returnurl = $CFG->wwwroot . '/mod/tsblog/view.php?user='.$user;
    $mreturnurl = new moodle_url('/mod/tsblog/view.php', array('user'=>$user));
} else {
    $returnurl = $CFG->wwwroot . '/mod/tsblog/view.php?id='.$id;
    $mreturnurl = new moodle_url('/mod/tsblog/view.php', array('id'=>$id));
}

// Set up groups
$currentgroup = tsblog_get_activity_group($cm, true);
$groupmode = tsblog_get_activity_groupmode($cm, $course);
// Note I am not sure this check is necessary, maybe it is handled by
// tsblog_get_activity_group? Or maybe more checks are needed? Not sure.
if ($currentgroup===0 && $groupmode==SEPARATEGROUPS) {
    require_capability('moodle/site:accessallgroups', $context);
}

// Print the header
$strtsblog      = get_string('modulename', 'tsblog');
$strblogsearch = get_string('searchthisblog', 'tsblog', tsblog_get_displayname($tsblog));
$strblogssearch  = get_string('searchblogs', 'tsblog');


if ($tsblog->global) {
    if (!is_null($tsbloginstance)) {
        $name = $tsbloginstance->name;
        $buttontext = tsblog_get_search_form('user', $tsbloguser->id, $strblogsearch,
                $querytexthtml);
    } else {
        $buttontext = tsblog_get_search_form('id', $cm->id, $strblogssearch,
                $querytexthtml);
    }

    if (isset($name)) {
        $PAGE->navbar->add(fullname($tsbloguser), new moodle_url('/user/view.php', array('id'=>$tsbloguser->id)));
        $PAGE->navbar->add(format_string($tsbloginstance->name), $mreturnurl);
    } else {
        $PAGE->navbar->add(format_string($tsblog->name), new moodle_url('/mod/tsblog/allposts.php'));
    }

} else {
    $name = $tsblog->name;

    $buttontext = tsblog_get_search_form('id', $cm->id, $strblogsearch, $querytexthtml);
}

$PAGE->navbar->add(get_string('searchfor', 'local_ousearch', $querytext));
$PAGE->set_button($buttontext);

echo $OUTPUT->header();

// Print Groups
groups_print_activity_menu($cm, $returnurl);

global $modulecontext, $personalblog;
$modulecontext=$context;
$personalblog=$tsblog->global ? true : false;

// FINALLY do the actual query
$query=new local_ousearch_search($querytext);
$query->set_coursemodule($cm);
if ($tsblog->global && isset($tsbloguser)) {
    $query->set_user_id($tsbloguser->id);
}
if ($groupmode && $currentgroup) {
    $query->set_group_id($currentgroup);
}
$query->set_filter('visibility_filter');

$searchurl = 'search.php?'.(empty($id) ? 'user='.$tsbloguser->id : 'id='.$cm->id);

$foundsomething=$query->display_results($searchurl);

if (!$foundsomething) {
    add_to_log($COURSE->id, 'tsblog', 'view searchfailure',
        $searchurl.'&query='.urlencode($querytext));
}
echo $foundsomething;

// Add link to search the rest of this website if service available.
if (!empty($CFG->block_resources_search_baseurl)) {
    $params = array('course' => $course->id, 'query' => $querytext);
    $restofwebsiteurl = new moodle_url('/blocks/resources_search/search.php', $params);
    $strrestofwebsite = get_string('restofwebsite', 'local_ousearch');
    $altlink = html_writer::start_tag('div', array('class' => 'advanced-search-link'));
    $altlink .= html_writer::link($restofwebsiteurl, $strrestofwebsite);
    $altlink .= html_writer::end_tag('div');
    print $altlink;
}

// Footer
echo $OUTPUT->footer();

/**
 * Function filters search results to exclude ones that don't meet the
 * visibility criterion.
 *
 * @param object $result Search result data
 */
function visibility_filter(&$result) {
    global $USER, $modulecontext, $personalblog;
    return tsblog_can_view_post($result->data, $USER, $modulecontext, $personalblog);
}
