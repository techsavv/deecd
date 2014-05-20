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

// Dodgy hack to setup the global blog instance (section not created yet on install).
if (!isset($CFG->tsblogsetup)) {
    if ($pbcm = get_coursemodule_from_instance('tsblog', 1 , SITEID, 1)) {
        $mod = new stdClass();
        $mod->id= $pbcm->id;
        $mod->section = course_add_cm_to_section($pbcm->course, $pbcm->id, 1);
        $DB->update_record('course_modules', $mod);
    }
    set_config('tsblogsetup', true);
}

$module = new stdClass();
require($CFG->dirroot . '/mod/tsblog/version.php');
$settings->add(new admin_setting_heading('tsblog_version', '',
    get_string('displayversion', 'tsblog', $module->displayversion)));

if (isset($CFG->maxbytes)) {
    // Default maximum size for attachments allowed per post per tsblog.
    $settings->add(new admin_setting_configselect('mod_tsblog/maxbytes',
            get_string('maxattachmentsize', 'tsblog'),
            get_string('configmaxbytes', 'tsblog'), 512000, get_max_upload_sizes($CFG->maxbytes)));
}

// Default number of attachments allowed per post in all tsblogs.
$settings->add(new admin_setting_configtext('mod_tsblog/maxattachments',
        get_string('maxattachments', 'tsblog'),
        get_string('configmaxattachments', 'tsblog'), 9, PARAM_INT));

$settings->add(new admin_setting_configcheckbox('tsblogallpostslogin',
        get_string('tsblogallpostslogin', 'tsblog'), get_string('tsblogallpostslogin_desc', 'tsblog'), 1));

$settings->add(new admin_setting_configtext('mod_tsblog/globalusageexclude',
        get_string('globalusageexclude', 'tsblog'), get_string('globalusageexclude_desc', 'tsblog'), ''));

$settings->add(new admin_setting_configtext('mod_tsblog/remoteserver',
        get_string('remoteserver', 'tsblog'),
        get_string('configremoteserver', 'tsblog'), '', PARAM_URL));
$settings->add(new admin_setting_configtext('mod_tsblog/remotetoken',
        get_string('remotetoken', 'tsblog'),
        get_string('configremotetoken', 'tsblog'), '', PARAM_ALPHANUM));
