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
 * Library of functions for the tsblog module.
 *
 * This contains functions that are called also from outside the tsblog module
 * Functions that are only called by the quiz module itself are in {@link locallib.php}
 *
 * @author Matt Clarkson <mattc@catalyst.net.nz>
 * @author Sam Marshall <s.marshall@open.ac.uk>
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package tsblog
 */




/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $tsblog the data from the mod form
 * @return int The id od the newly inserted module
 */
function tsblog_add_instance($tsblog) {
    global $DB;
    // Generate an accesstoken
    $tsblog->accesstoken = md5(uniqid(rand(), true));

    if (!$tsblog->id = $DB->insert_record('tsblog', $tsblog)) {
        return(false);
    }

    tsblog_grade_item_update($tsblog);

    return($tsblog->id);
}



/**
 * Given an object containing all the necessary data,(defined by the
 * form in mod_form.php) this function will update an existing instance
 * with new data.
 *
 * @param object $tsblog the data from the mod form
 * @return boolean true on success, false on failure.
 */
function tsblog_update_instance($tsblog) {
    global $DB;
    $tsblog->id = $tsblog->instance;

    if (!$blog = $DB->get_record('tsblog', array('id'=>$tsblog->id))) {
        return(false);
    }

    if (!$DB->update_record('tsblog', $tsblog)) {
        return(false);
    }

    tsblog_grade_item_update($tsblog);

    return(true);
}



/**
 * Given an ID of an instance of this module, this function will
 * permanently delete the instance and any data that depends on it.
 *
 * @param int $id The ID of the module instance
 * @return boolena true on success, false on failure.
 */
function tsblog_delete_instance($tsblogid) {
    global $DB, $CFG;
    if (!$tsblog = $DB->get_record('tsblog', array('id'=>$tsblogid))) {
        return(false);
    }

    if ($tsblog->global) {
        print_error('deleteglobalblog', 'tsblog');
    }

    if ($instances = $DB->get_records('tsblog_instances', array('tsblogid'=>$tsblog->id))) {

        foreach ($instances as $tsbloginstancesid => $bloginstance) {
            // tags
            $DB->delete_records('tsblog_taginstances', array('tsbloginstancesid'=>$tsbloginstancesid));

            if ($posts = $DB->get_records('tsblog_posts', array('tsbloginstancesid'=>$tsbloginstancesid))) {

                foreach ($posts as $postid => $post) {
                    // comments
                    $DB->delete_records('tsblog_comments', array('postid'=>$postid));

                    // edits
                    $DB->delete_records('tsblog_edits', array('postid'=>$postid));
                }

                // posts
                $DB->delete_records('tsblog_posts', array('tsbloginstancesid'=>$tsbloginstancesid));

            }
        }
    }

    // links
    $DB->delete_records('tsblog_links', array('tsblogid'=>$tsblog->id));

    // instances
    $DB->delete_records('tsblog_instances', array('tsblogid'=>$tsblog->id));

    // Fulltext search data
    require_once(dirname(__FILE__).'/locallib.php');
    if (tsblog_search_installed()) {
        $moduleid=$DB->get_field('modules', 'id', array('name'=>'tsblog'));
        $cm=$DB->get_record('course_modules', array('module'=>$moduleid, 'instance'=>$tsblog->id));
        if (!$cm) {
            print_error('invalidcoursemodule');
        }
        local_ousearch_document::delete_module_instance_data($cm);
    }

    tsblog_grade_item_delete($tsblog);

    // tsblog
    return($DB->delete_records('tsblog', array('id'=>$tsblog->id)));

}



/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $tsblog
 * @return object containing a time and info properties
 */
function tsblog_user_outline($course, $user, $mod, $tsblog) {
    global $CFG, $DB;

    $sql = "SELECT count(*) AS postcnt, MAX(timeposted) as lastpost
            FROM {tsblog_posts} p
                INNER JOIN {tsblog_instances} i ON p.tsbloginstancesid = i.id
            WHERE p.deletedby IS NULL AND i.userid = ? AND tsblogid = ?";

    if ($postinfo = $DB->get_record_sql($sql, array($user->id, $mod->instance))) {
        $result = new stdClass();
        $result->info = get_string('numposts', 'tsblog', $postinfo->postcnt);
        $result->time = $postinfo->lastpost;

        return($result);
    }

    return(null);
}



/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $tsblog
 * @return object containing a time and info properties
 */
function tsblog_user_complete($course, $user, $mod, $tsblog) {
    global $CFG, $DB, $PAGE;
    include_once($CFG->dirroot.'/mod/tsblog/locallib.php');

    $tsblogoutput = $PAGE->get_renderer('mod_tsblog');

    $baseurl = $CFG->wwwroot.'/mod/tsblog/view.php?id='.$mod->id;

    $sql = "SELECT p.*
            FROM {tsblog_posts} p
                INNER JOIN {tsblog_instances} i ON p.tsbloginstancesid = i.id
            WHERE p.deletedby IS NULL AND i.userid = ? AND tsblogid = ? ";

    if ($posts = $DB->get_records_sql($sql, array($user->id, $mod->instance))) {
        foreach ($posts as $post) {
            $postdata = tsblog_get_post($post->id);
            echo $tsblogoutput->render_post($mod, $tsblog, $postdata, $baseurl, 'course');
        }
    } else {
        echo get_string('noblogposts', 'tsblog');
    }

    return(null);
}



/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in newmodule activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @param object $course
 * @param bool $isteacher
 * @param int $timestart
 * @return boolean true on success, false on failure.
 **/
function tsblog_print_recent_activity($course, $isteacher, $timestart) {
    global $CFG, $DB, $OUTPUT;

    include_once('locallib.php');

    $sql = "SELECT i.tsblogid, p.id AS postid, p.*, u.firstname, u.lastname, u.email, u.idnumber, i.userid
            FROM {tsblog_posts} p
                INNER JOIN {tsblog_instances} i ON p.tsbloginstancesid = i.id
                INNER JOIN {tsblog} b ON i.tsblogid = b.id
                INNER JOIN {user} u ON i.userid = u.id
            WHERE b.course = ? AND p.deletedby IS NULL AND p.timeposted >= ? ";

    if (!$rs = $DB->get_recordset_sql($sql, array($course->id, $timestart))) {
        return(true);
    }

    $modinfo = get_fast_modinfo($course);

    $strftimerecent = get_string('strftimerecent');
    echo $OUTPUT->heading(get_string('newblogposts', 'tsblog'), 3);

    echo "\n<ul class='unlist'>\n";
    foreach ($rs as $blog) {
        if (!isset($modinfo->instances['tsblog'][$blog->tsblogid])) {
            // not visible
            continue;
        }
        $cm = $modinfo->instances['tsblog'][$blog->tsblogid];
        if (!$cm->uservisible) {
            continue;
        }
        if (!has_capability('mod/tsblog:view', get_context_instance(CONTEXT_MODULE, $cm->id))) {
            continue;
        }
        if (!has_capability('mod/tsblog:view', get_context_instance(CONTEXT_USER, $blog->userid))) {
            continue;
        }

        $groupmode = tsblog_get_activity_groupmode($cm, $course);

        if ($groupmode) {
            if ($blog->groupid && $groupmode != VISIBLEGROUPS) {
                // separate mode
                if (isguestuser()) {
                    // shortcut
                    continue;
                }

                if (is_null($modinfo->groups)) {
                    $modinfo->groups = groups_get_user_groups($course->id); // load all my groups and cache it in modinfo
                }

                if (!array_key_exists($blog->groupid, $modinfo->groups[0])) {
                    continue;
                }
            }
        }

        echo '<li><div class="head">'.
               '<div class="date">'.tsblog_date($blog->timeposted, $strftimerecent).'</div>'.
               '<div class="name">'.fullname($blog).'</div>'.
             '</div>';
        echo '<div class="info">';
        echo "<a href=\"{$CFG->wwwroot}/mod/tsblog/viewpost.php?post={$blog->postid}\">";
        echo break_up_long_words(format_string(empty($blog->title) ? $blog->message : $blog->title));
        echo '</a>';
        echo '</div>';
    }
    $rs->close();
    echo "</ul>\n";
}



/**
 * Get recent activity for a course
 *
 * @param array $activities
 * @param int $index
 * @param int $timestart
 * @param int $courseid
 * @param int $cmid
 * @param int $userid
 * @param int $groupid
 * @return bool
 */
function tsblog_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
    global $CFG, $COURSE, $DB;

    $sql = "SELECT i.tsblogid, p.id AS postid, p.*, u.firstname, u.lastname, u.email, u.idnumber, u.picture, u.imagealt, i.userid
            FROM {tsblog_posts} p
                INNER JOIN {tsblog_instances} i ON p.tsbloginstancesid = i.id
                INNER JOIN {tsblog} b ON i.tsblogid = b.id
                INNER JOIN {user} u ON i.userid = u.id
            WHERE b.course = ? AND p.deletedby IS NULL AND p.timeposted >= ? ";

    if (!$rs = $DB->get_recordset_sql($sql, array($courseid, $timestart))) {
        return(true);
    }

    $modinfo = get_fast_modinfo($COURSE);

    foreach ($rs as $blog) {
        if (!isset($modinfo->instances['tsblog'][$blog->tsblogid])) {
            // not visible
            continue;
        }
        $cm = $modinfo->instances['tsblog'][$blog->tsblogid];
        if (!$cm->uservisible) {
            continue;
        }
        if (!has_capability('mod/tsblog:view', get_context_instance(CONTEXT_MODULE, $cm->id))) {
            continue;
        }
        if (!has_capability('mod/tsblog:view', get_context_instance(CONTEXT_USER, $blog->userid))) {
            continue;
        }

        $groupmode = tsblog_get_activity_groupmode($cm, $COURSE);

        if ($groupmode) {
            if ($blog->groupid && $groupmode != VISIBLEGROUPS) {
                // separate mode
                if (isguestuser()) {
                    // shortcut
                    continue;
                }

                if (is_null($modinfo->groups)) {
                    $modinfo->groups = groups_get_user_groups($courseid); // load all my groups and cache it in modinfo
                }

                if (!array_key_exists($blog->groupid, $modinfo->groups[0])) {
                    continue;
                }
            }
        }

        $tmpactivity = new object();

        $tmpactivity->type         = 'tsblog';
        $tmpactivity->cmid         = $cm->id;
        $tmpactivity->name         = $blog->title;
        $tmpactivity->sectionnum   = $cm->sectionnum;
        $tmpactivity->timeposted    = $blog->timeposted;

        $tmpactivity->content = new object();
        $tmpactivity->content->postid   = $blog->postid;
        $tmpactivity->content->title    = format_string($blog->title);

        $tmpactivity->user = new object();
        $tmpactivity->user->id        = $blog->userid;
        $tmpactivity->user->firstname = $blog->firstname;
        $tmpactivity->user->lastname  = $blog->lastname;
        $tmpactivity->user->picture   = $blog->picture;
        $tmpactivity->user->imagealt  = $blog->imagealt;
        $tmpactivity->user->email     = $blog->email;

        $activities[$index++] = $tmpactivity;
    }
    $rs->close();
}


/**
 * Print recent tsblog activity for a course
 *
 * @param object $activity
 * @param int $courseid
 * @param bool $detail
 * @param array $modnames
 * @param bool $viewfullnames
 */
function tsblog_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
    global $CFG, $OUTPUT;

    echo '<table border="0" cellpadding="3" cellspacing="0" class=tsblog-recent">';

    echo "<tr><td class=\"userpicture\" valign=\"top\">";
    echo $OUTPUT->user_picture($activity->user, array('courseid'=>$courseid));
    echo "</td><td>";

    echo '<div class="title">';
    if ($detail) {
        echo "<img src=\"".$OUTPUT->pix_url('icon', $activity->type)."\" class=\"icon\" alt=\"".s($activity->title)."\" />";
    }
    echo "<a href=\"$CFG->wwwroot/mod/tsblog/viewpost.php?post={$activity->content->postid}\">{$activity->content->title}</a>";
    echo '</div>';

    echo '<div class="user">';
    $fullname = fullname($activity->user, $viewfullnames);
    echo "<a href=\"$CFG->wwwroot/user/view.php?id={$activity->user->id}&amp;course=$courseid\">"
    ."{$fullname}</a> - ".tsblog_date($activity->timeposted);
    echo '</div>';
    echo "</td></tr></table>";

    return;
}


/**
 * Function to be run periodically according to the moodle cron
 * This function runs every 4 hours.
 *
 * @uses $CFG
 * @return boolean true on success, false on failure.
 **/
function tsblog_cron() {
    global $DB;

    // Delete outdated (> 30 days) moderated comments
    $outofdate = time() - 30 * 24 * 3600;
    $DB->delete_records_select('tsblog_comments_moderated', "timeposted < ?", array($outofdate));

    return true;
}

/**
 * Obtains a search document given the ousearch parameters.
 * @param object $document Object containing fields from the ousearch documents table
 * @return mixed False if object can't be found, otherwise object containing the following
 *   fields: ->content, ->title, ->url, ->activityname, ->activityurl
 */
function tsblog_ousearch_get_document($document) {
    global $CFG, $DB;
    require_once('locallib.php');

    // Get data
    if (!($cm=$DB->get_record('course_modules', array('id' => $document->coursemoduleid)))) {
        return false;
    }
    if (!($tsblog=$DB->get_record('tsblog', array('id' => $cm->instance)))) {
        return false;
    }
    if (!($post=$DB->get_record_sql("
SELECT
    p.*,bi.userid
FROM
{tsblog_posts} p
    INNER JOIN {tsblog_instances} bi ON p.tsbloginstancesid=bi.id
WHERE
    p.id= ? ", array($document->intref1)))) {
        return false;
    }

    $result=new StdClass;

    // Set up activity name and URL
    $result->activityname=$tsblog->name;
    if ($tsblog->global) {
        $result->activityurl=$CFG->wwwroot.'/mod/tsblog/view.php?user='.
        $document->userid;
    } else {
        $result->activityurl=$CFG->wwwroot.'/mod/tsblog/view.php?id='.
        $document->coursemoduleid;
    }

    // Now do the post details
    $result->title=$post->title;
    $result->content=$post->message;
    $result->url=$CFG->wwwroot.'/mod/tsblog/viewpost.php?post='.$document->intref1;

    // Sort out tags for use as extrastrings
    $taglist=tsblog_get_post_tags($post, true);
    if (count($taglist)!=0) {
        $result->extrastrings=$taglist;
    }

    // Post object is used in filter
    $result->data=$post;

    return $result;
}

/**
 * Update all documents for ousearch.
 * @param bool $feedback If true, prints feedback as HTML list items
 * @param int $courseid If specified, restricts to particular courseid
 */
function tsblog_ousearch_update_all($feedback=false, $courseid=0) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/mod/tsblog/locallib.php');

    // Get all existing blogs as $cm objects (which we are going to need to
    // do the updates). get_records is ok here because we're only taking a
    // few fields and there's unlikely to be more than a few thousand blog
    // instances [user blogs all use a single course-module]
    $coursemodules=$DB->get_records_sql("
SELECT
    cm.id,cm.course,cm.instance
FROM
{modules} m
    INNER JOIN {course_modules} cm ON m.id=cm.module
WHERE
    m.name='tsblog'".($courseid ? " AND cm.course= ? " : ""), array($courseid));
    if (!$coursemodules) {
        $coursemodules = array();
    }

    // Display info and loop around each coursemodule
    if ($feedback) {
        print '<li><strong>'.count($coursemodules).'</strong> instances to process.</li>';
        $dotcount=0;
    }
    $posts=0; $instances=0;
    foreach ($coursemodules as $coursemodule) {

        // Get all the posts that aren't deleted
        $rs=$DB->get_recordset_sql("
SELECT
    p.id,p.title,p.message,p.groupid,i.userid
FROM
{tsblog_instances} i
    INNER JOIN {tsblog_posts} p ON p.tsbloginstancesid=i.id
WHERE
    p.deletedby IS NULL AND i.tsblogid= ? ", array($coursemodule->instance));

        foreach ($rs as $post) {
            tsblog_search_update($post, $coursemodule);

            // Add to count and do user feedback every 100 posts
            $posts++;
            if ($feedback && ($posts%100)==0) {
                if ($dotcount==0) {
                    print '<li>';
                }
                print '.';
                $dotcount++;
                if ($dotcount==20 || $count==count($coursemodules)) {
                    print "done $posts posts ($instances instances)</li>";
                    $dotcount=0;
                }
                flush();
            }
        }
        $rs->close();

        $instances++;
    }
    if ($feedback && ($dotcount!=0 || $posts<100)) {
        print ($dotcount==0?'<li>':'')."done $posts posts ($instances instances)</li>";
    }
}

/**
 * Indicates API features that the module supports.
 *
 * @param string $feature
 * @return mixed True if yes (some features may use other values)
 */
function tsblog_supports($feature) {
    switch($feature) {
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_COMPLETION_HAS_RULES: return true;
        case FEATURE_BACKUP_MOODLE2: return true;
        case FEATURE_MOD_INTRO: return true;
        case FEATURE_GROUPINGS: return true;
        case FEATURE_GROUPS: return true;
        case FEATURE_GROUPMEMBERSONLY: return true;
        case FEATURE_GRADE_HAS_GRADE: return true;
        default: return null;
    }
}

/**
 * Obtains the automatic completion state for this module based on any conditions
 * in module settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function tsblog_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    // Get tsblog details
    if (!($tsblog=$DB->get_record('tsblog', array('id' => $cm->instance)))) {
        throw new Exception("Can't find tsblog {$cm->instance}");
    }

    $result=$type; // Default return value

    if ($tsblog->completionposts) {
        // Count of posts by user
        $value = $tsblog->completionposts <= $DB->get_field_sql("
SELECT
    COUNT(1)
FROM
{tsblog_instances} i
    INNER JOIN {tsblog_posts} p ON i.id=p.tsbloginstancesid
WHERE
    i.userid= ? AND i.tsblogid=? AND p.deletedby IS NULL", array($userid, $tsblog->id));
        if ($type==COMPLETION_AND) {
            $result=$result && $value;
        } else {
            $result=$result || $value;
        }
    }
    if ($tsblog->completioncomments) {
        // Count of comments by user (on posts by any user)
        $value = $tsblog->completioncomments <= $DB->get_field_sql("
SELECT
    COUNT(1)
FROM
{tsblog_comments} c
    INNER JOIN {tsblog_posts} p ON p.id=c.postid
    INNER JOIN {tsblog_instances} i ON i.id=p.tsbloginstancesid
WHERE
    c.userid= ? AND i.tsblogid= ? AND p.deletedby IS NULL AND c.deletedby IS NULL", array($userid, $tsblog->id));
        if ($type==COMPLETION_AND) {
            $result=$result && $value;
        } else {
            $result=$result || $value;
        }
    }

    return $result;
}


/**
 * This function returns a summary of all the postings since the current user
 * last logged in.
 */
function tsblog_print_overview($courses, &$htmlarray) {
    global $USER, $CFG, $DB;

    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return array();
    }

    if (!$blogs = get_all_instances_in_courses('tsblog', $courses)) {
        return;
    }

    // get all  logs in ONE query
    $sql = "SELECT instance,cmid,l.course,COUNT(l.id) as count FROM {log} l "
    ." JOIN {course_modules} cm ON cm.id = cmid "
    ." WHERE (";
    $params = array();
    foreach ($courses as $course) {
        $sql .= '(l.course = ? AND l.time > ? )  OR ';
        $params[] = $course->id;
        $params[] = $course->lastaccess;
    }
    $sql = substr($sql, 0, -3); // take off the last OR

    // Ignore comment actions for now, only entries.
    $sql .= ") AND l.module = 'tsblog' AND action in('add post','edit post')
      AND userid != ? GROUP BY cmid,l.course,instance";
    $params[] = $USER->id;
    if (!$new = $DB->get_records_sql($sql, $params)) {
        $new = array(); // avoid warnings
    }

    $strblogs = get_string('modulenameplural', 'tsblog');

    $site = get_site();
    if (count( $courses ) == 1 && isset( $courses[$site->id])) {
        $strnumrespsince1 = get_string('overviewnumentrylog1', 'tsblog');
        $strnumrespsince = get_string('overviewnumentrylog', 'tsblog');
    } else {
        $strnumrespsince1 = get_string('overviewnumentryvw1', 'tsblog');
        $strnumrespsince = get_string('overviewnumentryvw', 'tsblog');
    }

    // Go through the list of all tsblog instances build previously, and check whether
    // they have had any activity.
    foreach ($blogs as $blog) {
        if (array_key_exists($blog->id, $new) && !empty($new[$blog->id])) {
            $count = $new[$blog->id]->count;
            if ($count > 0) {
                if ($count == 1) {
                    $strresp = $strnumrespsince1;
                } else {
                    $strresp = $strnumrespsince;
                }

                $str = '<div class="overview tsblog"><div class="name">'.
                $strblogs.': <a title="'.$strblogs.'" href="';
                if ($blog->global=='1') {
                    $str .= $CFG->wwwroot.'/mod/tsblog/allposts.php">'.$blog->name.'</a></div>';
                } else {
                    $str .= $CFG->wwwroot.'/mod/tsblog/view.php?id='.$new[$blog->id]->cmid.'">'.$blog->name.'</a></div>';
                }
                $str .= '<div class="info">';
                $str .= $count.' '.$strresp;
                $str .= '</div></div>';

                if (!array_key_exists($blog->course, $htmlarray)) {
                    $htmlarray[$blog->course] = array();
                }
                if (!array_key_exists('tsblog', $htmlarray[$blog->course])) {
                    $htmlarray[$blog->course]['tsblog'] = ''; // initialize, avoid warnings
                }
                $htmlarray[$blog->course]['tsblog'] .= $str;

            }

        }

    }

}

/**
 * Serves the tsblog attachments. Implements needed access control ;-)
 *
 * @param object $course
 * @param object $cm
 * @param object $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return bool false if file not found, does not return if found - justsend the file
 */
function tsblog_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB, $USER;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    $fileareas = array('attachment', 'message', 'edit', 'messagecomment', 'summary');
    if (!in_array($filearea, $fileareas)) {
        return false;
    }
    require_once(dirname(__FILE__).'/locallib.php');
    if ($filearea=='edit') {
        $editid = (int)array_shift($args);

        if (!$edit = $DB->get_record('tsblog_edits', array('id'=>$editid))) {
            return false;
        }
        $postid = $edit->postid;
        $fileid = $editid;
    } else {
        $postid = (int)array_shift($args);
        $fileid = $postid;
    }

    if ($filearea != 'summary') {
        if ($filearea == 'messagecomment') {
            if (!$comment = $DB->get_record('tsblog_comments', array('id' => $postid), 'postid')) {
                return false;
            }
            $postid = $comment->postid;
        }
        if (!$post = $DB->get_record('tsblog_posts', array('id'=>$postid))) {
            return false;
        }
        if (!($tsblog = tsblog_get_blog_from_postid($post->id))) {
            return false;
        }
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_tsblog/$filearea/$fileid/$relativepath";

    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    // Make sure we're allowed to see it...
    // Check if coming from webservice - if so always allow.
    $ajax = constant('AJAX_SCRIPT') ? true : false;
    if ($filearea != 'summary' && !$ajax && !tsblog_can_view_post($post, $USER, $context, $tsblog->global)) {
        return false;
    }
    if ($filearea == 'attachment') {
        $forcedownload = true;
    } else {
        $forcedownload = false;
    }
    // Finally send the file.
    send_stored_file($file, 0, 0, $forcedownload);
}

/**
 * File browsing support for tsblog module.
 * @param object $browser
 * @param object $areas
 * @param object $course
 * @param object $cm
 * @param object $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance Representing an actual file or folder (null if not found
 * or cannot access)
 */
function tsblog_get_file_info($browser, $areas, $course, $cm, $context, $filearea,
        $itemid, $filepath, $filename) {
    global $CFG, $USER, $DB;
    require_once($CFG->dirroot . '/mod/tsblog/locallib.php');

    if ($context->contextlevel != CONTEXT_MODULE) {
        return null;
    }
    $fileareas = array('attachment', 'message', 'edit', 'messagecomment');
    if (!in_array($filearea, $fileareas)) {
        return null;
    }
    $postid = $itemid;
    if ($filearea == 'messagecomment') {
        if (!$comment = $DB->get_record('tsblog_comments', array('id' => $postid), 'postid')) {
            return null;
        }
        $postid = $comment->postid;
    }

    if (!($tsblog = tsblog_get_blog_from_postid($postid))) {
        return null;
    }
    // Check if the user is allowed to view the blog.
    if (!has_capability('mod/tsblog:view', $context)) {
        return null;
    }

    if (!$post = tsblog_get_post($postid)) {
        return null;
    }
    // Check if the user is allowed to view the post
    try {
        if (!tsblog_can_view_post($post, $USER, $context, $tsblog->global)) {
            return null;
        }
    } catch (mod_forumng_exception $e) {
        return null;
    }

    $fs = get_file_storage();
    $filepath = is_null($filepath) ? '/' : $filepath;
    $filename = is_null($filename) ? '.' : $filename;
    if (!($storedfile = $fs->get_file($context->id, 'mod_tsblog', $filearea, $itemid,
            $filepath, $filename))) {
        return null;
    }

    $urlbase = $CFG->wwwroot . '/pluginfile.php';
    return new file_info_stored($browser, $context, $storedfile, $urlbase, $filearea,
            $itemid, true, true, false);
}

/**
 * Sets the module uservisible to false if the user has not got the view capability
 * @param cm_info $cm
 */
function tsblog_cm_info_dynamic(cm_info $cm) {
    $capability = 'mod/tsblog:view';
    if ($cm->course == SITEID && $cm->instance == 1) {
        // Is global blog (To save DB call we make suspect assumption it is instance 1)?
        $capability = 'mod/tsblog:viewpersonal';
    }
    if (!has_capability($capability,
            context_module::instance($cm->id))) {
        $cm->set_user_visible(false);
        $cm->set_available(false);
    }
}

/**
 * Create grade item for given tsblog
 *
 * @param object $tsblog
 * @param mixed $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function tsblog_grade_item_update($tsblog, $grades = null) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $params = array('itemname' => $tsblog->name);

    if ($tsblog->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $tsblog->grade;
        $params['grademin']  = 0;

    } else if ($tsblog->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$tsblog->grade;

    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/tsblog', $tsblog->course, 'mod',
        'tsblog', $tsblog->id, 0, $grades, $params);
}

/**
 * Delete grade item for given tsblog
 *
 * @param object $tsblog object
 * @return object tsblog
 */
function tsblog_grade_item_delete($tsblog) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/tsblog', $tsblog->course, 'mod',
        'tsblog', $tsblog->id, 0, null, array('deleted' => 1));
}

/**
 * Returns all other caps used in tsblog at module level.
 */
function tsblog_get_extra_capabilities() {
    return array('moodle/site:accessallgroups', 'moodle/site:viewfullnames',
            'report/oualerts:managealerts');
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the tsblog.
 *
 * @param object $mform form passed by reference
 */
function tsblog_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'tsblogheader', get_string('modulenameplural', 'tsblog'));
    $mform->addElement('advcheckbox', 'reset_tsblog', get_string('removeblogs', 'tsblog'));
}

/**
 * Actual implementation of the reset course functionality, delete all
 * tsblog posts.
 *
 * @global object
 * @global object
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function tsblog_reset_userdata($data) {
    global $DB;

    $componentstr = get_string('modulenameplural', 'tsblog');
    $status = array();

    if (!empty($data->reset_tsblog)) {
        // Delete post-related data.
        $postidsql = "
            SELECT pst.id
            FROM {tsblog_posts} pst
            JOIN {tsblog_instances} ins ON (ins.id = pst.tsbloginstancesid)
            JOIN {tsblog} obl ON (obl.id = ins.tsblogid)
            WHERE obl.course = ?
        ";
        $params = array($data->courseid);
        $DB->delete_records_select('tsblog_comments', "postid IN ($postidsql)", $params);
        $DB->delete_records_select('tsblog_comments_moderated', "postid IN ($postidsql)", $params);
        $DB->delete_records_select('tsblog_edits', "postid IN ($postidsql)", $params);

        // Delete instance-related data.
        $insidsql = "
            SELECT ins.id
            FROM {tsblog_instances} ins
            JOIN {tsblog} obl ON (obl.id = ins.tsblogid)
            WHERE obl.course = ?
        ";
        $DB->delete_records_select('tsblog_links', "tsbloginstancesid IN ($insidsql)", $params);
        $DB->delete_records_select('tsblog_taginstances', "tsbloginstancesid IN ($insidsql)", $params);
        $DB->delete_records_select('tsblog_posts', "tsbloginstancesid IN ($insidsql)", $params);

        $blogidsql = "
            SELECT obl.id
            FROM {tsblog} obl
            WHERE obl.course = ?
        ";
        // Delete instances:
        $DB->delete_records_select('tsblog_instances', "tsblogid IN ($blogidsql)", $params);

        // Reset views:
        $DB->execute("UPDATE {tsblog} SET views = 0 WHERE course = ?", $params);

        // Now get rid of all attachments.
        $fs = get_file_storage();
        $tsblogs = get_coursemodules_in_course('tsblog', $data->courseid);
        if ($tsblogs) {
            foreach ($tsblogs as $tsblogid => $unused) {
                if (!$cm = get_coursemodule_from_instance('tsblog', $tsblogid)) {
                    continue;
                }
                $context = context_module::instance($cm->id);
                $fs->delete_area_files($context->id, 'mod_tsblog', 'attachment');
                $fs->delete_area_files($context->id, 'mod_tsblog', 'message');
                $fs->delete_area_files($context->id, 'mod_tsblog', 'messagecomment');
            }
        }

        $status[] = array(
                'component' => $componentstr,
                'item' => get_string('removeblogs', 'tsblog'),
                'error' => false
        );
    }
    return $status;
}

/**
 * List of view style log actions
 * @return array
 */
function tsblog_get_view_actions() {
    return array('view', 'view all');
}

/**
 * List of update style log actions
 * @return array
 */
function tsblog_get_post_actions() {
    return array('update', 'add', 'add comment', 'add post', 'edit post');
}

function tsblog_oualerts_additional_recipients($type, $id) {
    global $CFG, $USER, $DB;
    require_once($CFG->dirroot . '/mod/tsblog/locallib.php');
    $additionalemails = '';

    switch ($type) {
        case 'post':
            $data = tsblog_get_blog_from_postid ($id);
            break;
        case 'comment':
            $postid = $DB->get_field('tsblog_comments', 'postid', array('id' => $id));
            $data = tsblog_get_blog_from_postid($postid);
            break;
        default:
            $data = false;
            break;
    }
    if ($data != false) {
        // Return alert recipients addresses for notification.
        $reportingemails = tsblog_get_reportingemail($data);
        if ($reportingemails != false) {
            $additionalemails = explode(',', trim($reportingemails));
        }
    }
    return $additionalemails;
}

function tsblog_oualerts_custom_info($item, $id) {
    global $CFG, $USER, $DB;

    require_once($CFG->dirroot . '/mod/tsblog/locallib.php');

    switch ($item) {
        case 'post':
            $data =  tsblog_get_post($id);
            $itemtitle = get_string('untitledpost', 'tsblog');
            break;
        case 'comment':
            $data = $DB->get_record('tsblog_comments', array('id' => $id));
            $itemtitle = get_string('untitledcomment', 'tsblog');
            break;
        default:
            $data = false;
            break;
    }

    if ($data != false && !empty($data->title)) {
        $itemtitle = $data->title;
    }
    // Return just the title string value of the post or comment.
    return $itemtitle;
}

/**
 * If OU alerts is enabled, and the blog has reporting email setup,
 * if the user has the report/oualerts:managealerts capability for the context then
 * the link to the alerts report should be added.
 *
 * @global object
 * @global object
 */
function tsblog_extend_settings_navigation(settings_navigation $settings, navigation_node $node) {
    global $DB, $CFG, $PAGE;

    if (!$tsblog = $DB->get_record("tsblog", array("id" => $PAGE->cm->instance))) {
        return;
    }

    include_once($CFG->dirroot.'/mod/tsblog/locallib.php');
    if (tsblog_oualerts_enabled() && tsblog_get_reportingemail($tsblog)) {
        if (has_capability('report/oualerts:managealerts',
                get_context_instance(CONTEXT_MODULE, $PAGE->cm->id))) {
            $node->add(get_string('tsblog_managealerts', 'tsblog'),
                    new moodle_url('/report/oualerts/manage.php', array('cmid' => $PAGE->cm->id,
                            'coursename' => $PAGE->course->id, 'contextcourseid' => $PAGE->course->id)),
                            settings_navigation::TYPE_CUSTOM);
        }
    }
}
