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
 * Library of functions used by the tsblog module.
 *
 * This contains functions that are called from within the tsblog module only
 * Functions that are also called by core Moodle are in {@link lib.php}
 *
 * @author Matt Clarkson <mattc@catalyst.net.nz>
 * @author Sam Marshall <s.marshall@open.ac.uk>
 * @author M Kassaei <m.kassaei@open.ac.uk>
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package tsblog
 */

require_once($CFG->libdir . '/portfolio/caller.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/filelib.php');
/**#@+
 * Constants defining the visibility levels of blog posts
 */
define('TS_BLOG_VISIBILITY_COURSEUSER',   100);
define('TS_BLOG_VISIBILITY_LOGGEDINUSER', 200);
define('TS_BLOG_VISIBILITY_PUBLIC',       300);
/**#@-*/

/**#@+
 * Constants defining the ability to post comments
 */
define('TS_BLOG_COMMENTS_PREVENT', 0);
define('TS_BLOG_COMMENTS_ALLOW',   1);
define('TS_BLOG_COMMENTS_ALLOWPUBLIC', 2);
/**#@-*/

/**#@+
 * Constants for the 'approval' field in modreated comments
 */
define('TS_BLOG_MODERATED_UNSET', 0);
define('TS_BLOG_MODERATED_APPROVED', 1);
define('TS_BLOG_MODERATED_REJECTED', 2);
/**#@-*/

/**#@+
 * Constant defining the number of posts to display per page
 */
define('TS_BLOG_POSTS_PER_PAGE', 20);
/**#@-*/

/**#@+
 * Constant defining the max number of items in an RSS or Atom feed
 */
define('TS_BLOG_MAX_FEED_ITEMS', 20);
/**#@-*/

/**#@+
 * Constants defining the visibility for participation pages
 */
define('TS_BLOG_USER_PARTICIPATION', 2);
define('TS_BLOG_MY_PARTICIPATION', 1);
define('TS_BLOG_NO_PARTICIPATION', 0);
define('TS_BLOG_PARTICIPATION_PERPAGE', 100);
/**#@-*/

// Constants defining stats time filter.
define('TS_BLOG_STATS_TIMEFILTER_ALL', 0);
define('TS_BLOG_STATS_TIMEFILTER_MONTH', 1);
define('TS_BLOG_STATS_TIMEFILTER_YEAR', 2);

/**
 * Get a blog from a user id
 *
 * @param int $userid
 * @return mixed Tsblog object on success, false on failure
 */
function tsblog_get_personal_blog($userid) {
    global $CFG, $DB;

    if (!$blog = $DB->get_record('tsblog', array('global'=>1))) {
        print_error('globalblogmissing', 'tsblog');
    }

    if (!$tsbloginstance = $DB->get_record('tsblog_instances', array('tsblogid'=>$blog->id, 'userid'=>$userid))) {
        $user = $DB->get_record('user', array('id'=>$userid));
        $a = (object) array('name' => fullname($user), 'displayname' => tsblog_get_displayname($blog));
        tsblog_add_bloginstance($blog->id, $userid, get_string('defaultpersonalblogname', 'tsblog', $a));
        if (!$tsbloginstance = $DB->get_record('tsblog_instances', array('tsblogid'=>$blog->id, 'userid'=>$user->id))) {
            print_error('invalidblog', 'tsblog');
        }
    }

    return(array($blog, $tsbloginstance));
}

/**
 * Obtains the tsblog object based on a post ID.
 * @param int $postid Post ID
 * @return object Moodle data object for tsblog row, or false if not found
 */
function tsblog_get_blog_from_postid($postid) {
    global $DB;
    $postid = (int)$postid;
    return $DB->get_record_sql("
SELECT
    o.*
FROM
    {tsblog_posts} p
    INNER JOIN {tsblog_instances} i on i.id = p.tsbloginstancesid
    INNER JOIN {tsblog} o ON o.id = i.tsblogid
WHERE
    p.id= ? ", array($postid));
}

/**
 * Checks if a user is allowed to view a blog. If not, will not return (calls
 * an error function and exits).
 * @param object $tsblog
 * @param object $context
 * @param object $cm
 * @return void
 */
function tsblog_check_view_permissions($tsblog, $context, $cm=null) {
    global $COURSE, $PAGE, $DB;

    $capability=$tsblog->global ? 'mod/tsblog:viewpersonal' : 'mod/tsblog:view';

    switch ($tsblog->maxvisibility) {
        case TS_BLOG_VISIBILITY_PUBLIC:
            if ($tsblog->course == $COURSE->id or empty($tsblog->course)) {
                $tsblogcourse = $COURSE;
            } else {
                $tsblogcourse = $DB->get_record('course', array('id'=>$tsblog->course),
                        '*', MUST_EXIST);
            }
            $PAGE->set_course($tsblogcourse);
            $PAGE->set_cm($cm, $tsblogcourse);
            $PAGE->set_pagelayout('incourse');
            return;

        case TS_BLOG_VISIBILITY_LOGGEDINUSER:
            require_login(SITEID, false);
            if ($tsblog->course == $COURSE->id or empty($tsblog->course)) {
                $tsblogcourse = $COURSE;
            } else {
                $tsblogcourse = $DB->get_record('course', array('id'=>$tsblog->course),
                        '*', MUST_EXIST);
            }
            $PAGE->set_course($tsblogcourse);
            $PAGE->set_cm($cm, $tsblogcourse);
            $PAGE->set_pagelayout('incourse');
            // Check tsblog:view cap
            if (!has_capability($capability, $context)) {
                print_error('accessdenied', 'tsblog');
            }
            return;

        case TS_BLOG_VISIBILITY_COURSEUSER:
            require_course_login($tsblog->course, false, $cm);
            // Check tsblog:view cap
            if (!has_capability($capability, $context)) {
                print_error('accessdenied', 'tsblog');
            }
            return;

        default:
            print_error('invalidvisibility', 'tsblog');
    }
}

/**
 * Determines whether the user can make a post to the given blog.
 * @param $tsblog Blog object
 * @param $bloguserid Userid of person who owns blog (only needed for
 *   personal blog)
 * @param $cm Course-module (only needed if not personal blog)
 * @return bool True if user is allowed to make posts
 */
function tsblog_can_post($tsblog, $bloguserid=0, $cm=null) {
    global $USER;
    if ($tsblog->global) {
        if ($bloguserid==0) {
            debugging('Calls to tsblog_can_post for personal blogs must supply userid!', DEBUG_DEVELOPER);
        }
        // This needs to be your blog and you need the 'contributepersonal'
        // permission at system level
        return $bloguserid==$USER->id &&
            has_capability('mod/tsblog:contributepersonal',
                get_context_instance(CONTEXT_SYSTEM));
    } else {
        // Need specific post permission in this blog
        return has_capability('mod/tsblog:post',
            get_context_instance(CONTEXT_MODULE, $cm->id));
    }
}

/**
 * Determines whether the user can comment on the given blog post, presuming
 * that they are allowed to see it.
 * @param $cm Course-module (null if personal blog)
 * @param $tsblog Blog object
 * @param $post Post object
 * @return bool True if user is allowed to make comments
 */
function tsblog_can_comment($cm, $tsblog, $post) {
    global $USER;
    if ($tsblog->global) {
        // Just need the 'contributepersonal' permission at system level, OR
        // if you are not logged in but the blog allows public comments.
        // Note that if you ARE logged in you must have the capability. This is
        // because logged-in comments do not go through moderation, so we want
        // to be able to prevent them by removing the capability. They will
        // still be able to make comments by logging out, but these will then
        // go through moderation.
        $blogok =
                (!isloggedin() && $tsblog->allowcomments == TS_BLOG_COMMENTS_ALLOWPUBLIC) ||
                has_capability('mod/tsblog:contributepersonal',
                    get_context_instance(CONTEXT_SYSTEM));
    } else {
        $modcontext = get_context_instance(CONTEXT_MODULE, $cm->id);

        // Three ways you can comment to a course blog:
        $blogok =
                // 1. Blog allows public comments and you're not logged in.
                $tsblog->allowcomments == (TS_BLOG_COMMENTS_ALLOWPUBLIC && !isloggedin()) ||

                // 2. Post is visible to all logged-in users+, and you have the
                // contributepersonal capabilty normally used for personal blogs.
                ($post->visibility >= TS_BLOG_VISIBILITY_LOGGEDINUSER
                    && $tsblog->maxvisibility >= TS_BLOG_VISIBILITY_LOGGEDINUSER
                    && has_capability('mod/tsblog:contributepersonal',
                        $modcontext)) ||

                // 3. You have comment permission in the specific context
                // (= course member) and you are allowed to write to the blog
                // group i.e. it's your group.
                (has_capability('mod/tsblog:comment', $modcontext) &&
                    tsblog_is_writable_group($cm));

        // Note this logic is still a bit weird with regard to groups. If
        // there is a course blog with visible groups, users in other groups
        // can't comment; there is a CONTRIB bug request for us to make an
        // option for this. In that same situation, if a post is set to be
        // visible to logged-in users, now people not in groups can suddenly
        // comment. Hmmm. We might need another level of ->allowcomments to
        // make this make sense, or some other changes.
    }

    // If the blog allows comments, this post must allow comments and either
    // it allows public comments or you're logged in (and not guest)
    return $blogok && $post->allowcomments &&
            ($post->allowcomments >= TS_BLOG_COMMENTS_ALLOWPUBLIC ||
                (isloggedin() && !isguestuser()));
}

/**
 * Wrapper around tsblog_get_activity_group for increased performance.
 * @param object $cm Moodle course-module (possibly with extra cache fields)
 * @param boolean $update True to update from URL (must be first call on page)
 */
function tsblog_get_activity_group($cm, $update=false) {
    if (!isset($cm->activitygroup) || $update) {
        if (isset($cm->activitygroup)) {
            debugging('Update parameter should be used only in first call to ' .
                    'tsblog_get_activity_group; please fix this to improve ' .
                    'performance slightly', DEBUG_DEVELOPER);
        }
        $cm->activitygroup = groups_get_activity_group($cm, $update);
    }
    return $cm->activitygroup;
}

/**
 * Wrapper around tsblog_get_activity_groupmode for increased performance.
 * @param object $cm Moodle course-module (possibly with extra cache fields)
 * @param object $course Optional course parameter; should be included in
 *   first call in page
 */
function tsblog_get_activity_groupmode($cm, $course=null) {
    if (!isset($cm->activitygroupmode)) {
        if (!$course) {
            debugging('Course parameter should be provided in first call to ' .
                    'tsblog_get_activity_groupmode; please fix this to improve ' .
                    'performance slightly', DEBUG_DEVELOPER);
        }
        $cm->activitygroupmode = groups_get_activity_groupmode($cm, $course);
    }
    return $cm->activitygroupmode;
}

/**
 * Checks whether a group is writable GIVEN THAT YOU CAN SEE THE BLOG
 * (i.e. this does not handle the separate-groups case, only visible-groups).
 * The information is static-cached so this function can be called multiple
 * times.
 * @param object $cm Moodle course-module
 */
function tsblog_is_writable_group($cm) {
    $groupmode = tsblog_get_activity_groupmode($cm, $cm->course);
    if ($groupmode != VISIBLEGROUPS) {
        // If no groups, then they must be allowed to access this;
        // if separate groups, then because this is defined to only work
        // for entries you can see, you must be allowed to access; so only
        // doubt is for visible groups.
        return true;
    }
    $groupid = tsblog_get_activity_group($cm);
    if (!isset($cm->writablegroups)) {
        $cm->writablegroups = array();
    }
    if (isset($cm->writablegroups[$groupid])) {
        return $cm->writablegroups[$groupid];
    }
    $cm->writablegroups[$groupid] = groups_is_member($groupid) ||
        has_capability('moodle/site:accessallgroups',
            get_context_instance(CONTEXT_MODULE, $cm->id));
    return $cm->writablegroups[$groupid];
}

/**
 * Determine if a user can view a post. Note that you must also call
 * tsblog_check_view_permissions for the blog as a whole.
 *
 * @param object $post
 * @param object $user
 * @param object $context
 * @param bool $personalblog True if this is on a personal blog
 * @return bool
 */
function tsblog_can_view_post($post, $user, $context, $personalblog) {
    if (empty($post->userid)) {
        // Not sent userid from pluginfile etc so get it.
        global $DB;
        if ($instance = $DB->get_record('tsblog_instances',
                array('id' => $post->tsbloginstancesid), 'userid')) {
            $post->userid = $instance->userid;
        }
    }
    // If you dont have capabilities and its not yours, you cant see it.
    if ($post->deletedby && !has_capability('mod/tsblog:manageposts', $context, $user->id) &&
                ($post->userid !== $user->id)) {
        return false;
    }
    // Public visibility means everyone
    if ($post->visibility == TS_BLOG_VISIBILITY_PUBLIC) {
        return true;
    }
    // Logged-in user visibility means everyone logged in, but no guests
    if ($post->visibility==TS_BLOG_VISIBILITY_LOGGEDINUSER &&
        (isloggedin() && !isguestuser())) {
        return true;
    } else if ($post->visibility==TS_BLOG_VISIBILITY_LOGGEDINUSER) {
        return false;
    }

    if ($post->visibility!=TS_BLOG_VISIBILITY_COURSEUSER) {
        print_error('invalidvisibilitylevel', 'tsblog', '', $post->visibility);
    }

    // Otherwise this is set to course visibility
    if ($personalblog) {
        // Private posts - only same user or has capability viewprivate can see.
        if (has_capability('mod/tsblog:viewprivate', context_system::instance(), $user->id)) {
            return true;
        }
        return $post->userid == $user->id;
    } else {
        // Check tsblog:view capability at module level
        // This might not have been checked yet because if the blog is
        // set to public, you're allowed to view it, but maybe not this
        // post.
        return has_capability('mod/tsblog:view', $context, $user->id);
    }
}



/**
 * Add a new blog post
 *
 * @param mixed $post An object containing all required post fields
 * @param object $cm Course-module for blog
 * @return mixed PostID on success or false
 */
function tsblog_add_post($post, $cm, $tsblog, $course) {
    global $DB, $CFG;
    require_once($CFG->libdir . '/completionlib.php');
    $post->itemid = $post->message['itemid'];
    $post->message = $post->message['text'];
    $modcontext = get_context_instance(CONTEXT_MODULE, $cm->id);

    if (!isset($post->tsbloginstancesid)) {
        if (!$post->tsbloginstancesid = $DB->get_field('tsblog_instances', 'id', array('tsblogid'=>$post->tsblogid, 'userid'=>$post->userid))) {
            if (!$post->tsbloginstancesid = tsblog_add_bloginstance($post->tsblogid, $post->userid)) {
                return(false);
            }
        }
    }
    if (!isset($post->timeposted)) {
        $post->timeposted = time();
    }

    // Begin transaction
    $tw = $DB->start_delegated_transaction();

    if (!$postid = $DB->insert_record('tsblog_posts', $post)) {
        return(false);
    }
    // Now do filestuff.
    if ($post->attachments) {
        file_save_draft_area_files($post->attachments, $modcontext->id, 'mod_tsblog', 'attachment', $postid, array('subdirs' => 0));
    }

    $post->message = file_save_draft_area_files($post->itemid, $modcontext->id, 'mod_tsblog', 'message', $postid, array('subdirs'=>0), $post->message);
    $DB->set_field('tsblog_posts', 'message', $post->message, array('id'=>$postid));
    if (isset($post->tags)) {
        tsblog_update_item_tags($post->tsbloginstancesid, $postid, $post->tags, $post->visibility);
    }

    $post->id=$postid; // Needed by the below
    if (!tsblog_search_update($post, $cm)) {
        return(false);
    }

    // Inform completion system, if available
    $completion = new completion_info($course);
    if ($completion->is_enabled($cm) && ($tsblog->completionposts)) {
        $completion->update_state($cm, COMPLETION_COMPLETE);
    }

    $tw->allow_commit();

    return($postid);
}



/**
 * Update a blog post
 *
 * @param mixed $post An object containing all required post fields
 * @param object $cm Course-module for blog
 * @return bool
 */
function tsblog_edit_post($post, $cm) {
    global $USER, $DB;
    $post->itemid = $post->message['itemid'];
    $post->message = $post->message['text'];
    $modcontext = get_context_instance(CONTEXT_MODULE, $cm->id);
    if (!isset($post->id) || !$oldpost = $DB->get_record('tsblog_posts', array('id'=>$post->id))) {
        return(false);
    }

    if (!$post->tsbloginstancesid = $DB->get_field('tsblog_instances', 'id', array('tsblogid'=>$post->tsblogid, 'userid'=>$post->userid))) {
        return(false);
    }

    // Begin transaction
    $tw = $DB->start_delegated_transaction();

    // insert edit history
    $edit = new stdClass();
    $edit->postid       = $post->id;
    $edit->userid       = $USER->id;
    $edit->timeupdated  = time();
    $edit->oldtitle     = $oldpost->title;
    $edit->oldmessage   = $oldpost->message;

    if (!$editid = $DB->insert_record('tsblog_edits', $edit)) {
        return(false);
    }
    // Get list of files attached to this post and attach them to the edit.
    $fs = get_file_storage();
    if ($files = $fs->get_area_files($modcontext->id, 'mod_tsblog', 'attachment', $post->id, "timemodified", false)) {
        foreach ($files as $file) {
            // Add this file to the edit record.
            $fs->create_file_from_storedfile(array(
                'filearea' => 'edit',
                'itemid' => $editid), $file);
        }
    }
    // Save new files.
    $post->message = file_save_draft_area_files($post->itemid, $modcontext->id, 'mod_tsblog', 'message', $post->id, array('subdirs'=>0), $post->message);
    file_save_draft_area_files($post->attachments, $modcontext->id, 'mod_tsblog', 'attachment', $post->id, array('subdirs' => 0));

    // Update tags
    if (!tsblog_update_item_tags($post->tsbloginstancesid, $post->id, $post->tags, $post->visibility)) {
        return(false);
    }

    // Update the post
    $post->timeupdated = $edit->timeupdated;
    $post->lasteditedby = $USER->id;

    if (isset($post->groupid)) {
        unset($post->groupid); // Can't change group
    }

    if (!$DB->update_record('tsblog_posts', $post)) {
        return(false);
    }

    if (!tsblog_search_update($post, $cm)) {
        return(false);
    }

    $tw->allow_commit();

    return(true);
}



/**
 * Get all data required to print a list of blog posts as efficiently as possible
 *
 *
 * @param object $tsblog
 * @param int $offset
 * @param int $userid
 * @param bool $ignoreprivate set true to not return private posts (global blog only)
 * @return mixed all data to print a list of blog posts
 */
function tsblog_get_posts($tsblog, $context, $offset = 0, $cm, $groupid, $individualid = -1,
        $userid = null, $tag = '', $canaudit = false, $ignoreprivate = null) {
    global $CFG, $USER, $DB;
    $params = array();
    $sqlwhere = "bi.tsblogid = ?";
    $params[] = $tsblog->id;
    $sqljoin = '';

    if (isset($userid)) {
        $sqlwhere .= " AND bi.userid = ? ";
        $params[] = $userid;
    }

    // Individual blog.
    if ($individualid > -1) {
        $capable = tsblog_individual_has_permissions($cm, $tsblog, $groupid, $individualid);
        tsblog_individual_add_to_sqlwhere($sqlwhere, $params, 'bi.userid', $tsblog->id, $groupid, $individualid, $capable);
    } else {// No individual blog.
        if (isset($groupid) && $groupid) {
            $sqlwhere .= " AND p.groupid =  ? ";
            $params[] = $groupid;
        }
    }
    if (!$canaudit) {
        $sqlwhere .= " AND (p.deletedby IS NULL or bi.userid = ?)";
        $params[] = $USER->id;
    }
    if ($tag) {
        $sqlwhere .= " AND t.tag = ? ";
        $params[] = $tag;
        $sqljoin  .= " INNER JOIN {tsblog_taginstances} ti ON p.id = ti.postid
                       INNER JOIN {tsblog_tags} t ON ti.tagid = t.id ";
    }

    // Visibility checks.
    if (!isloggedin() || isguestuser()) {
        $sqlwhere .= " AND p.visibility =" . TS_BLOG_VISIBILITY_PUBLIC;
    } else {
        if ($tsblog->global) {
            // Unless the current user has manageposts capability,
            // they cannot view 'private' posts except their own.
            if ($ignoreprivate) {
                $sqlwhere .= ' AND (p.visibility > ' . TS_BLOG_VISIBILITY_COURSEUSER . ')';
            } else if (!has_capability('mod/tsblog:manageposts', context_system::instance())) {
                $sqlwhere .= " AND (p.visibility >" . TS_BLOG_VISIBILITY_COURSEUSER .
                        " OR (p.visibility = " . TS_BLOG_VISIBILITY_COURSEUSER . " AND u.id = ?))";
                $params[] = $USER->id;
            }
        } else {
            $context = context_module::instance($cm->id);
            if (has_capability('mod/tsblog:view', $context)) {
                $sqlwhere .= " AND (p.visibility >= " . TS_BLOG_VISIBILITY_COURSEUSER . " )";
            } else {
                $sqlwhere .= " AND p.visibility > " . TS_BLOG_VISIBILITY_COURSEUSER;
            }
        }
    }

    // Get posts
    $fieldlist = "p.*, bi.tsblogid, u.firstname, u.lastname, bi.userid, u.idnumber, u.picture, u.imagealt, u.email, u.username,
                ud.firstname AS delfirstname, ud.lastname AS dellastname,
                ue.firstname AS edfirstname, ue.lastname AS edlastname";
    $from = "FROM {tsblog_posts} p
                INNER JOIN {tsblog_instances} bi ON p.tsbloginstancesid = bi.id
                INNER JOIN {user} u ON bi.userid = u.id
                LEFT JOIN {user} ud ON p.deletedby = ud.id
                LEFT JOIN {user} ue ON p.lasteditedby = ue.id
                $sqljoin";
    $sql = "SELECT $fieldlist
            $from
            WHERE  $sqlwhere
            ORDER BY p.timeposted DESC
            ";
    $countsql = "SELECT count(p.id) $from WHERE $sqlwhere";

    $rs = $DB->get_recordset_sql($sql, $params, $offset, TS_BLOG_POSTS_PER_PAGE);
    if (!$rs->valid()) {
        return(false);
    }
    // Get paging info
    $recordcnt = $DB->count_records_sql($countsql, $params);

    $cnt        = 0;
    $posts      = array();
    $postids    = array();

    foreach ($rs as $post) {
        if ($cnt > TS_BLOG_POSTS_PER_PAGE) {
            break;
        }
        if (tsblog_can_view_post($post, $USER, $context, $tsblog->global)) {
            if ($tsblog->maxvisibility < $post->visibility) {
                $post->visibility = $tsblog->maxvisibility;
            }
            if ($tsblog->allowcomments == TS_BLOG_COMMENTS_PREVENT) {
                $post->allowcomments = TS_BLOG_COMMENTS_PREVENT;
            }

            $posts[$post->id] = $post;
            $postids[] = (int)$post->id;
            $cnt++;
        }
    }
    $rs->close();

    if (empty($posts)) {
        return(true);
    }

    // Get tags for all posts on page
    $sql = "SELECT t.*, ti.postid
            FROM {tsblog_taginstances} ti
            INNER JOIN {tsblog_tags} t ON ti.tagid = t.id
            WHERE ti.postid IN (".implode(",", $postids).") ";

    $rs = $DB->get_recordset_sql($sql);
    foreach ($rs as $tag) {
        $posts[$tag->postid]->tags[$tag->id] = $tag->tag;
    }
    $rs->close();

    // Get comments for post on the page
    $sql = "SELECT c.id, c.postid, c.timeposted, c.authorname, c.authorip, c.timeapproved, c.userid, u.firstname, u.lastname, u.picture, u.imagealt, u.email, u.idnumber
            FROM {tsblog_comments} c
            LEFT JOIN {user} u ON c.userid = u.id
            WHERE c.postid IN (".implode(",", $postids).") AND c.deletedby IS NULL
            ORDER BY c.timeposted ASC ";

    $rs = $DB->get_recordset_sql($sql);
    foreach ($rs as $comment) {
        $posts[$comment->postid]->comments[$comment->id] = $comment;
    }
    $rs->close();
    // Get count of comments waiting approval for posts on the page...
    if ($tsblog->allowcomments >= TS_BLOG_COMMENTS_ALLOWPUBLIC) {
        // Make list of all posts that allow public comments
        $publicallowed = array();
        foreach ($posts as $post) {
            if ($post->allowcomments >= TS_BLOG_COMMENTS_ALLOWPUBLIC) {
                $publicallowed[] = (int)$post->id;
            }
        }
        // Only run a db query if there are some posts that allow public
        // comments (so, no performance degradation if feature is not used)
        if (count($publicallowed) > 0) {
            $sql = "SELECT cm.postid, COUNT(1) AS numpending
                    FROM {tsblog_comments_moderated} cm
                    WHERE cm.postid IN (".implode(",", $publicallowed).")
                    AND cm.approval = 0
                    GROUP BY cm.postid";
            $rs = $DB->get_recordset_sql($sql);
            foreach ($rs as $postinfo) {
                $posts[$postinfo->postid]->pendingcomments = $postinfo->numpending;
            }
            $rs->close();
        }
    }

    return(array($posts, $recordcnt));
}




/**
 * Get all data required to print a single blog post as efficiently as possible
 *
 *
 * @param int $postid
 * @return mixed all data to print a list of blog posts
 */
function tsblog_get_post($postid, $canaudit=false) {
    global $DB;

    // Get post
    $sql = "SELECT p.*, bi.tsblogid, u.firstname, u.lastname, u.picture, u.imagealt, bi.userid, u.idnumber, u.email, u.username,
                    ud.firstname AS delfirstname, ud.lastname AS dellastname,
                    ue.firstname AS edfirstname, ue.lastname AS edlastname
            FROM {tsblog_posts} p
                INNER JOIN {tsblog_instances} bi ON p.tsbloginstancesid = bi.id
                INNER JOIN {user} u ON bi.userid = u.id
                LEFT JOIN {user} ud ON p.deletedby = ud.id
                LEFT JOIN {user} ue ON p.lasteditedby = ue.id
            WHERE p.id = ?
            ORDER BY p.timeposted DESC
            ";

    if (!$post = $DB->get_record_sql($sql, array($postid))) {
        return(false);
    }

    // Get tags for all posts on page
    $sql = "SELECT t.*, ti.postid
            FROM {tsblog_taginstances} ti
            INNER JOIN {tsblog_tags} t ON ti.tagid = t.id
            WHERE ti.postid = ? ";

    $rs = $DB->get_recordset_sql($sql, array($postid));
    foreach ($rs as $tag) {
        $post->tags[$tag->id] = $tag->tag;
    }
    $rs->close();

    // Get comments for post on the page
    if ($post->allowcomments) {
        $sql = "SELECT c.*, u.firstname, u.lastname, u.picture, u.imagealt, u.email, u.idnumber,
                    ud.firstname AS delfirstname, ud.lastname AS dellastname
                FROM {tsblog_comments} c
                LEFT JOIN {user} u ON c.userid = u.id
                LEFT JOIN {user} ud ON c.deletedby = ud.id
                WHERE c.postid = ? ";

        if (!$canaudit) {
            $sql .= "AND c.deletedby IS NULL ";
        }

        $sql .= "ORDER BY c.timeposted ASC ";

        $rs = $DB->get_recordset_sql($sql, array($postid));
        foreach ($rs as $comment) {
            $post->comments[$comment->id] = $comment;
        }
        $rs->close();
    }

    // Get edits for this post
    $sql = "SELECT e.id, e.timeupdated, e.oldtitle, e.userid, u.firstname, u.lastname, u.picture, u.imagealt, u.email, u.idnumber
            FROM {tsblog_edits} e
            INNER JOIN {user} u ON e.userid = u.id
            WHERE e.postid = ?
            ORDER BY e.timeupdated DESC ";

    $rs = $DB->get_recordset_sql($sql, array($postid));
    foreach ($rs as $edit) {
        $post->edits[$edit->id] = $edit;
    }
    $rs->close();

    return($post);
}


/**
 * Add a blog_instance
 *
 * @param int $tsblogid
 * @param int $userid
 * @param string $name
 * @param string $summary
 * @return mixed tsbloginstancesid on success or false
 */
function tsblog_add_bloginstance($tsblogid, $userid, $name='', $summary=null) {
    global $DB;
    $tsbloginstance = new stdClass;
    $tsbloginstance->tsblogid      = $tsblogid;
    $tsbloginstance->userid        = $userid;
    $tsbloginstance->name          = $name;
    $tsbloginstance->summary       = $summary;
    $tsbloginstance->accesstoken   = md5(uniqid(rand(), true));

    return($DB->insert_record('tsblog_instances', $tsbloginstance));
}

/**
 * Clarifies a $tags value which may be a string or an array of values,
 * returning an array of strings.
 * @param mixed $tags
 * @return array Array of tag strings
 */
function tsblog_clarify_tags($tags) {
    if (is_string($tags)) {
        if (!$tags = explode(',', $tags)) {
            return array();
        }
    } else if (!is_array($tags)) {
        return array();
    }

    foreach ($tags as $idx => $tag) {
        $tag = textlib::strtolower(trim($tag));
        if (empty($tag)) {
            unset($tags[$idx]);
            continue;
        }

        $tags[$idx] = $tag;
    }

    $tags = array_unique($tags);

    return $tags;
}

/**
 * Update a posts tags
 *
 * @param int $tsbloginstanceid
 * @param int $postid
 * @param mixed $tags Comma separated string or an array
 * @uses $CFG
 */
function tsblog_update_item_tags($tsbloginstancesid, $postid, $tags, $postvisibility=TS_BLOG_VISIBILITY_COURSEUSER) {
    global $CFG, $DB;

    $tagssql = array();
    $tagids = array();

    // Removed any existing
    $DB->delete_records('tsblog_taginstances', array('postid'=>$postid));

    $tags=tsblog_clarify_tags($tags);

    if (empty($tags)) {
        return(true);
    }

    // get the id's of the know tags
    list($tagsql, $tagparams) = $DB->get_in_or_equal($tags);
    $sql = "SELECT tag, id FROM {tsblog_tags} WHERE tag $tagsql";
    $tagids = $DB->get_records_sql($sql, $tagparams);

    // insert the remainder
    foreach ($tags as $tag) {
        if (!isset($tagids[$tag])) {
            $tagobj = (object) array('tag' => $tag);
            $tagobj->id = $DB->insert_record('tsblog_tags', $tagobj);
            $tagids[$tag] = $tagobj;
        }
        $taginstance = new stdClass();
        $taginstance->tagid = $tagids[$tag]->id;
        $taginstance->postid = $postid;
        $taginstance->tsbloginstancesid = $tsbloginstancesid;

        $DB->insert_record('tsblog_taginstances', $taginstance);

    }

    return(true);
}



/**
 * Get post tags in a CSV format
 *
 * @param int $postid
 * @return string
 * @uses $CFG;
 */
function tsblog_get_tags_csv($postid) {
    global $DB;

    $sql = "SELECT t.tag
            FROM {tsblog_taginstances} ti
            INNER JOIN {tsblog_tags} t ON ti.tagid = t.id
            WHERE ti.postid = ? ";

    if ($tags = $DB->get_fieldset_sql($sql, array($postid))) {
        return(implode(', ', $tags));
    } else {
        return('');
    }
}



/**
 * Get weighted tags for a given blog or blog instance
 *
 * @param int $tsblogid
 * @param int $tsbloginstanceid
 * @return array Tag data
 */
function tsblog_get_tags($tsblog, $groupid, $cm, $tsbloginstanceid=null, $individualid=-1) {
    global $CFG, $DB, $USER;
    $tags = array();
    $params = array();
    $sqlwhere = "bi.tsblogid = ? ";
    $params[] = $tsblog->id;

    // If individual blog.
    if ($individualid > -1) {
        $capable = tsblog_individual_has_permissions($cm, $tsblog, $groupid, $individualid);
        tsblog_individual_add_to_sqlwhere($sqlwhere, $params, 'bi.userid', $tsblog->id, $groupid,
                $individualid, $capable);
    } else {
        // No individual blog.
        if (isset($tsbloginstanceid)) {
            $sqlwhere .= "AND ti.tsbloginstancesid = ? ";
            $params[] = $tsbloginstanceid;
        }
        if (isset($groupid) && $groupid) {
            $sqlwhere .= " AND p.groupid = ? ";
            $params[] = $groupid;
        }
        if (!empty($cm->groupingid)) {
            if ($groups = $DB->get_records('groupings_groups',
                    array('groupingid' => $cm->groupingid), null, 'groupid')) {
                $sqlwhere .= " AND p.groupid IN (" . implode(',', array_keys($groups)) . ") ";
            }
        }
    }
    // Visibility check.
    if (!isloggedin() || isguestuser()) {
        $sqlwhere .= " AND p.visibility = " . TS_BLOG_VISIBILITY_PUBLIC;
    } else {
        if ($tsblog->global) {
            $sqlwhere .= " AND (p.visibility > " . TS_BLOG_VISIBILITY_COURSEUSER .
                    " OR (p.visibility = " . TS_BLOG_VISIBILITY_COURSEUSER . " AND u.id = ?))";
            $params[] = $USER->id;
        } else {
            $context = context_module::instance($cm->id);
            if (!has_capability('mod/tsblog:view', $context, $USER->id)) {
                $sqlwhere .= " AND (p.visibility > " . TS_BLOG_VISIBILITY_COURSEUSER . ")";
            }
        }
    }

    $sql = "SELECT t.id, t.tag, COUNT(ti.id) AS count
            FROM {tsblog_instances} bi
                INNER JOIN {tsblog_taginstances} ti ON ti.tsbloginstancesid = bi.id
                INNER JOIN {tsblog_tags} t ON ti.tagid = t.id
                INNER JOIN {tsblog_posts} p ON ti.postid = p.id
                INNER JOIN {user} u ON u.id = bi.userid
            WHERE $sqlwhere
            GROUP BY t.id, t.tag
            ORDER BY count DESC";

    if ($tags = $DB->get_records_sql($sql, $params)) {
        $first = array_shift($tags);
        $max = $first->count;
        array_unshift($tags, $first);

        $last = array_pop($tags);
        $min = $last->count;
        array_push($tags, $last);

        $delta = $max-$min+0.00000001;

        foreach ($tags as $idx => $tag) {
            $tags[$idx]->weight = round(($tag->count-$min)/$delta*4);
        }
        uasort($tags, function($a, $b) {
            return strcmp ($a->tag,  $b->tag);
        });
    }
    return($tags);
}



/**
 * Print a tag cloud for a given blog or blog instance
 *
 * @param string $baseurl
 * @param int $tsblogid
 * @param int $groupid
 * @param object $cm
 * @param int $tsbloginstanceid
 * @return string Tag cloud HTML
 */
function tsblog_get_tag_cloud($baseurl, $tsblog, $groupid, $cm, $tsbloginstanceid=null, $individualid=-1) {
    $cloud = '';
    $urlparts= array();

    $baseurl = tsblog_replace_url_param($baseurl, 'tag');

    if (!$tags = tsblog_get_tags($tsblog, $groupid, $cm, $tsbloginstanceid, $individualid)) {
        return($cloud);
    }

    foreach ($tags as $tag) {
        $cloud .= '<a href="'.$baseurl.'&amp;tag='.urlencode($tag->tag).'" class="tsblog-tag-cloud-'.
            $tag->weight.'"><span class="tsblog-tagname">'.strtr(($tag->tag), array(' '=>'&nbsp;')).
            '</span><span class="tsblog-tagcount">('.$tag->count.')</span></a> ';
    }

    return($cloud);
}



/**
 * Translate a visibility number into a language string
 *
 * @param int $vislevel
 * @param bool $personal True if this is a personal blog
 * @return string
 */
function tsblog_get_visibility_string($vislevel, $personal) {

    // Modify visibility string for optional shared activity blog
    global $CFG, $COURSE;
    $visibleusers = 'visiblecourseusers';
    $sharedactvfile = $CFG->dirroot.'/course/format/sharedactv/sharedactv.php';
    if (file_exists($sharedactvfile)) {
        include_once($sharedactvfile);
        if (function_exists('sharedactv_is_magic_course') && sharedactv_is_magic_course($COURSE)) {
            $visibleusers = 'visibleblogusers';
        }
    }

    switch ($vislevel) {
        case TS_BLOG_VISIBILITY_COURSEUSER:
            return get_string($personal ? 'visibleyou' : $visibleusers, 'tsblog');
        case TS_BLOG_VISIBILITY_LOGGEDINUSER:
            return(get_string('visibleloggedinusers', 'tsblog'));
        case TS_BLOG_VISIBILITY_PUBLIC:
            return(get_string('visiblepublic', 'tsblog'));
        default:
            print_error('invalidvisibility', 'tsblog');
    }
}


/**
 * Add a blog comment
 *
 * @param object $comment
 * @return mixed commentid on success or false
 */
function tsblog_add_comment($course, $cm, $tsblog, $comment) {
    global $DB, $CFG;
    require_once($CFG->libdir . '/completionlib.php');
    if (!isset($comment->timeposted)) {
        $comment->timeposted = time();
    }
    // Begin transaction.
    $tw = $DB->start_delegated_transaction();
    // Prepare comment id for draft area.
    $comment->message = '';
    $id = $DB->insert_record('tsblog_comments', $comment);
    // Save out any images from the comment message text.
    $context = context_module::instance($cm->id);
    $draftid = file_get_submitted_draft_itemid('messagecomment');
    $comment->message = file_save_draft_area_files($draftid, $context->id, 'mod_tsblog',
            'messagecomment', $id, array('subdirs' => true), $comment->messagecomment['text']);
    $comment->id = $id;
    // Save the comment.
    $DB->set_field('tsblog_comments', 'message', $comment->message, array('id' => $id));
    if ($id) {
        // Inform completion system, if available.
        $completion = new completion_info($course);
        if ($completion->is_enabled($cm) && ($tsblog->completioncomments)) {
            $completion->update_state($cm, COMPLETION_COMPLETE);
        }
    }
    // Commit transaction and return id.
    $tw->allow_commit();
    return $id;
}



/**
 * Update the hit count for a blog and return the current hits
 *
 * @param object $tsblog
 * @param object $tsbloginstance
 * @return int
 */
function tsblog_update_views($tsblog, $tsbloginstance) {
    global $SESSION, $DB;

    if ($tsblog->global && isset($tsbloginstance)) {
        if (!isset($SESSION->bloginstanceview[$tsbloginstance->id])) {
            $SESSION->bloginstanceview[$tsbloginstance->id] = true;
            $tsbloginstance->views++;
            $sql = "UPDATE {tsblog_instances} SET views = views + 1 WHERE id = ?";
            $DB->execute($sql, array($tsbloginstance->id));
        }
        return($tsbloginstance->views);
    } else {
        if (!isset($SESSION->blogview[$tsblog->id])) {
            $SESSION->blogview[$tsblog->id] = true;
            $tsblog->views++;
            $sql = "UPDATE {tsblog} SET views = views + 1 WHERE id = ?";
            $DB->execute($sql, array($tsblog->id));
        }
        return($tsblog->views);
    }

}

/**
 * Checks for a permission which you have EITHER if you have the specific
 * permission OR if it's your own personal blog and you have post permission to
 * that blog.
 *
 * @param string $capability
 * @param object $tsblog
 * @param object $tsbloginstance (required for personal blog access)
 * @param object $context
 * @return bool True if you have permission
 */
function tsblog_has_userblog_permission($capability, $tsblog, $tsbloginstance, $context) {
    // For personal blogs you can do these things EITHER if you have the capability
    // (ie for admins) OR if you are that user and you are allowed to post
    // to blog (not banned)
    global $USER;
    if ($tsblog->global && $tsbloginstance && $USER->id == $tsbloginstance->userid &&
        has_capability('mod/tsblog:contributepersonal', $context)) {
        return true;
    }
    // Otherwise require the capability (note this also allows eg admin access
    // to personal blogs)
    return has_capability($capability, $context);
}

function tsblog_require_userblog_permission($capability, $tsblog, $tsbloginstance, $context) {
    if (!tsblog_has_userblog_permission($capability, $tsblog, $tsbloginstance, $context)) {
        require_capability($capability, $context);
    }
}

/**
 * Get the list of relevant links in HTML format
 *
 * @param object $tsblog
 * @param object $tsbloginstance
 * @param object $context
 * @return string HTML on success, false on failure
 */
function tsblog_get_links($tsblog, $tsbloginstance, $context) {
    global $CFG, $DB, $OUTPUT;

    $strmoveup      = get_string('moveup');
    $strmovedown    = get_string('movedown');
    $stredit        = get_string('edit');
    $strdelete      = get_string('delete');

    $canmanagelinks = tsblog_has_userblog_permission('mod/tsblog:managelinks', $tsblog, $tsbloginstance, $context);

    if ($tsblog->global) {
        $links = $DB->get_records('tsblog_links', array('tsbloginstancesid'=>$tsbloginstance->id), 'sortorder');
    } else {
        $links = $DB->get_records('tsblog_links', array('tsblogid'=>$tsblog->id), 'sortorder');
    }
    $html = '';

    if ($links) {

        $html .= '<ul class="unlist">';
        $numlinks = count($links);
        $i=0;
        foreach ($links as $link) {
            $i++;
            $html .= '<li>';
            $html .= '<a href="'.htmlentities($link->url).'" class="tsblog-elink">'.format_string($link->title).'</a> ';

            if ($canmanagelinks) {
                if ($i > 1) {
                    $html .= '<form action="movelink.php" method="post" style="display:inline" title="'.$strmoveup.'">';
                    $html .= '<div>';
                    $html .= '<input type="image" src="'.$OUTPUT->pix_url('t/up').'" alt="'.$strmoveup.'" />';
                    $html .= '<input type="hidden" name="down" value="0" />';
                    $html .= '<input type="hidden" name="link" value="'.$link->id.'" />';
                    $html .= '<input type="hidden" name="returnurl" value="'.$_SERVER['REQUEST_URI'].'" />';
                    $html .= '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
                    $html .= '</div>';
                    $html .= '</form>';
                }
                if ($i < $numlinks) {
                    $html .= '<form action="movelink.php" method="post" style="display:inline" title="'.$strmovedown.'">';
                    $html .= '<div>';
                    $html .= '<input type="image" src="'.$OUTPUT->pix_url('t/down').'" alt="'.$strmovedown.'" />';
                    $html .= '<input type="hidden" name="down" value="1" />';
                    $html .= '<input type="hidden" name="link" value="'.$link->id.'" />';
                    $html .= '<input type="hidden" name="returnurl" value="'.$_SERVER['REQUEST_URI'].'" />';
                    $html .= '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
                    $html .= '</div>';
                    $html .= '</form>';
                }
                $html .= '<a href="editlink.php?blog='.$tsblog->id.'&amp;link='.$link->id.'" title="'.
                    $stredit.'"><img src="'.$OUTPUT->pix_url('t/edit').'" alt="'.$stredit.
                    '" class="iconsmall" /></a>';
                $html .= '<a href="deletelink.php?blog='.$tsblog->id.'&amp;link='.$link->id.'" title="'.
                    $strdelete.'"><img src="'.$OUTPUT->pix_url('t/delete').'" alt="'.$strdelete.
                    '" class="iconsmall" /></a>';
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
    }

    if ($canmanagelinks) {
        $html .= '<br />';
        if ($tsblog->global) {
            $html .= '<a href="editlink.php?blog='.$tsblog->id.'&amp;bloginstance='.$tsbloginstance->id.'" class="tsblog-links">'.get_string('addlink', 'tsblog').'</a>';
        } else {
            $html .= '<a href="editlink.php?blog='.$tsblog->id.'"  class="tsblog-links">'.get_string('addlink', 'tsblog').'</a>';
        }
    }

    return($html);

}



/**
 * Insert a link into the DB
 *
 * @param object $link
 * @return bool true on success, false on faulure
 */
function tsblog_add_link($link) {
    global $DB;

    // $link->tsbloginstancesid is only set for personal blogs
    if (isset($link->tsbloginstanceid)) {
        $sql = "SELECT MAX(sortorder) AS sortorder FROM {tsblog_links} WHERE tsbloginstancesid = ? ";
        $sortorder = $DB->get_field_sql($sql, array($link->tsbloginstancesid));
        $sortorder++;
    } else {
        $sql = "SELECT MAX(sortorder) AS sortorder FROM {tsblog_links} WHERE tsblogid = ?";
        $sortorder = $DB->get_field_sql($sql, array($link->tsblogid));
        $sortorder++;
    }

    $link->sortorder = $sortorder;
    if (!$DB->insert_record('tsblog_links', $link)) {
        return(false);
    }

    return(true);
}



/**
 * Update a link in the DB
 *
 * @param object $link
 * @return bool true on success, false on faulure
 */
function tsblog_edit_link($link) {
    global $DB;
    unset($link->sortorder);

    return($DB->update_record('tsblog_links', $link));
}



/**
 * Delete a link from the DB
 *
 * @param object $tsblog
 * @param object $link
 * @return bool true on success, false on faulure
 */
function tsblog_delete_link($tsblog, $link) {
    global $DB;
    $params = array();
    if ($tsblog->global) {
        $where = "tsbloginstancesid = ? ";
        $params[] = $link->tsbloginstancesid;
    } else {
        $where = "tsblogid = ? ";
        $params[] = $link->tsblogid;
    }

    if (!$DB->delete_records('tsblog_links', array('id'=>$link->id))) {
        return(false);
    }

    $sql = "UPDATE {tsblog_links}
            SET sortorder = sortorder - 1
            WHERE $where AND sortorder > ?";
    $params[] = $link->sortorder;
    return($DB->execute($sql, $params));
}



/**
 * Return a timestamp of when a blog, or comment was last updated
 *
 * @param int $blogid
 * @param int $bloginstancesid
 * @param int $postid
 * @param bool $comments
 * @return int last modified timestamp
 */
function tsblog_feed_last_changed($blogid, $bloginstancesid, $postid, $comments) {
    global $DB;
    $params = array();

    // Comments or posts?
    if ($comments) {
        $sql = "SELECT MAX(c.timeposted) AS timeposted
                FROM {tsblog_comments} c ";
        if ($postid) {
            $sqljoin = '';
            $sqlwhere = "WHERE p.postid = ? ";
            $params[] = $postid;
        } else if ($bloginstancesid) {
            $sqljoin  = "INNER JOIN {tsblog_posts} p ON c.postid = p.id ";
            $sqlwhere = "WHERE p.tsbloginstancesid = ? ";
            $params[] = $bloginstancesid;
        } else {
            $sqljoin  = "INNER JOIN {tsblog_posts} p ON c.postid = p.id
                         INNER JOIN {tsblog_instances} i ON p.tsbloginstancesid = i.id ";
            $sqlwhere = "WHERE i.tsblogid = ? ";
            $params[] = $blogid;
        }

    } else {
        $sql = "SELECT MAX(p.timeposted) AS timeposted
                FROM {tsblog_posts} p ";

        if ($bloginstancesid) {
            $sqljoin  = '';
            $sqlwhere = "WHERE p.tsbloginstancesid = ? ";
            $params[] = $bloginstancesid;
        } else {
            $sqljoin  = "INNER JOIN {tsblog_instances} i ON p.tsbloginstancesid = i.id ";
            $sqlwhere = "WHERE i.tsblogid = ? ";
            $params[] = $blogid;
        }
    }

    return($DB->get_field_sql($sql.$sqljoin.$sqlwhere, $params));
}



/**
 * Get blog comments in a format compatable with RSS lib
 *
 * @param int $blogid
 * @param int $bloginstancesid
 * @param int $postid
 * @param object $user
 * @param int $allowedvisibility
 * @param int $groupid
 * @param object $cm
 * @param object $tsblog
 * @param int $individualid
 * @return array
 */
function tsblog_get_feed_comments($blogid, $bloginstancesid, $postid, $user, $allowedvisibility,
        $groupid, $cm, $tsblog, $individualid = -1) {
    global $CFG, $DB;
    $params = array();
    $items = array();

    if ($postid) {
        $sqlwhere = "AND p.id = ? ";
        $params[] = $postid;
    } else if ($bloginstancesid) {
        $sqlwhere = "AND p.tsbloginstancesid = ? ";
        $params[] = $bloginstancesid;
    } else {
        $sqlwhere = "AND i.tsblogid = ? ";
        $params[] = $blogid;
    }
    if ($individualid > 0 || $tsblog->individual > TS_BLOG_NO_INDIVIDUAL_BLOGS) {
        $capable = tsblog_individual_has_permissions($cm, $tsblog, $groupid, $individualid, $user->id);
        tsblog_individual_add_to_sqlwhere($sqlwhere, $params, 'i.userid', $tsblog->id, $groupid, $individualid, $capable);
    } else {
        if (isset($groupid) && $groupid) {
            $sqlwhere .= " AND p.groupid = ? ";
            $params[] = $groupid;
        } else if (!empty($cm->groupingid)) {
            if ($groups = $DB->get_records('groupings_groups',
                    array('groupingid' => $cm->groupingid), null, 'groupid')) {
                $sqlwhere .= " AND p.groupid ";
                list ($grpssql, $grpsparams) = $DB->get_in_or_equal(array_keys($groups));
                $params = array_merge($params, $grpsparams);
                $sqlwhere .= $grpssql;
            }
        }
    }

    $sql = "SELECT p.title AS posttitle, p.message AS postmessage, c.id, c.postid, c.title,
                    c.message AS description, c.timeposted AS pubdate, c.authorname, c.authorip,
                    c.timeapproved, i.userid, u.firstname, u.lastname, u.picture, u.imagealt,
                    u.email, u.idnumber
            FROM {tsblog_comments} c
            INNER JOIN {tsblog_posts} p ON c.postid = p.id
            INNER JOIN {tsblog_instances} i ON p.tsbloginstancesid = i.id
            LEFT JOIN {user} u ON c.userid = u.id
            WHERE c.deletedby IS NULL AND p.deletedby IS NULL
            AND p.visibility >= $allowedvisibility $sqlwhere
            ORDER BY GREATEST(c.timeapproved, c.timeposted) DESC ";

    $rs = $DB->get_recordset_sql($sql, $params, 0, TS_BLOG_MAX_FEED_ITEMS);
    $modcontext = get_context_instance(CONTEXT_MODULE, $cm->id);

    foreach ($rs as $item) {
        $item->link = $CFG->wwwroot.'/mod/tsblog/viewpost.php?post='.$item->postid;
        // Rewrite image urls in tsblog posts comments.
        $item->description = file_rewrite_pluginfile_urls($item->description,
                'mod/tsblog/pluginfile.php', $modcontext->id, 'mod_tsblog',
                'messagecomment', $item->id);

        if ($item->title) {
            $item->description = "<h3>" . s($item->title) . "</h3>"
                    . $item->description;
        }

        // Add post title if there, otherwise add shorten post message instead.
        if ($item->posttitle) {
            $linktopost = get_string('re', 'tsblog', $item->posttitle);
        } else {
            $linktopost = get_string('re', 'tsblog', html_to_text(shorten_text($item->postmessage)));
        }
        $item->title = $linktopost;
        $item->author = fullname($item);

        // For moderated posts, use different data
        if ($item->authorname) {
            // Author name from name instead of user field
            $item->authorname = $item->author;

            // Keep posted time just in case
            $item->timeposted = $item->pubdate;

            // Publication date = approval time (should be consistent with
            // expected feed behaviour)
            $item->pubdate = $item->timeapproved;
        }

        $items[] = $item;
    }
    $rs->close();
    return($items);
}



/**
 * Get post in a format compatable with RSS lib
 *
 * @param int $blogid
 * @param int $bloginstancesid
 * @param object $user
 * @param bool $allowedvisibility
 * @param int $groupid
 * @param object $cm
 * @return array
 */
function tsblog_get_feed_posts($blogid, $bloginstance, $user, $allowedvisibility, $groupid, $cm, $tsblog, $individualid=-1) {
    global $CFG, $DB;
    $params = array();
    $items = array();

    if ($bloginstance) {
        $sqlwhere = "AND p.tsbloginstancesid = ? ";
        $params[] = $bloginstance->id;
    } else {
        $sqlwhere = "AND i.tsblogid = ? ";
        $params[] = $blogid;
    }
    // If individual blog.
    if ($individualid > 0 || $tsblog->individual > TS_BLOG_NO_INDIVIDUAL_BLOGS) {
        $capable = tsblog_individual_has_permissions($cm, $tsblog, $groupid, $individualid, $user->id);
        tsblog_individual_add_to_sqlwhere($sqlwhere, $params, 'i.userid', $tsblog->id, $groupid, $individualid, $capable);
    } else {// No individual blog.
        if ($groupid) {
            $sqlwhere .= " AND p.groupid = ? ";
            $params[] = $groupid;
        } else if (!empty($cm->groupingid)) {
            if ($groups = $DB->get_records('groupings_groups', array('groupingid' => $cm->groupingid), null, 'groupid')) {
                $sqlwhere .= "AND p.groupid IN (".implode(',', array_keys($groups)).") ";
            }
        }
    }
    // Scheme URL for tags
    $scheme = $CFG->wwwroot . '/mod/tsblog/';
    if ($tsblog->global) {
        if (!$bloginstance) {
            $scheme .= 'allposts.php?tag=';
        } else {
            $scheme .= 'view.php?user=' . $bloginstance->userid . '&tag=';
        }
    } else {
        $scheme .= 'view.php?id=' . $cm->id;
        if ($groupid) {
            $scheme .= '&group=' . $groupid;
        }
        $scheme .= '&tag=';
    }

    // Get posts
    $sql = "SELECT p.id, p.title, p.message AS description, p.timeposted AS pubdate, i.userid, u.firstname, u.lastname, u.email, u.picture, u.imagealt, u.idnumber
            FROM {tsblog_posts} p
            INNER JOIN {tsblog_instances} i ON p.tsbloginstancesid = i.id
            INNER JOIN {user} u ON i.userid = u.id
            WHERE p.deletedby IS NULL AND p.visibility >= $allowedvisibility $sqlwhere
            ORDER BY p.timeposted DESC ";

    $rs = $DB->get_recordset_sql($sql, $params, 0, TS_BLOG_MAX_FEED_ITEMS);
    $modcontext = get_context_instance(CONTEXT_MODULE, $cm->id);
    foreach ($rs as $item) {
        $item->link = $CFG->wwwroot.'/mod/tsblog/viewpost.php?post='.$item->id;
        $item->author = fullname($item);
        $item->tags = array();
        $item->tagscheme = $scheme;
        // Feeds do not allow blank titles
        if ((string)$item->title === '') {
            $item->title = html_to_text(shorten_text($item->description));
        }
        // Rewrite image urls in tsblog posts.
        $item->description = file_rewrite_pluginfile_urls($item->description,
                'mod/tsblog/pluginfile.php', $modcontext->id,
                'mod_tsblog', 'message', $item->id);
        $items[$item->id] = $item;
    }
    $rs->close();

    // Get all tags related to these posts and fill them in
    $sql = "SELECT p.id AS postid, t.id AS tagid, t.tag
            FROM {tsblog_posts} p
            INNER JOIN {tsblog_instances} i ON p.tsbloginstancesid = i.id
            INNER JOIN {tsblog_taginstances} ti ON p.id = ti.postid
            INNER JOIN {tsblog_tags} t ON ti.tagid = t.id
            WHERE p.deletedby IS NULL AND p.visibility >= $allowedvisibility $sqlwhere";

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach ($rs as $tag) {
        if (array_key_exists($tag->postid, $items)) {
            $items[$tag->postid]->tags[$tag->tagid] = $tag->tag;
        }
    }
    $rs->close();
    return($items);
}



/**
 * Get a url to a feed
 *
 * @param string $format atom or rss
 * @param object $tsblog
 * @param object $bloginstance
 * @param int $groupid
 * @param bool $comments
 * @param int $postid
 * @param unknown_type $context
 * @return string
 * @uses $CFG
 * @uses $USER
 */
function tsblog_get_feedurl($format, $tsblog, $bloginstance, $groupid, $comments, $postid, $cm, $individualid=0) {
    global $CFG, $USER;
    $url  = $CFG->wwwroot.'/mod/tsblog/feed.php';
    $url .= '?format='.$format;
    $url .= '&amp;blog='.$tsblog->id;
    if ($tsblog->global) {
        if ((is_null($bloginstance) || is_string($bloginstance) && $bloginstance=='all')) {
            $url .= '&amp;bloginstance=all';
            $accesstoken = $tsblog->accesstoken;
        } else {
            $url .= '&amp;bloginstance='.$bloginstance->id;
            $accesstoken = $bloginstance->accesstoken;
        }
    } else {
        $accesstoken = $tsblog->accesstoken;
    }

    if ($groupid) {
        $url .= '&amp;group='.$groupid;
    }
    // If individual blog.
    if ($individualid > 0) {
        $url .= '&amp;individual='.$individualid;
    }

    $url .= '&amp;comments='.$comments;

    // Visibility level
    if (isloggedin() && !isguestuser()) {
        $url .= '&amp;viewer='.$USER->id;
        // Don't use the 'full' token in personal blogs. We don't need personal
        // blog feeds to include draft posts, even for the user (who's the only
        // one allowed to see them) and it generates potential confusion.
        if (!$tsblog->global && tsblog_can_post($tsblog, 0, $cm)) {
            // Full token changed to v2 after a security issue
            $url .= '&amp;full='.md5($accesstoken.$USER->id.TS_BLOG_VISIBILITY_COURSEUSER . 'v2');
        } else {
            $url .= '&amp;loggedin='.md5($accesstoken.$USER->id.TS_BLOG_VISIBILITY_LOGGEDINUSER);
        }
    }

    return($url);
}



/**
 * Get a block containing links to the Atom and RSS feeds
 *
 * @param object $tsblog
 * @param object $bloginstance
 * @param int $groupid
 * @param int $postid
 * @param object $context
 * @return string HTML of block
 * @uses $CFG
 */
function tsblog_get_feedblock($tsblog, $bloginstance, $groupid, $postid, $cm, $individualid=-1) {
    global $CFG, $OUTPUT;

    if (!$CFG->enablerssfeeds) {
        return(false);
    }

    $blogurlatom = tsblog_get_feedurl('atom',  $tsblog, $bloginstance, $groupid, false, false, $cm, $individualid);
    $blogurlrss = tsblog_get_feedurl('rss',  $tsblog, $bloginstance, $groupid, false, false, $cm, $individualid);

    if (!is_string($bloginstance)) {
        $commentsurlatom = tsblog_get_feedurl('atom',  $tsblog, $bloginstance, $groupid, true, $postid, $cm, $individualid);
        $commentsurlrss = tsblog_get_feedurl('rss',  $tsblog, $bloginstance, $groupid, true, $postid, $cm, $individualid);
    }

    $html  = '<div id="tsblog-feedtext">' . get_string('subscribefeed', 'tsblog',
            tsblog_get_displayname($tsblog)) . '</div>';
    $html .= $OUTPUT->help_icon('feedhelp', 'tsblog');
    $html .= '<div class="tsblog-feedlinks">';
    $html .= get_string('blogfeed', 'tsblog', tsblog_get_displayname($tsblog, true)).': ';
    $html .= '<a href="'.$blogurlatom.'">'.get_string('atom', 'tsblog').'</a> ';
    $html .= '<a href="'.$blogurlrss.'">'.get_string('rss', 'tsblog').'</a>';

    if ($tsblog->allowcomments) {
        if (!is_string($bloginstance)) {
            $html .= '<div class="tsblog-links">'.get_string('commentsfeed', 'tsblog').': ';
            $html .= '<br/><a href="'.$commentsurlatom.'">'.get_string('comments', 'tsblog').' '.get_string('atom', 'tsblog').'</a> ';
            $html .= '<br/><a href="'.$commentsurlrss.'">'.get_string('comments', 'tsblog').' '.get_string('rss', 'tsblog').'</a>';
            $html .= '</div>';
        }
    }
    $html .= '</div>';
    return ($html);
}



/**
 * Get extra meta tags that need to go into the page header
 *
 * @param object $tsblog
 * @param object $bloginstance
 * @param int $groupid
 * @param object $context
 * @return string
 */
function tsblog_get_meta_tags($tsblog, $bloginstance, $groupid, $cm) {
    global $CFG;

    $meta = '';
    $blogurlatom = tsblog_get_feedurl('atom',  $tsblog, $bloginstance, $groupid, false, false, $cm);
    $blogurlrss = tsblog_get_feedurl('rss',  $tsblog, $bloginstance, $groupid, false, false, $cm);

    if ($CFG->enablerssfeeds) {
        $meta .= '<link rel="alternate" type="application/atom+xml" title="'.get_string('atomfeed', 'tsblog').'" href="'.$blogurlatom .'" />';
        $meta .= '<link rel="alternate" type="application/atom+xml" title="'.get_string('rssfeed', 'tsblog').'" href="'.$blogurlrss .'" />';
    }

    return ($meta);
}



/**
 * replace a variable withing a querystring
 *
 * @param string $url
 * @param string $replacekey
 * @param string $newvalue
 * @return string
 */
function tsblog_replace_url_param($url, $replacekey, $newvalue=null) {

    $urlparts = parse_url(html_entity_decode($url));

    $queryparts = array();

    parse_str($urlparts['query'], $queryparts);

    unset($queryparts[$replacekey]);

    if ($newvalue) {
        $queryparts[$replacekey] = $newvalue;
    }

    foreach ($queryparts as $key => $value) {
        $queryparts[$key] = "$key=$value";
    }
    $url = $urlparts['path'].'?'.implode('&amp;', $queryparts);

    return($url);
}

/** @return True if OU search extension is installed */
function tsblog_search_installed() {
    return @include_once(dirname(__FILE__).'/../../local/ousearch/searchlib.php');
}

/**
 * Obtains a search document relating to a particular blog post.
 *
 * @param object $post Post object. Required fields: id (optionally also
 *   groupid, userid save a db query)
 * @param object $cm Course-module object. Required fields: id, course
 * @return ousearch_doument
 */
function tsblog_get_search_document($post, $cm) {
    global $DB;
    // Set up 'search document' to refer to this post
    $doc=new local_ousearch_document();
    $doc->init_module_instance('tsblog', $cm);
    if (!isset($post->userid) || !isset($post->groupid)) {
        $results=$DB->get_record_sql("
SELECT
    p.groupid,i.userid
FROM
{tsblog_posts} p
    INNER JOIN {tsblog_instances} i ON p.tsbloginstancesid=i.id
WHERE
    p.id= ?", array($post->id));
        if (!$results) {
            print_error('invalidblogdetails', 'tsblog');
        }
        $post->userid=$results->userid;
        $post->groupid=$results->groupid;
    }
    if ($post->groupid) {
        $doc->set_group_id($post->groupid);
    }
    $doc->set_user_id($post->userid);
    $doc->set_int_refs($post->id);
    return $doc;
}

/**
 * Obtains tags for a $post object whether or not it currently has them
 * defined in some way. (If they're not defined, uses a database query.)
 *
 * @param object $post Post object, must contain ->id at least
 * @param bool $includespaces If true, replaces the _ with space again
 * @return array Array of tags (may be empty)
 */
function tsblog_get_post_tags($post, $includespaces = false) {
    global $CFG, $DB;

    // Work out tags from existing data if possible (to save adding a query)
    if (isset($post->tags)) {
        $taglist=tsblog_clarify_tags($post->tags);
    } else {
        // Tags aren't in post so use database query
        $rs=$DB->get_recordset_sql("
SELECT
    t.tag
FROM
    {tsblog_taginstances} ti
    INNER JOIN {tsblog_tags} t ON ti.tagid = t.id
WHERE
    ti.postid=?", array($post->id));
        $taglist=array();
        foreach ($rs as $rec) {
            $taglist[]=$rec->tag;
        }
        $rs->close();
    }
    if ($includespaces) {
        foreach ($taglist as $ix => $tag) {
            // Make the spaces in tags back into spaces so they're searchable (sigh)
            $taglist[$ix]=str_replace('_', ' ', $tag);
        }
    }

    return $taglist;
}

/**
 * Updates the fulltext search information for a post which is being added or
 * updated.
 * @param object $post Post data, including slashes for database. Must have
 *   fields id,userid,groupid (if applicable), title, message
 * @param object $cm Course-module
 * @return True if search update was successful
 */
function tsblog_search_update($post, $cm) {
    // Do nothing if OU search is not installed
    if (!tsblog_search_installed()) {
        return true;
    }

    // Get search document
    $doc=tsblog_get_search_document($post, $cm);

    // Sort out tags for use as extrastrings
    $taglist=tsblog_get_post_tags($post, true);
    if (count($taglist)==0) {
        $taglist=null;
    }

    // Update information about this post (works ok for add or edit)
    $doc->update($post->title, $post->message, null, null, $taglist);
    return true;
}

function tsblog_date($time, $insentence = false) {
    if (function_exists('specially_shrunken_date')) {
        return specially_shrunken_date($time, $insentence);
    } else {
        return userdate($time);
    }
}

/**
 * Creates navigation for a blog page header.
 * @param object $cm Moodle course-modules object
 * @param object $tsblog Row object from 'tsblog' table
 * @param object $tsbloginstance Row object from 'tsbloginstance' table
 * @param object $tsbloguser Moodle user object
 * @param array $extranav Optional additional navigation entry; may be an array
 *   of nav entries, a single nav entry (array with
 *   'name', optional 'link', and 'type' fields), or null for none
 * @return object Navigation item object
 */
function tsblog_build_navigation($tsblog, $tsbloginstance, $tsbloguser) {
    global $PAGE;
    if ($tsblog->global && !empty($tsbloguser)) {
        $PAGE->navbar->add(fullname($tsbloguser), new moodle_url('/user/view.php', array('id'=>$tsbloguser->id)));
        $PAGE->navbar->add(format_string($tsbloginstance->name), new moodle_url('/mod/tsblog/view.php', array('user'=>$tsbloguser->id)));
    }
}

/*///////////////////////////////*/
// Constants for individual blogs.
define('TS_BLOG_NO_INDIVIDUAL_BLOGS', 0);
define('TS_BLOG_SEPARATE_INDIVIDUAL_BLOGS', 1);
define('TS_BLOG_VISIBLE_INDIVIDUAL_BLOGS', 2);

/**
 * Get an object with details of individual activity
 * @param $cm
 * @param $urlroot
 * @param $tsblog
 * @param $currentgroup
 * @param $context
 * @return an object
 */
function tsblog_individual_get_activity_details($cm, $urlroot, $tsblog, $currentgroup, $context) {
    global $CFG, $USER, $SESSION, $OUTPUT;
    if (strpos($urlroot, 'http') !== 0) { // Will also work for https
        debugging('tsblog_print_individual_activity_menu requires absolute URL for ' .
            '$urlroot, not <tt>' . s($urlroot) . '</tt>. Example: ' .
            'tsblog_print_individual_activity_menu($cm, $CFG->wwwroot . \'/mod/mymodule/view.php?id=13\');',
        DEBUG_DEVELOPER);
    }
    // get groupmode and individualmode
    $groupmode = tsblog_get_activity_groupmode($cm);
    $individualmode = $tsblog->individual;

    // No individual blogs.
    if ($individualmode == TS_BLOG_NO_INDIVIDUAL_BLOGS) {
        return '';
    }
    // If no groups or 'all groups' selection' ($currentgroup == 0).
    if ($groupmode == NOGROUPS || $currentgroup == 0) {
        // Visible individual.
        if ($individualmode == TS_BLOG_VISIBLE_INDIVIDUAL_BLOGS) {
            $allowedindividuals = tsblog_individual_get_all_users($cm->course, $cm->instance);
        }
        // Separate individual (check capability of present user.
        if ($individualmode == TS_BLOG_SEPARATE_INDIVIDUAL_BLOGS) {
            $allowedindividuals = tsblog_individual_get_all_users($cm->course, $cm->instance, 0, $context);
        }
    }

    // If a group is selected ($currentgroup > 0).
    if ($currentgroup > 0) {
        // Visible individual.
        if ($individualmode == TS_BLOG_VISIBLE_INDIVIDUAL_BLOGS) {
            $allowedindividuals = tsblog_individual_get_all_users($cm->course, $cm->instance, $currentgroup );
        }
        // Separate individual.
        if ($individualmode == TS_BLOG_SEPARATE_INDIVIDUAL_BLOGS) {
            $allowedindividuals = tsblog_individual_get_all_users($cm->course, $cm->instance, $currentgroup, $context);
        }
    }
    $activeindividual = tsblog_individual_get_active_user($allowedindividuals, $individualmode, true);

    // Setup the drop-down menu.
    $menu = array();
    if (count($allowedindividuals) > 1) {
        if ($currentgroup > 0) {// Selected group.
            $menu[0] = get_string('viewallusersingroup', 'tsblog');
        } else {// No groups or all groups.
            $menu[0] = get_string('viewallusers', 'tsblog');
        }
    }

    if ($allowedindividuals) {
        foreach ($allowedindividuals as $user) {
            $menu[$user->id] = format_string($user->firstname . '&nbsp;' . $user->lastname);
        }
    }

    if ($individualmode == TS_BLOG_VISIBLE_INDIVIDUAL_BLOGS) {
        $label = get_string('visibleindividual', 'tsblog') . '&nbsp;';
    } else {
        $label = get_string('separateindividual', 'tsblog') . '&nbsp;';
    }

    $output = "";

    if (count($menu) == 1) {
        $name = reset($menu);
        $output = $label.':&nbsp;'.$name;
    } else {
        foreach ($menu as $value => $item) {
            $url = $urlroot.'&amp;individual='.$value;
            $url = str_replace($CFG->wwwroot, '', $url);
            $url = str_replace('&amp;', '&', $url);
            $urls[$url] = $item;
            if ($activeindividual == $value) {
                $active = $url;
            }
        }
        if (!empty($urls)) {
            $select = new url_select($urls, $active, null, 'selectindividual');

            $select->set_label($label);

            $output = $OUTPUT->render($select);
        }
    }

    $output = '<div class="tsblog-individualselector">'.$output.'</div>';
    // Set up the object details needed.
    $individualdetails = new stdClass;
    $individualdetails->display = $output;
    $individualdetails->activeindividual = $activeindividual;
    $individualdetails->mode = $individualmode;
    $individualdetails->userids = array_keys($allowedindividuals);
    $individualdetails->newblogpost = true;

    // Hid the "New blog post" button.
    if ((($activeindividual == 0) && !array_key_exists($USER->id, $allowedindividuals))
        ||($activeindividual > 0 && $activeindividual != $USER->id)) {
        $individualdetails->newblogpost = false;
    }
    return $individualdetails;
}


function tsblog_individual_get_all_users($courseid, $tsblogid, $currentgroup=0, $context=0) {
    global $CFG, $USER;
    // Add present user to the list.
    $currentuser = array();
    $user = new stdClass;
    $user->firstname = $USER->firstname;
    $user->lastname = $USER->lastname;
    $user->id = $USER->id;
    $currentuser[$USER->id] = $user;
    if ($context && !has_capability('mod/tsblog:viewindividual', $context)) {
        return $currentuser;
    }
    // No groups or all groups selected.
    if ($currentgroup == 0) {
        $userswhoposted = tsblog_individual_get_users_who_posted_to_this_blog($tsblogid);
    } else if ($currentgroup > 0) {// A group is selected.
        // Users who posted to the blog and belong to the selected group.
        $userswhoposted = tsblog_individual_get_users_who_posted_to_this_blog($tsblogid, $currentgroup);
    }

    if (!$userswhoposted) {
        $userswhoposted = array();
    }
    $keys = array_keys($userswhoposted);
    if (in_array($USER->id, $keys)) {
        return $userswhoposted;
    } else {
        if ($currentgroup == 0 || groups_is_member($currentgroup, $USER->id)) {
            return $currentuser + $userswhoposted;
        } else {
            return $userswhoposted;
        }
    }
}


function tsblog_individual_get_users_who_posted_to_this_blog($tsblogid, $currentgroup=0) {
    global $DB;
    $params = array();
    if ($currentgroup > 0) {
        $sql = "SELECT u.id, u.firstname, u.lastname
                FROM {user} u
                INNER JOIN {tsblog_instances} bi
                ON bi.tsblogid = ? AND bi.userid = u.id
                INNER JOIN {groups_members} gm
                ON bi.userid = gm.userid
                WHERE gm.groupid = ?
                ORDER BY u.firstname ASC, u.lastname ASC";
        $params[] = $tsblogid;
        $params[] = $currentgroup;
    } else {
        $sql = "SELECT u.id, u.firstname, u.lastname
            FROM {user} u
            JOIN {tsblog_instances} bi
            ON bi.tsblogid = $tsblogid AND bi.userid = u.id
            ORDER BY u.firstname ASC, u.lastname ASC";
    }
    if ($userswhoposted = $DB->get_records_sql($sql, $params)) {
        return $userswhoposted;
    }
    return array();
}


function tsblog_individual_get_active_user($allowedindividuals, $individualmode, $update=false) {
    global $USER, $SESSION;

    // Only one userid in the list (this could be $USER->id or any other userid).
    if (count($allowedindividuals) == 1) {
        $chosenindividual =  array_keys($allowedindividuals);
        return $chosenindividual[0];
    }
    if (!property_exists($SESSION, 'tsblog_individualid')) {
        $SESSION->tsblog_individualid = '';
    }

    // set new active individual if requested
    $changeindividual = optional_param('individual', -1, PARAM_INT);

    if ($update && $changeindividual != -1) {
        $SESSION->tsblog_individualid = $changeindividual;
    } else if (isset($SESSION->tsblog_individualid)) {
        return $SESSION->tsblog_individualid;
    } else {
        $SESSION->tsblog_individualid = $USER->id;
    }
    return $SESSION->tsblog_individualid;
}


function tsblog_individual_has_permissions($cm, $tsblog, $groupid, $individualid=-1, $userid=0) {
    global $USER;
    if (!$userid) {
        $userid = $USER->id;
    }

    // Chosen an individual user who is the logged-in user.
    if ($individualid > 0 && $userid == $individualid) {
        return true;
    }

    // Get context.
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    // No individual blogs.
    $individualmode = $tsblog->individual;
    if ($individualmode == TS_BLOG_NO_INDIVIDUAL_BLOGS) {
        return true;
    }

    $groupmode = tsblog_get_activity_groupmode($cm);

    // Separate individual.
    if ($individualmode == TS_BLOG_SEPARATE_INDIVIDUAL_BLOGS) {
        if (!has_capability('mod/tsblog:viewindividual', $context, $userid)) {
            return false;
        }

        // No group.
        if ($groupmode == NOGROUPS) {
            return true;
        }

        // Chosen a group.
        if ($groupid > 0) {
            // Visible group.
            if ($groupmode == VISIBLEGROUPS) {
                return true;
            }
            // Has access to all groups.
            if (has_capability('moodle/site:accessallgroups', $context, $userid)) {
                return true;
            }
            // Same group.
            if (groups_is_member($groupid, $userid) &&
                groups_is_member($groupid, $individualid)) {
                return true;
            }
            return false;
        } else {
            if (has_capability('moodle/site:accessallgroups', $context, $userid)) {
                return true;
            }
        }

        return false;
    } else if ($individualmode == TS_BLOG_VISIBLE_INDIVIDUAL_BLOGS) {// Visible individual.
        // No group.
        if ($groupmode == NOGROUPS) {
            return true;
        }
        // Visible group.
        if ($groupmode == VISIBLEGROUPS) {
            return true;
        }
        // Separate groups.
        if ($groupmode == SEPARATEGROUPS) {
            // Have accessallgroups.
            if (has_capability('moodle/site:accessallgroups', $context, $userid)) {
                return true;
            }
            // If they don't have accessallgroups then they must select a group
            if (!$groupid) {
                return false;
            }
            // Chosen individual.
            if ($individualid > 0) {
                // Same group.
                if (groups_is_member($groupid, $userid) &&
                    groups_is_member($groupid, $individualid)) {
                    return true;
                }
                return false;
            } else {
                // Chosen all users in the group.
                if (groups_is_member($groupid, $userid)) {
                    return true;
                }
                return false;
            }
        }
        return false;
    }
    return false;
}


function tsblog_individual_add_to_sqlwhere(&$sqlwhere, &$params, $userfield, $tsblogid, $groupid=0, $individualid=0, $capable=true) {
    // Has not capability.
    if (!$capable) {
        return;
    }

    // Only one user is chosen.
    if ($individualid > 0) {
        $sqlwhere .= " AND $userfield = ? ";
        $params[] = $individualid;
        return;
    }

    // A list of user is chosen.
    $from = " FROM {tsblog_instances} bi ";
    $where = " WHERE bi.tsblogid=$tsblogid ";

    // Individuals within a group.
    if (isset($groupid) && $groupid > 0) {
        $from .= " INNER JOIN {groups_members} gm
                    ON bi.userid = gm.userid";
        $where .= " AND gm.groupid= ? ";
        $params[] = $groupid;
        $where .= " AND bi.userid=gm.userid ";
    }
    $subsql =  "SELECT bi.userid $from $where";
    $sqlwhere .= " AND $userfield IN ($subsql)";
}

/**
 * Get last-modified time for blog, as it appears to this user. This takes into
 * account the user's groups/individual settings if required. Only works on
 * course blogs. (Does not check that user can view the blog.)
 * @param object $cm Course-modules entry for wiki
 * @param object $Course Course object
 * @param int $userid User ID or 0 = current
 * @return int Last-modified time for this user as seconds since epoch
 */
function tsblog_get_last_modified($cm, $course, $userid=0) {
    global $USER, $DB;
    if (!$userid) {
        $userid = $USER->id;
    }

    // Get blog record and groupmode
    if (!($tsblog = $DB->get_record('tsblog', array('id'=>$cm->instance)))) {
        return false;
    }
    $groupmode = tsblog_get_activity_groupmode($cm, $course);

    // Default applies no restriction
    $restrictjoin = '';
    $restrictwhere = '';
    $rwparam = array();
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    // Restrict to separate groups
    if ($groupmode == SEPARATEGROUPS &&
        !has_capability('moodle/site:accessallgroups', $context, $userid)) {

        if ($tsblog->individual) {
            // In individual mode, group restriction works by shared grouping
            $restrictjoin .= "
INNER JOIN {groups_members} gm2 ON gm2.userid = bi.userid
INNER JOIN {groups} g ON g.id = gm2.groupid";
            $groupfield = "g.id";
            $restrictwhere .= "
AND g.courseid = ?";
            $rwparam[] = $course->id;
        } else {
            // Outside individual mode, group restriction works based on groupid
            // in post.
            $groupfield = "p.groupid";
        }
        $restrictjoin .= "
INNER JOIN {groups_members} gm ON gm.groupid = $groupfield";
        $restrictwhere .= "
AND gm.userid = ?";
        $rwparam[] = $userid;

        if ($cm->groupingid) {
            $restrictjoin .= "
INNER JOIN {groupings_groups} gg ON gg.groupid = $groupfield";
            $restrictwhere .= "
AND gg.groupingid = ?";
            $rwparam[] = $cm->groupingid;
        }
    }

    // Restrict to separate individuals
    if ($tsblog->individual == TS_BLOG_SEPARATE_INDIVIDUAL_BLOGS &&
        !has_capability('mod/tsblog:viewindividual', $context, $userid)) {
        // Um, only your own blog
        $restrictwhere .= "
AND bi.userid = ?";
        $rwparam[] = $userid;
    }

    // Query for newest version that follows these restrictions.
    $result = $DB->get_field_sql("
SELECT
    MAX(p.timeposted)
FROM
    {tsblog_posts} p
    INNER JOIN {tsblog_instances} bi ON p.tsbloginstancesid = bi.id
    $restrictjoin
WHERE
    bi.tsblogid = ?
    AND p.timedeleted IS NULL
    $restrictwhere", array_merge(array($tsblog->id), $rwparam));
    return $result;
}

/**
 * For moderated users; rate-limits comments by IP address.
 * @return True if user has made too many comments within past hour
 */
function tsblog_too_many_comments_from_ip() {
    global $DB;
    $ip = getremoteaddr();
    $hourago = time() - 3600;
    $count = $DB->count_records_sql("SELECT COUNT(1) FROM " .
        "{tsblog_comments_moderated} WHERE authorip = ? " .
        "AND timeposted > ?", array($ip, $hourago));
    // Max comments per hour = 10 at present
    return $count > 10;
}

/**
 * Works out the typical time it takes a given user to approve blog entries,
 * based on all entries from the past year.
 * @param int $userid User ID
 * @return string String representation of typical time e.g. '24 hours' or
 *   '3 days', or false if unable to estimate yet
 */
function tsblog_get_typical_approval_time($userid) {
    global $DB;

    // Get delays for all the posts they approved in the past year
    $rs = $DB->get_recordset_sql("
SELECT (bc.timeapproved - bc.timeposted) AS delay FROM {tsblog_comments} bc
INNER JOIN {tsblog_posts} bp on bc.postid = bp.id
INNER JOIN {tsblog_instances} bi on bp.tsbloginstancesid = bi.id
WHERE
bi.userid=?
AND bc.userid IS NULL
ORDER BY (bc.timeapproved - bc.timeposted)", array($userid));
    if (empty($rs)) {
        print_error('invalidblog', 'tsblog');
    }
    $times = array();
    foreach ($rs as $rec) {
        $times[] = $rec->delay;
    }
    $rs->close();

    // If the author hasn't approved that many comments, don't give an estimate
    if (count($times) < 5) {
        return false;
    }

    // Use the 75th percentile
    $index = floor((count($times) * 3) / 4);
    $delay = $times[$index];

    // If it's less than a day
    if ($delay < 24 * 3600) {
        // Round up to hours (at least 2)
        $delay = ceil($delay/3600);
        if ($delay < 2) {
            $delay = 2;
        }
        return get_string('numhours', '', $delay);
    } else {
        // Round up to days (at least 2)
        $delay = ceil($delay / (24*3600));
        if ($delay < 2) {
            $delay = 2;
        }
        return get_string('numdays', '', $delay);
    }
}

/**
 * Applies high security restrictions to HTML input from moderated comments.
 * Recursive function.
 * @param $element DOM element
 */
function tsblog_apply_high_security(DOMElement $element) {
    // Note that Moodle security should probably already prevent this (and
    // should include a whitelist approach), but just to increase the paranoia
    // level a bit with these comments.
    static $allowtags = array(
        'html' => 1, 'body' => 1,
        'em' => 1, 'strong' => 1, 'b' => 1, 'i' => 1, 'del' => 1, 'sup' => 1,
            'sub' => 1, 'span' => 1, 'a' => 1, 'img' => 1,
        'p' => 1, 'div' => 1
    );

    // Chuck away any disallowed tags
    if (!array_key_exists(strtolower($element->tagName), $allowtags)) {
        $parent = $element->parentNode;
        while ($child = $element->firstChild) {
            $element->removeChild($child);
            $parent->insertBefore($child, $element);
            if ($child->nodeType == XML_ELEMENT_NODE) {
                tsblog_apply_high_security($child);
            }
        }
        $parent->removeChild($element);
        return;
    }

    // Chuck away all attributes except href, src pointing to a folder or HTML, image
    // (this prevents SWF embed by link, if site is unwise enough to have that
    // turned on)
    $attributenames = array();
    $keepattributes = array();
    foreach ($element->attributes as $name => $value) {
        $attributenames[] = $name;
        $keep = false;
        if ($name === 'href' && preg_match('~^https?://~', $value->nodeValue)) {
            $keep = true;
        } else if ($name === 'src' &&
                preg_match('~^https?://.*\.(jpg|jpeg|png|gif|svg)$~', $value->nodeValue)) {
            $keep = true;
        } else if ($name === 'alt') {
            $keep = true;
        }
        if ($keep) {
            $keepattributes[$name] = $value->nodeValue;
        }
    }
    foreach ($attributenames as $name) {
        $element->removeAttribute($name);
    }
    foreach ($keepattributes as $name => $value) {
        $element->setAttribute($name, $value);
    }

    // Recurse to children
    $children = array();
    foreach ($element->childNodes as $child) {
        $children[] = $child;
    }
    foreach ($children as $child) {
        if ($child->nodeType == XML_ELEMENT_NODE) {
            tsblog_apply_high_security($child);
        }
    }
}

/**
 * Adds a new moderated comment into the database ready for approval, and sends
 * an approval email to the moderator (person who owns the blog post).
 * @param object $tsblog Blog object
 * @param object $tsbloginstance Blog instance object
 * @param object $post Blog post object
 * @param object $comment Comment object (including slashes)
 */
function tsblog_add_comment_moderated($tsblog, $tsbloginstance, $post, $comment) {
    global $CFG, $USER, $SESSION, $SITE, $DB;

    // Extra security on moderated comment
    $dom = @DOMDocument::loadHTML('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body><div>' .
            $comment->messagecomment . '</div></body></html>');
    tsblog_apply_high_security($dom->documentElement);
    $html = $dom->saveHTML();
    $start = strpos($html, '<body><div>') + 11;
    $end = strrpos($html, '</div></body>');
    $comment->message = substr($html, $start, $end - $start);
    $comment->title = trim($comment->title);

    // Add comment to database
    unset($comment->userid);
    $comment->timeposted = time();
    $comment->authorip = getremoteaddr();
    // Secret key is half the SHA-1 of a random number plus current time
    $comment->secretkey = substr(sha1(rand() . microtime(true)), 0, 20);
    $comment->id = $DB->insert_record('tsblog_comments_moderated', $comment);
    if (!$comment->id) {
        return false;
    }

    // Get blog owner
    $result = true;
    $user = $DB->get_record('user', array('id'=>$tsbloginstance->userid));
    if (!$user) {
        $result = false;
        $user = (object)array('lang'=>'');
    }

    // Change language temporarily (UGH - this is horrible but there doesn't
    // seem to be a way to do it in moodle without logging in as the other
    // user). This is based on (defeating) the logic in current_language.
    $oldsessionlang = null;
    if (!empty($SESSION->lang)) {
        // If this user has chosen a language in session, turn that off
        $oldsessionlang = $SESSION->lang;
        $SESSION->lang = null;
    }
    $USER->lang = $user->lang;

    // Subject line
    $commenterhtml = s($comment->authorname);
    $a = (object)array(
        'blog' => s($tsblog->global ? $tsbloginstance->name : $tsblog->name),
        'commenter' => $commenterhtml
    );
    $subject = get_string('moderated_emailsubject', 'tsblog', $a);

    // Main text
    $approvebase = $CFG->wwwroot . '/mod/tsblog/approve.php?mcomment=' .
        $comment->id . '&amp;key=' . $comment->secretkey;
    $a = (object)array(
        'postlink' => '<a href="' . $CFG->wwwroot .
            '/mod/tsblog/viewpost.php?post=' . $post->id . '">' .
            ($post->title ? s($post->title) : shorten_text(strip_tags($post->message))) .
            '</a>',
        'commenter' => $commenterhtml,
        'commenttitle' => $comment->title ? $comment->title : '',
        'comment' =>
            format_text($comment->message, FORMAT_MOODLE,
            null, $tsblog->course),
        'approvelink' => $approvebase . '&amp;approve=1',
        'approvetext' => get_string('moderated_approve', 'tsblog'),
        'rejectlink' => $approvebase . '&amp;approve=0',
        'rejecttext' => get_string('moderated_reject', 'tsblog'),
        'restrictpostlink' => $CFG->wwwroot .
            '/mod/tsblog/restrictcomments.php?post=' . $post->id,
        'restrictposttext' => get_string('moderated_restrictpost', 'tsblog'),
        'restrictbloglink' => $CFG->wwwroot .
            '/mod/tsblog/restrictcomments.php?blog=' . $tsblog->id,
        'restrictblogtext' => get_string('moderated_restrictblog', 'tsblog')
    );
    $messagetext = get_string('moderated_emailtext', 'tsblog', $a);
    $messagehtml = get_string('moderated_emailhtml', 'tsblog', $a);
    // hack to remove empty tags when there is no title
    $messagehtml = str_replace('<h3></h3>', '', $messagehtml);
    $result = $result && email_to_user($user, $SITE->fullname,
        $subject, $messagetext, $messagehtml);

    // Put language back
    if ($oldsessionlang) {
        $SESSION->lang = $oldsessionlang;
    }
    $USER->lang = null;

    if (!$result) {
        // Oh well, better delete it from database
        $DB->delete_records('tsblog_comments_moderated', array('id'=>$comment->id));
        return false;
    }
    return true;
}

/**
 * Obtains comments that are awaiting moderation for a particulasr post
 * @param object $tsblog Moodle data object from tsblog
 * @param object $post Moodle data object from tsblog_posts
 * @param bool $includeset If true, includes comments which have already been
 *   rejected or approved, as well as those which await processing
 * @return array Array of Moodle data objects from tsblog_comments_moderated;
 *   empty array (not false) if none; objects are in date order (oldest first)
 */
function tsblog_get_moderated_comments($tsblog, $post, $includeset=false) {
    global $DB;
    // Don't bother checking if public comments are not allowed
    if ($tsblog->allowcomments < TS_BLOG_COMMENTS_ALLOWPUBLIC
            && $post->allowcomments < TS_BLOG_COMMENTS_ALLOWPUBLIC) {
        return array();
    }

    // Query for moderated comments
    $result = $DB->get_records_select('tsblog_comments_moderated',
        ($includeset ? '' : 'approval=0 AND ') . 'postid= ?', array($post->id), 'id');
    return $result ? $result : array();
}

/**
 * Approves or rejects a moderated comment.
 * @param object $mcomment Moderated comment object (no slashes)
 * @param bool $approve True to approve, false to reject
 * @return ID of new comment row, or false if failure
 */
function tsblog_approve_comment($mcomment, $approve) {
    global $DB;
    // Get current time and start transaction
    $now = time();
    $tw = $DB->start_delegated_transaction();;

    // Update the moderated comment record
    $update = (object)array(
        'id' => $mcomment->id,
        'approval' => $approve ? TS_BLOG_MODERATED_APPROVED :
            TS_BLOG_MODERATED_REJECTED,
        'timeset' => $now
    );
    if (!$DB->update_record('tsblog_comments_moderated', $update)) {
        return false;
    }

    // Add the new comment record
    if ($approve) {
        $insert = (object)array(
            'postid' => $mcomment->postid,
            'title' => $mcomment->title,
            'message' => $mcomment->message,
            'timeposted' => $mcomment->timeposted,
            'authorname' => $mcomment->authorname,
            'authorip' => $mcomment->authorip,
            'timeapproved' => $now);
        if (!($id = $DB->insert_record('tsblog_comments', $insert))) {
            return false;
        }
    } else {
        $id = true;
    }

    // Commit transaction and return id
    $tw->allow_commit();
    return $id;
}

/**
 * Gets the extra navigation needed for pages relating to a post.
 * @param object $post Moodle database object for post
 * @param bool $link True if post name should be a link
 */
function tsblog_get_post_extranav($post, $link=true) {
    global $PAGE;
    if ($link) {
        $url = new moodle_url('/mod/tsblog/viewpost.php', array('post'=>$post->id));
    } else {
        $url = null;
    }
    if (!empty($post->title)) {
        $PAGE->navbar->add(format_string($post->title), $url);
    } else {
        $PAGE->navbar->add(shorten_text(format_string($post->message, 30)), $url);
    }
}

class tsblog_portfolio_caller extends portfolio_module_caller_base {

    protected $postid;
    protected $attachment;

    private $post;
    private $keyedfiles = array(); // keyed on entry

    /**
     * @return array
     */
    public static function expected_callbackargs() {
        return array(
            'postid'       => false,
            'attachment'   => false,
        );
    }
    /**
     * @param array $callbackargs
     */
    public function __construct($callbackargs) {
        parent::__construct($callbackargs);
        if (!$this->postid) {
            throw new portfolio_caller_exception('mustprovidepost', 'tsblog');
        }
    }
    /**
     * @global object
     */
    public function load_data() {
        global $DB;

        if ($this->postid) {
            if (!$this->post = tsblog_get_post($this->postid, false)) {
                throw new portfolio_caller_exception('invalidpostid', 'tsblog');
            }
        }

        if (!$this->tsbloginstance = $DB->get_record('tsblog_instances', array('id'=>$this->post->tsbloginstancesid))) {
            throw new portfolio_caller_exception('postid', 'tsblog');
        }

        if (!$this->tsblog = $DB->get_record('tsblog', array('id' => $this->tsbloginstance->tsblogid))) {
            throw new portfolio_caller_exception('invalidpostid', 'tsblog');
        }

        if (!$this->cm = get_coursemodule_from_instance('tsblog', $this->tsblog->id)) {
            throw new portfolio_caller_exception('invalidcoursemodule');
        }

        $this->modcontext = get_context_instance(CONTEXT_MODULE, $this->cm->id);
        $fs = get_file_storage();
        $files = array();
        $attach = $fs->get_area_files($this->modcontext->id, 'mod_tsblog', 'attachment', $this->post->id);
        $embed = $fs->get_area_files($this->modcontext->id, 'mod_tsblog', 'message', $this->post->id);
        if (!empty($this->post->comments)) {
            foreach ($this->post->comments as $comment) {
                $comments = $fs->get_area_files($this->modcontext->id, 'mod_tsblog',
                        'messagecomment', $comment->id);
                $files = array_merge($files, $comments);
            }
        }
        $files = array_merge($attach, $embed, $files);
        $this->set_file_and_format_data($files);
        if (!empty($this->multifiles)) {
            $this->keyedfiles[$this->post->id] = $this->multifiles;
        } else if (!empty($this->singlefile)) {
            $this->keyedfiles[$this->post->id] = array($this->singlefile);
        }
        if (empty($this->multifiles) && !empty($this->singlefile)) {
            $this->multifiles = array($this->singlefile); // copy_files workaround
        }
        // depending on whether there are files or not, we might have to change richhtml/plainhtml
        if (!empty($this->multifiles)) {
            $this->add_format(PORTFOLIO_FORMAT_RICHHTML);
        } else {
            $this->add_format(PORTFOLIO_FORMAT_PLAINHTML);
        }
    }

    /**
     * @global object
     * @return string
     */
    public function get_return_url() {
        global $CFG;
        return $CFG->wwwroot . '/mod/tsblog/viewpost.php?post=' . $this->post->id;
    }
    /**
     * @global object
     * @return array
     */
    public function get_navigation() {
        global $CFG;
        $title = '';
        if (!empty($this->post->title)) {
            $title = format_string($this->post->title);
        } else {
            $title = shorten_text(format_string($this->post->message, 30));
        }
        $navlinks = array();
        $navlinks[] = array(
            'name' => $title,
            'link' => $CFG->wwwroot . '/mod/tsblog/viewpost.php?post=' . $this->post->id,
            'type' => 'title'
        );
        return array($navlinks, $this->cm);
    }
    /**
     * either a whole discussion
     * a single post, with or without attachment
     * or just an attachment with no post
     *
     * @global object
     * @global object
     * @uses PORTFOLIO_FORMAT_RICH
     * @return mixed
     */
    public function prepare_package() {
        global $CFG;

        $posthtml = $this->prepare_post($this->post, true);

        $content = $posthtml;
        $name = 'post.html';
        $manifest = ($this->exporter->get('format') instanceof PORTFOLIO_FORMAT_RICH);
        if (!empty($this->multifiles)) {
            foreach ($this->multifiles as $f) {
                $this->get('exporter')->copy_existing_file($f);
            }
        }
        $this->get('exporter')->write_new_file($content, $name, $manifest);
    }

    /**
     * this is a very cut down version of what is in forum_make_mail_post
     *
     * @global object
     * @param int $post
     * @return string
     */
    protected function prepare_post($post, $usehtmls = true) {
        global $PAGE;
        $output = '';
        if ($usehtmls) {
            $output .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" ' .
                    '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' .
                    html_writer::start_tag('html', array('xmlns' => 'http://www.w3.org/1999/xhtml'));
            $output .= html_writer::tag('head',
                    html_writer::empty_tag('meta',
                    array('http-equiv' => 'Content-Type', 'content' => 'text/html; charset=utf-8')) .
                    html_writer::tag('title', get_string('exportedpost', 'tsblog')));
            $output .= html_writer::start_tag('body') . "\n";
        }
        if (!$tsblog = tsblog_get_blog_from_postid($post->id)) {
            print_error('invalidpost', 'tsblog');
        }
        if (!$cm = get_coursemodule_from_instance('tsblog', $tsblog->id)) {
            print_error('invalidcoursemodule');
        }
        $tsblogoutput = $PAGE->get_renderer('mod_tsblog');
        $context = context_module::instance($cm->id);
        $canmanageposts = has_capability('mod/tsblog:manageposts', $context);

        if ($tsblog->global) {
            $blogtype = 'personal';
        } else {
            $blogtype = 'course';
        }
        $post->allowcomments = false;
        // Provide format from the exporter to renderers incase its required.
        $format = $this->get('exporter')->get('format');
        $output .= $tsblogoutput->render_post($cm, $tsblog, $post, false, $blogtype,
                $canmanageposts, false, false, true, $format);
        if (!empty($post->comments)) {
            $output .= $tsblogoutput->render_comments($post, $tsblog, false, false, true, $cm, $format);
        }
        if ($usehtmls) {
            $output .= html_writer::end_tag('body') . html_writer::end_tag('html');
        }
        return $output;
    }
    /**
     * @return string
     */
    public function get_sha1() {
        $filesha = '';
        try {
            $filesha = $this->get_sha1_file();
        } catch (portfolio_caller_exception $e) {
            // No files.
        }

        return sha1($filesha . ',' . $this->post->title . ',' . $this->post->message);
    }

    public function expected_time() {
        return $this->expected_time_file();
    }
    /**
     * @uses CONTEXT_MODULE
     * @return bool
     */
    public function check_permissions() {
        $context = get_context_instance(CONTEXT_MODULE, $this->cm->id);
        return (has_capability('mod/tsblog:exportpost', $context)
            || ($this->tsbloginstance->userid == $this->user->id
                && has_capability('mod/tsblog:exportownpost', $context)));
    }
    /**
     * @return string
     */
    public static function display_name() {
        return get_string('modulename', 'tsblog');
    }

    public static function base_supported_formats() {
        return array(PORTFOLIO_FORMAT_FILE, PORTFOLIO_FORMAT_RICHHTML, PORTFOLIO_FORMAT_PLAINHTML);
    }
}

/**
 * Returns html for a search form for the nav bar
 * @param string $name blog identifier field e.g. id
 * @param string $value blog identifier value e.g. 266
 * @param string $strblogsearch search this blog text
 * @param string $querytext optional search term
 * @returns string html
 */
function tsblog_get_search_form($name, $value, $strblogsearch, $querytext='') {
    if (!tsblog_search_installed()) {
        return '';
    }
    global $OUTPUT;
    $out = html_writer::start_tag('form', array('action' => 'search.php', 'method' => 'get'));
    $out .= html_writer::start_tag('div');
    $out .= html_writer::tag('label', $strblogsearch . ' ', array('for' => 'tsblog_searchquery'));
    $out .= $OUTPUT->help_icon('searchblogs', 'tsblog');
    $out .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $name,
            'value' => $value));
    $out .= html_writer::empty_tag('input', array('type' => 'text', 'name' => 'query',
            'id' => 'tsblog_searchquery', 'value' => $querytext));
    $out .= html_writer::empty_tag('input', array('type' => 'image',
            'id' => 'ousearch_searchbutton', 'alt' => get_string('search'),
            'title' => get_string('search'), 'src' => $OUTPUT->pix_url('i/search')));
    $out .= html_writer::end_tag('div');
    $out .= html_writer::end_tag('form');
    return $out;
}

/**
 * Checks what level of participation the currently
 * logged in user can view
 *
 * @param object $course current course object
 * @param object $tsblog current tsblog object
 * @param object $cm current course module object
 * @param int $groupid optional group id term
 */
function tsblog_can_view_participation($course, $tsblog, $cm, $groupid=0) {
    global $USER;

    // no participation at all on global blogs
    if ($tsblog->global == 1) {
        return TS_BLOG_NO_PARTICIPATION;
    }

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    $groupmode = groups_get_activity_groupmode($cm, $course);
    $allowgroup =
            ($groupmode == NOGROUPS || $groupmode == VISIBLEGROUPS)
            || (has_capability('moodle/site:accessallgroups', $context))
            || (groups_is_member($groupid, $USER->id));

    if (has_capability('mod/tsblog:viewparticipation', $context)
        && $allowgroup
        && (($tsblog->individual == TS_BLOG_VISIBLE_INDIVIDUAL_BLOGS
        || $tsblog->individual == TS_BLOG_NO_INDIVIDUAL_BLOGS)
        || has_capability('mod/tsblog:viewindividual', $context))) {
        return TS_BLOG_USER_PARTICIPATION;
    } else if ((has_capability('mod/tsblog:post', $context)
        || has_capability('mod/tsblog:comment', $context))
        && $allowgroup) {
        return TS_BLOG_MY_PARTICIPATION;
    }

    return TS_BLOG_NO_PARTICIPATION;
}

/**
 * Checks if current user is allowed to grade the given blog.
 * @param object $course Moodle course object
 * @param object $tsblog Row from tsblog table
 * @param object $cm Course-module object
 * @param int $groupid Optional group id
 * @return bool True if you can grade the blog
 */
function tsblog_can_grade($course, $tsblog, $cm, $groupid=0) {
    global $USER;

    // Cannot grade if blog has grading turned off
    if (!$tsblog->grade) {
        return false;
    }

    // Cannot grade if you do not have the capability
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    if (!has_capability('mod/tsblog:grade', $context)) {
        return false;
    }

    // Grading is a 'write' activity so you can only do it for your own
    // group unless you have accessallgroups
    $groupmode = groups_get_activity_groupmode($cm, $course);
    $ok = $groupmode == NOGROUPS ||
            has_capability('moodle/site:accessallgroups', $context) ||
            ($groupid && groups_is_member($groupid, $USER->id));
    return $ok;
}

/**
 * Returns information about the participation of users in this blog.
 *
 * @param object $tsblog current tsblog object
 * @param object $context current context
 * @param int $groupid optional group id term
 * @param object $cm course-module object
 * @param object $course current course object
 * @param string $sort optional string to sort users by fields
 * @return array user participation
 */
function tsblog_get_participation($tsblog, $context, $groupid=0, $cm,
    $course, $sort='u.firstname,u.lastname') {
    global $DB;

    // get user objects
    list($esql, $params) = get_enrolled_sql($context, 'mod/tsblog:post', $groupid);
    $fields = user_picture::fields('u');
    $fields .= ',u.username,u.idnumber';
    $sql = "SELECT $fields
                FROM {user} u
                JOIN ($esql) eu ON eu.id = u.id
                ORDER BY $sort ASC";
    $users = $DB->get_records_sql($sql, $params);
    if (empty($users)) {
        return array();
    }
    if ($tsblog->individual > 0) {
        $groupid = 0;
    }
    $postswhere = ' WHERE bi.userid IN (' . implode(',', array_keys($users)) .')';
    $commentswhere = ' WHERE c.userid IN (' . implode(',', array_keys($users)) .')';

    $groupcheck = $groupid ? 'AND groupid = :groupid' : '';

    $postssql = 'SELECT bi.userid, p.posts
        FROM {tsblog_instances} bi
        LEFT OUTER JOIN (
            SELECT tsbloginstancesid, COUNT(id) as posts
            FROM {tsblog_posts}
            WHERE timedeleted IS NULL ' . $groupcheck . '
            GROUP BY tsbloginstancesid
        ) p ON p.tsbloginstancesid = bi.id' .
        $postswhere .
        ' AND bi.tsblogid = :tsblogid';

    $commentssql = 'SELECT c.userid, COUNT(c.id) AS comments
        FROM {tsblog_comments} c, {tsblog_instances} bi ' .
        $commentswhere .
        ' AND c.postid IN (
            SELECT id
            FROM {tsblog_posts}
            WHERE tsbloginstancesid = bi.id ' . $groupcheck . '
            AND timedeleted IS NULL
        )
        AND c.timedeleted IS NULL
        AND bi.tsblogid = :tsblogid GROUP BY c.userid';
    $params['tsblogid'] = $tsblog->id;
    $params['groupid'] = $groupid;

    // get all user post information
    $posts = $DB->get_records_sql($postssql, $params);

    // get all user comment information
    $comments = $DB->get_records_sql($commentssql, $params);

    if (!empty($users)) {
        // is grading enabled and available for the current user
        $gradinginfo = null;
        if (tsblog_can_grade($course, $tsblog, $cm, $groupid)) {
            $gradinginfo = grade_get_grades($course->id, 'mod',
                'tsblog', $tsblog->id, array_keys($users));
        }

        foreach ($users as $user) {
            if (!empty($posts) && isset($posts[$user->id])) {
                $user->posts = $posts[$user->id]->posts;
            }
            if (!empty($comments) && isset($comments[$user->id])) {
                $user->comments = $comments[$user->id]->comments;
            }
            if ($gradinginfo && !empty($gradinginfo->items[0]->grades)) {
                if (isset($gradinginfo->items[0]->grades[$user->id])) {
                    $user->gradeobj = $gradinginfo->items[0]->grades[$user->id];
                }
            }
        }
    }

    return $users;
}

/**
 * Returns user participation to view in userparticipation.php
 *
 * @param object $tsblog current tsblog object
 * @param object $context current context
 * @param int $userid required userid term for participation being viewed
 * @param int $groupid optional group id term
 * @param object $cm course-module object
 * @param object $course current course object
 * @param int $start optional start date
 * @param int $end optional end date
 * @return array user participation
 */
function tsblog_get_user_participation($tsblog, $context,
        $userid, $groupid=0, $cm, $course, $start = null, $end = null) {
    global $DB;
    $testgroupid = $groupid;
    if ($tsblog->individual > 0) {
        $testgroupid = 0;
    }
    $groupcheck = $testgroupid ? 'AND groupid = :groupid' : '';
    $period = $cperiod = '';
    if ($start) {
        $period = 'AND timeposted > :timestart ';
    }
    if ($end) {
        $period .= 'AND timeposted < :timeend ';
    }

    $postssql = 'SELECT id, title, message, timeposted
        FROM {tsblog_posts}
        WHERE tsbloginstancesid = (
            SELECT id
            FROM {tsblog_instances}
            WHERE tsblogid = :tsblogid AND userid = :userid
        )
        AND timedeleted IS NULL ' . $groupcheck . $period . '
        ORDER BY timeposted DESC';

    if ($start) {
        $cperiod = 'AND c.timeposted > :timestart ';
    }
    if ($end) {
        $cperiod .= 'AND c.timeposted < :timeend ';
    }
    $commentssql = 'SELECT c.id, c.postid, c.title, c.message, c.timeposted,
        a.id AS authorid, a.firstname, a.lastname,
        p.title AS posttitle, p.timeposted AS postdate
        FROM {user} a, {tsblog_comments} c
            INNER JOIN {tsblog_posts} p ON (c.postid = p.id)
            INNER JOIN {tsblog_instances} bi ON (bi.id = p.tsbloginstancesid)
        WHERE bi.tsblogid = :tsblogid AND a.id = bi.userid
        AND p.timedeleted IS NULL ' . $groupcheck . $cperiod . '
        AND c.userid = :userid AND c.timedeleted IS NULL
            ORDER BY c.timeposted DESC';

    $params = array(
        'tsblogid' => $tsblog->id,
        'userid' => $userid,
        'groupid' => $testgroupid,
        'timestart' => $start,
        'timeend' => $end
    );

    $fields = user_picture::fields();
    $fields .= ',username,idnumber';
    $user = $DB->get_record('user', array('id' => $userid), $fields, MUST_EXIST);
    $participation = new StdClass;
    $participation->user = $user;
    $participation->posts = $DB->get_records_sql($postssql, $params);
    $participation->comments = $DB->get_records_sql($commentssql, $params);
    if (tsblog_can_grade($course, $tsblog, $cm, $groupid)) {
        $gradinginfo = grade_get_grades($course->id, 'mod',
            'tsblog', $tsblog->id, array($userid));
        $participation->gradeobj = $gradinginfo->items[0]->grades[$userid];
    }
    return $participation;
}

/**
 * Grades users from the participation.php page
 *
 * @param array $newgrades array of grade records to update
 * @param array $oldgrades array of old grade records to check
 * @param object $cm current course module object
 * @param object $tsblog current tsblog object
 * @param object $course current course object
 */
function tsblog_update_grades($newgrades, $oldgrades, $cm, $tsblog, $course) {
    global $CFG, $SESSION;

    require_once($CFG->libdir.'/gradelib.php');

    $grades = array();
    foreach ($oldgrades as $key => $user) {
        if (array_key_exists($key, $newgrades)) {
            if (empty($user->gradeobj->grade)
                || ($newgrades[$key] != $user->gradeobj->grade)) {
                $grade = new StdClass;
                $grade->userid = $key;
                $grade->dategraded = time();
                if ($newgrades[$key] == -1) {
                    // no grade
                    $grade->rawgrade = null;
                } else {
                    $grade->rawgrade = $newgrades[$key];
                }
                $tsblog->cmidnumber = $cm->id;

                $grades[$key] = $grade;
            }
        }
    }
    tsblog_grade_item_update($tsblog, $grades);

    // add a message to display to the page
    if (!isset($SESSION->tsbloggradesupdated)) {
        $SESSION->tsbloggradesupdated = get_string('gradesupdated', 'tsblog');
    }
}

// Blog 'discovery'/stats functions.
/**
 * Generates tsblog visitor statistics output.
 * @param object $tsblog
 * @param object $cm
 * @param mod_tsblog_renderer $renderer
 * @param bool $ajax true to return data object rather than html
 */
function tsblog_stats_output_visitstats($tsblog, $cm, $renderer = null, $ajax = false) {
    global $PAGE, $DB;
    if (!$renderer) {
        $renderer = $PAGE->get_renderer('mod_tsblog');
    }
    // This is only for personal blogs (can't support course, individual or group blogs).
    if (!$tsblog->global || ($tsblog->global && ($tsblog->individual != TS_BLOG_NO_INDIVIDUAL_BLOGS
            || $cm->groupmode != NOGROUPS))) {
        return;
    }
    if (isset($_POST['timefilter_visitstats']) && isloggedin()) {
        // Get the posted form value to set user pref (do this from post as need to to init form).
        set_user_preference('mod_tsblog_visitformfilter', $_POST['timefilter_visitstats']);
    }

    $default = get_user_preferences('mod_tsblog_visitformfilter', TS_BLOG_STATS_TIMEFILTER_MONTH);

    // Create time filter options form.
    $customdata = array(
            'options' => array(TS_BLOG_STATS_TIMEFILTER_ALL => get_string('timefilter_alltime', 'tsblog'),
                    TS_BLOG_STATS_TIMEFILTER_MONTH => get_string('activeblogs', 'tsblog')),
            'default' => $default,
            'type' => 'visitstats',
            );
    if ($tsblog->global && $curindividual = optional_param('user', 0, PARAM_INT)) {
        $customdata['params']['user'] = $curindividual;
    }
    if (!$tsblog->global) {
        $customdata['cmid'] = $cm->id;
    }

    $timefilter = new tsblog_stats_timefilter_form(null, $customdata);

    // First, get the stats for this blog.
    list($filtertime, $filterselected) = $timefilter->get_selected_time($default);

    if ($filtertime == 0) {
        // No time filter - just get instances from table.
        if ($tsblog->global && $excludedlist = get_config('mod_tsblog', 'globalusageexclude')) {
            // There are user ids to exclude in the global blog stats.
            $sql = 'SELECT * from {tsblog_instances} WHERE tsblogid =? AND views >0';
            $params = array($tsblog->id);
            list($insql, $inparams) = $DB->get_in_or_equal(explode(',', $excludedlist),
                    SQL_PARAMS_QM, 'param', false);
            $sql .= " AND userid $insql";
            $params = array_merge($params, $inparams);
            $sql .= ' order by views DESC, name ASC';
            $blogs = $DB->get_records_sql($sql, $params, 0, 5);
        } else {
            $blogs = $DB->get_records_select('tsblog_instances', 'tsblogid =? AND views >0',
                    array($tsblog->id), 'views DESC, name ASC', '*', 0, 5);
        }
    } else {
        // Time filter - get instances from sub-query based on matching post criteria.
        $sql = 'SELECT bi.* FROM {tsblog_instances} bi
            WHERE bi.id IN (
                (SELECT bi2.id FROM {tsblog_instances} bi2
                JOIN {tsblog_posts} p on p.tsbloginstancesid = bi2.id
                JOIN {user} u on u.id = bi2.userid
                WHERE bi2.tsblogid = ?
                AND bi2.views > 0
                AND p.deletedby IS NULL AND p.timeposted >= ?';
        $params = array($tsblog->id, $filtertime);
        if ($tsblog->global || ($tsblog->maxvisibility == TS_BLOG_VISIBILITY_PUBLIC && !isloggedin())) {
            // Only include visible posts on global blogs and public blogs when not logged in.
            $sql .= 'AND p.visibility >= ? ';
            if (!isloggedin()) {
                $params[] = TS_BLOG_VISIBILITY_PUBLIC;
            } else {
                $params[] = TS_BLOG_VISIBILITY_LOGGEDINUSER;
            }
        }
        if ($tsblog->global) {
            if ($excludedlist = get_config('mod_tsblog', 'globalusageexclude')) {
                // There are user ids to exclude in the global blog stats.
                list($insql, $inparams) = $DB->get_in_or_equal(explode(',', $excludedlist),
                        SQL_PARAMS_QM, 'param', false);
                $sql .= " AND bi2.userid $insql";
                $params = array_merge($params, $inparams);
            }
        }
        $sql .= ' GROUP BY bi2.id)) ORDER BY bi.views DESC, bi.name ASC';
        $blogs = $DB->get_records_sql($sql, $params, 0, 5);
    }
    // Generate content data ready to send to renderer.
    $maintitle = get_string('visits', 'tsblog');// The title of the 'section';
    if ($filterselected == TS_BLOG_STATS_TIMEFILTER_ALL) {
        $title = get_string('timefilter_alltime', 'tsblog');// Sub-heading.
        $info = get_string('visits_info_alltime', 'tsblog', tsblog_get_displayname($tsblog, true));
    } else {
        $title = get_string('activeblogs', 'tsblog');// Sub-heading.
        $info = get_string('visits_info_active', 'tsblog', tsblog_get_displayname($tsblog));
    }
    $content = '';
    if ($blogs) {
        $maxnum = reset($blogs)->views;
        foreach ($blogs as $blog) {
            // Create the stat view for the blog.
            $percent = $blog->views / $maxnum * 100;
            $stat = get_string('numberviews', 'tsblog', number_format($blog->views));
            if ($tsblog->global) {
                $url = new moodle_url('/mod/tsblog/view.php', array('user' => $blog->userid));
            } else {
                $url = new moodle_url('/mod/tsblog/view.php',
                        array('id' => $cm->id, 'individual' => $blog->userid));
            }
            $user = $DB->get_record('user', array('id' => $blog->userid));
            if ($blog->name == '') {
                $a = (object) array('name' => fullname($user),
                        'displayname' => tsblog_get_displayname($tsblog));
                $blog->name = get_string('defaultpersonalblogname', 'tsblog', $a);
            }
            $label = html_writer::link($url, $blog->name);
            $statinfo = new tsblog_statsinfo($user, $percent, $stat, $url, $label);
            $content .= $renderer->render($statinfo);
        }
    }

    return $renderer->render_stats_view('visitstats', $maintitle, $content, $title, $info, $timefilter, $ajax);
}
/**
 * Generates tsblog most posts statistics output.
 * @param object $tsblog
 * @param object $cm
 * @param mod_tsblog_renderer $renderer
 * @param bool $ajax true to return data object rather than html
 */
function tsblog_stats_output_poststats($tsblog, $cm, $renderer = null, $ajax = false) {
    global $PAGE, $DB;
    if (!$renderer) {
        $renderer = $PAGE->get_renderer('mod_tsblog');
    }
    // This is only for personal blogs, visible individual blogs, visible group blogs.
    if (!$tsblog->global && ($tsblog->individual == TS_BLOG_SEPARATE_INDIVIDUAL_BLOGS ||
            ($tsblog->individual == TS_BLOG_NO_INDIVIDUAL_BLOGS && $cm->groupmode <= SEPARATEGROUPS))) {
        return;
    }

    $curgroup = -1;
    if ($cm->groupmode > NOGROUPS) {
        // Get currently viewed group.
        $curgroup = optional_param('curgroup', tsblog_get_activity_group($cm), PARAM_INT);
    }

    if (isset($_POST['timefilter_poststats']) && isloggedin()) {
        // Get the posted form value to set user pref (do this from post as need to to init form).
        set_user_preference('mod_tsblog_postformfilter', $_POST['timefilter_poststats']);
    }
    $default = get_user_preferences('mod_tsblog_postformfilter', TS_BLOG_STATS_TIMEFILTER_MONTH);

    // Create time filter options form.
    $customdata = array(
            'options' => array(
                    TS_BLOG_STATS_TIMEFILTER_ALL => get_string('timefilter_alltime', 'tsblog'),
                    TS_BLOG_STATS_TIMEFILTER_YEAR => get_string('timefilter_thisyear', 'tsblog'),
                    TS_BLOG_STATS_TIMEFILTER_MONTH => get_string('timefilter_thismonth', 'tsblog')),
            'default' => $default,
            'type' => 'poststats',
            'params' => array('curgroup', $curgroup)
    );
    if ($tsblog->global && $curindividual = optional_param('user', 0, PARAM_INT)) {
        $customdata['params']['user'] = $curindividual;
    }
    if (!$tsblog->global) {
        $customdata['cmid'] = $cm->id;
    }

    $timefilter = new tsblog_stats_timefilter_form(null, $customdata);

    // First, get the stats for this blog.
    list($filtertime, $filterselected) = $timefilter->get_selected_time($default);

    $listgroups = false;
    if (!$tsblog->global && $cm->groupmode == VISIBLEGROUPS &&
            $tsblog->individual == TS_BLOG_NO_INDIVIDUAL_BLOGS) {
        // We show groups rather than individuals (Visible groups set).
        $listgroups = true;
    }

    if ($listgroups) {
        // Get group posts, not individuals.
        $params = array($tsblog->id, $filtertime);
        $sql = "SELECT p.groupid, count(p.id) as posts
                    FROM {tsblog_posts} p
                    JOIN {tsblog_instances} bi on p.tsbloginstancesid = bi.id
                    JOIN {groups} as g on g.id = p.groupid
                    WHERE bi.tsblogid = ?
                    AND p.deletedby IS NULL AND p.timeposted >= ?
                    AND p.groupid > 0
                    GROUP BY p.groupid
                    ORDER BY posts DESC";
    } else {
        $subwhere = '';
        $extrajoin = '';
        $params = array($tsblog->id, $filtertime);
        if ($tsblog->global || ($tsblog->maxvisibility == TS_BLOG_VISIBILITY_PUBLIC && !isloggedin())) {
            // Only include visible posts on global blogs and public blogs when not logged in.
            $subwhere .= 'AND p.visibility >= ? ';
            if (!isloggedin()) {
                $params[] = TS_BLOG_VISIBILITY_PUBLIC;
            } else {
                $params[] = TS_BLOG_VISIBILITY_LOGGEDINUSER;
            }
        }
        if (!$tsblog->global && $cm->groupmode != NOGROUPS && $curgroup > 0 &&
                $tsblog->individual > TS_BLOG_NO_INDIVIDUAL_BLOGS) {
            // Selected a group in an individual blog - get users in that group to filter results.
            if ($users = groups_get_members($curgroup, 'u.id')) {
                list($insql, $inparams) = $DB->get_in_or_equal(array_keys($users));
                $subwhere .= " AND bi2.userid $insql";
                $params = array_merge($params, $inparams);
            } else {
                // No users in this group! Hack to return nothing.
                $subwhere .= " AND bi2.userid IS NULL";
            }
        } else {
            if ($tsblog->global) {
                if ($excludedlist = get_config('mod_tsblog', 'globalusageexclude')) {
                    // There are user ids to exclude in the global blog stats.
                    list($insql, $inparams) = $DB->get_in_or_equal(explode(',', $excludedlist),
                            SQL_PARAMS_QM, 'param', false);
                    $subwhere .= " AND bi2.userid $insql";
                    $params = array_merge($params, $inparams);
                }
            }
            // Not getting specific user(s) so join user table to ensure they still exist in system.
            $extrajoin .= 'JOIN {user} u on u.id = bi2.userid';
        }
        // Time filter - get instances from sub query based on matching post criteria.
        $sql = "SELECT bi.*, pos.posts
        FROM {tsblog_instances} bi
        JOIN (SELECT p.tsbloginstancesid, count(p.id) as posts
              FROM {tsblog_posts} p
              JOIN {tsblog_instances} bi2 on bi2.id = p.tsbloginstancesid
              $extrajoin
              WHERE bi2.tsblogid = ?
              AND p.deletedby IS NULL AND p.timeposted >= ? $subwhere
              GROUP BY p.tsbloginstancesid
        ) as pos on pos.tsbloginstancesid = bi.id
        ORDER BY posts DESC";
    }
    $blogs = $DB->get_records_sql($sql, $params, 0, 5);

    // Generate content data ready to send to renderer.
    $maintitle = get_string('mostposts', 'tsblog');// The title of the 'section';
    switch ($filterselected) {
        case TS_BLOG_STATS_TIMEFILTER_ALL:
            $title = get_string('timefilter_alltime', 'tsblog');// Sub-heading.
            $info = get_string('posts_info_alltime', 'tsblog', tsblog_get_displayname($tsblog, true));
            break;
        case TS_BLOG_STATS_TIMEFILTER_YEAR:
            $title = get_string('timefilter_thisyear', 'tsblog');// Sub-heading.
            $info = get_string('posts_info_thisyear', 'tsblog', tsblog_get_displayname($tsblog, true));
            break;
        case TS_BLOG_STATS_TIMEFILTER_MONTH:
            $title = get_string('timefilter_thismonth', 'tsblog');// Sub-heading.
            $info = get_string('posts_info_thismonth', 'tsblog', tsblog_get_displayname($tsblog, true));
            break;
    }
    $content = '';
    if ($blogs) {
        $maxnum = reset($blogs)->posts;
        foreach ($blogs as $blog) {
            // Create the stat view for the blog.
            $percent = $blog->posts / $maxnum * 100;
            $stat = get_string('numberposts', 'tsblog', number_format($blog->posts));
            if ($tsblog->global) {
                $url = new moodle_url('/mod/tsblog/view.php', array('user' => $blog->userid));
            } else if ($listgroups && isset($blog->groupid)) {
                $url = new moodle_url('/mod/tsblog/view.php',
                        array('id' => $cm->id, 'group' => $blog->groupid));
            } else {
                $urlparams = array('id' => $cm->id, 'individual' => $blog->userid);
                if ($curgroup != -1) {
                    $urlparams['group'] = $curgroup;
                }
                $url = new moodle_url('/mod/tsblog/view.php', $urlparams);
            }
            if ($listgroups && isset($blog->groupid)) {
                // We are reffering to a group, not a user.
                $user = $DB->get_record('groups', array('id' => $blog->groupid));
                $a = (object) array('name' => $user->name,
                        'displayname' => tsblog_get_displayname($tsblog));
                $blog->name = format_string(get_string('defaultpersonalblogname', 'tsblog', $a));
            } else {
                $user = $DB->get_record('user', array('id' => $blog->userid));
                if ($blog->name == '') {
                    $a = (object) array('name' => fullname($user),
                            'displayname' => tsblog_get_displayname($tsblog));
                    $blog->name = get_string('defaultpersonalblogname', 'tsblog', $a);
                }
            }
            $label = html_writer::link($url, $blog->name);
            $statinfo = new tsblog_statsinfo($user, $percent, $stat, $url, $label);
            $content .= $renderer->render($statinfo);
        }
    }

    return $renderer->render_stats_view('poststats', $maintitle, $content, $title, $info, $timefilter, $ajax);
}
/**
 * Generates tsblog most number of comments statistics output.
 * @param object $tsblog
 * @param object $cm
 * @param mod_tsblog_renderer $renderer
 * @param bool $ajax true to return data object rather than html
 */
function tsblog_stats_output_commentstats($tsblog, $cm, $renderer = null, $ajax = false) {
    global $PAGE, $DB;
    if (!$renderer) {
        $renderer = $PAGE->get_renderer('mod_tsblog');
    }
    // This is only for personal blogs, visible individual blogs, visible group blogs.
    if ($tsblog->allowcomments == TS_BLOG_COMMENTS_PREVENT ||
            $tsblog->individual == TS_BLOG_SEPARATE_INDIVIDUAL_BLOGS ||
            (!$tsblog->global && $tsblog->individual == TS_BLOG_NO_INDIVIDUAL_BLOGS &&
                    $cm->groupmode <= SEPARATEGROUPS)) {
        return;
    }

    $curgroup = -1;
    if ($cm->groupmode > NOGROUPS) {
        // Get currently viewed group.
        $curgroup = optional_param('curgroup', tsblog_get_activity_group($cm), PARAM_INT);
    }

    if (isset($_POST['timefilter_commentstats']) && isloggedin()) {
        // Get the posted form value to set user pref (do this from post as need to to init form).
        set_user_preference('mod_tsblog_commentformfilter', $_POST['timefilter_commentstats']);
    }
    $default = get_user_preferences('mod_tsblog_commentformfilter', TS_BLOG_STATS_TIMEFILTER_MONTH);

    // Create time filter options form.
    $customdata = array(
            'options' => array(TS_BLOG_STATS_TIMEFILTER_ALL => get_string('timefilter_alltime', 'tsblog'),
                    TS_BLOG_STATS_TIMEFILTER_YEAR => get_string('timefilter_thisyear', 'tsblog'),
                    TS_BLOG_STATS_TIMEFILTER_MONTH => get_string('timefilter_thismonth', 'tsblog')),
            'default' => $default,
            'type' => 'commentstats',
            'params' => array('curgroup', $curgroup)
    );
    if ($tsblog->global && $curindividual = optional_param('user', 0, PARAM_INT)) {
        $customdata['params']['user'] = $curindividual;
    }
    if (!$tsblog->global) {
        $customdata['cmid'] = $cm->id;
    }

    $timefilter = new tsblog_stats_timefilter_form(null, $customdata);

    // First, get the stats for this blog.
    list($filtertime, $filterselected) = $timefilter->get_selected_time($default);

    $listgroups = false;
    if (!$tsblog->global && $cm->groupmode == VISIBLEGROUPS &&
            $tsblog->individual == TS_BLOG_NO_INDIVIDUAL_BLOGS) {
        // We show groups rather than individuals.
        $listgroups = true;
    }

    if ($listgroups) {
        // Get group posts, not individuals.
        $sql = 'SELECT p.groupid, count(c.id) as comments
            FROM {tsblog_comments} c
            JOIN {tsblog_posts} p on p.id = c.postid
            JOIN {tsblog_instances} bi on p.tsbloginstancesid = bi.id
            JOIN {groups} as g on g.id = p.groupid
            WHERE bi.tsblogid = ?
            AND p.groupid > 0
            AND p.deletedby IS NULL
            AND p.allowcomments > ?
            AND c.deletedby IS NULL AND c.timeposted >= ? AND c.userid <> bi.userid
            GROUP BY p.groupid
            ORDER BY comments DESC';
        $params = array($tsblog->id, TS_BLOG_COMMENTS_PREVENT, $filtertime);
    } else {
        $subwhere = '';
        $extrajoin = '';
        $params = array($tsblog->id, TS_BLOG_COMMENTS_PREVENT);
        if ($tsblog->global || ($tsblog->maxvisibility == TS_BLOG_VISIBILITY_PUBLIC && !isloggedin())) {
            // Only include visible posts on global blogs and public blogs when not logged in.
            $subwhere .= 'AND p.visibility >= ? ';
            if (!isloggedin()) {
                $params[] = TS_BLOG_VISIBILITY_PUBLIC;
            } else {
                $params[] = TS_BLOG_VISIBILITY_LOGGEDINUSER;
            }
        }
        if (!$tsblog->global && $cm->groupmode != NOGROUPS && $curgroup > 0 &&
                $tsblog->individual > TS_BLOG_NO_INDIVIDUAL_BLOGS) {
            // Selected a group in an individual blog - get users in that group to filter results.
            if ($users = groups_get_members($curgroup, 'u.id')) {
                list($insql, $inparams) = $DB->get_in_or_equal(array_keys($users));
                $subwhere .= " AND bi2.userid $insql";
                $params = array_merge($params, $inparams);
            } else {
                // No users in this group! Hack to return nothing.
                $subwhere .= " AND bi2.userid IS NULL";
            }
        } else {
            if ($tsblog->global) {
                if ($excludedlist = get_config('mod_tsblog', 'globalusageexclude')) {
                    // There are user ids to exclude in the global blog stats.
                    list($insql, $inparams) = $DB->get_in_or_equal(explode(',', $excludedlist),
                            SQL_PARAMS_QM, 'param', false);
                    $subwhere .= " AND bi2.userid $insql";
                    $params = array_merge($params, $inparams);
                }
            }
            // Not getting specific user(s) so join user table to ensure they still exist in system.
            $extrajoin .= 'JOIN {user} u on u.id = bi2.userid';
        }
        $params[] = $filtertime;
        // Time filter - get instances from sub query based on matching post criteria.
        $sql = "SELECT bi.*, pos.comments
        FROM {tsblog_instances} bi
        JOIN (SELECT p.tsbloginstancesid, count(c.id) as comments
            FROM {tsblog_comments} c
            JOIN {tsblog_posts} p on p.id = c.postid
            JOIN {tsblog_instances} bi2 on bi2.id = p.tsbloginstancesid
            $extrajoin
            WHERE bi2.tsblogid = ?
            AND p.allowcomments > ?
            AND p.deletedby IS NULL $subwhere
            AND c.deletedby IS NULL AND c.timeposted >= ?
            AND (c.userid <> bi2.userid OR c.userid IS NULL)
            GROUP BY p.tsbloginstancesid
        ) as pos on pos.tsbloginstancesid = bi.id
        ORDER BY comments DESC";
    }
    $blogs = $DB->get_records_sql($sql, $params, 0, 5);

    // Generate content data ready to send to renderer.
    $maintitle = get_string('mostcomments', 'tsblog');// The title of the 'section';
    switch ($filterselected) {
        case TS_BLOG_STATS_TIMEFILTER_ALL:
            $title = get_string('timefilter_alltime', 'tsblog');// Sub-heading.
            $info = get_string('comments_info_alltime', 'tsblog', tsblog_get_displayname($tsblog, true));
        break;
        case TS_BLOG_STATS_TIMEFILTER_YEAR:
            $title = get_string('timefilter_thisyear', 'tsblog');// Sub-heading.
            $info = get_string('comments_info_thisyear', 'tsblog', tsblog_get_displayname($tsblog, true));
                break;
        case TS_BLOG_STATS_TIMEFILTER_MONTH:
            $title = get_string('timefilter_thismonth', 'tsblog');// Sub-heading.
            $info = get_string('comments_info_thismonth', 'tsblog', tsblog_get_displayname($tsblog, true));
        break;
    }
    $content = '';
    if ($blogs) {
        $maxnum = reset($blogs)->comments;
        foreach ($blogs as $blog) {
            // Create the stat view for the blog.
            $percent = $blog->comments / $maxnum * 100;
            $stat = get_string('numbercomments', 'tsblog', number_format($blog->comments));
            if ($tsblog->global) {
                $url = new moodle_url('/mod/tsblog/view.php', array('user' => $blog->userid));
            } else if ($listgroups && isset($blog->groupid)) {
                $url = new moodle_url('/mod/tsblog/view.php',
                    array('id' => $cm->id, 'group' => $blog->groupid));
            } else {
                $urlparams = array('id' => $cm->id, 'individual' => $blog->userid);
                if ($curgroup != -1) {
                    $urlparams['group'] = $curgroup;
                }
                $url = new moodle_url('/mod/tsblog/view.php', $urlparams);
            }
            if ($listgroups && isset($blog->groupid)) {
                // We are reffering to a group, not a user.
                $user = $DB->get_record('groups', array('id' => $blog->groupid));
                $a = (object) array('name' => $user->name,
                        'displayname' => tsblog_get_displayname($tsblog));
                $blog->name = format_string(get_string('defaultpersonalblogname', 'tsblog', $a));
            } else {
                $user = $DB->get_record('user', array('id' => $blog->userid));
                if ($blog->name == '') {
                    $a = (object) array('name' => fullname($user),
                            'displayname' => tsblog_get_displayname($tsblog));
                    $blog->name = get_string('defaultpersonalblogname', 'tsblog', $a);
                }
            }
            $label = html_writer::link($url, $blog->name);
            $statinfo = new tsblog_statsinfo($user, $percent, $stat, $url, $label);
            $content .= $renderer->render($statinfo);
        }
    }

    return $renderer->render_stats_view('commentstats', $maintitle, $content, $title, $info, $timefilter, $ajax);
}
/**
 * Generates tsblog most commented posts statistics output.
 * @param object $tsblog
 * @param object $cm
 * @param mod_tsblog_renderer $renderer
 * @param bool $ajax true to return data object rather than html
 * @param bool $allposts true to include posts across all blogs in instance (personal blog only)
 * @param int $curindividual User ID for current individual blog instance
 */
function tsblog_stats_output_commentpoststats($tsblog, $cm, $renderer = null, $ajax = false,
        $allposts = false, $curindividual = -1, $globalindividual = null) {
    global $PAGE, $DB, $USER;
    if (!$renderer) {
        $renderer = $PAGE->get_renderer('mod_tsblog');
    }
    // This is not for separate individual blogs.
    if ($tsblog->allowcomments == TS_BLOG_COMMENTS_PREVENT ||
            $tsblog->individual == TS_BLOG_SEPARATE_INDIVIDUAL_BLOGS) {
        return;
    }
    if ($tsblog->global) {
        // Only search for sent value if global blog as we only support in this.
        $allposts = optional_param('allposts', $allposts, PARAM_BOOL);
    }
    $curgroup = -1;
    if (!$allposts && $cm->groupmode > NOGROUPS) {
        // Get currently viewed group.
        $curgroup = optional_param('curgroup', tsblog_get_activity_group($cm), PARAM_INT);
    }
    if (!$allposts && $tsblog->individual > TS_BLOG_NO_INDIVIDUAL_BLOGS) {
        // Work out current individual.
        $curindividual = optional_param('curindividual', $curindividual, PARAM_INT);
    } else if ($tsblog->global && !$allposts) {
        $curindividual = optional_param('globalindividual', $globalindividual, PARAM_INT);
        if ($curindividual == -1) {
            // Get current user as not sent.
            $curindividual = optional_param('user', $USER->id, PARAM_INT);
        }
    }

    if (isset($_POST['timefilter_commentpoststats']) && isloggedin()) {
        // Get the posted form value to set user pref (do this from post as need to to init form).
        set_user_preference('mod_tsblog_commentpostformfilter', $_POST['timefilter_commentpoststats']);
    }

    $default = get_user_preferences('mod_tsblog_commentpostformfilter', TS_BLOG_STATS_TIMEFILTER_MONTH);

    // Create time filter options form.
    $customdata = array(
            'options' => array(TS_BLOG_STATS_TIMEFILTER_ALL => get_string('timefilter_alltime', 'tsblog'),
                    TS_BLOG_STATS_TIMEFILTER_YEAR => get_string('timefilter_thisyear', 'tsblog'),
                    TS_BLOG_STATS_TIMEFILTER_MONTH => get_string('timefilter_thismonth', 'tsblog')),
            'default' => $default,
            'type' => 'commentpoststats',
            'params' => array(
                    'allposts' => $allposts,
                    'curgroup' => $curgroup,
                    'curindividual' => $curindividual,
                    'globalindividual' => $globalindividual
                    )
    );
    if ($tsblog->global && !$allposts && $curindividual != -1) {
        $customdata['params']['user'] = $curindividual;
    }
    if (!$tsblog->global) {
        $customdata['cmid'] = $cm->id;
    }

    $timefilter = new tsblog_stats_timefilter_form(null, $customdata);

    // First, get the stats for this blog.
    list($filtertime, $filterselected) = $timefilter->get_selected_time($default);

    $instwhere = '';
    $postwhere = '';
    $extrajoin = '';
    if ($cm->groupmode > NOGROUPS && $tsblog->individual == TS_BLOG_NO_INDIVIDUAL_BLOGS) {
        // Join the group table so only groups that still exist included.
        $extrajoin .= 'JOIN {groups} g on g.id = p.groupid ';
    }
    $params = array($tsblog->id);
    if ($curindividual > 0) {
        $instwhere = 'AND bi2.userid = ?';
        $params[] = $curindividual;
    } else if ($cm->groupmode != NOGROUPS && $curgroup > 0 &&
            $tsblog->individual > TS_BLOG_NO_INDIVIDUAL_BLOGS) {
        // Selected a group in an individual blog (no user selected) - get group to filter results.
        if ($users = groups_get_members($curgroup, 'u.id')) {
            list($insql, $inparams) = $DB->get_in_or_equal(array_keys($users));
            $instwhere .= " AND bi2.userid $insql";
            $params = array_merge($params, $inparams);
        } else {
            // No users in this group! Hack to return nothing.
            $instwhere .= " AND bi2.userid IS NULL";
        }
    } else {
        // Not getting specific user(s) so join user table to ensure they still exist in system.
        $extrajoin .= 'JOIN {user} u on u.id = bi2.userid';
        if ($allposts) {
            // Get any excluded user ids, add not in() against instance user id.
            if ($excludedlist = get_config('mod_tsblog', 'globalusageexclude')) {
                 list($insql, $inparams) = $DB->get_in_or_equal(explode(',', $excludedlist),
                         SQL_PARAMS_QM, 'param', false);
                 $instwhere .= ' AND (bi2.userid ' . $insql . ')';
                 $params = array_merge($params, $inparams);
            }
        }
    }
    $params[] = TS_BLOG_COMMENTS_PREVENT;
    if ($tsblog->global || ($tsblog->maxvisibility == TS_BLOG_VISIBILITY_PUBLIC && !isloggedin())) {
        // Only include visible posts on global blogs and public blogs when not logged in.
        $postwhere .= 'AND p.visibility >= ? ';
        if (!isloggedin()) {
            $params[] = TS_BLOG_VISIBILITY_PUBLIC;
        } else {
            $params[] = TS_BLOG_VISIBILITY_LOGGEDINUSER;
        }
    }
    if ($curgroup > 0 && $tsblog->individual == TS_BLOG_NO_INDIVIDUAL_BLOGS) {
        $postwhere .= 'AND p.groupid = ?';
        $params[] = $curgroup;
    }
    $params[] = $filtertime;
    // Time filter - get instances from sub query based on matching post criteria.
    $sql = "SELECT posts.*, bi.userid, bi.name, pos.comments
        FROM {tsblog_posts} posts
        JOIN (SELECT p.id as pid, count(c.id) as comments
            FROM {tsblog_comments} c
            JOIN {tsblog_posts} p on p.id = c.postid
            JOIN {tsblog_instances} bi2 on bi2.id = p.tsbloginstancesid
            $extrajoin
            WHERE bi2.tsblogid = ? $instwhere
            AND p.allowcomments > ?
            AND p.deletedby IS NULL $postwhere
            AND c.deletedby IS NULL AND c.timeposted >= ?
            AND (c.userid <> bi2.userid OR c.userid IS NULL)
            GROUP BY p.id
        ) as pos on pos.pid = posts.id
        JOIN {tsblog_instances} bi on bi.id = posts.tsbloginstancesid
        ORDER BY pos.comments DESC, posts.title ASC, posts.id DESC";

    $blogs = $DB->get_records_sql($sql, $params, 0, 5);

    // Generate content data ready to send to renderer.
    $maintitle = get_string('commentposts', 'tsblog');// The title of the 'section';
    switch ($filterselected) {
        case TS_BLOG_STATS_TIMEFILTER_ALL:
            $title = get_string('timefilter_alltime', 'tsblog');// Sub-heading.
            $info = get_string('commentposts_info_alltime', 'tsblog');
        break;
        case TS_BLOG_STATS_TIMEFILTER_YEAR:
            $title = get_string('timefilter_thisyear', 'tsblog');// Sub-heading.
            $info = get_string('commentposts_info_thisyear', 'tsblog');
        break;
        case TS_BLOG_STATS_TIMEFILTER_MONTH:
            $title = get_string('timefilter_thismonth', 'tsblog');// Sub-heading.
            $info = get_string('commentposts_info_thismonth', 'tsblog');
        break;
    }
    $content = '';
    if ($blogs) {
        $maxnum = reset($blogs)->comments;
        foreach ($blogs as $blog) {
            // Create the stat view for the blog.
            $percent = $blog->comments / $maxnum * 100;
            $stat = get_string('numbercomments', 'tsblog', number_format($blog->comments));
            $url = new moodle_url('/mod/tsblog/viewpost.php', array('post' => $blog->id));
            $user = $DB->get_record('user', array('id' => $blog->userid));
            if ($blog->name == '') {
                // Default name.
                $a = (object) array('name' => fullname($user),
                        'displayname' => tsblog_get_displayname($tsblog));
                $blog->name = get_string('defaultpersonalblogname', 'tsblog', $a);
            }
            $showblogname = false;
            if (!$tsblog->global && $tsblog->individual == TS_BLOG_NO_INDIVIDUAL_BLOGS &&
                    $cm->groupmode > NOGROUPS && $curgroup == 0) {
                // We are reffering to all group blogs, not an individual or single group.
                $group = $DB->get_record('groups', array('id' => $blog->groupid), 'name');
                $a = (object) array('name' => $group->name,
                        'displayname' => tsblog_get_displayname($tsblog));
                $blog->name = format_string(get_string('defaultpersonalblogname', 'tsblog', $a));
                $showblogname = true;
            }
            if (($tsblog->global && $curindividual <= 0) ||
                    ($tsblog->individual > TS_BLOG_NO_INDIVIDUAL_BLOGS && $curindividual <= 0)) {
                // Show the blog name in these cases.
                $showblogname = true;
            }

            $postname = !(empty($blog->title)) ? $blog->title : get_string('untitledpost', 'tsblog');
            $label = html_writer::div(html_writer::link($url, $postname), 'tsblogstats_commentposts_posttitle');
            if ($showblogname) {
                if ($tsblog->global) {
                    $bparams = array('user' => $blog->userid);
                } else {
                    $bparams = array('id' => $cm->id);
                    if ($cm->groupmode > NOGROUPS && $tsblog->individual >= TS_BLOG_NO_INDIVIDUAL_BLOGS
                            && $curgroup > 0) {
                        // Force link to current group as post might be associated elsewhere(or 0).
                        $bparams['group'] = $curgroup;
                    } else if ($cm->groupmode > NOGROUPS && $blog->groupid > 0) {
                        $bparams['group'] = $blog->groupid;
                    }
                    if ($tsblog->individual != TS_BLOG_NO_INDIVIDUAL_BLOGS) {
                        $bparams['individual'] = $blog->userid;
                    }
                }
                $burl = new moodle_url('/mod/tsblog/view.php', $bparams);
                $label .= html_writer::div(tsblog_date($blog->timeposted) .
                             '<br/>' .
                          html_writer::link($burl, $blog->name), 'tsblogstats_commentpostsblogname');
            } else {
                // We have room to put post time instead.
                $label .= html_writer::div(tsblog_date($blog->timeposted) , 'tsblogstats_commentpostsblogname');
            }
            $statinfo = new tsblog_statsinfo($user, $percent, $stat, $url, $label);
            $content .= $renderer->render($statinfo);
        }
    }

    return $renderer->render_stats_view('commentpoststats', $maintitle, $content, $title, $info, $timefilter, $ajax);
}
/**
 * Generates tsblog user participation statistics output.
 * @param object $tsblog
 * @param object $cm
 * @param mod_tsblog_renderer $renderer
 * @param bool $ajax true to return data object rather than html
 */
function tsblog_stats_output_myparticipation($tsblog, $cm, $renderer = null, $course, $currentindividual, $globalindividual = null) {
    global $PAGE, $DB, $USER, $OUTPUT;
    if (!isloggedin()) {// My participation is only visible to actual users.
        return;
    }
    if (!$renderer) {
        $renderer = $PAGE->get_renderer('mod_tsblog');
    }
    // Setup My Participation capability check.
    $curgroup = tsblog_get_activity_group($cm);
    $canview = tsblog_can_view_participation($course, $tsblog, $cm, $curgroup);
    if ($tsblog->global) {
        $currentindividual = $globalindividual;
    }
    // Dont show the 'block' if user cant participate.
    if (($tsblog->global && $currentindividual != $USER->id) ||
            ($tsblog->individual > TS_BLOG_NO_INDIVIDUAL_BLOGS && $currentindividual != $USER->id)) {
        return;
    }
    if (!$tsblog->global && $canview == TS_BLOG_NO_PARTICIPATION) {
        return;
    }
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    // Get the participation object containing User, Posts and Comments.
    $participation = tsblog_get_user_participation($tsblog, $context,
            $USER->id, $curgroup, $cm, $course);
    // Generate content data to send to renderer.
    $maintitle = get_string('myparticipation', 'tsblog');// The title of the block 'section'.
    $content = '';
    $postedcount = $commentedcount = $commenttotal = 0;
    $postshow = 8;
    $postscount = count($participation->posts);
    if (count($participation->comments) <= 4) {
        $commenttotal = count($participation->comments);
    } else {
        $commenttotal = 4;
    }
    if (!$participation->posts) {
        $content .= html_writer::tag('p', get_string('nouserposts', 'tsblog'));
    } else {
        $percent = $stat = null;
        $content .= html_writer::tag('h3', get_string('numberposts', 'tsblog', count($participation->posts)));
        foreach ($participation->posts as $post) {
            if ($postedcount >= ($postshow - $commenttotal)) {
                break;
            }
            $url = new moodle_url('/mod/tsblog/viewpost.php', array('post' => $post->id));
            $postname = !(empty($post->title)) ? $post->title : get_string('untitledpost', 'tsblog');
            $label = html_writer::div(html_writer::link($url, $postname), 'tsblogstats_posts_posttitle');
            $label .= html_writer::div(tsblog_date($post->timeposted) , 'tsblogstats_commentpostsblogname');
            $statinfo = new tsblog_statsinfo($participation->user, $percent, $stat, $url, $label);
            $content .= $renderer->render($statinfo);
            $postedcount++;
        }
    }
    // Pre test the numbers of posts/comments for display upto max.
    $postspluscount = count($participation->posts) - $postedcount;
    if ($postspluscount >= 1) {
        $content .= html_writer::tag('p', get_string('numberpostsmore', 'tsblog', $postspluscount));
    }
    if (!$participation->comments) {
        $content .= html_writer::tag('p', get_string('nousercomments', 'tsblog'));
    } else {
        $percent = $stat = null;// Removing all stats div.
        $content .= html_writer::tag('h3', get_string('numbercomments', 'tsblog', count($participation->comments)));
        foreach ($participation->comments as $comment) {
            if (($commentedcount + $postedcount) >= $postshow ) {
                break;
            }
            $url = new moodle_url('/mod/tsblog/viewpost.php', array('post' => $comment->postid));
            $lnkurl = $url->out() . '#cid' . $comment->id;
            $commentname = !(empty($comment->title)) ? $comment->title : get_string('untitledcomment', 'tsblog');
            $label = html_writer::div(html_writer::link($lnkurl, $commentname), 'tsblogstats_commentposts_posttitle');
            $label .= html_writer::div(tsblog_date($comment->timeposted) , 'tsblogstats_commentpostsblogname');
            $statinfo = new tsblog_statsinfo($participation->user, $percent, $stat, $url, $label);
            $content .= $renderer->render($statinfo);
            $commentedcount++;
        }
    }
    // If the number of comments is more than can be shown.
    $commentspluscount = count($participation->comments) - $commentedcount;
    if ($commentspluscount >= 1) {
        $content .= html_writer::tag('p', get_string('numbercommentsmore', 'tsblog', $commentspluscount));
    }
    $params = array(
        'id' => $cm->id,
        'group' => $curgroup,
        'user' => $participation->user->id
    );
    $url = new moodle_url('/mod/tsblog/userparticipation.php', $params);
    $viewmyparticipation = html_writer::link($url, get_string('viewmyparticipation', 'tsblog'));
    $content .= html_writer::start_tag('div', array('class' => 'tsblog-post-content'));
    $content .= html_writer::tag('h3', $viewmyparticipation, array('class' => 'tsblog-post-title'));
    $content .= html_writer::end_tag('div');

    return $renderer->render_stats_view('myparticipation', $maintitle,
            $content, null, null , null, false);
}

require_once($CFG->libdir . '/formslib.php');
class tsblog_stats_timefilter_form extends moodleform {

    private $type = '';

    public function definition() {
        global $CFG;

        $mform =& $this->_form;
        $cdata = $this->_customdata;
        /*
         * We Expect custom data to have following format:
         * 'options' => array used for select drop down
         * 'default' => default/selected option
         * 'type' => 'type' of stat this is used in - must be same as function name suffix
         * 'cmid' => blog course module id
         * 'params' => key(name)/value array to make into hidden inputs (value must be integer)
         */
        if (isset($cdata['type'])) {
            $this->type = $cdata['type'];
        }
        if (!empty($cdata['params']) && is_array($cdata['params'])) {
            foreach ($cdata['params'] as $param => $value) {
                $mform->addElement('hidden', $param, $value);
                $mform->setType($param, PARAM_INT);
            }
        }
        if (!empty($cdata['options'])) {
            if (!isset($cdata['attributes']) || !is_array($cdata['attributes'])) {
                $cdata['attributes'] = array();
            }
            $mform->addElement('select', 'timefilter_' . $this->type, get_string('timefilter_label', 'tsblog'), $cdata['options'], $cdata['attributes']);
            if (isset($cdata['default'])) {
                $mform->setDefault('timefilter_' . $this->type, $cdata['default']);
            }
            if (isset($cdata['type'])) {
                $mform->addElement('hidden', 'type', $cdata['type']);
                $mform->setType('type', PARAM_ALPHA);
            }
            if (isset($cdata['cmid'])) {
                $mform->addElement('hidden', 'id', $cdata['cmid']);
                $mform->setType('id', PARAM_INT);
            }
            $this->add_action_buttons(false, get_string('timefilter_submit', 'tsblog'));
        }
    }

    public function render() {
        // Override render so we can output js to page.
        global $PAGE;
        if (isset($this->type)) {
            $PAGE->requires->yui_module('moodle-mod_tsblog-statsupdate', 'M.mod_tsblog.statsupdate.init',
                    array($this->type));
        }
        return parent::render();
    }

    protected function get_form_identifier() {
        // Override form name to ensure unique.
        return parent::get_form_identifier() . '_' . $this->type;
    }

    public function add_action_buttons($cancel = true, $submitlabel = null) {
        // Override submit to ensure name unique.
        $mform =& $this->_form;
        $mform->addElement('submit', 'submitbutton' . '_' . $this->type, $submitlabel);
        $mform->closeHeaderBefore('submitbutton' . '_' . $this->type);
    }

    /**
     * Returns time selected in this form or uses default if none yet.
     * Returns as a unix time to use in sql or 0 if no time filter.
     * @param int $default
     * @return array (time ago, selected constant)
     */
    public function get_selected_time($default) {
        if ($data = $this->get_data()) {
            $elname = 'timefilter_' . $this->type;
            if (isset($data->$elname)) {
                $default = $data->$elname;
            }
        }
        switch ($default) {
            case TS_BLOG_STATS_TIMEFILTER_ALL:
                return array(0, TS_BLOG_STATS_TIMEFILTER_ALL);
                break;
            case TS_BLOG_STATS_TIMEFILTER_MONTH:
                return array(strtotime('1 month ago'), TS_BLOG_STATS_TIMEFILTER_MONTH);
                break;
            case TS_BLOG_STATS_TIMEFILTER_YEAR:
                return array(strtotime('1 year ago'), TS_BLOG_STATS_TIMEFILTER_YEAR);
                break;
        }
    }
}

class tsblog_all_portfolio_caller extends tsblog_portfolio_caller {

    protected $postid;
    protected $attachment;
    protected $currentgroup;
    protected $offset;
    protected $currentindividual;
    protected $tsbloguserid;
    protected $canaudit;
    protected $tag;
    protected $tsblogid;
    protected $cmid;

    private $post;
    protected $files = array();
    private $keyedfiles = array();// Keyed on entry.

    /**
     * @return array
     */
    public static function expected_callbackargs() {
        return array(
                'postid' => false,
                'tsblogid' => true,
                'currentgroup' => true,
                'offset' => true,
                'currentindividual' => true,
                'tsbloguserid' => true,
                'canaudit' => true,
                'cmid' => true,
                'tag' => true,
        );
    }

    /**
     * @param array $callbackargs
     */
    public function __construct($callbackargs) {
        parent::__construct($callbackargs);
        if (!$this->tsblogid) {
            throw new portfolio_caller_exception('mustprovidepost', 'tsblog');
        }
    }

    /**
     * @global object
     */
    public function load_data() {
        global $DB, $COURSE;
        if (!$this->tsblog = $DB->get_record('tsblog', array('id' => $this->tsblogid))) {
            throw new portfolio_caller_exception('invalidpostid', 'tsblog');
        }
        if (!$this->cm = get_coursemodule_from_instance('tsblog', $this->tsblogid)) {
            throw new portfolio_caller_exception('invalidcoursemodule');
        }
        // Convert tag from id to name.
        if (!empty($this->tag)) {
            if ($tagrec = $DB->get_record('tsblog_tags', array('id' => $this->tag), 'tag')) {
                $this->tag = $tagrec->tag;
            }
        }
        // Call early to cache group mode - stops debugging warning from tsblog_get_posts later.
        $this->cm->activitygroupmode = tsblog_get_activity_groupmode($this->cm, $COURSE);
        $context = context_module::instance($this->cm->id);
        $this->modcontext = $context;
        if ($this->canaudit == 1) {
            $this->canaudit = true;
        } else {
            $this->canaudit = false;
        }
        if (empty($this->tsbloguserid)) {
            $this->tsbloguserid = null;
        }
        if (empty($this->currentindividual) || $this->currentindividual == 0) {
            $this->currentindividual = -1;
        }
        list($this->posts, $recordcount) = tsblog_get_posts($this->tsblog,
                $context, $this->offset, $this->cm, $this->currentgroup, $this->currentindividual,
                $this->tsbloguserid, $this->tag, $this->canaudit);

        $fs = get_file_storage();
        $this->multifiles = array();
        foreach ($this->posts as $post) {
            $files = array();
            $attach = $fs->get_area_files($this->modcontext->id,
                    'mod_tsblog', 'attachment', $post->id);
            $embed  = $fs->get_area_files($this->modcontext->id,
                    'mod_tsblog', 'message', $post->id);
            if (!empty($post->comments)) {
                foreach ($post->comments as $commentpost) {
                    $embedcomments  = $fs->get_area_files($this->modcontext->id,
                            'mod_tsblog', 'messagecomment', $commentpost->id);
                    $files = array_merge($files, $embedcomments);
                }
            }
            $files = array_merge($files, $attach, $embed);
            if ($files) {
                $this->keyedfiles[$post->id] = $files;
            } else {
                continue;
            }
            $this->multifiles = array_merge($this->multifiles, $files);
        }
        $this->set_file_and_format_data($this->multifiles);

        if (empty($this->multifiles) && !empty($this->singlefile)) {
            $this->multifiles = array($this->singlefile); // Copy_files workaround.
        }
        // Depending on whether there are files or not, we might have to change richhtml/plainhtml.
        if (!empty($this->multifiles)) {
            $this->add_format(PORTFOLIO_FORMAT_RICHHTML);
        } else {
            $this->add_format(PORTFOLIO_FORMAT_PLAINHTML);
        }
    }

    /**
     * @global object
     * @return string
     */
    public function get_return_url() {
        global $CFG;
        return $CFG->wwwroot . '/mod/tsblog/view.php?id=' . $this->cmid;
    }

    /**
     * @global object
     * @return array
     */
    public function get_navigation() {
        global $CFG;
        $navlinks = array();
        return array($navlinks, $this->cm);
    }

    /**
     * A whole blog from a single post, with or without attachments
     *
     * @global object
     * @uses PORTFOLIO_FORMAT_RICH
     * @return mixed
     */
    public function prepare_package() {
        global $CFG;
        $plugin = $this->get('exporter')->get('instance')->get('plugin');
        $posttitles = array();
        $outputhtml = '';
        // Exporting a set of posts from the view page.
        foreach ($this->posts as $post) {
            $post = tsblog_get_post($post->id);
            if ($plugin != 'rtf') {
                $outputhtml = $this->prepare_post($post, true);
                // If post is titled use that as file name for export.
                if ($post->title) {
                    $name = $post->title . '.html';
                } else {
                    $name = get_string('exportuntitledpost', 'tsblog') . $post->id . '.html';
                }
                // If post title already exists make it unique.
                if (in_array(strtolower($post->title), $posttitles) and $post->title != '' ) {
                    $name = $post->title . ' ' . $post->id . '.html';
                    $post->title = $post->title . ' id ' . $post->id;
                }
            } else {
                // Ensure multiple posts and their comments
                // are included in the html for export.
                $outputhtml .= $this->prepare_post($post, false);
            }
            // Ensure multiple files contained within this post and it's comments
            // are included in the exported file.
            $manifest = ($this->exporter->get('format') instanceof PORTFOLIO_FORMAT_RICH);
            if (!empty($this->multifiles)) {
                foreach ($this->multifiles as $file) {
                    $this->get('exporter')->copy_existing_file($file);
                }
            }
            if ($plugin != 'rtf') {
                $this->get('exporter')->write_new_file($outputhtml, $name, $manifest);
                $posttitles[] = strtolower($post->title);
            }
        }
        if ($plugin == 'rtf') {
            $name = $this->tsblog->name . '.html';
            $this->get('exporter')->write_new_file($outputhtml, $name, $manifest);
        }
    }

    /**
     * @return string
     */
    public function get_sha1() {
        $filesha = '';
        try {
            $filesha = $this->get_sha1_file();
        } catch (portfolio_caller_exception $e) {
            // No files.
        }
        if ($this->tsblog) {
            return sha1($filesha . ',' . $this->tsblog->name . ',' . $this->tsblog->intro);
        } else {
            $sha1s = array($filesha);
            foreach ($this->posts as $post) {
                $sha1s[] = sha1($post->title . ',' . $post->message);
            }
            return sha1(implode(',', $sha1s));
        }
    }
    /**
     * @uses CONTEXT_MODULE
     * @return bool
     */
    public function check_permissions() {
        $context = get_context_instance(CONTEXT_MODULE, $this->cm->id);
        return (has_capability('mod/tsblog:exportpost', $context));
    }
}

function tsblog_get_displayname($tsblog, $upperfirst = false) {
    if (empty($tsblog->displayname)) {
        $string = get_string('displayname_default', 'tsblog');
    } else {
        $string = $tsblog->displayname;
    }
    if ($upperfirst) {
        return ucfirst($string);
    } else {
        return $string;
    }
}

function tsblog_get_reportingemail($tsblog) {
    return $tsblog->reportingemail;
}

/*
 * Call to check if OU Alerts plugin exists.
 * If so, includes the library suppport, otherwise return false.
 *
 * @return bool True if OU Alerts extension is enabled.
 */
function tsblog_oualerts_enabled() {
    global $CFG;

    if (file_exists($CFG->dirroot . '/report/oualerts/locallib.php')) {
        @include_once($CFG->dirroot . '/report/oualerts/locallib.php');
        return oualerts_enabled();
    }

    return false;
}

/**
 * Calls a remote server externallib web services during import
 * We use the Moodle curl cache to store responses (for 120 secs default)
 * @param string $function
 * @param array $params (name => value)
 * @return array json decoded result or false if not configured
 */
function tsblog_import_remote_call($function, $params = null) {
    $settings = get_config('mod_tsblog');
    if (empty($settings->remoteserver) && empty($settings->remotetoken)) {
        return false;
    }
    if (is_null($params)) {
        $params = array();
    }
    $curl = new curl(array('cache' => true, 'module_cache' => 'tsblog_import'));
    $url = $settings->remoteserver . '/webservice/rest/server.php';
    $params['moodlewsrestformat'] = 'json';
    $params['wsfunction'] = $function;
    $params['wstoken'] = $settings->remotetoken;
    $options = array();
    $options['RETURNTRANSFER'] = true;
    $options['SSL_VERIFYPEER'] = false;
    $result = $curl->get($url, $params, $options);
    $json = json_decode($result);
    if (empty($result) || $curl->get_errno() || !empty($json->exception)) {
        $errinfo = !empty($json->exception) ? !empty($json->debuginfo) ? $json->debuginfo : $json->message : $curl->error;
        throw new moodle_exception('Failed to contact ' . $settings->remoteserver . ' : ' . $errinfo);
        return false;
    }
    return $json;
}

/**
 * Class defined here to extend the curl class and call the multi() function with no options set.
 */
class tsblog_public_curl_multi extends curl {
    public function public_multi($requests, $options = array()) {
        return $this->multi($requests, $options);
    }
}

/**
 * Downloads files from remote system and adds into local file table
 * Uses webservice pluginfile so lib picks up is from this and allows access to files.
 * @param array $files - array of file-like objects (returned from externallib get_posts)
 * @param int $newcontid - context id to use for new files
 * @param int $newitemid - item id to use for new files
 */
function tsblog_import_remotefiles($files, $newcontid, $newitemid) {
    $settings = get_config('mod_tsblog');
    if (empty($settings->remoteserver) && empty($settings->remotetoken) || empty($files)) {
        return false;
    }
    $fs = get_file_storage();
    $options = array('RETURNTRANSFER' => true);
    $requests = array();
    foreach ($files as $file) {
        $requests[] = array('url' => $settings->remoteserver . '/webservice/pluginfile.php/' .
                $file->contextid . '/mod_tsblog/' . $file->filearea . '/' . $file->itemid . $file->filepath .
                rawurlencode($file->filename) . '?token=' . $settings->remotetoken);
    }
    $curl = new tsblog_public_curl_multi();
    $responses = $curl->public_multi($requests, $options);
    $count = count($files);
    for ($i = 0; $i < $count; $i++) {
        if (empty($files[$i])) {
            continue;
        }
        $fileinfo = $files[$i];
        $fileinfo->contextid = $newcontid;
        $fileinfo->itemid = $newitemid;
        $fileinfo->component = 'mod_tsblog';
        $fs->create_file_from_string($fileinfo, $responses[$i]);
    }
    return true;
}

/**
 * Gets all blogs on the system (and on remote system if defined) that can be imported from
 * @param int $userid
 * @param int $curcmid Current blog cmid (excludes this from list returned)
 * @return array of blog 'info objects' [cmid, name, coursename, numposts]
 */
function tsblog_import_getblogs($userid = 0, $curcmid = null) {
    global $DB, $USER, $SITE;
    if ($userid == 0) {
        $userid = $USER->id;
    }
    $retarray = array();
    $courses = enrol_get_users_courses($userid, true, array('modinfo', 'sectioncache'));
    array_unshift($courses, get_site());
    $courses[0]->site = true;// Mark the global site.
    foreach ($courses as $course) {
        $crsmodinfo = get_fast_modinfo($course, $userid);
        $blogs = $crsmodinfo->get_instances_of('tsblog');
        foreach ($blogs as $blogcm) {
            if ($curcmid && $blogcm->id == $curcmid) {
                continue;// Ignore current blog.
            }
            $blogcontext = context_module::instance($blogcm->id);
            if ($blogtsblog = $DB->get_record('tsblog', array('id' => $blogcm->instance))) {
                $canview = $blogcm->uservisible;
                if ($canview) {
                    $canview = has_capability('mod/tsblog:view', $blogcontext, $userid);
                }
                if ($blogtsblog->global) {
                    // Ignore uservisible for global blog and only check cap.
                    $canview = has_capability('mod/tsblog:viewpersonal', context_system::instance(), $userid);
                }
                if ($canview) {
                    if ($blogtsblog->global) {
                        // Global blog, only show if user instance available.
                        if (!$blogtsbloginst = $DB->get_record('tsblog_instances',
                                array('tsblogid' => $blogtsblog->id, 'userid' => $userid))) {
                            continue;
                        }
                    } else if ($blogtsblog->individual == TS_BLOG_NO_INDIVIDUAL_BLOGS) {
                        // Only allow individual blogs.
                        continue;
                    }
                    $blogob = new stdClass();
                    $blogob->cmid = $blogcm->id;
                    $blogob->coursename = '';
                    if (!$blogtsblog->global) {
                        $blogob->coursename = $blogcm->get_course()->shortname . ' ' .
                                get_course_display_name_for_list($blogcm->get_course());
                    }
                    // Get number of posts (specific to user, doesn't work with group blogs).
                    $sql = 'SELECT count(p.id) as total
                        FROM {tsblog_posts} p
                        INNER JOIN {tsblog_instances} bi on bi.id = p.tsbloginstancesid
                        WHERE bi.userid = ?
                        AND bi.tsblogid = ?
                        AND p.deletedby IS NULL';
                    $count = $DB->get_field_sql($sql, array($userid, $blogtsblog->id));
                    $blogob->numposts = $count ? $count : 0;
                    $blogtsblogname = $blogcm->get_formatted_name();
                    if ($blogtsblog->global) {
                        $blogtsblogname = $blogtsbloginst->name;
                    }
                    $blogob->name = $blogtsblogname;
                    $retarray[] = $blogob;
                }
            }
        }
    }
    return $retarray;
}
/**
 * Returns blog info - cm, tsblog
 * Also checks is a valid blog for import
 * (Throws exception on access error)
 * @param int $cmid
 * @param int $userid
 * @return array (cm id, tsblog id, context id, blog name, course shortname)
 */
function tsblog_import_getbloginfo($cmid, $userid = 0) {
    global $DB, $USER;
    if ($userid == 0) {
        $userid = $USER->id;
    }
    $bcourse = $DB->get_record_select('course',
            'id = (SELECT course FROM {course_modules} WHERE id = ?)', array($cmid),
            '*', MUST_EXIST);
    $bmodinfo = get_fast_modinfo($bcourse, $userid);
    $bcm = $bmodinfo->get_cm($cmid);
    if ($bcm->modname !== 'tsblog') {
        throw new moodle_exception('invalidcoursemodule', 'error');
    }
    if (!$btsblog = $DB->get_record('tsblog', array('id' => $bcm->instance))) {
        throw new moodle_exception('invalidcoursemodule', 'error');
    }
    $bcontext = get_context_instance(CONTEXT_MODULE, $bcm->id);
    $canview = $bcm->uservisible;
    if ($canview) {
        $canview = has_capability('mod/tsblog:view', $bcontext, $userid);
    }
    if ($btsblog->global) {
        // Ignore uservisible for global blog and only check cap.
        $canview = has_capability('mod/tsblog:viewpersonal', context_system::instance(), $userid);
    }
    if (!$canview ||
            (!$btsblog->global && $btsblog->individual == TS_BLOG_NO_INDIVIDUAL_BLOGS)) {
        // Not allowed to get pages from selected blog.
        throw new moodle_exception('import_notallowed', 'tsblog', '', tsblog_get_displayname($btsblog));
    }
    if ($btsblog->global) {
        $btsblogname = $DB->get_field('tsblog_instances', 'name',
                array('tsblogid' => $btsblog->id, 'userid' => $userid));
        $shortname = '';
    } else {
        $btsblogname = $bcm->get_course()->shortname . ' ' .
                get_course_display_name_for_list($bcm->get_course()) .
                ' : ' . $bcm->get_formatted_name();
        $shortname = $bcm->get_course()->shortname;
    }
    return array($bcm->id, $btsblog->id, $bcontext->id, $btsblogname, $shortname);
}

/**
 * Returns importable posts, total posts and selected tag info
 * @param int $blogid - ID of blog
 * @param string $sort - SQL sort for posts
 * @param int $userid
 * @param int $page - page number for pagination (100 per page)
 * @param array $tags - comma separated sequence of selected tag ids to filter by
 * @return array (posts, total in DB, selected tag info)
 */
function tsblog_import_getallposts($blogid, $sort, $userid = 0, $page = 0, $tags = null) {
    global $DB, $USER;
    if ($userid == 0) {
        $userid = $USER->id;
    }
    $perpage = 100;// Must match value in import.php.
    $sqlparams = array($userid, $blogid);
    $tagjoin = '';
    $tagwhere = '';
    $tagnames = '';
    $total = 0;
    if ($tags) {
        $tagarr = array_unique(explode(',', $tags));
        // Filter by joining tag instances.
        list($taginwhere, $tagparams) = $DB->get_in_or_equal($tagarr);
        $tagjoin = "INNER JOIN (
        SELECT ti.postid, count(*) as tagcount FROM {tsblog_taginstances} ti WHERE ti.tagid $taginwhere
        group by ti.postid) as hastags on hastags.postid = p.id";
        $tagwhere = 'AND hastags.tagcount = ?';
        $sqlparams = array_merge($tagparams, $sqlparams, array(count($tagarr)));
        // Get selected tag names.
        $tagnames = $DB->get_records_select('tsblog_tags', "id $taginwhere", $tagparams, 'tag ASC');
    }
    $sql = "SELECT p.id, p.timeposted, p.title
        FROM {tsblog_posts} p
        INNER JOIN {tsblog_instances} bi on bi.id = p.tsbloginstancesid
        $tagjoin
        WHERE bi.userid = ?
        AND bi.tsblogid = ?
        AND p.deletedby IS NULL
        $tagwhere
        ORDER BY p." . $sort;

    $limitfrom = $page * $perpage;

    if ($posts = $DB->get_records_sql($sql, $sqlparams, $limitfrom, $perpage)) {
        // Add in post tags from single query.
        list($inwhere, $inparams) = $DB->get_in_or_equal(array_keys($posts));
        $tsql = 'SELECT t.*, ti.postid
            FROM {tsblog_taginstances} ti
            INNER JOIN {tsblog_tags} t ON ti.tagid = t.id
            WHERE ti.postid ' . $inwhere . ' ORDER BY t.tag';
        $rs = $DB->get_recordset_sql($tsql, $inparams);
        foreach ($rs as $tag) {
            $postid = $tag->postid;
            if (!isset($posts[$postid]->tags)) {
                $posts[$postid]->tags = array();
            }
            $posts[$postid]->tags[$tag->id] = $tag->tag;
        }
        $rs->close();
        // Add total record count.
        $total = $DB->get_field_sql('SELECT count(tot.id) FROM (' . $sql . ') as tot', $sqlparams);
    }
    return array($posts, $total, $tagnames);
}

/**
 * Returns posts specified (inc tags and comments)
 * @param int $blogid - tsblog id
 * @param int $bcontextid - tsblog mod context id
 * @param array $selected - array of selected post ids
 * @param bool $inccomments - include comments?
 * @param int $userid - user id (ensures user is post author)
 * @return array posts
 */
function tsblog_import_getposts($blogid, $bcontextid, $selected, $inccomments = false, $userid = 0) {
    global $DB, $USER;
    if ($userid == 0) {
        $userid = $USER->id;
    }
    list($inwhere, $sqlparams) = $DB->get_in_or_equal($selected);
    $sql = "SELECT p.*
        FROM {tsblog_posts} p
        INNER JOIN {tsblog_instances} bi on bi.id = p.tsbloginstancesid
        WHERE bi.userid = ?
        AND bi.tsblogid = ?
        AND p.deletedby IS NULL
        AND p.id $inwhere
        ORDER BY p.id ASC";

    $sqlparams = array_merge(array($userid, $blogid), $sqlparams);
    if (!$posts = $DB->get_records_sql($sql, $sqlparams)) {
        return array();
    }
    $files = get_file_storage();
    // Get post images and attachments.
    foreach ($posts as &$post) {
        $post->comments = array();// Add in a comment array for use later.
        $post->images = $files->get_area_files($bcontextid, 'mod_tsblog', 'message', $post->id, 'itemid', false);
        $post->attachments = $files->get_area_files($bcontextid, 'mod_tsblog', 'attachment', $post->id, 'itemid', false);
        if (empty($post->images)) {
            $post->images = array();
        }
        if (empty($post->attachments)) {
            $post->attachments = array();
        }
    }
    // Add in post tags from single query.
    list($inwhere, $inparams) = $DB->get_in_or_equal(array_keys($posts));
    $tsql = 'SELECT t.*, ti.postid
        FROM {tsblog_taginstances} ti
        INNER JOIN {tsblog_tags} t ON ti.tagid = t.id
        WHERE ti.postid ' . $inwhere;
    $rs = $DB->get_recordset_sql($tsql, $inparams);
    foreach ($rs as $tag) {
        $postid = $tag->postid;
        if (!isset($posts[$postid]->tags)) {
            $posts[$postid]->tags = array();
        }
        $posts[$postid]->tags[] = $tag;
    }
    $rs->close();
    if ($inccomments) {
        // Get comments for post on the page.
        $sql = "SELECT c.*
            FROM {tsblog_comments} c
            WHERE c.postid $inwhere AND c.deletedby IS NULL AND c.userid = ?
            ORDER BY c.timeposted ASC";
        $inparams[] = $userid;
        $rs = $DB->get_recordset_sql($sql, $inparams);
        foreach ($rs as $comment) {
            $comment->images = $files->get_area_files($bcontextid, 'mod_tsblog', 'messagecomment',
                        $comment->id, 'itemid', false);
            if (empty($comment->images)) {
                $comment->images = array();
            }
            $posts[$comment->postid]->comments[$comment->id] = $comment;
        }
        $rs->close();
    }
    return $posts;
}

class tsblog_participation_timefilter_form extends moodleform {

    public function definition() {
        global $CFG;

        $mform =& $this->_form;
        $cdata = $this->_customdata;
        /*
         * We Expect custom data to have following format:
        * 'options' => array used for select drop down
        * 'default' => default/selected option
        * 'cmid' => blog course module id
        * 'params' => key(name)/value array to make into hidden inputs (value must be integer)
        */
        if (!empty($cdata['params']) && is_array($cdata['params'])) {
            foreach ($cdata['params'] as $param => $value) {
                $mform->addElement('hidden', $param, $value);
                $mform->setType($param, PARAM_INT);
            }
        }
        // Data selectors, with optional enabling checkboxes.
        $mform->addElement('date_selector', 'start',
                get_string('start', 'tsblog'), array('startyear' => 2000, 'stopyear' => gmdate("Y"),
                        'optional' => true));
        $mform->addHelpButton('start', 'displayperiod', 'tsblog');

        $mform->addElement('date_selector', 'end',
                get_string('end', 'tsblog'), array('startyear' => 2000, 'stopyear' => gmdate("Y"),
                        'optional' => true));

        if (isset($cdata['type'])) {
            $mform->addElement('hidden', 'type', $cdata['type']);
            $mform->setType('type', PARAM_ALPHA);
        }
        if (isset($cdata['cmid'])) {
            $mform->addElement('hidden', 'id', $cdata['cmid']);
            $mform->setType('id', PARAM_INT);
        }
        if (isset($cdata['user'])) {
            $mform->addElement('hidden', 'user', $cdata['user']);
            $mform->setType('user', PARAM_INT);
        }
        if (isset($cdata['group'])) {
            $mform->addElement('hidden', 'group', $cdata['group']);
            $mform->setType('group', PARAM_INT);
        }
        $this->add_action_buttons(false, get_string('timefilter_submit', 'tsblog'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (!empty($data['start']) and !empty($data['end'])) {
            if ($data['start'] > $data['end']) {
                $errors['start'] = get_string('timestartenderror', 'tsblog');
            }
        }
        return $errors;
    }
}
