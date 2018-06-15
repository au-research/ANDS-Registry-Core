<?php

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');

define('EXTRIF_NAMESPACE', "http://ands.org.au/standards/rif-cs/extendedRegistryObjects");
define('RIFCS_NAMESPACE', "http://ands.org.au/standards/rif-cs/registryObjects");
define('EXTRIF_SCHEME', 'extrif');
define('OAI_NAMESPACE', "http://www.openarchives.org/OAI/2.0/");
define('RIFCS_SCHEME','rif');

define('BR','<br/>');
define('NL',"\n");
define('TAB',"\t");

define('DB_TRUE',1);
define('DB_FALSE',0);

define('ENGINE_ENABLED_MODULE_LIST','ENGINE_ENABLED_MODULE_LIST');


/*
 * Authentication Methods
 */
define('gCOSI_AUTH_METHOD_BUILT_IN', 'AUTHENTICATION_BUILT_IN');
define('gCOSI_AUTH_METHOD_LDAP', 'AUTHENTICATION_LDAP');
define('gCOSI_AUTH_METHOD_SHIBBOLETH', 'AUTHENTICATION_SHIBBOLETH');

define('gCOSI_AUTH_ROLE_FUNCTIONAL', 'ROLE_FUNCTIONAL');
define('gCOSI_AUTH_ROLE_ORGANISATIONAL', 'ROLE_ORGANISATIONAL');
define('gCOSI_COSI_ADMIN', 'COSI_ADMIN');


define('gCOSI_AUTH_LDAP_HOST', "ldap://ldap.anu.edu.au");
define('gCOSI_AUTH_LDAP_PORT', 389); // 636 | 389
define('gCOSI_AUTH_LDAP_BASE_DN', "ou=People, o=anu.edu.au");
// The resource distinguished name.
// The string @@ROLE_ID@@ will be replace with the user role_id, and escaped
// for LDAP reserved characters before the bind is attempted.
define('gCOSI_AUTH_LDAP_UID', "uid=@@ROLE_ID@@"); 
define('gCOSI_AUTH_LDAP_DN', gCOSI_AUTH_LDAP_UID . ", " . gCOSI_AUTH_LDAP_BASE_DN); 

define('gSHIBBOLETH_SESSION_INITIATOR', '/Shibboleth.sso/Login');

define('gPIDS_IDENTIFIER_SUFFIX','researchdata.ands.org.au');

define('AUTH_USER_FRIENDLY_NAME', 'USER_FRIENDLY_NAME');
define('AUTH_DEFAULT_FRIENDLY_NAME', 'unnamed user');
define('AUTH_USER_IDENTIFIER','UNIQUE_USER_IDENTIFIER');
define('AUTH_METHOD','AUTH_METHOD');
define('AUTH_DOMAIN','AUTH_DOMAIN');
define('PIDS_USER_IDENTIFIER','PIDS_USER_IDENTIFIER');
define('PIDS_USER_DOMAIN','PIDS_USER_DOMAIN');

define('AUTH_FUNCTION_ARRAY', 'registry_functions');
define('AUTH_FUNCTION_DEFAULT_ATTRIBUTE', 'PUBLIC');
define('AUTH_FUNCTION_LOGGED_IN_ATTRIBUTE','AUTHENTICATED_USER');
define('AUTH_FUNCTION_SUPERUSER','REGISTRY_SUPERUSER');

define('AUTH_AFFILIATION_ARRAY', 'registry_affiliations');

/* classes for records */
define('COLLECTION','collection');
define('PARTY','party');
define('ACTIVITY','activity');
define('SERVICE','service');

/*
 * Status for records
 */
define('PUBLISHED', 'PUBLISHED');
define('APPROVED', 'APPROVED');
define('ASSESSMENT_IN_PROGRESS', 'ASSESSMENT_IN_PROGRESS');
define('SUBMITTED_FOR_ASSESSMENT', 'SUBMITTED_FOR_ASSESSMENT');
define('DRAFT', 'DRAFT');
define('MORE_WORK_REQUIRED', 'MORE_WORK_REQUIRED');
define('DELETED', 'DELETED');

define('ONE_HOUR', 60*60);
define('ONE_DAY', 60*ONE_HOUR);
define('ONE_WEEK', 7*ONE_DAY);
define('ONE_MONTH', 30*ONE_DAY);

define('CONTRIBUTOR_PAGE_TEMPLATE', 'contributor');
define('CONTRIBUTOR_PAGE_KEY_PREFIX', 'a61e9d0d');

define('REGISTRY_APP_PATH', 'applications/registry/');
define('PORTAL_APP_PATH', 'applications/portal/');
define('API_APP_PATH', 'applications/api/');
define('APPS_APP_PATH', 'applications/apps/');
define('TEST_APP_PATH', 'applications/test/');
define('CACHE_PATH', 'engine/cache');

define('NATIVE_HARVEST_FORMAT_TYPE','nativeHarvestData');

define('HARVEST_ERROR','error');
define('HARVEST_WARNING','warning');
define('HARVEST_INFO','info');
define('HARVEST_TEST_MODE','TEST');
define('HARVEST_COMPLETE','TRUE');

define('IMPORT_INFO','info');

// define('EXTRIF_SCHEME','extRif');

define('DRAFT_RECORD_SLUG','draft_record_');

define('SUCCESS','SUCCESS');
define('FAILURE','FAILURE');

define('PRIMARY_RELATIONSHIP','PRIMARY');

/* Search boost scores */
// Amount to boost for contributor pages
define('SEARCH_BOOST_CONTRIBUTOR_PAGE', 3);

// Exponential per relation (1.1**6)
define('SEARCH_BOOST_PER_RELATION_EXP', 1.1);

// Max to allocate based on relations/connectedness
define('SEARCH_BOOST_RELATION_MAX', 4);

//Secret tag for allowing record to be classified as open
define('SECRET_TAG_ACCESS_OPEN', 'accessRightsType_open');
define('SECRET_TAG_ACCESS_RESTRICTED', 'accessRightsType_restricted');
define('SECRET_TAG_ACCESS_CONDITIONAL', 'accessRightsType_conditional');


/* End of file constants.php */
/* Location: ./application/config/constants.php */