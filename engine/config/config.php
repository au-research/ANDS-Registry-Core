<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

global $ENV;

/* What authencation class should we use to power the login/ACL? */
$config['authentication_class'] = "role_authentication";

// initial datasource details
$config['example_ds_key'] = 'example_data_source';
$config['example_ds_title'] = 'DataSource Example with 4 Registry Objects';

// Merge in the config options from global_config.php
$config = array_merge($config, $ENV);
$config[ENGINE_ENABLED_MODULE_LIST] = &$config['ENABLED_MODULES'];


$config['authenticators'] = Array(gCOSI_AUTH_METHOD_BUILT_IN => 'Built-in Authentication', gCOSI_AUTH_METHOD_LDAP=>'LDAP');
if (isset($config['shibboleth_sp']) && $config['shibboleth_sp'])
{
	$config['authenticators'][gCOSI_AUTH_METHOD_SHIBBOLETH] = 'Australian Access Federation (AAF) credentials';
	$config['default_authenticator'] = gCOSI_AUTH_METHOD_SHIBBOLETH;
}
else
{
	$config['default_authenticator'] = gCOSI_AUTH_METHOD_BUILT_IN;
}


// Default resolver
if (!isset($config['sissvoc_url']))
{
	$config['sissvoc_url'] = "http://ands3.anu.edu.au:8080/sissvoc/api/";
}

//default locale for character type conversion, instead of C or POSIX
setlocale(LC_CTYPE, 'en_AU');

// Fix URL resolution issues with aboslute URLs (for now...)
if (isset($config['default_base_url']))
{
	if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' && strpos($config['default_base_url'],"https:") == FALSE)
	{
		$default_base_url = str_replace("http:","https:",$config['default_base_url']);
	}
	else
	{
		$default_base_url = $config['default_base_url'];
	}
}
else
{
	die("Must specify an \$ENV['default_base_url'] in global_config.php");
}

/* For multiple-application environments, this "app" will be matched 
by the $_GET['app'] which is rewritten in .htaccess. The array key is
the full match (above). The active_application is the subfolder within 
applications/ that contains this application's modules.  */
$application_directives = array(
	"registry" => 
			array(	
				"base_url" => "%%BASEURL%%/registry/",
				"active_application" => "registry",
				"default_controller" => "auth/dashboard",
			),

	"portal" => 
			array(	
				"base_url" => "%%BASEURL%%/",
				"active_application" => "portal",
				"default_controller" => "home/index",
				"routes" => array(
					"topic/(:any)" => "topic/view_topic/$1",
					"themes" => "theme_page/index",
					"theme/(:any)" => "theme_page/view/$1",
					"(:any)"=>"core/dispatcher/$1",
					),
			),
	"apps" =>
			array(
				"base_url" => "%%BASEURL%%/apps/",
				"active_application" => "apps",
				"default_controller" => "uploader/index",
				"routes" => array(
                     "apps/mydois/([a-z]+)\.([a-z]+)" => "mydois/$1",
              	),
			),
	"roles" =>
			array(
				"base_url" => "%%BASEURL%%/roles/",
				"active_application" => "roles",
				"default_controller" => "role/index"
			),
	"developers" =>
			array(
				"base_url" => "%%BASEURL%%/developers/",
				"active_application" => "developers",
				"default_controller" => "documentation/index"
			)
);
$config['application_directives'] = $application_directives;
/* If no application is matched, what should we default to? */
if (PHP_SAPI == 'cli')
{
	if (array_key_exists($_SERVER['argv'][1], $application_directives))
	{
		$default_application = $_SERVER['argv'][1];
	}
	else
	{
		$default_application = 'registry';
	}
} 
else
{
	$default_application = 'portal';
}

$_GET['app'] = (!isset($_GET['app']) || $_GET['app'] == "" ? $default_application : $_GET['app']);

/* Where in the world are we anyway? */
$config['default_tz'] = 'Australia/Canberra';
date_default_timezone_set($config['default_tz']);
ini_set( 'default_charset', 'UTF-8' );

/*
|--------------------------------------------------------------------------
| Index File
|--------------------------------------------------------------------------
|
| Typically this will be your index.php file, unless you've renamed it to
| something else. If you are using mod_rewrite to remove the page set this
| variable so that it is blank.
|
*/
$config['index_page'] = '';

/*
|--------------------------------------------------------------------------
| URI PROTOCOL
|--------------------------------------------------------------------------
|
| This item determines which server global should be used to retrieve the
| URI string.  The default setting of 'AUTO' works for most servers.
| If your links do not seem to work, try one of the other delicious flavors:
|
| 'AUTO'			Default - auto detects
| 'PATH_INFO'		Uses the PATH_INFO
| 'QUERY_STRING'	Uses the QUERY_STRING
| 'REQUEST_URI'		Uses the REQUEST_URI
| 'ORIG_PATH_INFO'	Uses the ORIG_PATH_INFO
|
*/
$config['uri_protocol']	= 'AUTO';

/*
|--------------------------------------------------------------------------
| URL suffix
|--------------------------------------------------------------------------
|
| This option allows you to add a suffix to all URLs generated by CodeIgniter.
| For more information please see the user guide:
|
| http://codeigniter.com/user_guide/general/urls.html
*/

$config['url_suffix'] = '';

/*
|--------------------------------------------------------------------------
| Default Language
|--------------------------------------------------------------------------
|
| This determines which set of language files should be used. Make sure
| there is an available translation if you intend to use something other
| than english.
|
*/
$config['language']	= 'english';

/*
|--------------------------------------------------------------------------
| Default Character Set
|--------------------------------------------------------------------------
|
| This determines which character set is used by default in various methods
| that require a character set to be provided.
|
*/
$config['charset'] = 'UTF-8';

/*
|--------------------------------------------------------------------------
| Enable/Disable System Hooks
|--------------------------------------------------------------------------
|
| If you would like to use the 'hooks' feature you must enable it by
| setting this variable to TRUE (boolean).  See the user guide for details.
|
*/
$config['enable_hooks'] = FALSE;


/*
|--------------------------------------------------------------------------
| Class Extension Prefix
|--------------------------------------------------------------------------
|
| This item allows you to set the filename/classname prefix when extending
| native libraries.  For more information please see the user guide:
|
| http://codeigniter.com/user_guide/general/core_classes.html
| http://codeigniter.com/user_guide/general/creating_libraries.html
|
*/
$config['subclass_prefix'] = 'MY_';


/*
|--------------------------------------------------------------------------
| Allowed URL Characters
|--------------------------------------------------------------------------
|
| This lets you specify with a regular expression which characters are permitted
| within your URLs.  When someone tries to submit a URL with disallowed
| characters they will get a warning message.
|
| As a security measure you are STRONGLY encouraged to restrict URLs to
| as few characters as possible.  By default only these are allowed: a-z 0-9~%.:_-
|
| Leave blank to allow all characters -- but only if you are insane.
|
| DO NOT CHANGE THIS UNLESS YOU FULLY UNDERSTAND THE REPERCUSSIONS!!
|
*/
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-';


/*
|--------------------------------------------------------------------------
| Enable Query Strings
|--------------------------------------------------------------------------
|
| By default CodeIgniter uses search-engine friendly segment based URLs:
| example.com/who/what/where/
|
| By default CodeIgniter enables access to the $_GET array.  If for some
| reason you would like to disable it, set 'allow_get_array' to FALSE.
|
| You can optionally enable standard query string based URLs:
| example.com?who=me&what=something&where=here
|
| Options are: TRUE or FALSE (boolean)
|
| The other items let you set the query string 'words' that will
| invoke your controllers and its functions:
| example.com/index.php?c=controller&m=function
|
| Please note that some of the helpers won't work as expected when
| this feature is enabled, since CodeIgniter is designed primarily to
| use segment based URLs.
|
*/
$config['allow_get_array']		= TRUE;
$config['enable_query_strings'] = FALSE;
$config['controller_trigger']	= 'c';
$config['function_trigger']		= 'm';
$config['directory_trigger']	= 'd'; // experimental not currently in use

/*
|--------------------------------------------------------------------------
| Error Logging Threshold
|--------------------------------------------------------------------------
|
| If you have enabled error logging, you can set an error threshold to
| determine what gets logged. Threshold options are:
| You can enable error logging by setting a threshold over zero. The
| threshold determines what gets logged. Threshold options are:
|
|	0 = Disables logging, Error logging TURNED OFF
|	1 = Error Messages (including PHP errors)
|	2 = Debug Messages
|	3 = Informational Messages
|	4 = All Messages
|
| For a live site you'll usually only enable Errors (1) to be logged otherwise
| your log files will fill up very fast.
|
*/
$config['log_threshold'] = 0;

/*
|--------------------------------------------------------------------------
| Error Logging Directory Path
|--------------------------------------------------------------------------
|
| Leave this BLANK unless you would like to set something other than the default
| application/logs/ folder. Use a full server path with trailing slash.
|
*/
$config['log_path'] = '';

/*
|--------------------------------------------------------------------------
| Date Format for Logs
|--------------------------------------------------------------------------
|
| Each item that is logged has an associated date. You can use PHP date
| codes to set your own date formatting
|
*/
$config['log_date_format'] = 'Y-m-d H:i:s';

/*
|--------------------------------------------------------------------------
| Cache Directory Path
|--------------------------------------------------------------------------
|
| Leave this BLANK unless you would like to set something other than the default
| system/cache/ folder.  Use a full server path with trailing slash.
|
*/
$config['cache_path'] = '';

/*
|--------------------------------------------------------------------------
| Encryption Key
|--------------------------------------------------------------------------
|
| If you use the Encryption class or the Session class you
| MUST set an encryption key.  See the user guide for info.
|
*/
$config['encryption_key'] = 'dlk;df093uhjnkdfsa94123jknasdjklsda8921kljjlk';

/*
|--------------------------------------------------------------------------
| Session Variables
|--------------------------------------------------------------------------
|
| 'sess_cookie_name'		= the name you want for the cookie
| 'sess_expiration'			= the number of SECONDS you want the session to last.
|   by default sessions last 7200 seconds (two hours).  Set to zero for no expiration.
| 'sess_expire_on_close'	= Whether to cause the session to expire automatically
|   when the browser window is closed
| 'sess_encrypt_cookie'		= Whether to encrypt the cookie
| 'sess_use_database'		= Whether to save the session data to a database
| 'sess_table_name'			= The name of the session database table
| 'sess_match_ip'			= Whether to match the user's IP address when reading the session data
| 'sess_match_useragent'	= Whether to match the User Agent when reading the session data
| 'sess_time_to_update'		= how many seconds between CI refreshing Session Information
|
*/

//fix logging out thing, expire in a long time!
$config['sess_cookie_name']     = 'arms';
$config['sess_expiration']      = (isset($ENV['session_timeout']) ? $ENV['session_timeout'] : 0);
$config['sess_expire_on_close']	= FALSE;
$config['sess_encrypt_cookie']  = FALSE;
$config['sess_table_name']		= 'sessions';
$config['sess_use_database']    = TRUE;
$config['sess_match_ip']        = FALSE;
$config['sess_match_useragent'] = FALSE;
$config['sess_time_to_update']  = 10000;

/*
|--------------------------------------------------------------------------
| Cookie Related Variables
|--------------------------------------------------------------------------
|
| 'cookie_prefix' = Set a prefix if you need to avoid collisions
| 'cookie_domain' = Set to .your-domain.com for site-wide cookies
| 'cookie_path'   =  Typically will be a forward slash
| 'cookie_secure' =  Cookies will only be set if a secure HTTPS connection exists.
|
*/
$config['cookie_prefix']	= "";
$config['cookie_domain']	= "";
$config['cookie_path']		= "/";
$config['cookie_secure']	= FALSE;

/*
|--------------------------------------------------------------------------
| Global XSS Filtering
|--------------------------------------------------------------------------
|
| Determines whether the XSS filter is always active when GET, POST or
| COOKIE data is encountered
|
*/
$config['global_xss_filtering'] = FALSE;

/*
|--------------------------------------------------------------------------
| Cross Site Request Forgery
|--------------------------------------------------------------------------
| Enables a CSRF cookie token to be set. When set to TRUE, token will be
| checked on a submitted form. If you are accepting user data, it is strongly
| recommended CSRF protection be enabled.
|
| 'csrf_token_name' = The token name
| 'csrf_cookie_name' = The cookie name
| 'csrf_expire' = The number in seconds the token should expire.
*/
$config['csrf_protection'] = FALSE;
$config['csrf_token_name'] = 'csrf_test_name';
$config['csrf_cookie_name'] = 'csrf_cookie_name';
$config['csrf_expire'] = 7200;

/*
|--------------------------------------------------------------------------
| Output Compression
|--------------------------------------------------------------------------
|
| Enables Gzip output compression for faster page loads.  When enabled,
| the output class will test whether your server supports Gzip.
| Even if it does, however, not all browsers support compression
| so enable only if you are reasonably sure your visitors can handle it.
|
| VERY IMPORTANT:  If you are getting a blank page when compression is enabled it
| means you are prematurely outputting something to your browser. It could
| even be a line of whitespace at the end of one of your scripts.  For
| compression to work, nothing can be sent before the output buffer is called
| by the output class.  Do not 'echo' any values with compression enabled.
|
*/
$config['compress_output'] = FALSE;

/*
|--------------------------------------------------------------------------
| Master Time Reference
|--------------------------------------------------------------------------
|
| Options are 'local' or 'gmt'.  This pref tells the system whether to use
| your server's local time as the master 'now' reference, or convert it to
| GMT.  See the 'date helper' page of the user guide for information
| regarding date handling.
|
*/
$config['time_reference'] = 'local';


/*
|--------------------------------------------------------------------------
| Rewrite PHP Short Tags
|--------------------------------------------------------------------------
|
| If your PHP installation does not have short tag support enabled CI
| can rewrite the tags on-the-fly, enabling you to utilize that syntax
| in your view files.  Options are TRUE or FALSE (boolean)
|
*/
$config['rewrite_short_tags'] = TRUE;


/*
|--------------------------------------------------------------------------
| Reverse Proxy IPs
|--------------------------------------------------------------------------
|
| If your server is behind a reverse proxy, you must whitelist the proxy IP
| addresses from which CodeIgniter should trust the HTTP_X_FORWARDED_FOR
| header in order to properly identify the visitor's IP address.
| Comma-delimited, e.g. '10.0.1.200,10.0.1.201'
|
*/
$config['proxy_ips'] = '';

/*
$default_base_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
$default_base_url .= '://'. (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "cli");
$default_base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
*/

$config['default_base_url'] = $default_base_url;

$config['app_routes'] = array();
// Portal is the default app
if (!array_key_exists($_GET['app'], $application_directives))
{
	$_GET['app'] = "portal";
}

/* Reroute our requests and setup the CI routing environment based on the active application */
if (isset($application_directives[$_GET['app']]))
{
	$active_application = $application_directives[$_GET['app']]['active_application'];
	$base_url = str_replace("%%BASEURL%%/", $default_base_url, $application_directives[$_GET['app']]['base_url']);
	$_SERVER['SCRIPT_NAME'] = dirname($_SERVER['SCRIPT_NAME']) . "/" . $active_application . '/';

	/* What is the default controller for this app? (will be inserted as the default route) */
	$config['default_controller'] = $application_directives[$_GET['app']]['default_controller'];
	$config['app_routes'] = (isset($application_directives[$_GET['app']]['routes']) ? $application_directives[$_GET['app']]['routes'] : array());
	define("APP_PATH",'./applications/'.$active_application.'/');
}
else
{
	$active_application = "unknown";
	$base_url = "";
}



$config['active_application'] = $active_application;

$config['modules_locations'] = array(
       'applications/'.$active_application . '/' => '../../applications/'.$active_application . '/',
);


/*
|--------------------------------------------------------------------------
| Base Site URL
|--------------------------------------------------------------------------
|
| URL to your CodeIgniter root. Typically this will be your base URL,
| WITH a trailing slash:
|
|	http://example.com/
|
| If this is not set then CodeIgniter will guess the protocol, domain and
| path to your installation.
|
*/
$config['base_url']	= $base_url;
$config['solr_url'] = $ENV['solr_url'];


/*
HTML Purifier config
 */

$config['Core_Encoding'] = 'UTF-8';
$config['HTML_Doctype'] = 'XHTML 1.0 Transitional';
$config['HTML_AllowedElements'] = 'a, abbr, acronym, b, blockquote, br, caption, cite, code, dd, del, dfn, div, dl, dt, em, h1, h2, h3, h4, h5, h6, i, img, ins, kbd, li, ol, p, pre, s, span, strike, strong, sub, sup, table, tbody, td, tfoot, th, thead, tr, tt, u, ul, var';
$config['HTML_AllowedAttributes'] = 'a.href, a.rev, a.title, a.target, a.rel, abbr.title, acronym.title, blockquote.cite, div.align, div.class, div.id, img.src, img.alt, img.title, img.class, img.align, span.class, span.id, table.class, table.id, table.border, table.cellpadding, table.cellspacing, table.width, td.abbr, td.align, td.class, td.id, td.colspan, td.rowspan, td.valign, tr.align, tr.class, tr.id, tr.valign, th.abbr, th.align, th.class, th.id, th.colspan, th.rowspan, th.valign, img.width, img.height, img.style';
$config['Cache_DefinitionImpl'] = null;

/* End of file config.php */
/* Location: ./application/config/config.php */
