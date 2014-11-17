<?php
global $ENV;
$config['test_doi_prefix'] = '10.5072';

$config['gDOIS_SERVICE_BASE_URI'] = "https://mds.datacite.org/";
$config['gDOIS_DATACENTRE_NAME_PREFIX'] = "ANDS";
$config['gDOIS_DATACENTRE_NAME_MIDDLE'] = "CENTRE";

$config['gDOIS_DATACENTRE_PREFIXS'] = array( '10.4225/','10.4226/','10.4227/','10.5072/');
$config['gCMD_SCHEMA_URIS'] = array( '3'=>'/kernel-3/metadata.xsd','2.2'=>'/kernel-2.2/metadata.xsd','2.1'=>'/kernel-2.1/metadata.xsd');
if (!isset($ENV['gDOIS_DATACITE_PASSWORD']))
{
	throw new Exception ("System is not configured for use with Data Cite API. Please set gDOIS_DATACITE_PASSWORD in global_config.php");
}
$config['gDOIS_DATACITE_PASSWORD'] = $ENV['gDOIS_DATACITE_PASSWORD'];
$config['gDOIS_RESPONSE_SUCCESS'] = "OK";

define('gDOIS_SERVICE_BASE_URI',$config['gDOIS_SERVICE_BASE_URI']);
define('gDOIS_DATACENTRE_NAME_PREFIX',$config['gDOIS_DATACENTRE_NAME_PREFIX']);
define('gDOIS_DATACENTRE_NAME_MIDDLE',$config['gDOIS_DATACENTRE_NAME_MIDDLE']);
define('gDOIS_DATACITE_PASSWORD',$config['gDOIS_DATACITE_PASSWORD']);
define('gDOIS_RESPONSE_SUCCESS',$config['gDOIS_RESPONSE_SUCCESS']);

define('gCMD_SCHEMA_URI', 'http://schema.datacite.org/meta/kernel-2.1/metadata.xsd');
?>