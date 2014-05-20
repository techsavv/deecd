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
 * Moodle renderer used to display special elements of the lesson module
 *
 * @package    mod
 * @subpackage tsblog
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

defined('MOODLE_INTERNAL') || die();

class mod_tsblog_renderer extends plugin_renderer_base {
    /**
     * Print a single blog post
     *
     * @param object $tsblog Blog object
     * @param object $post Structure containing all post info and comments
     * @param string $baseurl Base URL of current page
     * @param string $blogtype Blog level ie course or above
     * @param bool $canmanageposts Has capability toggle
     * @param bool $canaudit Has capability toggle
     * @param bool $cancomment Has capability toggle
     * @param bool $forexport Export output rendering toggle
     * @param bool $email Email output rendering toggle
     * @return bool
     */
    public function render_post($cm, $tsblog, $post, $baseurl, $blogtype,
            $canmanageposts = false, $canaudit = false, $commentcount = true,
            $forexport = false, $format = false, $email = false) {
        global $CFG, $USER;
        $output = '';
        $modcontext = get_context_instance(CONTEXT_MODULE, $cm->id);
        // Get rid of any existing tag from the URL as we only support one at a time.
        $baseurl = preg_replace('~&amp;tag=[^&]*~', '', $baseurl);

        $strcomment = get_string('comment', 'tsblog');
        $strtags = get_string('tags', 'tsblog');
        $stredit = get_string('edit', 'tsblog');
        $strdelete = get_string('delete', 'tsblog');
        $strpermalink = get_string('permalink', 'tsblog');

        $row = '';
        if (isset($post->row)) {
            $row = ($post->row % 2) ? 'tsblog-odd' : 'tsblog-even';
        }

        $extraclasses = $post->deletedby ? ' tsblog-deleted' : '';
        $extraclasses .= ' tsblog-hasuserpic';
        $extraclasses .= ' ' . $row;

        $output .= html_writer::start_tag('div', array('class' => 'tsblog-post'. $extraclasses));
        $output .= html_writer::start_tag('div', array('class' => 'tsblog-post-top'));
        $fs = get_file_storage();
        if ($files = $fs->get_area_files($modcontext->id, 'mod_tsblog', 'attachment', $post->id,
                "timemodified", false)) {
            $output .= html_writer::start_tag('div', array('class'=>'tsblog-post-attachments'));
            $output .= get_string('attachments', 'mod_tsblog') . ': ';
            foreach ($files as $file) {
                if (!$forexport && !$email) {
                    $filename = $file->get_filename();
                    $mimetype = $file->get_mimetype();
                    $iconimage = html_writer::empty_tag('img',
                            array('src' => $this->output->pix_url(file_mimetype_icon($mimetype)),
                            'alt' => $mimetype, 'class' => 'icon'));
                    if ($post->visibility == TS_BLOG_VISIBILITY_PUBLIC) {
                        $fileurlbase = '/mod/tsblog/pluginfile.php';
                    } else {
                        $fileurlbase = '/pluginfile.php';
                    }
                    $filepath = '/' . $modcontext->id . '/mod_tsblog/attachment/'
                            . $post->id . '/' . $filename;
                    $path = moodle_url::make_file_url($fileurlbase, $filepath, true);
                    $output .= html_writer::start_tag('div', array('class'=>'tsblog-post-attachment'));
                    $output .= html_writer::tag('a', $iconimage, array('href' => $path));
                    $output .= html_writer::tag('a', s($filename), array('href' => $path));
                    $output .= html_writer::end_tag('div');
                } else {
                    $filename = $file->get_filename();
                    if (is_object($format)) {
                        $output .= $format->file_output($file) . ' ';
                    } else {
                        $output .= $filename . ' ';
                    }
                }
            }
            $output .= html_writer::end_tag('div');
        }
        $output .= html_writer::start_tag('div', array('class' => 'tsblog-post-top-content'));
        if (!$forexport) {
            $output .= html_writer::start_tag('div', array('class' => 'tsblog-userpic'));
            $postuser = new object();
            $postuser->id = $post->userid;
            $postuser->firstname = $post->firstname;
            $postuser->lastname = $post->lastname;
            $postuser->email = $post->email;
            $postuser->imagealt = $post->imagealt;
            $postuser->picture = $post->picture;
            $output .= $this->output->user_picture($postuser,
                    array('courseid' => $tsblog->course, 'size' => 70));
            $output .= html_writer::end_tag('div');
        }
        $output .= html_writer::start_tag('div', array('class' => 'tsblog-post-top-details'));
        $formattedtitle = format_string($post->title);
        if (trim($formattedtitle) !== '') {
            $output .= html_writer::tag('h2',
                    format_string($post->title), array('class' => 'tsblog-title'));
        } else if (!$forexport) {
            $posttitle = get_accesshide(get_string('newpost', 'mod_tsblog',
                    tsblog_get_displayname($tsblog)));
            $output .= html_writer::tag('h2', $posttitle, array('class' => 'tsblog-title'));
        }

        if ($post->deletedby) {
            $deluser = new stdClass();
            $deluser->firstname = $post->delfirstname;
            $deluser->lastname = $post->dellastname;

            $a = new stdClass();

            $a->fullname = html_writer::tag('a', fullname($deluser),
                    array('href' => $CFG->wwwroot . '/user/view.php?id=' . $post->deletedby));
            $a->timedeleted = tsblog_date($post->timedeleted);
            $output .= html_writer::tag('div', get_string('deletedby', 'tsblog', $a),
                    array('class' => 'tsblog-post-deletedby'));
        }

        $output .= html_writer::start_tag('div', array('class' => 'tsblog-post-date'));
        $output .= tsblog_date($post->timeposted);
        $output .= html_writer::empty_tag('br', array());
        $output .= ' ';
        if ($blogtype == 'course' || strpos($_SERVER['REQUEST_URI'], 'allposts.php') != 0) {
            $output .= html_writer::start_tag('div', array('class' => 'tsblog-postedby'));
            if (!$forexport) {
                $output .= get_string('postedby', 'tsblog', '<a href="' .
                        $CFG->wwwroot.'/user/view.php?id=' . $post->userid . '&amp;course=' .
                        $tsblog->course . '">' . fullname($post) . '</a>');
            } else {
                $output .= get_string('postedby', 'tsblog',  fullname($post));
            }
            $output .= html_writer::end_tag('div');
        }
        $output .= html_writer::end_tag('div');

        if (!$tsblog->individual) {
            $output .= html_writer::start_tag('div', array('class' => 'tsblog-post-visibility'));
            $output .= tsblog_get_visibility_string($post->visibility, $blogtype == 'personal');
            $output .= html_writer::end_tag('div');
        }

        if (isset($post->edits) && ($canaudit || $post->userid == $USER->id)) {
            $output .= html_writer::start_tag('div', array('class' => 'tsblog-post-editsummary'));
            foreach ($post->edits as $edit) {
                $a = new stdClass();
                $a->editby = fullname($edit);
                $a->editdate = tsblog_date($edit->timeupdated);
                if (!$forexport && !$email) {
                    if ($edit->userid == $post->userid) {
                        $output .= '- '.html_writer::tag('a', get_string('editsummary',
                                'tsblog', $a), array('href' =>
                                $CFG->wwwroot . '/mod/tsblog/viewedit.php?edit=' . $edit->id));
                    } else {
                        $output .= '- '.html_writer::tag('a', get_string('editonsummary',
                                'tsblog', $a), array('href' =>
                                $CFG->wwwroot . '/mod/tsblog/viewedit.php?edit=' . $edit->id));
                    }
                } else {
                    if ($edit->userid == $post->userid) {
                        $output .= '- '.  get_string('editsummary', 'tsblog', $a);
                    } else {
                        $output .= '- '. get_string('editonsummary', 'tsblog', $a);
                    }
                }
                $output .= html_writer::empty_tag('br', array());
            }
            $output .= html_writer::end_tag('div');
        } else if ($post->lasteditedby) {
            $edit = new StdClass;
            $edit->firstname = $post->edfirstname;
            $edit->lastname = $post->edlastname;

            $a = new stdClass();
            $a->editby = fullname($edit);
            $a->editdate = tsblog_date($post->timeupdated);
            $output .= html_writer::tag('div', get_string('editsummary', 'tsblog', $a),
                    array('class' => 'tsblog-post-editsummary'));
        }

        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');
        $output .= html_writer::start_tag('div', array('class' => 'tsblog-post-content'));
        if (!$forexport) {
            if ($post->visibility == TS_BLOG_VISIBILITY_PUBLIC || $email) {
                $fileurlbase = 'mod/tsblog/pluginfile.php';
            } else {
                $fileurlbase = 'pluginfile.php';
            }
            $post->message = file_rewrite_pluginfile_urls($post->message, $fileurlbase,
                    $modcontext->id, 'mod_tsblog', 'message', $post->id);
        } else {
            require_once($CFG->libdir . '/portfoliolib.php');
            $post->message = portfolio_rewrite_pluginfile_urls($post->message, $modcontext->id,
                    'mod_tsblog', 'message', $post->id, $format);
        }
        $posttextoptions = new stdClass();
        if (trusttext_active() && has_capability('moodle/site:trustcontent', $modcontext,
                $post->userid)) {
            // Support trusted text when initial author is safe (post editors are not checked!).
            $posttextoptions->trusted = true;
            $posttextoptions->context = $modcontext;
        }
        $output .= format_text($post->message, FORMAT_HTML, $posttextoptions);
        $output .= html_writer::end_tag('div');
        $output .= html_writer::start_tag('div', array('class' => 'tsblog-post-bottom'));

        if (isset($post->tags)) {
            $output .= html_writer::start_tag('div', array('class' => 'tsblog-post-tags')) .
                    $strtags . ': ';
            $tagcounter = 1;
            foreach ($post->tags as $taglink) {
                $taglinktext = $taglink;
                if ($tagcounter < count($post->tags)) {
                    $taglinktext .= ',';
                }
                if (!$forexport && !$email) {
                    $output .= html_writer::tag('a', $taglinktext, array('href' => $baseurl .
                            '&tag=' . urlencode($taglink))) . ' ';
                } else {
                    $output .= $taglinktext . ' ';
                }
                $tagcounter++;
            }
            $output .= html_writer::end_tag('div');
        }

        $output .= html_writer::start_tag('div', array('class' => 'tsblog-post-links'));
        if (!$forexport && !$email) {
            $output .= html_writer::tag('a', $strpermalink, array('href' => $CFG->wwwroot .
                    '/mod/tsblog/viewpost.php?post=' . $post->id)).' ';
        }

        if (!$post->deletedby) {
            if (($post->userid == $USER->id || $canmanageposts)) {
                if (!$forexport && !$email) {
                    $output .= html_writer::tag('a', $stredit, array('href' => $CFG->wwwroot .
                            '/mod/tsblog/editpost.php?blog=' . $post->tsblogid .
                            '&post=' . $post->id)).' ';
                    if (($post->userid !== $USER->id)) {
                        // Add email and 'tsblog_deleteandemail' to delete link.
                        $output .= html_writer::tag('a', $strdelete, array('href' => $CFG->wwwroot .
                                '/mod/tsblog/deletepost.php?blog=' . $post->tsblogid .
                                '&post=' . $post->id . '&delete=1',
                                'class' => 'tsblog_deleteandemail_' . $post->id));
                        self::render_tsblog_print_delete_dialog($cm->id, $post->id);
                    } else {
                        $output .= html_writer::tag('a', $strdelete, array('href' => $CFG->wwwroot .
                                '/mod/tsblog/deletepost.php?blog=' . $post->tsblogid .
                                '&post=' . $post->id . '&delete=1'));
                    }
                }
            }
            // Show portfolio export link.
            if (!empty($CFG->enableportfolios) &&
                    (has_capability('mod/tsblog:exportpost', $modcontext) ||
                    ($post->userid == $USER->id &&
                    has_capability('mod/tsblog:exportownpost', $modcontext)))) {
                if (!$forexport && !$email) {
                    require_once($CFG->libdir . '/portfoliolib.php');
                    $button = new portfolio_add_button();
                    $button->set_callback_options('tsblog_portfolio_caller',
                            array('postid' => $post->id), 'mod_tsblog');
                    if (empty($files)) {
                        $button->set_formats(PORTFOLIO_FORMAT_PLAINHTML);
                    } else {
                        $button->set_formats(PORTFOLIO_FORMAT_RICHHTML);
                    }
                    $output .= $button->to_html(PORTFOLIO_ADD_TEXT_LINK).' ';
                }
            }
            // Show OU Alerts reporting link.
            if (isloggedin() && tsblog_oualerts_enabled()
                    && tsblog_get_reportingemail($tsblog) && !($post->userid == $USER->id)
                    && !$post->deletedby) {
                $itemnurl = new moodle_url('/mod/tsblog/viewpost.php', array('post' => $post->id));
                $reportlink = oualerts_generate_alert_form_url('tsblog', $modcontext->id,
                        'post', $post->id, $itemnurl, $itemnurl, '', false, true);
                if ($reportlink != '' && !$forexport && !$email) {
                    $output .= html_writer::tag('a', get_string('postalert', 'tsblog'),
                            array('href' => $reportlink));
                }
            }

            // Show comments.
            if ($post->allowcomments) {
                // If this is the current user's post, show pending comments too.
                $showpendingcomments = $post->userid == $USER->id && !empty($post->pendingcomments);
                if ((isset($post->comments) || $showpendingcomments) && $commentcount) {
                    // Show number of comments.
                    if (isset($post->comments)) {
                        $linktext = get_string(
                                count($post->comments) == 1 ? 'onecomment' : 'ncomments',
                                'tsblog', count($post->comments));
                    }
                    // Show number of pending comments.
                    if (isset($post->pendingcomments)) {
                        // Use different string if we already have normal comments too.
                        if (isset($post->comments)) {
                            $linktext .= get_string(
                                    $post->pendingcomments == 1 ? 'onependingafter' :
                                    'npendingafter', 'tsblog', $post->pendingcomments);
                        } else {
                            $linktext = get_string(
                                    $post->pendingcomments == 1 ? 'onepending' : 'npending',
                                    'tsblog', $post->pendingcomments);
                        }
                    }
                    if (!$forexport) {
                        // Display link.
                        $output .= html_writer::tag('a', $linktext, array('href' =>
                                $CFG->wwwroot . '/mod/tsblog/viewpost.php?post=' . $post->id . '#tsblogcomments'));
                    } else {
                        $output .= $linktext;
                    }
                    // Display information about most recent comment.
                    if (isset($post->comments)) {
                        $last = array_pop($post->comments);
                        array_push($post->comments, $last);
                        $a = new stdClass();
                        if ($last->userid) {
                            $a->fullname = fullname($last);
                        } else {
                            $a->fullname = s($last->authorname);
                        }
                        $a->timeposted = tsblog_date($last->timeposted, true);
                        $output .= ' ' . get_string('lastcomment', 'tsblog', $a);
                    }
                } else if (tsblog_can_comment($cm, $tsblog, $post)) {
                    if (!$forexport && !$email) {
                        $output .= html_writer::tag('a', $strcomment, array('href' =>
                                $CFG->wwwroot . '/mod/tsblog/editcomment.php?blog=' . $post->tsblogid .
                                '&post=' . $post->id));
                    }
                }
            }
        }
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');

        return $output;
    }

    /**
     * Print all user participation records for display
     *
     * @param object $cm current course module object
     * @param object $course current course object
     * @param object $tsblog current tsblog object
     * @param int $groupid optional group id, no group = 0
     * @param string $download download type (csv only, default '')
     * @param int $page flexible_table pagination page
     * @param array $participation mixed array of user participation values
     * @param object $context current context
     * @param bool $viewfullnames flag for global users fullnames capability
     * @param string groupname group name for display, default ''
     */
    public function render_participation_list($cm, $course, $tsblog, $groupid,
        $download, $page, $participation, $context, $viewfullnames, $groupname) {
        global $DB, $CFG, $OUTPUT;

        require_once($CFG->dirroot.'/mod/tsblog/participation_table.php');
        $perpage = TS_BLOG_PARTICIPATION_PERPAGE;

        // Filename for downloading setup.
        $filename = "$course->shortname-".format_string($tsblog->name, true);
        if (!empty($groupname)) {
            $filename .= '-'.format_string($groupname, true);
        }

        $hasgrades = !empty($participation) && isset(reset($participation)->gradeobj);
        $table = new tsblog_participation_table($cm, $course, $tsblog,
            $groupid, $groupname, $hasgrades);
        $table->setup($download);
        $table->is_downloading($download, $filename, get_string('participation', 'tsblog'));

        if (!empty($participation)) {
            if (!$table->is_downloading()) {
                if ($perpage > count($participation)) {
                    $perpage = count($participation);
                }
                $table->pagesize($perpage, count($participation));
                $offset = $page * $perpage;
                $endposition = $offset + $perpage;
            } else {
                // Always export all users.
                $endposition = count($participation);
                $offset = 0;
            }
            $currentposition = 0;
            foreach ($participation as $user) {
                if ($currentposition == $offset && $offset < $endposition) {
                    $fullname = fullname($user, $viewfullnames);

                    // Control details link.
                    $details = false;

                    // Counts.
                    $posts = 0;
                    if (isset($user->posts)) {
                        $posts = $user->posts;
                        $details = true;
                    }
                    $comments = 0;
                    if (isset($user->comments)) {
                        $comments = $user->comments;
                        $details = true;
                    }

                    // User details.
                    if (!$table->is_downloading()) {
                        $picture = $OUTPUT->user_picture($user);
                        $userurl = new moodle_url('/user/view.php?',
                            array('id' => $user->id, 'course' => $course->id));
                        $userdetails = html_writer::link($userurl, $fullname);
                        if ($details) {
                            $detailparams = array('id' => $cm->id,
                                'user' => $user->id, 'group' => $groupid);
                            $detailurl = new moodle_url('/mod/tsblog/userparticipation.php',
                                $detailparams);
                            $accesshidetext = get_string('foruser', 'tsblog', $fullname);
                            $accesshide = html_writer::tag('span', $accesshidetext,
                                array('class' => 'accesshide'));
                            $detaillink = html_writer::start_tag('small');
                            $detaillink .= ' (';
                            $detaillink .= html_writer::link($detailurl,
                                get_string('details', 'tsblog') . $accesshide);
                            $detaillink .= ')';
                            $detaillink .= html_writer::end_tag('small');
                            $userdetails .= $detaillink;
                        }
                    }

                    // Grades.
                    if ($tsblog->grade != 0 && isset($user->gradeobj)) {
                        if (!$table->is_downloading()) {
                            $attributes = array('userid' => $user->id);
                            if (empty($user->gradeobj->grade)) {
                                $user->grade = -1;
                            } else {
                                $user->grade = abs($user->gradeobj->grade);
                            }
                            $menu = html_writer::select(make_grades_menu($tsblog->grade),
                                'menu['.$user->id.']', $user->grade,
                                array(-1 => get_string('nograde')), $attributes);
                            $gradeitem = '<div id="gradeuser'.$user->id.'">'. $menu .'</div>';
                        } else {
                            if (!isset($user->gradeobj->grade)) {
                                $gradeitem = get_string('nograde');
                            } else {
                                $gradeitem = $user->gradeobj->grade;
                            }
                        }
                    }

                    // Add row.
                    if (!$table->is_downloading()) {
                        $row = array($picture, $userdetails, $posts, $comments);
                    } else {
                        $row = array($fullname, $posts, $comments);
                    }
                    if (isset($gradeitem)) {
                        $row[] = $gradeitem;
                    }
                    $table->add_data($row);
                    $offset++;
                }
                $currentposition++;
            }
        }
        $table->finish_output();
        if (!$table->is_downloading()) {
            // Print the grade form footer if necessary.
            if ($tsblog->grade != 0 && !empty($participation)) {
                echo $table->grade_form_footer();
            }
        }
    }

    /**
     * Print single user participation for display
     *
     * @param object $cm current course module object
     * @param object $course current course object
     * @param object $tsblog current tsblog object
     * @param int $userid user id of user to view participation for
     * @param int $groupid optional group id, no group = 0
     * @param string $download download type (csv only, default '')
     * @param int $page flexible_table pagination page
     * @param array $participation mixed array of user participation values
     * @param object $context current context
     * @param bool $viewfullnames flag for global users fullnames capability
     * @param string groupname group name for display, default ''
     */
    public function render_user_participation_list($cm, $course, $tsblog, $participation, $groupid,
        $download, $page, $context, $viewfullnames, $groupname, $start, $end) {
        global $DB, $CFG;

        $user = $participation->user;
        $fullname = fullname($user, $viewfullnames);

        // Setup the table.
        require_once($CFG->dirroot.'/mod/tsblog/participation_table.php');
        $filename = "$course->shortname-".format_string($tsblog->name, true);
        if ($groupname !== '') {
            $filename .= '-'.format_string($groupname, true);
        }
        $filename .= '-'.format_string($fullname, true);
        $table = new tsblog_user_participation_table($cm->id, $course, $tsblog,
            $user->id, $fullname, $groupname, $groupid, $start, $end);
        $table->setup($download);
        $table->is_downloading($download, $filename, get_string('participation', 'tsblog'));

        // Print standard output.
        $output = '';
        $modcontext = get_context_instance(CONTEXT_MODULE, $cm->id);
        if (!$table->is_downloading()) {
            $output .= html_writer::tag('h2', get_string('postsby', 'tsblog', $fullname));
            if (!$participation->posts) {
                $output .= html_writer::tag('p', get_string('nouserposts', 'tsblog'));
            } else {
                $counter = 0;
                foreach ($participation->posts as $post) {
                    $row = ($counter % 2) ? 'tsblog-odd' : 'tsblog-even';
                    $counter++;
                    $output .= html_writer::start_tag('div',
                        array('class' => 'tsblog-post ' . $row));
                    $output .= html_writer::start_tag('div',
                            array('class' => 'tsblog-post-top'));
                    // Post attachments.
                    $fs = get_file_storage();
                    if ($files = $fs->get_area_files($modcontext->id, 'mod_tsblog', 'attachment',
                            $post->id, 'timemodified', false)) {
                        $output .= html_writer::start_tag('div',
                                array('class'=>'tsblog-post-attachments'));
                        foreach ($files as $file) {
                            $filename = $file->get_filename();
                            $mimetype = $file->get_mimetype();
                            $iconimage = html_writer::empty_tag('img', array(
                                    'src' => $this->output->pix_url(file_mimetype_icon($mimetype)),
                                    'alt' => $mimetype, 'class' => 'icon'
                            ));
                            $fileurlbase = $CFG->wwwroot . '/pluginfile.php';
                            $filepath = '/' . $modcontext->id . '/mod_tsblog/attachment/'
                            . $post->id . '/' . $filename;
                            $path = moodle_url::make_file_url($fileurlbase, $filepath);
                            $output .= html_writer::tag('a', $iconimage, array('href' => $path));
                            $output .= html_writer::tag('a', s($filename), array('href' => $path));
                        }
                        $output .= html_writer::end_tag('div');
                    }
                    // Post title and date.
                    if (isset($post->title) && !empty($post->title)) {
                        $viewposturl = new moodle_url('/mod/tsblog/viewpost.php',
                            array('post' => $post->id));
                        $viewpost = html_writer::link($viewposturl, s($post->title));
                        $output .= html_writer::tag('h3', $viewpost,
                            array('class' => 'tsblog-post-title'));
                        $output .= html_writer::start_tag('div',
                            array('class' => 'tsblog-post-date'));
                        $output .= tsblog_date($post->timeposted);
                        $output .= html_writer::end_tag('div');
                    } else {
                        $viewposturl = new moodle_url('/mod/tsblog/viewpost.php',
                            array('post' => $post->id));
                        $viewpost = html_writer::link($viewposturl,
                            tsblog_date($post->timeposted));
                        $output .= html_writer::tag('h3', $viewpost,
                            array('class' => 'tsblog-post-title'));
                    }
                    $output .= html_writer::end_tag('div');
                    // Post content.
                    $output .= html_writer::start_tag('div',
                        array('class' => 'tsblog-post-content'));
                    $post->message = file_rewrite_pluginfile_urls($post->message,
                        'pluginfile.php', $modcontext->id, 'mod_tsblog',
                        'message', $post->id);
                    $output .= format_text($post->message, FORMAT_HTML);
                    $output .= html_writer::end_tag('div');

                    // End display box.
                    $output .= html_writer::end_tag('div');
                }
            }

            $output .= html_writer::tag('h2', get_string('commentsby', 'tsblog', $fullname));
            if (!$participation->comments) {
                $output .= html_writer::tag('p', get_string('nousercomments', 'tsblog'));
            } else {
                $output .= html_writer::start_tag('div',
                        array('id' => 'tsblogcomments', 'class' => 'tsblog-post-comments tsblogpartcomments'));
                foreach ($participation->comments as $comment) {
                    $output .= html_writer::start_tag('div', array('class'=>'tsblog-comment'));

                    $author = new StdClass;
                    $author->id = $comment->authorid;
                    $author->firstname = $comment->firstname;
                    $author->lastname = $comment->lastname;
                    $authorurl = new moodle_url('/user/view.php', array('id' => $author->id));
                    $authorlink = html_writer::link($authorurl, fullname($author, $viewfullnames));
                    if (isset($comment->posttitle) && !empty($comment->posttitle)) {
                        $viewposturl = new moodle_url('/mod/tsblog/viewpost.php',
                            array('post' => $comment->postid));
                        $viewpostlink = html_writer::link($viewposturl, s($comment->posttitle));
                        $strparams = array('title' => $viewpostlink, 'author' => $authorlink);
                        $output .= html_writer::tag('h3', get_string('commentonby', 'tsblog',
                            $strparams));
                    } else {
                        $viewposturl = new moodle_url('/mod/tsblog/viewpost.php',
                            array('post' => $comment->postid));
                        $viewpostlink = html_writer::link($viewposturl,
                            tsblog_date($comment->postdate));
                        $strparams = array('title' => $viewpostlink, 'author' => $authorlink);
                        $output .= html_writer::tag('h3', get_string('commentonby', 'tsblog',
                            $strparams));
                    }

                    // Comment title.
                    if (isset($comment->title) && !empty($comment->title)) {
                        $output .= html_writer::tag('h3', s($comment->title),
                            array('class' => 'tsblog-comment-title'));
                    }

                    // Comment content and date.
                    $output .= html_writer::start_tag('div',
                        array('class' => 'tsblog-comment-date'));
                    $output .= tsblog_date($comment->timeposted);
                    $output .= html_writer::end_tag('div');
                    $output .= html_writer::start_tag('div',
                        array('class' => 'tsblog-comment-content'));
                    $comment->message = file_rewrite_pluginfile_urls($comment->message,
                            'pluginfile.php', $modcontext->id, 'mod_tsblog',
                            'messagecomment', $comment->id);
                    $output .= format_text($comment->message, FORMAT_HTML);
                    $output .= html_writer::end_tag('div');

                    // End display box.
                    $output .= html_writer::end_tag('div');
                }
                $output .= html_writer::end_tag('div');
            }
            // Only printing the download buttons.
            echo $table->download_buttons();

            // Print the actual output.
            echo $output;

            // Grade.
            if (isset($participation->gradeobj)) {
                $this->render_user_grade($course, $cm, $tsblog, $participation, $groupid);
            }
        } else {
            // Posts.
            if ($participation->posts) {
                $table->add_data($table->posts);
                $table->add_data($table->postsheader);
                foreach ($participation->posts as $post) {
                    $row = array();
                    $row[] = userdate($post->timeposted, get_string('strftimedate'));
                    $row[] = userdate($post->timeposted, get_string('strftimetime'));
                    $row[] = (isset($post->title) && !empty($post->title)) ? $post->title : '';
                    $post->message = file_rewrite_pluginfile_urls($post->message,
                        'pluginfile.php', $modcontext->id, 'mod_tsblog',
                        'message', $post->id);
                    $row[] = format_text($post->message, FORMAT_HTML);
                    $fs = get_file_storage();
                    if ($files = $fs->get_area_files($modcontext->id, 'mod_tsblog', 'attachment',
                            $post->id, 'timemodified', false)) {
                        $attachmentstring = '';
                        foreach ($files as $file) {
                            $filename = $file->get_filename();
                            $attachmentstring .= ' ' . $filename . ', ';
                        }
                        $attachmentstring = substr($attachmentstring, 0, -2);
                        $row[] = $attachmentstring;
                    } else {
                        $row[] = '';
                    }
                    $table->add_data($row);
                }
            }

            // Comments.
            if ($participation->comments) {
                $table->add_data($table->comments);
                $table->add_data($table->commentsheader);
                foreach ($participation->comments as $comment) {
                    $author = new StdClass;
                    $author->id = $comment->authorid;
                    $author->firstname = $comment->firstname;
                    $author->lastname = $comment->lastname;
                    $authorfullname = fullname($author, $viewfullnames);

                    $row = array();
                    $row[] = userdate($comment->timeposted, get_string('strftimedate'));
                    $row[] = userdate($comment->timeposted, get_string('strftimetime'));
                    $row[] = (isset($comment->title)) ? $comment->title : '';
                    $comment->message = file_rewrite_pluginfile_urls($comment->message,
                            'pluginfile.php', $modcontext->id, 'mod_tsblog',
                            'messagecomment', $comment->id);
                    $row[] = format_text($comment->message, FORMAT_HTML);
                    $row[] = $authorfullname;
                    $row[] = userdate($comment->postdate, get_string('strftimedate'));
                    $row[] = userdate($comment->postdate, get_string('strftimetime'));
                    $row[] = (isset($comment->posttitle)) ? $comment->posttitle : '';
                    $table->add_data($row);
                }
            }
            $table->finish_output();
        }

    }

    /**
     * Render single users grading form
     *
     * @param object $course current course object
     * @param object $cm current course module object
     * @param object $tsblog current tsblog object
     * @param object $user current user participation object
     * @param id $groupid optional group id, no group = 0
     */
    public function render_user_grade($course, $cm, $tsblog, $user, $groupid) {
        global $CFG, $USER;

        if (is_null($user->gradeobj->grade)) {
            $user->gradeobj->grade = -1;
        }
        if ($user->gradeobj->grade != -1) {
            $user->grade = abs($user->gradeobj->grade);
        }
        $grademenu = make_grades_menu($tsblog->grade);
        $grademenu[-1] = get_string('nograde');

        $formparams = array();
        $formparams['id'] = $cm->id;
        $formparams['user'] = $user->user->id;
        $formparams['group'] = $groupid;
        $formparams['sesskey'] = $USER->sesskey;
        $formaction = new moodle_url('/mod/tsblog/savegrades.php', $formparams);
        $mform = new MoodleQuickForm('savegrade', 'post', $formaction,
            '', array('class' => 'savegrade'));
        $mform->addElement('header', 'usergrade', get_string('usergrade', 'tsblog'));
        $mform->addElement('select', 'grade', get_string('grade'), $grademenu);
        $mform->setDefault('grade', $user->gradeobj->grade);
        $mform->addElement('submit', 'savechanges', get_string('savechanges'));

        $mform->display();
    }

    /**
     * Print comments which relate to a single blog post
     *
     * @param object $post Structure containing all post info and comments
     * @param object $tsblog Blog object
     * @param bool $canmanagecomments Has capability toggle
     * @param bool $canaudit Has capability toggle
     * @param bool $forexport Export output rendering toggle
     * @param object $cm Current course module object
     * @return html
     */
    public function render_comments($post, $tsblog, $canaudit, $canmanagecomments, $forexport,
            $cm, $format = false) {
        global $DB, $CFG, $USER, $OUTPUT;
        $viewfullnames = true;
        $strdelete      = get_string('delete', 'tsblog');
        $strcomments    = get_string('comments', 'tsblog');
        $output = '';
        $modcontext = get_context_instance(CONTEXT_MODULE, $cm->id);
        if (!$canmanagecomments) {
            $context = context_module::instance($cm->id);
            $canmanagecomments = has_capability('mod/tsblog:managecomments', $context);
        }

        $output .= html_writer::start_tag('div', array('class' => 'tsblog-post-comments',
                'id' => 'tsblogcomments'));
        $counter = 0;
        foreach ($post->comments as $comment) {
            $extraclasses = $comment->deletedby ? ' tsblog-deleted' : '';
            $extraclasses .= ' tsblog-hasuserpic';

            $output .= html_writer::start_tag('div', array('class' =>
                    'tsblog-comment' . $extraclasses, 'id' => 'cid' . $comment->id));
            if ($counter == 0) {
                $output .= html_writer::tag('h2', format_string($strcomments),
                        array('class' => 'tsblog-commentstitle'));
            }
            if ($comment->deletedby) {
                $deluser = new stdClass();
                $deluser->firstname = $comment->delfirstname;
                $deluser->lastname  = $comment->dellastname;

                $a = new stdClass();
                $a->fullname = '<a href="../../user/view.php?id=' . $comment->deletedby . '">' .
                        fullname($deluser) . '</a>';
                $a->timedeleted = tsblog_date($comment->timedeleted);

                $output .= html_writer::tag('div', get_string('deletedby', 'tsblog', $a),
                        array('class' => 'tsblog-comment-deletedby'));
            }
            if ($comment->userid && !$forexport) {
                $output .= html_writer::start_tag('div', array('class' => 'tsblog-userpic'));
                $commentuser = new object();
                $commentuser->id        = $comment->userid;
                $commentuser->firstname = $comment->firstname;
                $commentuser->lastname  = $comment->lastname;
                $commentuser->email  = $comment->email;
                $commentuser->imagealt  = $comment->imagealt;
                $commentuser->picture   = $comment->picture;
                $output .= $OUTPUT->user_picture($commentuser,
                        array('courseid' => $tsblog->course, 'size' => 70));
                $output .= html_writer::end_tag('div');
            }
            if (trim(format_string($comment->title))!=='') {
                $output .= html_writer::tag('h2', format_string($comment->title),
                        array('class' => 'tsblog-title'));
            } else if (!$forexport) {
                $commenttitle = get_accesshide(get_string('newcomment', 'mod_tsblog'));
                $output .= html_writer::tag('h2', $commenttitle, array('class' => 'tsblog-title'));
            }
            $output .= html_writer::start_tag('div', array('class' => 'tsblog-post-date'));
            $output .= tsblog_date($comment->timeposted);
            $output .= html_writer::start_tag('div', array('class' => 'tsblog-postedby'));
            if ($comment->userid ) {
                if (!$forexport) {
                    $output .= get_string('postedby', 'tsblog',
                            '<a href="../../user/view.php?id=' . $comment->userid .
                            '&amp;course=' . $tsblog->course . '">' .
                            fullname($comment) . '</a>');
                } else {
                    $output .= get_string('postedby', 'tsblog', fullname($comment) );
                }
            } else {
                $output .= get_string(
                        $canaudit ? 'postedbymoderatedaudit' : 'postedbymoderated',
                        'tsblog', (object)array(
                                'commenter' => s($comment->authorname),
                                'approver' => '<a href="../../user/view.php?id=' .
                                $comment->userid . '&amp;course=' . $tsblog->course .
                                '">' . fullname($post) . '</a>',
                                'approvedate' => tsblog_date($comment->timeapproved),
                                'ip' => s($comment->authorip)));
            }
            $output .= html_writer::end_tag('div');
            $output .= html_writer::end_tag('div');
            $output .= html_writer::start_tag('div',
                    array('class' => 'tsblog-comment-content'));
            if (!$forexport) {
                if ($post->visibility == TS_BLOG_VISIBILITY_PUBLIC) {
                    $fileurlbase = 'mod/tsblog/pluginfile.php';
                } else {
                    $fileurlbase = 'pluginfile.php';
                }
                $comment->message = file_rewrite_pluginfile_urls($comment->message,
                        $fileurlbase, $modcontext->id, 'mod_tsblog', 'messagecomment',
                        $comment->id);
            } else {
                $comment->message = portfolio_rewrite_pluginfile_urls($comment->message,
                        $modcontext->id, 'mod_tsblog', 'messagecomment', $comment->id, $format);
            }
            $output .= format_text($comment->message, FORMAT_HTML);
            $output .= html_writer::end_tag('div');
            $output .= html_writer::start_tag('div',
                    array('class' => 'tsblog-post-links'));
            if (!$comment->deletedby) {
                // You can delete your own comments, or comments on your own
                // personal blog, or if you can manage comments.
                if (($comment->userid && $comment->userid == $USER->id) ||
                        ($tsblog->global && $post->userid == $USER->id) ||
                        $canmanagecomments ) {
                    if (!$forexport) {
                        $output .= '<a href="deletecomment.php?comment=' .
                                $comment->id . '">' . $strdelete.'</a>';
                    } else {
                        $output .= $strdelete;
                    }
                }
            }
            // Show OU Alerts reporting link.
            if (isloggedin() && tsblog_oualerts_enabled()
                    && tsblog_get_reportingemail($tsblog) && !($comment->userid == $USER->id)
                    && !$comment->deletedby) {
                $itmurl = new moodle_url('/mod/tsblog/viewpost.php',
                         array('post' => $post->id));
                $itemurl = $itmurl->out() . '#cid' . $comment->id;
                $retnurl = new moodle_url('/mod/tsblog/viewpost.php',
                         array('post' => $post->id));
                $returnurl = $retnurl->out() . '#cid' . $comment->id;
                $reportlink = oualerts_generate_alert_form_url('tsblog', $modcontext->id,
                        'comment', $comment->id, $itemurl, $returnurl, '', false, true);
                if ($reportlink != '') {
                    $output .= html_writer::tag('a', get_string('commentalert', 'tsblog'),
                            array('href' => $reportlink));
                }
            }

            $output .= html_writer::end_tag('div');
            $output .= html_writer::end_tag('div');
            $counter++;
        }

        $output .= html_writer::end_tag('div');
        return $output;
    }

    /**
     * Override this within theme to add content before posts in view.php
     */
    public function render_viewpage_prepost() {
        return;
    }

    function render_pre_postform($tsblog, $cm) {
        // Render 'hook' before post edit form. Override in theme.
    }

    // Blog stats renderers.

    /**
     * Output an unordered list - for accordion
     * @param string $name
     * @param array $tabs
     * @param int $default Default tab to open
     */
    public function render_stats_container($name, $tabs, $default = 1) {
        global $PAGE;
        $out = html_writer::start_tag('ul', array('class' => "tsblog-accordion tsblog-accordion-$name"));
        foreach ($tabs as $tab) {
            if (!empty($tab)) {
                $out .= html_writer::tag('li', $tab);
            }
        }
        $out .= html_writer::end_tag('ul');

        $default = get_user_preferences("tsblog_accordion_{$name}_open", $default);
        user_preference_allow_ajax_update("tsblog_accordion_{$name}_open", PARAM_INT);
        $PAGE->requires->yui_module('moodle-mod_tsblog-accordion', 'M.mod_tsblog.accordion.init',
                array($name, $default));

        return $out;
    }

    public function render_stats_view($name, $maintitle, $content, $subtitle = '', $info = '', $form = null, $ajax = false) {
        global $PAGE, $OUTPUT;
        if ($ajax) {
            // Don't render - return the data.
            $out = new stdClass();
            $out->name = $name;
            $out->maintitle = $maintitle;
            $out->maintitleclass = 'tsblog_statsview_title';
            $out->subtitle = $subtitle;
            $out->subtitleclass = 'tsblog_statsview_subtitle';
            $out->content = $content;
            $out->info = $info;
            $out->infoclass = "tsblog_{$name}_info";
            $out->containerclass = "tsblog_statsview_content_$name";
            $out->contentclass = "tsblog_statsview_innercontent_$name";
            return $out;
        }
        $out = '';
        if (!empty($subtitle)) {
            $out .= $OUTPUT->heading($subtitle, 3, 'tsblog_statsview_subtitle');
        }
        if (!empty($info)) {
            $out .= html_writer::start_tag('a', array('class' => 'block_action_tsblog', 'tabindex' => 0, 'href' => '#'));

            $minushide = '';
            $plushide = ' tsblog_displaynone';
            if ($userpref = get_user_preferences("mod_tsblog_hidestatsform_$name", false)) {
                $minushide = ' tsblog_displaynone';
                $plushide = '';
            }
            // Setup Javascript for stats view.
            user_preference_allow_ajax_update("mod_tsblog_hidestatsform_$name", PARAM_BOOL);
            $PAGE->requires->js('/mod/tsblog/module.js');
            $module = array ('name' => 'mod_tsblog');
            $module['fullpath'] = '/mod/tsblog/module.js';
            $module['requires'] = array('node', 'node-event-delegate');
            $module['strings'] = array();
            $PAGE->requires->js_init_call('M.mod_tsblog.init_showhide', array($name, $userpref), false, $module);

            $out .= $this->output->pix_icon('t/switch_minus', get_string('timefilter_close', 'tsblog'), 'moodle',
                    array('class' => 'tsblog_stats_minus' . $minushide));
            $out .= $this->output->pix_icon('t/switch_plus', get_string('timefilter_open', 'tsblog'), 'moodle',
                    array('class' => 'tsblog_stats_plus' . $plushide));
            $out .= html_writer::end_tag('a');

            // Stats bar - call once per 'view'.
            $PAGE->requires->yui_module('moodle-mod_tsblog-statsbar', 'M.mod_tsblog.statsbar.init',
                    array("tsblog_statsview_content_$name"));
            $out .= html_writer::tag('p', $info, array('class' => "tsblog_{$name}_info"));
        }
        if (!empty($form)) {
            $out .= $form->render();
        }
        $out .= html_writer::div($content, "tsblog_statsview_innercontent tsblog_statsview_innercontent_$name");
        return html_writer::div($this->output->heading($maintitle, 2), 'tsblog_statsview_title') .
            $this->output->container($out, "tsblog_statsview_content tsblog_statsview_content_$name");
    }

    /**
     * Renders the 'statsinfo' widget - info chart on a blog/post
     * @param tsblog_statsinfo $info
     * @return string
     */
    public function render_tsblog_statsinfo(tsblog_statsinfo $info) {
        global $COURSE, $OUTPUT;
        $out = '';
        // Get the avatar picture for user/group.
        if (isset($info->user->courseid)) {
            // Group not user.
            if (!$userpic = print_group_picture($info->user, $info->user->courseid, true, true, false)) {
                // No group pic set, use default user image.
                $userpic = $OUTPUT->pix_icon('u/f2', '');
            }
        } else {
            $userpic = $this->output->user_picture($info->user, array('courseid' => $COURSE->id, 'link' => false));
        }
        $avatar = html_writer::link($info->url, $userpic, array('class' => 'tsblog_statsinfo_avatar'));
        $infodiv = html_writer::start_div('tsblog_statsinfo_infocol');
        if ($info->stat) {
            $infodiv .= html_writer::start_div('tsblog_statsinfo_bar');
            $infodiv .= html_writer::tag('span', $info->stat, array('class' => 'percent_' . $info->percent));
            $infodiv .= html_writer::end_div();
        }
        $infodiv .= html_writer::div($info->label, 'tsblog_statsinfo_label');
        $infodiv .= html_writer::end_div();
        $out = $avatar . $infodiv;
        return $this->output->container($out, 'tsblog_statsinfo');
    }

    public function render_tsblog_print_delete_dialog($cmid, $postid) {
        global $PAGE;
        $PAGE->requires->js('/mod/tsblog/module.js');
        $stringlist[] = array('deleteemailpostdescription', 'tsblog');
        $stringlist[] = array('delete', 'tsblog');
        $stringlist[] = array('deleteandemail', 'tsblog');
        $stringlist[] = array('cancel', 'tsblog');
        $jsmodule = array(
                'name' => 'mod_tsblog.init_deleteandemail',
                'fullpath' => '/mod/tsblog/module.js',
                'requires' => array('base', 'event', 'node', 'panel', 'anim', 'moodle-core-notification', 'button'),
                'strings' => $stringlist);
        $PAGE->requires->js_init_call('M.mod_tsblog.init_deleteandemail', array($cmid, $postid), true, $jsmodule);
    }

}

class tsblog_statsinfo implements renderable {
    public $percent = 0;
    public $url = '';
    public $label = '';
    public $user;
    public $stat = '';

    /**
     *
     * @param stdClass $user user/group for avatar picture
     * @param int $percent Percent bar will go
     * @param string $stat Stat text shown in bar
     * @param moodle_url $url url for user pic link
     * @param string $label Label that appears under bar
     */
    public function __construct(stdClass $user, $percent, $stat, moodle_url $url, $label) {
        $this->percent = round($percent);
        $this->label = $label;
        $this->url = $url;
        $this->user = $user;
        $this->stat = $stat;
    }
}
