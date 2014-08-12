<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
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
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A registry table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By registry there is only one group (the 'registry' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$active_group = 'registry';
$active_record = TRUE;

// Temporary workaround to pull this from the global_config.php
global $eDBCONF;

foreach($eDBCONF as $groupname=>$value)
{
	if(!isset($eDBCONF[$groupname]['hostname'])) $eDBCONF[$groupname]['hostname'] = $eDBCONF['default']['hostname'];
	if(!isset($eDBCONF[$groupname]['username'])) $eDBCONF[$groupname]['username'] = $eDBCONF['default']['username'];
	if(!isset($eDBCONF[$groupname]['password'])) $eDBCONF[$groupname]['password'] = $eDBCONF['default']['password'];
	if(!isset($eDBCONF[$groupname]['dbdriver'])) $eDBCONF[$groupname]['dbdriver'] = $eDBCONF['default']['dbdriver'];
	if(!isset($eDBCONF[$groupname]['dbprefix'])) $eDBCONF[$groupname]['dbprefix'] = '';
	if(!isset($eDBCONF[$groupname]['pconnect'])) $eDBCONF[$groupname]['pconnect'] = FALSE;
	if(!isset($eDBCONF[$groupname]['db_debug'])) $eDBCONF[$groupname]['db_debug'] = FALSE;
	if(!isset($eDBCONF[$groupname]['cache_on'])) $eDBCONF[$groupname]['cache_on'] = FALSE;
	if(!isset($eDBCONF[$groupname]['cachedir'])) $eDBCONF[$groupname]['cachedir'] = '';
	if(!isset($eDBCONF[$groupname]['char_set'])) $eDBCONF[$groupname]['char_set'] = 'utf8';
	if(!isset($eDBCONF[$groupname]['dbcollat'])) $eDBCONF[$groupname]['dbcollat'] = 'utf8_general_ci';
	if(!isset($eDBCONF[$groupname]['swap_pre'])) $eDBCONF[$groupname]['swap_pre'] = '';
	if(!isset($eDBCONF[$groupname]['autoinit'])) $eDBCONF[$groupname]['autoinit'] = TRUE;
	if(!isset($eDBCONF[$groupname]['stricton'])) $eDBCONF[$groupname]['stricton'] = FALSE;
	if(!isset($eDBCONF[$groupname]['save_queries'])) $eDBCONF[$groupname]['save_queries'] = FALSE; 
}

$db = $eDBCONF;

/* End of file database.php */
/* Location: ./application/config/database.php */

