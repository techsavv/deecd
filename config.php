<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

defined('DB_USER')
|| define('DB_USER', (getenv('DB_USER') ? getenv('DB_USER') : 'globitow_moodle'));

defined('DB_PASSWORD')
|| define('DB_PASSWORD', (getenv('DB_PASSWORD') ? getenv('DB_PASSWORD') : 'T3chS@vvy!'));

defined('DB_NAME')
|| define('DB_NAME', (getenv('DB_NAME') ? getenv('DB_NAME') : 'globitow_moodle2'));

defined('DB_HOST')
|| define('DB_HOST', (getenv('DB_HOST') ? getenv('DB_HOST') : 'localhost'));

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = DB_HOST;
$CFG->dbname    = DB_NAME;
$CFG->dbuser    = DB_USER;
$CFG->dbpass    = DB_PASSWORD;
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
);

if (APPLICATION_ENV === 'production') {
  $CFG->wwwroot   = 'http://deecd.tech-savvy.com.au';
  $CFG->dataroot  = '/home/techsavv/subdomains/data/moodle-deecd';
}
else {
  $CFG->wwwroot   = 'http://deecd.techsavvysolutions.com.au';
  $CFG->dataroot  = '/home/globitow/data/deecd/moodledata';
}

$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

require_once(dirname(__FILE__) . '/lib/setup.php');

$CFG->lang=‘en’;
$CFG->langcache=0;
$CFG->langstringcache=0;

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
