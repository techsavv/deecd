<?php

class block_course_participants extends block_list {
    function init() {
        $this->title = 'Course participants';
    }

    function get_content() {

        global $USER, $DB, $CFG, $OUTPUT;

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        // user/index.php expect course context, so get one if page has module context.
        $currentcontext = $this->page->context->get_course_context(false);

        if (empty($currentcontext)) {
            $this->content = '';
            return $this->content;
        } else if ($this->page->course->id == SITEID) {
            if (!has_capability('moodle/site:viewparticipants', context_system::instance())) {
                $this->content = '';
                return $this->content;
            }
        } else {
            if (!has_capability('moodle/course:viewparticipants', $currentcontext)) {
                $this->content = '';
                return $this->content;
            }
        }

        $icon = '<img style="float:left;" src="'.$OUTPUT->pix_url('collapsed','theme') . '" class="icon" alt="" />';
        $this->content->items[] = $icon.'<a  style="display:block; padding-bottom:5px;" title="'.get_string('listofallpeople').'" href="'.
                                  $CFG->wwwroot.'/user/index.php?contextid='.$currentcontext->id.'">Participant list</a>';

        $timetoshowusers = 300; //Seconds default
        if (isset($CFG->block_online_users_timetosee)) {
            $timetoshowusers = $CFG->block_online_users_timetosee * 60;
        }
        $now = time();
        $timefrom = 100 * floor(($now - $timetoshowusers) / 100); // Round to nearest 100 seconds for better query cache

        //Calculate if we are in separate groups
        $isseparategroups = ($this->page->course->groupmode == SEPARATEGROUPS
                             && $this->page->course->groupmodeforce
                             && !has_capability('moodle/site:accessallgroups', $this->page->context));

        //Get the user current group
        $currentgroup = $isseparategroups ? groups_get_course_group($this->page->course) : NULL;

        $groupmembers = "";
        $groupselect  = "";
        $params = array();

        //Add this to the SQL to show only group users
        if ($currentgroup !== NULL) {
            $groupmembers = ", {groups_members} gm";
            $groupselect = "AND u.id = gm.userid AND gm.groupid = :currentgroup";
            $params['currentgroup'] = $currentgroup;
        }

        $userfields = user_picture::fields('u', array('username'));
        $params['now'] = $now;
        $params['timefrom'] = $timefrom;
        if ($this->page->course->id == SITEID or $this->page->context->contextlevel < CONTEXT_COURSE) {  // Site-level
            $sql = "SELECT $userfields, MAX(u.lastaccess) AS lastaccess
                      FROM {user} u $groupmembers
                     WHERE u.lastaccess > :timefrom
                           AND u.lastaccess <= :now
                           AND u.deleted = 0
                           $groupselect
                  GROUP BY $userfields
                  ORDER BY lastaccess DESC ";

           $csql = "SELECT COUNT(u.id)
                      FROM {user} u $groupmembers
                     WHERE u.lastaccess > :timefrom
                           AND u.lastaccess <= :now
                           AND u.deleted = 0
                           $groupselect";

        } else {
            // Course level - show only enrolled users for now
            // TODO: add a new capability for viewing of all users (guests+enrolled+viewing)

            list($esqljoin, $eparams) = get_enrolled_sql($this->page->context);
            $params = array_merge($params, $eparams);

            $sql = "SELECT $userfields, MAX(ul.timeaccess) AS lastaccess
                      FROM {user_lastaccess} ul $groupmembers, {user} u
                      JOIN ($esqljoin) euj ON euj.id = u.id
                     WHERE ul.timeaccess > :timefrom
                           AND u.id = ul.userid
                           AND ul.courseid = :courseid
                           AND ul.timeaccess <= :now
                           AND u.deleted = 0
                           $groupselect
                  GROUP BY $userfields
                  ORDER BY lastaccess DESC";

           $csql = "SELECT COUNT(u.id)
                      FROM {user_lastaccess} ul $groupmembers, {user} u
                      JOIN ($esqljoin) euj ON euj.id = u.id
                     WHERE ul.timeaccess > :timefrom
                           AND u.id = ul.userid
                           AND ul.courseid = :courseid
                           AND ul.timeaccess <= :now
                           AND u.deleted = 0
                           $groupselect";

            $params['courseid'] = $this->page->course->id;
        }

        //Calculate minutes
        $minutes  = floor($timetoshowusers/60);

        if ($users = $DB->get_records_sql($sql, $params, 0, 50)) {   // We'll just take the most recent 50 maximum
            foreach ($users as $user) {
                $users[$user->id]->fullname = fullname($user);
            }
        } else {
            $users = array();
        }

        if (count($users) < 50) {
            $usercount = "";
        } else {
            $usercount = $DB->count_records_sql($csql, $params);
            $usercount = ": $usercount";
        }

        $this->content->text .= "<div style=\"padding-bottom:10px; font-size:0.95em; color:#4681c2;\" class=\"info\">Users online in last ".$minutes." mins</div>";

        //Now, we have in users, the list of users to show
        //Because they are online
        if (!empty($users)) {
            //Accessibility: Don't want 'Alt' text for the user picture; DO want it for the envelope/message link (existing lang string).
            //Accessibility: Converted <div> to <ul>, inherit existing classes & styles.
            $this->content->text .= "<ul class='list'>\n";
            if (isloggedin() && has_capability('moodle/site:sendmessage', $this->page->context)
                           && !empty($CFG->messaging) && !isguestuser()) {
                $canshowicon = true;
            } else {
                $canshowicon = false;
            }
            foreach ($users as $user) {
                $this->content->text .= '<li class="listentry">';
                $timeago = format_time($now - $user->lastaccess); //bruno to calculate correctly on frontpage

                if (isguestuser($user)) {
                    $this->content->text .= '<div class="user">'.$OUTPUT->user_picture($user, array('size'=>16, 'alttext'=>false));
                    $this->content->text .= get_string('guestuser').'</div>';

                } else {
                    $this->content->text .= '<div class="user">';
                    $this->content->text .= '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$this->page->course->id.'" title="'.$timeago.'">';
                    $this->content->text .= $OUTPUT->user_picture($user, array('size'=>16, 'alttext'=>false, 'link'=>false)) .$user->fullname.'</a></div>';
                }
                if ($canshowicon and ($USER->id != $user->id) and !isguestuser($user)) {  // Only when logged in and messaging active etc
                    $anchortagcontents = '<img class="iconsmall" src="'.$OUTPUT->pix_url('t/message') . '" alt="'. get_string('messageselectadd') .'" />';
                    $anchortag = '<a href="'.$CFG->wwwroot.'/message/index.php?id='.$user->id.'" title="'.get_string('messageselectadd').'">'.$anchortagcontents .'</a>';

                    $this->content->text .= '<div class="message">'.$anchortag.'</div>';
                }
                $this->content->text .= "</li>\n";
            }
            $this->content->text .= '</ul><div class="clearer"><!-- --></div>';
        } else {
            $this->content->text .= "<div class=\"info\">".get_string("none")."</div>";
        }

        $this->content->items[0] .= $this->content->text;
        return $this->content;
    }

    // my moodle can only have SITEID and it's redundant here, so take it away
    function applicable_formats() {
        return array('all' => true, 'my' => false, 'tag' => false);
    }

}
