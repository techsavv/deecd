<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('block_courseadmin_allowcssclasses', get_string('allowadditionalcssclasses', 'block_courseadmin'),
                       get_string('configallowadditionalcssclasses', 'block_courseadmin'), 0));
}


