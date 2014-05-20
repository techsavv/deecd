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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_tsblog_activity_task
 */

/**
 * Structure step to restore one tsblog activity
 */
class restore_tsblog_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('tsblog', '/activity/tsblog');

        if ($userinfo) {
            $paths[] = new restore_path_element('tsblog_instance', '/activity/tsblog/instances/instance');
            $paths[] = new restore_path_element('tsblog_link', '/activity/tsblog/links/link');
            $paths[] = new restore_path_element('tsblog_post', '/activity/tsblog/instances/instance/posts/post');
            $paths[] = new restore_path_element('tsblog_comment', '/activity/tsblog/instances/instance/posts/post/comments/comment');
            $paths[] = new restore_path_element('tsblog_edit', '/activity/tsblog/instances/instance/posts/post/edits/edit');
            $paths[] = new restore_path_element('tsblog_tag', '/activity/tsblog/instances/instance/posts/post/tags/tag');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_tsblog($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        if (!isset($data->intro) && isset($data->summary)) {
            $data->intro = $data->summary;
            $data->introformat = FORMAT_HTML;
        }

        // if it's the global blog and we already have one then assume we can't restore this module since it already exits
        if (!empty($data->global) && $DB->record_exists('tsblog', array('global'=> 1))) {
            $this->set_mapping('tsblog', $oldid, $oldid, true);
            return(true);
        }

        // insert the tsblog record
        $newitemid = $DB->insert_record('tsblog', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_tsblog_instance($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->tsblogid = $this->get_new_parentid('tsblog');
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('tsblog_instances', $data);
        $this->set_mapping('tsblog_instance', $oldid, $newitemid, true);
    }

    protected function process_tsblog_link($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->tsblogid = $this->get_new_parentid('tsblog');
        $data->tsbloginstancesid =  $this->get_mappingid('tsblog_instance', $data->tsbloginstancesid);

        $newitemid = $DB->insert_record('tsblog_links', $data);
        $this->set_mapping('tsblog_link', $oldid, $newitemid);
    }

    protected function process_tsblog_post($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->tsbloginstancesid = $this->get_new_parentid('tsblog_instance');
        $data->groupid = $this->get_mappingid('group', $data->groupid);

        // Following comment copied from old 1.9 restore code.
        // Currently TSBlog has no "start time" or "deadline" fields
        // that make sense to offset at restore time. Edit and delete times
        // must remain stable even through restores with startdateoffsets.

        $newitemid = $DB->insert_record('tsblog_posts', $data);
        $this->set_mapping('tsblog_post', $oldid, $newitemid, true);
    }

    protected function process_tsblog_comment($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->postid = $this->get_new_parentid('tsblog_post');
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('tsblog_comments', $data);
        $this->set_mapping('tsblog_comment', $oldid, $newitemid, true);
    }

    protected function process_tsblog_edit($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->postid = $this->get_new_parentid('tsblog_post');
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('tsblog_edits', $data);
        $this->set_mapping('tsblog_edit', $oldid, $newitemid, true);
    }

    protected function process_tsblog_tag($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // First check to see if tag exists:
        $existingtag = $DB->get_record('tsblog_tags', array('tag'=>$data->tag));
        if (empty($existingtag->id)) {
            $tag = new stdclass();
            $tag->tag = $data->tag;
            $tagid = $DB->insert_record('tsblog_tags', $tag);
        } else {
            $tagid = $existingtag->id;
        }
        // Now insert taginstance record.
        $taginstance = new stdclass();
        $taginstance->tsbloginstancesid = $this->get_new_parentid('tsblog_instance');
        $taginstance->postid = $this->get_new_parentid('tsblog_post');
        $taginstance->tagid = $tagid;
        $newitemid = $DB->insert_record('tsblog_taginstances', $taginstance);

        $this->set_mapping('tsblog_tag', $oldid, $newitemid);
    }

    protected function after_execute() {

        // Add tsblog related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_tsblog', 'intro', null);
        $this->add_related_files('mod_tsblog', 'summary', 'tsblog_instance');
        // Add post related files
        $this->add_related_files('mod_tsblog', 'attachment', 'tsblog_post');
        $this->add_related_files('mod_tsblog', 'message', 'tsblog_post');
        $this->add_related_files('mod_tsblog', 'messagecomment', 'tsblog_comment');
        $this->add_related_files('mod_tsblog', 'edit', 'tsblog_edit');
    }
}
