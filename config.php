<?php  // Moodle configuration file

defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'techsavv_deecd';
$CFG->dbuser    = 'techsavv_moodle';
$CFG->dbpass    = 'T3chS@vvy!';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbsocket' => 0,
);

if (APPLICATION_ENV == 'production') {
  $CFG->wwwroot   = 'http://deecd.tech-savvy.com.au';
  $CFG->dataroot  = '/home/techsavv/moodledata_deecd';
}
else {
  $CFG->wwwroot   = 'http://deecd.damiandennis.com';
  $CFG->dataroot  = '/home/deecd/public_html/moodledata';
}
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
