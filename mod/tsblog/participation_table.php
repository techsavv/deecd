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
 * Subclass of flexible_table for participation and download
 *
 * @package    mod
 * @subpackage tsblog
 * @copyright 2011 The Open University
 * @author Stacey Walker <stacey@catalyst-eu.net>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

require_once($CFG->libdir.'/tablelib.php');


/**
 * Class tsblog_participation_table
 * extends flexible_table to override header and download rules
 */
class tsblog_participation_table extends flexible_table {

    public $cm;
    public $course;
    public $tsblog;
    public $groupid;
    public $groupname;
    public $extraheaders;
    private $hasgrades;

    public function __construct($cm, $course, $tsblog, $groupid = 0,
        $groupname, $hasgrades) {

        $this->cm = $cm;
        $this->course = $course;
        $this->tsblog = $tsblog;
        $this->groupid = $groupid;
        $this->groupname = $groupname;
        $this->hasgrades = $hasgrades;
        parent::__construct('mod-tsblog-participation');
    }

    /**
     * Setup the columns and headers and other properties of the table and then
     * call flexible_table::setup() method.
     */
    public function setup($download = '') {
        global $CFG;

        // extra headers for export only
        if (!empty($download)) {
            $this->extraheaders = array(
                format_string($this->course->shortname, true),
                format_string($this->tsblog->name, true),
            );
            if (!empty($this->groupname)) {
                $this->extraheaders[] = $this->groupname;
            }
        }

        // Define table columns
        $columns = array(
            'picture',
            'fullname',
            'posts',
            'comments'
        );
        $headers = array(
            '',
            get_string('user'),
            get_string('posts', 'tsblog'),
            get_string('comments', 'tsblog'),
        );

        // unset picture column + headers if download
        if (!empty($download)) {
            unset($columns[0]);
            unset($headers[0]);
        }

        if ($this->hasgrades) {
            $columns[] = 'grade';
            $headers[] = get_string('grades');
        }

        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->define_baseurl($CFG->wwwroot . '/mod/tsblog/participation.php?id=' .
            $this->cm->id . '&amp;group=' . $this->groupid);

        $this->column_class('fullname', 'fullname');
        $this->column_class('posts', 'posts');
        $this->column_class('comments', 'comments');

        $this->set_attribute('cellspacing', '0');
        $this->set_attribute('id', 'participation');
        $this->set_attribute('class', 'generaltable');
        $this->set_attribute('width', '100%');
        $this->set_attribute('align', 'center');
        $this->sortable(false);

        parent::setup();
    }

    /**
     * This function is not part of the public api.
     *
     * Overriding here to avoid downloading in unsupported formats
     */
    public function get_download_menu() {
        $exportclasses = array('csv' => get_string('downloadcsv', 'table'));
        return $exportclasses;
    }

    /**
     * This function is not part of the public api.
     * You don't normally need to call this. It is called automatically when
     * needed when you start adding data to the table.
     *
     */
    public function start_output() {
        $this->started_output = true;
        if ($this->exportclass !== null) {
            $this->exportclass->start_table($this->sheettitle);
            $this->exportclass->output_headers($this->extraheaders);
            $this->exportclass->output_headers($this->headers);
        } else {
            $this->start_html();
            $this->print_headers();
        }
    }

    /**
     * Override to output grade form header
     * @see flexible_table::wrap_html_start()
     */
    public function wrap_html_start() {
        if ($this->hasgrades && !$this->is_downloading()) {
            echo $this->grade_form_header();
        }
    }

    public function grade_form_header() {
        global $USER;
        $output = '';
        $formattrs = array();
        $formattrs['action'] = new moodle_url('/mod/tsblog/savegrades.php');
        $formattrs['id']     = 'savegrades';
        $formattrs['method'] = 'post';
        $output = html_writer::start_tag('form', $formattrs);
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id',
            'value' => $this->cm->id));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'group',
            'value' => $this->groupid));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey',
            'value' => $USER->sesskey));
        return $output;
    }

    public function grade_form_footer() {
        $output = '';
        $savegrades = html_writer::empty_tag('input', array('type' => 'submit',
            'name' => 'savegrades', 'value' => get_string('savegrades', 'tsblog')));
        $output = html_writer::tag('div', $savegrades, array('class' => 'savegradesbutton'));
        $output .= html_writer::end_tag('form');
        return $output;
    }

}


/**
 * Class tsblog_user_participation_table
 * extends flexible_table to override header and download rules
 *
 * The table for the context of tsblog is only used for facilitating
 * the CSV download all other view is done using standard tsblog divs
 * and classes
 */
class tsblog_user_participation_table extends flexible_table {

    public $cmid;
    public $course;
    public $tsblog;
    public $userid;
    public $userfullname;
    public $groupname;
    public $groupid;
    public $start;
    public $end;

    // Customised column and header info
    public $detailsheader;
    public $postsheader;
    public $commentsheader;
    public $posts;
    public $comments;

    public function __construct($cmid, $course, $tsblog, $userid, $userfullname,
        $groupname, $groupid, $start, $end) {

        $this->cmid = $cmid;
        $this->course = $course;
        $this->tsblog = $tsblog;
        $this->userid = $userid;
        $this->userfullname = $userfullname;
        $this->groupname = $groupname;
        $this->groupid = $groupid;
        $this->start = $start;
        $this->end = $end;
        parent::__construct('mod-tsblog-user-participation');
    }

    public function setup($download = '') {
        global $CFG;

        // Extra headers
        $this->postsheader = array(
            get_string('date'),
            get_string('time'),
            get_string('title', 'tsblog'),
            get_string('content'),
            get_string('attachments', 'tsblog'),
        );

        $this->commentsheader = array(
            get_string('date'),
            get_string('time'),
            get_string('title', 'tsblog'),
            get_string('content'),
            get_string('postauthor', 'tsblog'),
            get_string('postdate', 'tsblog'),
            get_string('posttime', 'tsblog'),
            get_string('posttitle', 'tsblog'),
        );
        $this->posts = array(get_string('posts', 'tsblog'));
        $this->comments = array(get_string('comments', 'tsblog'));

        $headers = array(
            format_string($this->course->shortname, true),
            format_string($this->tsblog->name, true),
        );
        if (!empty($this->groupname)) {
            $headers[] = $this->groupname;
        }
        $headers[] = $this->userfullname;

        // set columns as the maximum otherwise the table
        // won't add_data correctly
        $columns = array();
        for ($i = 1; $i <= 8; $i++) {
            $columns[] = 'column'.$i;
        }

        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->define_baseurl($CFG->wwwroot . '/mod/tsblog/userparticipation.php?id=' .
            $this->cmid . '&amp;user=' . $this->userid . '&amp;group=' . $this->groupid .
                '&amp;start=' . $this->start .'&amp;end=' . $this->end);

        $this->set_attribute('cellspacing', '0');
        $this->set_attribute('id', 'participation');
        $this->set_attribute('class', 'participation');
        $this->set_attribute('width', '100%');
        $this->set_attribute('align', 'center');
        $this->sortable(false);

        parent::setup();
    }

    /**
     * This function is not part of the public api.
     *
     * Overriding here to avoid downloading in unsupported formats
     */
    public function get_download_menu() {
        $exportclasses = array('csv' => get_string('downloadcsv', 'table'));
        return $exportclasses;
    }

    /**
     * This function is not part of the public api.
     *
     * Overriding here to only print the download button on this page
     */
    public function download_buttons() {
        $html = '';
        if ($this->is_downloadable() && !$this->is_downloading()) {
            $downloadoptions = $this->get_download_menu();
            $html = '<form action="'. $this->baseurl .'" method="post">';
            $html .= '<div class="mdl-align">';
            $html .= '<input type="submit" value="'.get_string('downloadas', 'tsblog').'"/>';
            $html .= html_writer::select($downloadoptions, 'download',
                $this->defaultdownloadformat, false);
            $html .= '</div></form>';
        }
        return $html;
    }
}
