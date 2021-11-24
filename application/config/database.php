<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the "Database Connection"
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
| - NOTE: ['dbcollat'] is only used in the ‘MySQLi’ driver.
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the "default" group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$active_group = "default";
$active_record = TRUE;

$db['default']['hostname'] = "127.0.0.1";

// MySQL
$db['default']['username'] = "root";
$db['default']['password'] = "password";
$db['default']['database'] = "kalkun";
$db['default']['dbdriver'] = "mysqli";
$db['default']['char_set'] = "utf8mb4";
$db['default']['dbcollat'] = "utf8mb4_general_ci";

// PostgreSQL
// $db['default']['username'] = "postgres";
// $db['default']['password'] = "password";
// $db['default']['database'] = "kalkun";
// $db['default']['dbdriver'] = "postgre";

// SQLite3
// $db['default']['database'] = "sqlite:/path/to/kalkun.sqlite";
// $db['default']['dbdriver'] = "pdo";

$db['default']['dbprefix'] = "";
$db['default']['pconnect'] = FALSE;
$db['default']['db_debug'] = TRUE;
$db['default']['cache_on'] = FALSE;
$db['default']['cachedir'] = "";

// Character set for non-MySQLi drivers
if (strcasecmp($db['default']['dbdriver'], "mysqli") != 0) {
	$db['default']['char_set'] = "utf8";
}

/* End of file database.php */
/* Location: ./application/config/database.php */
