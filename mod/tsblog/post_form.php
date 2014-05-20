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

require_once($CFG->libdir.'/formslib.php');

class mod_tsblog_post_form extends moodleform {

    public function definition() {

        global $CFG;

        $individualblog = $this->_customdata['individual'];
        $maxvisibility = $this->_customdata['maxvisibility'];
        $allowcomments = $this->_customdata['allowcomments'];
        $edit          = $this->_customdata['edit'];
        $personal      = $this->_customdata['personal'];
        $maxbytes      = $this->_customdata['maxbytes'];
        $maxattachments = $this->_customdata['maxattachments'];

        $mform    =& $this->_form;

        $mform->addElement('header', 'general', '');

        $mform->addElement('text', 'title', get_string('title', 'tsblog'), 'size="48"');
        $mform->setType('title', PARAM_TEXT);

        $mform->addElement('editor', 'message', get_string('message', 'tsblog'),
                array('cols' => 50, 'rows' => 30),
                array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $maxbytes));
        $mform->addRule('message', get_string('required'), 'required', null, 'client');

        $mform->addElement('textarea', 'tags', get_string('tagsfield', 'tsblog'), array('cols'=>48, 'rows'=>2));
        $mform->setType('tags', PARAM_TAGLIST);
        $mform->addHelpButton('tags', 'tags', 'tsblog');

        $options = array();
        if ($allowcomments) {
            $options[TS_BLOG_COMMENTS_ALLOW] = get_string('logincomments', 'tsblog');
            if ($allowcomments >= TS_BLOG_COMMENTS_ALLOWPUBLIC
                && TS_BLOG_VISIBILITY_PUBLIC <= $maxvisibility) {
                $maybepubliccomments = true;
                $options[TS_BLOG_COMMENTS_ALLOWPUBLIC] = get_string('publiccomments', 'tsblog');
            }
            $options[TS_BLOG_COMMENTS_PREVENT] = get_string('no', 'tsblog');

            $mform->addElement('select', 'allowcomments', get_string('allowcomments', 'tsblog'), $options);
            $mform->setType('allowcomments', PARAM_INT);
            $mform->addHelpButton('allowcomments', 'allowcomments', 'tsblog');

            if (isset($maybepubliccomments)) {
                // NOTE - module.js adds a listener to allowcomments that hides/shows this element as mforms doesn't support this.
                $mform->addElement('static', 'publicwarning', '', '<div id="publicwarningmarker"></div>'. get_string('publiccomments_info', 'tsblog'));
            }
        } else {
            $mform->addElement('hidden', 'allowcomments', TS_BLOG_COMMENTS_PREVENT);
            $mform->setType('allowcomments', PARAM_INT);
        }

        $options = array();
        if (TS_BLOG_VISIBILITY_COURSEUSER <= $maxvisibility) {
            $options[TS_BLOG_VISIBILITY_COURSEUSER] = tsblog_get_visibility_string(TS_BLOG_VISIBILITY_COURSEUSER, $personal);
        }
        if (TS_BLOG_VISIBILITY_LOGGEDINUSER <= $maxvisibility) {
            $options[TS_BLOG_VISIBILITY_LOGGEDINUSER] = tsblog_get_visibility_string(TS_BLOG_VISIBILITY_LOGGEDINUSER, $personal);
        }
        if (TS_BLOG_VISIBILITY_PUBLIC <= $maxvisibility) {
            $options[TS_BLOG_VISIBILITY_PUBLIC] = tsblog_get_visibility_string(TS_BLOG_VISIBILITY_PUBLIC, $personal);
        }
        if ($individualblog > TS_BLOG_NO_INDIVIDUAL_BLOGS) {
            $mform->addElement('hidden', 'visibility', TS_BLOG_VISIBILITY_COURSEUSER);
            $mform->setType('visibility', PARAM_INT);
        } else if (TS_BLOG_VISIBILITY_COURSEUSER != $maxvisibility) {
            $mform->addElement('select', 'visibility', get_string('visibility', 'tsblog'), $options);
            $mform->setType('visibility', PARAM_INT);
            $mform->addHelpButton('visibility', 'visibility', 'tsblog');
        } else {
            $mform->addElement('hidden', 'visibility', TS_BLOG_VISIBILITY_COURSEUSER);
            $mform->setType('visibility', PARAM_INT);
        }
        if ($maxattachments > 0) {
            $mform->addElement('filemanager', 'attachments', get_string('attachments', 'tsblog'), null,
                    array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => $maxattachments));
            $mform->addHelpButton('attachments', 'attachments', 'tsblog');
        }

        if ($edit) {
            $submitstring = get_string('savechanges');
        } else {
            $submitstring = get_string('addpost', 'tsblog');
        }

        $this->add_action_buttons(true, $submitstring);

        // Hidden form vars.
        $mform->addElement('hidden', 'blog');
        $mform->setType('blog', PARAM_INT);

        $mform->addElement('hidden', 'post');
        $mform->setType('post', PARAM_INT);

    }
}
