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
 * Define all the backup steps that will be used by the backup_ts_blog_activity_task
 */

/**
 * Define the complete ts_blog structure for backup, with file and id annotations
 */
class backup_ts_blog_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $ts_blog = new backup_nested_element('ts_blog', array('id'), array('name', 'course',
                'accesstoken', 'intro', 'introformat', 'allowcomments', 'individual',
                'maxbytes', 'maxattachments', 'maxvisibility', 'global', 'views',
                'completionposts', 'completioncomments', 'reportingemail', 'displayname',
                'statblockon', 'allowimport'));

        $instances = new backup_nested_element('instances');

        $instance = new backup_nested_element('instance', array('id'), array(
            'userid', 'name', 'summary', 'accesstoken', 'views'));

        $links = new backup_nested_element('links');
        $link = new backup_nested_element('link', array('id'), array('title', 'url', 'sortorder'));

        $posts = new backup_nested_element('posts');
        $post  = new backup_nested_element('post', array('id'), array('groupid', 'title',
                                                                             'message', 'timeposted', 'allowcomments',
                                                                             'timeupdated', 'lasteditedby', 'deletedby',
                                                                             'timedeleted', 'visibility'));

        $comments = new backup_nested_element('comments');
        $comment  = new backup_nested_element('comment', array('id'), array('userid', 'title', 'message',
                                                                                   'timeposted', 'deletedby', 'timedeleted',
                                                                                    'authorname', 'authorip', 'timeapproved'));

        $edits = new backup_nested_element('edits');
        $edit  = new backup_nested_element('edit', array('id'), array('userid', 'oldtitle',
                                                                             'oldmessage', 'timeupdated'));

        $taginstances = new backup_nested_element('tags');
        $taginstance  = new backup_nested_element('tag', array('id'), array('tag'));

        // Build the tree
        $ts_blog->add_child($instances);
        $instances->add_child($instance);

        $ts_blog->add_child($links);
        $links->add_child($link);

        $instance->add_child($posts);
        $posts->add_child($post);

        $post->add_child($comments);
        $comments->add_child($comment);

        $post->add_child($edits);
        $edits->add_child($edit);

        $post->add_child($taginstances);
        $taginstances->add_child($taginstance);

        // Define sources
        $ts_blog->set_source_table('ts_blog', array('id' => backup::VAR_ACTIVITYID));

        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
            $instance->set_source_table('ts_blog_instances', array('ts_blogid'=> backup::VAR_PARENTID));
            $link->set_source_table('ts_blog_links', array('ts_bloginstancesid'=> backup::VAR_PARENTID));
            $post->set_source_table('ts_blog_posts', array('ts_bloginstancesid'=> backup::VAR_PARENTID));
            $comment->set_source_table('ts_blog_comments', array('postid'=> backup::VAR_PARENTID));
            $edit->set_source_table('ts_blog_edits', array('postid'=> backup::VAR_PARENTID));
            $taginstance->set_source_sql("SELECT t.id, t.tag
                                          FROM {ts_blog_tags} t
                                          JOIN {ts_blog_taginstances} ti
                                           ON t.id=ti.tagid
                                          WHERE ti.postid=?", array(backup::VAR_PARENTID));
        }

        // Define id annotations
        $instance->annotate_ids('user', 'userid');
        $post->annotate_ids('group', 'groupid');
        $post->annotate_ids('user', 'lasteditedby');
        $post->annotate_ids('user', 'deletedby');
        $comment->annotate_ids('user', 'userid');
        $edit->annotate_ids('user', 'userid');
        $link->annotate_ids('ts_blog_instances', 'id');

        // Define file annotations
        $ts_blog->annotate_files('mod_ts_blog', 'intro', null); // This file area hasn't itemid
        $instance->annotate_files('mod_ts_blog', 'summary', 'id');
        $post->annotate_files('mod_ts_blog', 'attachment', 'id');
        $post->annotate_files('mod_ts_blog', 'message', 'id');
        $edit->annotate_files('mod_ts_blog', 'edit', 'id');
        $comment->annotate_files('mod_ts_blog', 'messagecomment', 'id');

        // Return the root element (ts_blog), wrapped into standard activity structure
        return $this->prepare_activity_structure($ts_blog);
    }
}
