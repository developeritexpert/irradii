<?php
date_default_timezone_set('America/Los_Angeles');
//ob_start();

//#define('DATABASE_SERVER', 'live.cypu3wsnk6wt.us-west-2.rds.amazonaws.com');  ////////Database server name
// define('DATABASE_SERVER', 'localhost');
define('DATABASE_SERVER', '127.0.0.1');

// define('DATABASE_NAME', 'bucontra_propertyhookup'); ////////Database  name
define('DATABASE_NAME', 'ippraisall');

//#define('DATABASE_USERNAME', 'irradii'); ////////Database user name
define('DATABASE_USERNAME', 'root');

//#define('DATABASE_PASSWORD', $_ENV['LEGACY_DB_PASSWORD']);   ////////Database Password
// define('DATABASE_PASSWORD', 'cerfgflkf123');

define('DATABASE_PASSWORD', 'password');
define('TESTING', false);


define("HTTP_BASE","http://www.propertyhookup.com/");

define("HTTPS_BASE","http://www.propertyhookup.com/");

define("HTTPS_DIRECTORY",HTTPS_BASE."members/");

define("BASE","http://www.propertyhookup.com/");

define("ROOT_BASE","/var/www/html/irradi_latest/crons/");

//define('REDIS_SERVER', 'redis-server');  ////////redis server name redis-server
define('REDIS_SERVER', 'localhost');
define('REDIS_PORT', 6379); ////////redis  port
