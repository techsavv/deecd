<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('block_course_participants_timetosee', get_string('timetosee', 'block_course_participants'),
                   get_string('configtimetosee', 'block_course_participants'), 5, PARAM_INT));
}

