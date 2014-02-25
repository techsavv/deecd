<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'tech_savvy_deecd';
$CFG->dbuser    = 'tech_savvy_user';
$CFG->dbpass    = 'tech@123';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
);

$CFG->wwwroot   = 'http://deecd.sandeepgill.com.au';
$CFG->dataroot  = '/var/www/TECH_SAVVY/DEECD_DEV/moodledata';

$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

require_once(dirname(__FILE__) . '/lib/setup.php');

$CFG->theme = 'mcb';

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
