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
 * Activity progress reports
 *
 * @package    report
 * @subpackage progress
 * @copyright  2008 Sam Marshall
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->libdir . '/completionlib.php');

define('COMPLETION_REPORT_PAGE', 50);

// Get course
$id = required_param('course',PARAM_INT);
$course = $DB->get_record('course',array('id'=>$id));
if (!$course) {
    print_error('invalidcourseid');
}
$context = context_course::instance($course->id);

// Sort (default lastname, optionally firstname)
$sort = optional_param('sort','',PARAM_ALPHA);
$firstnamesort = $sort == 'firstname';

// CSV format
$format = optional_param('format','',PARAM_ALPHA);
$excel = $format == 'excelcsv';
$csv = $format == 'csv' || $excel;

// Paging
$start   = optional_param('start', 0, PARAM_INT);
$sifirst = optional_param('sifirst', 'all', PARAM_ALPHA);
$silast  = optional_param('silast', 'all', PARAM_ALPHA);
$start   = optional_param('start', 0, PARAM_INT);

// Whether to show extra user identity information
$extrafields = get_extra_user_fields($context);
$leftcols = 1 + count($extrafields);

function csv_quote($value) {
    global $excel;
    if ($excel) {
        return core_text::convert('"'.str_replace('"',"'",$value).'"','UTF-8','UTF-16LE');
    } else {
        return '"'.str_replace('"',"'",$value).'"';
    }
}

$url = new moodle_url('/report/progress/index.php', array('course'=>$id));
if ($sort !== '') {
    $url->param('sort', $sort);
}
if ($format !== '') {
    $url->param('format', $format);
}
if ($start !== 0) {
    $url->param('start', $start);
}
$PAGE->set_url($url);
$PAGE->set_pagelayout('report');

require_login($course);

// Check basic permission
require_capability('report/progress:view',$context);

// Get group mode
$group = groups_get_course_group($course,true); // Supposed to verify group
if ($group===0 && $course->groupmode==SEPARATEGROUPS) {
    require_capability('moodle/site:accessallgroups',$context);
}

// Get data on activities and progress of all users, and give error if we've
// nothing to display (no users or no activities)
$reportsurl = $CFG->wwwroot.'/course/report.php?id='.$course->id;
$completion = new completion_info($course);
$activities = $completion->get_activities();

// Generate where clause
$where = array();
$where_params = array();

if ($sifirst !== 'all') {
    $where[] = $DB->sql_like('u.firstname', ':sifirst', false);
    $where_params['sifirst'] = $sifirst.'%';
}

if ($silast !== 'all') {
    $where[] = $DB->sql_like('u.lastname', ':silast', false);
    $where_params['silast'] = $silast.'%';
}

// Get user match count
$total = $completion->get_num_tracked_users(implode(' AND ', $where), $where_params, $group);

// Total user count
$grandtotal = $completion->get_num_tracked_users('', array(), $group);

// Get user data
$progress = array();

if ($total) {
    $progress = $completion->get_progress_all(
        implode(' AND ', $where),
        $where_params,
        $group,
        $firstnamesort ? 'u.firstname ASC' : 'u.lastname ASC',
        $csv ? 0 : COMPLETION_REPORT_PAGE,
        $csv ? 0 : $start,
        $context
    );
}

if ($csv && $grandtotal && count($activities)>0) { // Only show CSV if there are some users/actvs

    $shortname = format_string($course->shortname, true, array('context' => $context));
    header('Content-Disposition: attachment; filename=progress.'.
        preg_replace('/[^a-z0-9-]/','_',core_text::strtolower(strip_tags($shortname))).'.csv');
    // Unicode byte-order mark for Excel
    if ($excel) {
        header('Content-Type: text/csv; charset=UTF-16LE');
        print chr(0xFF).chr(0xFE);
        $sep="\t".chr(0);
        $line="\n".chr(0);
    } else {
        header('Content-Type: text/csv; charset=UTF-8');
        $sep=",";
        $line="\n";
    }
} else {

    // Navigation and header
    $strreports = get_string("reports");
    $strcompletion = get_string('activitycompletion', 'completion');

    $PAGE->set_title($strcompletion);
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
    $PAGE->requires->js('/report/progress/textrotate.js');
    //$PAGE->requires->js('/lib/jquery/jquery-1.9.1.min.js');
    $PAGE->requires->js_function_call('textrotate_init', null, true);
    $PAGE->requires->js('/report/progress/section.js');

    // Handle groups (if enabled)
    groups_print_course_menu($course,$CFG->wwwroot.'/report/progress/?course='.$course->id);
}

if (count($activities)==0) {
    echo $OUTPUT->container(get_string('err_noactivities', 'completion'), 'errorbox errorboxcontent');
    echo $OUTPUT->footer();
    exit;
}

// If no users in this course what-so-ever
if (!$grandtotal) {
    echo $OUTPUT->container(get_string('err_nousers', 'completion'), 'errorbox errorboxcontent');
    echo $OUTPUT->footer();
    exit;
}

// Build link for paging
$link = $CFG->wwwroot.'/report/progress/?course='.$course->id;
if (strlen($sort)) {
    $link .= '&amp;sort='.$sort;
}
$link .= '&amp;start=';

// Build the the page by Initial bar
$initials = array('first', 'last');
$alphabet = explode(',', get_string('alphabet', 'langconfig'));

$pagingbar = '';
foreach ($initials as $initial) {
    $var = 'si'.$initial;

    $othervar = $initial == 'first' ? 'silast' : 'sifirst';
    $othervar = $$othervar != 'all' ? "&amp;{$othervar}={$$othervar}" : '';

    $pagingbar .= ' <div class="initialbar '.$initial.'initial">';
    $pagingbar .= get_string($initial.'name').':&nbsp;';

    if ($$var == 'all') {
        $pagingbar .= '<strong>'.get_string('all').'</strong> ';
    }
    else {
        $pagingbar .= "<a href=\"{$link}{$othervar}\">".get_string('all').'</a> ';
    }

    foreach ($alphabet as $letter) {
        if ($$var === $letter) {
            $pagingbar .= '<strong>'.$letter.'</strong> ';
        }
        else {
            $pagingbar .= "<a href=\"$link&amp;$var={$letter}{$othervar}\">$letter</a> ";
        }
    }

    $pagingbar .= '</div>';
}

// Do we need a paging bar?
if ($total > COMPLETION_REPORT_PAGE) {

    // Paging bar
    $pagingbar .= '<div class="paging">';
    $pagingbar .= get_string('page').': ';

    $sistrings = array();
    if ($sifirst != 'all') {
        $sistrings[] =  "sifirst={$sifirst}";
    }
    if ($silast != 'all') {
        $sistrings[] =  "silast={$silast}";
    }
    $sistring = !empty($sistrings) ? '&amp;'.implode('&amp;', $sistrings) : '';

    // Display previous link
    if ($start > 0) {
        $pstart = max($start - COMPLETION_REPORT_PAGE, 0);
        $pagingbar .= "(<a class=\"previous\" href=\"{$link}{$pstart}{$sistring}\">".get_string('previous').'</a>)&nbsp;';
    }

    // Create page links
    $curstart = 0;
    $curpage = 0;
    while ($curstart < $total) {
        $curpage++;

        if ($curstart == $start) {
            $pagingbar .= '&nbsp;'.$curpage.'&nbsp;';
        } else {
            $pagingbar .= "&nbsp;<a href=\"{$link}{$curstart}{$sistring}\">$curpage</a>&nbsp;";
        }

        $curstart += COMPLETION_REPORT_PAGE;
    }

    // Display next link
    $nstart = $start + COMPLETION_REPORT_PAGE;
    if ($nstart < $total) {
        $pagingbar .= "&nbsp;(<a class=\"next\" href=\"{$link}{$nstart}{$sistring}\">".get_string('next').'</a>)';
    }

    $pagingbar .= '</div>';
}

// Okay, let's draw the table of progress info,

// Start of table
if (!$csv) {
    print '<br class="clearer"/>'; // ugh

    print $pagingbar;

    if (!$total) {
        echo $OUTPUT->heading(get_string('nothingtodisplay'));
        echo $OUTPUT->footer();
        exit;
    }

    print '<div id="completion-progress-wrapper" class="no-overflow">';
    print '<table id="completion-progress" class="generaltable flexible boxaligncenter" style="text-align:left"><tr style="vertical-align:top">';

    // section th
    print '<tr><th></th>';
    $activityColSpan = 1;
    $previousActivity = null;
    $actIterator = 0;
    $activityNum = count($activities);
    foreach ($activities as $activity){
        $actIterator++;
        $realSection = $DB->get_record_sql('SELECT * FROM {course_sections} WHERE id = '.$activity->section);
        if($realSection->section == $previousActivity->section){
            $activityColSpan++;
            if($actIterator == $activityNum){ // if last activity
                print '<th data-depth="0" class="open toggle Mod'.($realSection->section).'" colspan="'.$activityColSpan.'">Module '.($realSection->section).'<span class="icon"></span></th>';
                $activityColSpan = 1;
            } 
        }elseif($previousActivity != null){
            print '<th data-depth="0" class="open toggle Mod'.($previousActivity->section).'" colspan="'.$activityColSpan.'">Module '.($previousActivity->section).'<span class="icon"></span></th>';
            $activityColSpan = 1;
            if($actIterator == $activityNum){ // if last activity
                print '<th data-depth="0" class="open toggle Mod'.($realSection->section).'" colspan="'.$activityColSpan.'">Module '.($realSection->section).'<span class="icon"></span></th>';
            }
        }
        $previousActivity = $realSection;
    }
    print '</tr>';

    // User heading / sort option
    print '<th scope="col" class="completion-sortchoice">';

    $sistring = "&amp;silast={$silast}&amp;sifirst={$sifirst}";

    if ($firstnamesort) {
        print
            get_string('firstname')." / <a href=\"./?course={$course->id}{$sistring}\">".
            get_string('lastname').'</a>';
    } else {
        print "<a href=\"./?course={$course->id}&amp;sort=firstname{$sistring}\">".
            get_string('firstname').'</a> / '.
            get_string('lastname');
    }
    print '</th>';
} else {
  //-------------- USER ROLE - CSV HEADER----------------//
  echo $sep . csv_quote('Role');
  echo $sep . csv_quote('Setting');
  echo $sep . csv_quote('Sector');
  echo $sep . csv_quote('Region');
  //-------------- USER ROLE - CSV HEADER----------------//
    foreach ($extrafields as $field) {
        echo $sep . csv_quote(get_user_field_name($field));
    }
    //------------------------ GROUP/REGION/NETWORK CSV HEADER-----------------------//
    echo $sep . csv_quote('Group');
    echo $sep . csv_quote('Group ID');
    echo $sep . csv_quote('Group Region');
    echo $sep . csv_quote('Group Network');
    echo $sep . csv_quote('Group School Type');
    //------------------------ GROUP/REGION/NETWORK CSV HEADER-----------------------//
}

// Activities
$formattedactivities = array();
foreach($activities as $activity) {
    $datepassed = $activity->completionexpected && $activity->completionexpected <= time();
    $datepassedclass = $datepassed ? 'completion-expired' : '';

    if ($activity->completionexpected) {
        $datetext=userdate($activity->completionexpected,get_string('strftimedate','langconfig'));
    } else {
        $datetext='';
    }

    // Some names (labels) come URL-encoded and can be very long, so shorten them
    $displayname = shorten_text($activity->name);

    if ($csv) {
        print $sep.csv_quote(strip_tags($activity->name)).$sep.csv_quote($datetext);
    } else {
        $realSection = $DB->get_record_sql('SELECT * FROM {course_sections} WHERE id = '.$activity->section);
        $formattedactivityname = format_string($activity->name, true, array('context' => $context));
        print '<th data-depth="1" scope="col" class="'.$activity->datepassedclass.'module'.($realSection->section).'">'.
            '<a href="'.$CFG->wwwroot.'/mod/'.$activity->modname.
            '/view.php?id='.$activity->id.'" title="' . $formattedactivityname . '">'.
            '<img src="'.$OUTPUT->pix_url('icon', $activity->modname).'" alt="'.
            get_string('modulename',$activity->modname).'" /> <span class="completion-activityname">'.
            $formattedactivityname.'</span></a>';
        if ($activity->completionexpected) {
            print '<div class="completion-expected"><span>'.$datetext.'</span></div>';
        }
        print '</th>';
    }
    $formattedactivities[$activity->id] = (object)array(
        'datepassedclass' => $datepassedclass,
        'displayname' => $displayname,
    );
}

if ($csv) {
    print $line;
} else {
    print '</tr>';
}

// Row for each user
foreach($progress as $user) {
    // User name
    if ($csv) {
        print csv_quote(fullname($user));
        //************************** ADDED/UPDATED USER ROLE *****************************//
        $userrole = null;
      $sql = "SELECT id FROM {user_info_field} WHERE " . $DB->sql_compare_text('name') . " = " . $DB->sql_compare_text(':name') . "";
      if($role_fieldid = $DB->get_field_sql($sql, array('name' => 'role'))) {
        $rolesql = "SELECT data FROM {$CFG->prefix}user_info_data WHERE userid = $user->id AND fieldid = $role_fieldid";
      $userrole = $DB->get_field_sql($rolesql);
      }
      print $sep.csv_quote($userrole);
      //************************** ADDED/UPDATED USER SETTING *****************************//
        $usersetting = null;
      $sql = "SELECT id FROM {user_info_field} WHERE " . $DB->sql_compare_text('name') . " = " . $DB->sql_compare_text(':name') . "";
      if($setting_fieldid = $DB->get_field_sql($sql, array('name' => 'setting'))) {
        $settingsql = "SELECT data FROM {$CFG->prefix}user_info_data WHERE userid = $user->id AND fieldid = $setting_fieldid";
      $usersetting = $DB->get_field_sql($settingsql);
      }
      print $sep.csv_quote($usersetting);

      //************************** ADDED/UPDATED USER SECTOR *****************************//
        $usersector = null;
      $sql = "SELECT id FROM {user_info_field} WHERE " . $DB->sql_compare_text('name') . " = " . $DB->sql_compare_text(':name') . "";
      if($sector_fieldid = $DB->get_field_sql($sql, array('name' => 'sector'))) {
        $sectorsql = "SELECT data FROM {$CFG->prefix}user_info_data WHERE userid = $user->id AND fieldid = $sector_fieldid";
      $usersector = $DB->get_field_sql($sectorsql);
      }
      print $sep.csv_quote($usersector);

      //************************** ADDED/UPDATED USER REGION *****************************//
        $userregion = null;
      $sql = "SELECT id FROM {user_info_field} WHERE " . $DB->sql_compare_text('name') . " = " . $DB->sql_compare_text(':name') . "";
      if($region_fieldid = $DB->get_field_sql($sql, array('name' => 'region'))) {
        $regionsql = "SELECT data FROM {$CFG->prefix}user_info_data WHERE userid = $user->id AND fieldid = $region_fieldid";
      $userregion = $DB->get_field_sql($regionsql);
      }
      print $sep.csv_quote($userregion);

      /*****END CHANGE***///

        foreach ($extrafields as $field) {
            echo $sep . csv_quote($user->{$field});
        }
    } else {
        print '<tr><th scope="row"><a href="'.$CFG->wwwroot.'/user/view.php?id='.
            $user->id.'&amp;course='.$course->id.'">'.fullname($user).'</a></th>';
    }

    //**************************************** ADDED/UPDATED*****************************************//
    if($csv) {
      $sql = "SELECT GROUP_CONCAT(g.name) AS groupname, g.idnumber, g.region, g.network, g.schooltype
              FROM {groups} g, {groups_members} gm 
        WHERE g.id = gm.groupid AND g.courseid = $course->id AND gm.userid = $user->id 
        GROUP BY gm.userid";

        if($groups = $DB->get_record_sql($sql)) {
          $groupidnumber = isset($groups->idnumber) && !empty($groups->idnumber) ? $groups->idnumber : null;
          $groupname = isset($groups->groupname) && !empty($groups->groupname) ? $groups->groupname : null;
          $groupregion = isset($groups->region) && !empty($groups->region) ? $groups->region : null;
          $groupnetwork = isset($groups->network) && !empty($groups->network) ? $groups->network : null;
          $groupschooltype = isset($groups->schooltype) && !empty($groups->schooltype) ? $groups->schooltype : null;
          
          print $sep.csv_quote($groupname);
          print $sep.csv_quote($groupidnumber);
          print $sep.csv_quote($groupregion);
          print $sep.csv_quote($groupnetwork);
          print $sep.csv_quote($groupschooltype);
        } else {
            print $sep.csv_quote('');
          print $sep.csv_quote('');
          print $sep.csv_quote('');
          print $sep.csv_quote('');
          print $sep.csv_quote('');
        }
    }
    //**************************************** ADDED/UPDATED*****************************************//
    
    // Progress for each activity
    foreach($activities as $activity) {

        // Get progress information and state
        if (array_key_exists($activity->id,$user->progress)) {
            $thisprogress=$user->progress[$activity->id];
            $state=$thisprogress->completionstate;
            $date=userdate($thisprogress->timemodified);
        } else {
            $state=COMPLETION_INCOMPLETE;
            $date='';
        }

        // Work out how it corresponds to an icon
        switch($state) {
            case COMPLETION_INCOMPLETE : $completiontype='n'; break;
            case COMPLETION_COMPLETE : $completiontype='y'; break;
            case COMPLETION_COMPLETE_PASS : $completiontype='pass'; break;
            case COMPLETION_COMPLETE_FAIL : $completiontype='fail'; break;
        }

        $completionicon='completion-'.
            ($activity->completion==COMPLETION_TRACKING_AUTOMATIC ? 'auto' : 'manual').
            '-'.$completiontype;

        $describe = get_string('completion-' . $completiontype, 'completion');
        $a=new StdClass;
        $a->state=$describe;
        $a->date=$date;
        $a->user=fullname($user);
        $a->activity = format_string($formattedactivities[$activity->id]->displayname, true, array('context' => $activity->context));
        $fulldescribe=get_string('progress-title','completion',$a);

        if ($csv) {
            print $sep.csv_quote($describe).$sep.csv_quote($date);
        } else {
            $realSection = $DB->get_record_sql('SELECT * FROM {course_sections} WHERE id = '.$activity->section);
            print '<td data-depth="1" class="completion-progresscell module'.($realSection->section).' '.$activity->datepassedclass.'">'.
                '<img src="'.$OUTPUT->pix_url('i/'.$completionicon).
                '" alt="'.$describe.'" title="'.$fulldescribe.'" /></td>';
        }
    }

    if ($csv) {
        print $line;
    } else {
        print '</tr>';
    }
}

if ($csv) {
    exit;
}
print '</table>';
print '</div>';
print $pagingbar;

print '<ul class="progress-actions"><li><a href="index.php?course='.$course->id.
    '&amp;format=csv">'.get_string('csvdownload','completion').'</a></li>
    <li><a href="index.php?course='.$course->id.'&amp;format=excelcsv">'.
    get_string('excelcsvdownload','completion').'</a></li></ul>';

echo $OUTPUT->footer();