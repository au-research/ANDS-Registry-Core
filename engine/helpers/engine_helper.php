<?php

use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use MinhD\SolrClient\SolrClient;
use Symfony\Component\Filesystem\Filesystem;
use ANDS\Util\Config as ConfigUtil;

function get_config_item($name) {

    monolog("calling config $name, shouldn't be called", "error", "error", true);

    if (env($name)) {
        return env($name);
    }
	$_ci =& get_instance();
	if($_ci->config->item($name)) {
		return $_ci->config->item($name);
	} else {
		//it's in the database table
		$result = $_ci->db->get_where('configs', array('key'=>$name));
		if($result && $result->num_rows() > 0) {
			$result_array = $result->result_array();
			$result_item = $result_array[0];
			if($result_item['type']=='json') {
				$string = trim(preg_replace('/\s+/', ' ', $result_item['value']));
				return json_decode($string, true);
			} else {
				return $result_item['value'];
			}
		} else {
			return false;
		}
	}
}

function get_global_config_item($name) {
	$_ci =& get_instance();
	if($_ci->config->item($name)) {
		return $_ci->config->item($name);
	} else {
		return false;
	}
}

function get_db_config_item($name) {
	$_ci =& get_instance();
	$result = $_ci->db->get_where('configs', array('key'=>$name));
	if($result->num_rows() > 0) {
		$result_array = $result->result_array();
		$result_item = $result_array[0];
		if($result_item['type']=='json') {
			$string = trim(preg_replace('/\s+/', ' ', $result_item['value']));
			return json_decode($string, true);
		} else {
			return $result_item['value'];
		}
	} else {
		return false;
	}
}

function set_config_item($key, $type, $value) {
	$_ci =& get_instance();
	if($value=='json' && is_array($value)) $value = json_encode($value);

	$action = null;

	if(get_db_config_item($key)){
		$action = 'update';
	} else {
		$action = 'create';
	}

	// if(!get_global_config_item($key)){
	// 	if(get_db_config_item($key)){
	// 		$action = 'update';
	// 	} else {
	// 		$action = 'create';
	// 	}
	// } else {
	// 	if(get_db_config_item($key)){
	// 		$action='update';
	// 	} else {
	// 		$action = 'create';
	// 	}
	// }

	if($action=='create'){
		$data = array(
			'key' => $key,
			'type' => $type,
			'value' => $value
		);
		$insert_query = $_ci->db->insert('configs', $data);
		if($insert_query) {
			// log_message('info', 'CONFIG creating '.$value.' to '.$key.' as '.$type);
			return true;
		} else return false;
	} elseif($action=='update') {
		$_ci->db->where('key', $key);
		$update_query = $_ci->db->update('configs', array(
			'type' => $type,
			'value' => $value
		));
		// log_message('info', 'CONFIG update '.$value.' to '.$key.' as '.$type);
	} else {
		return false;
	}
}

function mod_enabled($module_name)
{
	$CI =& get_instance();
	return in_array($module_name, $CI->config->item(ENGINE_ENABLED_MODULE_LIST));
}

function mod_enforce($module_name)
{
	$CI =& get_instance();
	if(!in_array($module_name, $CI->config->item(ENGINE_ENABLED_MODULE_LIST)))
	{
		die('This module is not enabled. Check your configuration item: $ENV[ENGINE_ENABLED_MODULE_LIST]['.$module_name.'] (config/app.php)');
	}
}

function acl_enforce($function_name, $message = '', $portal=false)
{
	$_ci =& get_instance();
	if (!$_ci->user->isLoggedIn()) {
		if($portal) {
			redirect('profile/login/?redirect='.curPageURL());
		} else {
			redirect('auth/login/#/?error=login_required&redirect='.curPageURL());
		}
		// throw new Exception (($message ?: "Access to this function requires you to be logged in. Perhaps you have been automatically logged out?"));
	}
	else if (!$_ci->user->hasFunction($function_name))
	{
		throw new Exception (($message ?: "You do not have permission to use this function (".$function_name."). Perhaps you have been logged out?"));
	}
}

function curPageURL() {
	$pageURL = 'http';
	if (isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

function ds_acl_enforce($ds_id, $message = ''){
	$_ci =& get_instance();
	$_ci->load->model('data_source/data_sources', 'ds');
	$ds = $_ci->ds->getByID($ds_id);
	if($ds){
		if (!$_ci->user->hasAffiliation($ds->record_owner)){
			throw new Exception (($message ?: "You do not have permission to access this data source: ".$ds->title." (".$ds->record_owner.")"));
		}
	}else{
		throw new Exception ("Data Source does not exists!");
	}
}


/* Error handling */

function default_error_handler($errno, $errstr, $errfile, $errline)
{
	$event = [
		'event' => 'error',
		'message' => $errstr . " > on line " . $errline . " (" . $errfile .")". 'Error: '.error_level_tostring($errno)
	];
	monolog($event, 'error', 'error');
    debug($errstr . " > on line " . $errline . " (" . $errfile .")". 'Error: '.error_level_tostring($errno), 'error');

	ulog($errstr . " > on line " . $errline . " (" . $errfile .")". 'Error: '.error_level_tostring($errno), 'error', 'error');

	// Ignore when error_reporting is turned off (sometimes inline with @ symbol)
	if (error_reporting() == 0) { return true; }

	// Ignore E_STRICT no email either
	if ($errno == E_STRICT) { return true; }

	if (ConfigUtil::get("app.deployment_state") == "development" || ini_get('display_errors') == 1)
	{
		throw new Exception($errstr . NL . "on line " . $errline . " (" . $errfile .")");
	}
	else
	{
		// hide E_NOTICE from users
		if ($errno == E_NOTICE) { return true; }
		throw new Exception("An unexpected system error has occured. Please try again or report this error to the system administrator.");
	}

	return true;   /* Don't execute PHP internal error handler */
}

function error_level_tostring($errno)
{
    $errorlevels = array(
    	2048 => 'E_STRICT',
        2047 => 'E_ALL',
        1024 => 'E_USER_NOTICE',
        512 => 'E_USER_WARNING',
        256 => 'E_USER_ERROR',
        128 => 'E_COMPILE_WARNING',
        64 => 'E_COMPILE_ERROR',
        32 => 'E_CORE_WARNING',
        16 => 'E_CORE_ERROR',
        8 => 'E_NOTICE',
        4 => 'E_PARSE',
        2 => 'E_WARNING',
        1 => 'E_ERROR');
    $result = "ERROR #(".$errno.")";
    if(array_key_exists($errno, $errorlevels))
    {
    	$result =  $errorlevels[$errno]. " #(".$errno.")";
    }
    return $result;
}

function notifySiteAdmin($errno, $errstr, $errfile, $errline)
{
	$_ci =& get_instance();
	if(ConfigUtil::get('app.site_admin_email') && ConfigUtil::get('app.site_admin_email') != '<admin @ email>')
	{
		$siteAdmin = (ConfigUtil::get('app.site_admin') ? ConfigUtil::get('app.site_admin') : 'Site Admin');

		$siteInstance = (ConfigUtil::get('app.environment_name') ? ConfigUtil::get('app.environment_name') : 'Site Instance');
		$siteState = (ConfigUtil::get('app.deployment_state') ? " (".ConfigUtil::get('app.deployment_state').")" : '');


		$email = $_ci->load->library('email');
		$email->from(ConfigUtil::get('app.site_admin_email'), $siteAdmin);
		$email->to(ConfigUtil::get('app.site_admin_email'));
		$errDisp = error_level_tostring($errno);

		$email->subject($errDisp.' occured on ' .$siteInstance.$siteState);
		$message = 'MESSAGE:'.NL.$errstr . NL . "on line " . $errline . " (" . $errfile .")".NL.NL;
		$serverArr = $_SERVER;
		$serverArr['HTTP_COOKIE'] = 'NOT SHOWING...';
		$message .= 'SERVER VARIABLES: '.NL.print_r($serverArr, true);

		$email->message($message);
		$email->send();
	}
}

set_error_handler("default_error_handler");

function default_exception_handler( $e ) {

    if (!function_exists('get_instance')) {
        echo get_exception_msg($e);
        die();
    }

    $_ci =& get_instance(); // CI super object to access load etc.

	$data['js_lib'] = array('core');
	$data['scripts'] = array();
	$data['title'] = 'An error occurred!';

    echo $_ci->load->view( 'header' , $data , true);

   	echo $_ci->load->view( 'exception' , array("message" => get_exception_msg($e)) , true );

    echo $_ci->load->view( 'footer' , $data , true);
}

function get_exception_msg(Exception $e) {
    $msg = $e->getMessage();
    if (!$msg) {
       return implode(" ", array_first($e->getTrace())['args']);
    }
    return $msg;
}
set_exception_handler('default_exception_handler');

function json_exception_handler( Exception $e ) {
    echo json_encode(array("status"=>"ERROR", "message"=> get_exception_msg($e)));
}

function json_error_handler($errno, $errstr, $errfile, $errline) {
	throw new Exception($errstr);
	// echo json_encode(array('status'=>'ERROR', 'message'=>'MESSAGE:'.$errstr ."on line " . $errline . " (" . $errfile .")"));
}
if (function_exists('xdebug_disable')) xdebug_disable();

/**
 * Very widely used helper function for getting a URL to an asset
 *
 * TODO: Refactor to not use CI anymore
 * @param $path
 * @param string $loc
 * @return string
 */
function asset_url( $path, $loc = 'modules')
{
	if($loc == 'base'){
		return baseUrl().'assets/'.$path;
	} else if ($loc == 'shared'){
		return baseUrl().'assets/shared/'.$path;
	} else if( $loc == 'core'){
		return base_url( 'assets/core/' . $path );
	} elseif ($loc == "dist") {
	  return base_url('assets/core/dist/'. $path);
    } else if ($loc == 'modules'){
	    $CI =& get_instance();
		if ($module_path = $CI->router->fetch_module()){
			return base_url( 'assets/' . $module_path . "/" . $path );
		}
		else{
			return base_url( 'assets/' . $path );
		}
	} else if ($loc == 'templates'){
		return base_url('assets/templates/'.$path);
	} else if ($loc =='base_path'){
		return baseUrl() . $path;
	} else if ($loc == 'full_base_path') {
		return base_url('assets/'.$path);
	}

	return null;
}

function registry_url($suffix='')
{
	$CI =& get_instance();
	return $CI->config->item('default_base_url') . 'registry/' . $suffix;
}

function portal_url($suffix='')
{
	$CI =& get_instance();

	return $CI->config->item('default_base_url') . $suffix;
}

function roles_url($suffix=''){
	$CI =& get_instance();

	return $CI->config->item('default_base_url') . 'roles/'. $suffix;
}

function apps_url($suffix=''){
	$CI =& get_instance();

	return $CI->config->item('default_base_url') . 'apps/'. $suffix;
}

function api_url($suffix=''){
	$CI =& get_instance();

	return $CI->config->item('default_base_url') . 'api/'. $suffix;
}

function identifier_api_url($suffix=''){
    $CI =& get_instance();

    return $CI->config->item('identifier_base_url') . $suffix;
}

function identifier_url($suffix=''){
	$CI =& get_instance();

	return $CI->config->item('default_base_url') . 'identifier/'. $suffix;
}

function developer_url($suffix=''){
	return 'http://developers.ands.org.au';
}

function current_protocol()
{
	$url = parse_url(site_url());
	return $url['scheme'].'://';
}

function host_url(){
	$url = parse_url(site_url());
	return $url['scheme'].'://'.$url['host'];
}

function secure_host_url(){
	$url = parse_url(site_url());
	$protocol = 'https://';
	$host = $url['host'];
	return $protocol.$host;
}

function secure_base_url(){
	$url = parse_url(site_url());
	$protocol = 'https://';
	return $protocol.$url['host'].$url['path'];
}

function url_suffix(){
	return '#!/';
}

function remove_scheme($url){
	return str_replace(array("https://","http://"), array("//","//"), $url);
}

function utc_timezone()
{
	date_default_timezone_set('UTC');
}

function reset_timezone()
{
	$CI =& get_instance();
	date_default_timezone_set($CI->config->item('default_tz'));
}

$_gc_cycles = 0;

function clean_cycles()
{
	global $_gc_cycles;
	$_gc_cycles++;
	if ($_gc_cycles > 100)
	{
		gc_collect_cycles();
		$_gc_cycles = 0;
	}
}

function urchin_for($account)
{
	if (isset($account) && !empty($account)) {
		$snippet = <<<URCHIN
var _gaq = _gaq || [];
	_gaq.push(['_setAccount', '%s']);
	_gaq.push(['_trackPageview']);
	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();

URCHIN;
		return sprintf($snippet, $account);
	}
	else {
		return "<!-- this would be the code snippet for Google Analytics, " .
		"but the provided account details were empty... -->\n";
	}
}

function is_dev(){
	if(ENVIRONMENT=='development' || $_SERVER['REMOTE_ADDR'] == env('DEBUGGER_IP')){
		return true;
	}else return false;
}

function safe_dump($var){
	if(ENVIRONMENT=='development' || $_SERVER['REMOTE_ADDR'] == env('DEBUGGER_IP')){
		var_dump($var);
	}
}

function safe_dd($var){
	if(ENVIRONMENT=='development' || $_SERVER['REMOTE_ADDR'] == env('DEBUGGER_IP')){
		dd($var);
	}
}

function check_services(){
	$CI =& get_instance();
	$solr_status = curl_post(ConfigUtil::get('app.solr_url').'admin/ping?wt=json', '', array());
	$solr_status = json_decode($solr_status, true);

	$data['message'] = '';
	if(!$solr_status){
		$data['message'] = 'SOLR Service is unreachable. please check the SOLR URL in app config: '. ConfigUtil::get('app.solr_url');
	}else if($solr_status['responseHeader']['status']!=0){
		$data['message'] = 'SOLR ping service returns '.$solr_status['responseHeader']['status'].', please check your SOLR configuration';
	}else{
		$data['message'] = 'Unknown error';
	}

	if(!$solr_status || (!isset($solr_status['responseHeader']['status']) && !$solr_status['responseHeader']['status']==0)) {
		$error = $CI->load->view('soft500' , $data, true);
		echo $error;
		die();
	}
}

function maxUploadSizeBytes(){
	// Helper function to convert "2M" to bytes
	$normalize = function($size) {
		if (preg_match('/^([\d\.]+)([KMG])$/i', $size, $match)) {
		$pos = array_search($match[2], array("K", "M", "G"));
			if ($pos !== false) {
			$size = $match[1] * pow(1024, $pos + 1);
			}
		}
		return $size;
	};
	$max_upload = $normalize(ini_get('upload_max_filesize'));
	$max_post = $normalize(ini_get('post_max_size'));
	$memory_limit = $normalize(ini_get('memory_limit'));
	$maxFileSize = min($max_upload, $max_post, $memory_limit);
	return $maxFileSize;
}

//check if xml is valid document
function isValidXML($xml) {
    $doc = @simplexml_load_string($xml);
    if ($doc) {
        return true; //this is valid
    } else {
        return false; //this is not valid
    }
}

function alphasort_name($a, $b){
	if($a->name == $b->name) return 0;
	return ($a->name < $a->name) ? -1 : 1;
}

function alphasort_byattr_title($a, $b) {
	if(strtolower($a['title'])==strtolower($b['title'])) return 0;
	return (strtolower($a['title']) < strtolower($b['title'])) ? -1 : 1;
}

function monolog($message, $logger = "activity", $type = "info", $allowBot = false) {
    if (function_exists('get_instance')) {
        $CI =& get_instance();
        if (!class_exists('ANDSLogging')) {
            $CI->load->library('ANDSLogging');
        }

        \ANDSLogging::log($message, $logger, $type, $allowBot);
    }
}

function debug($message, $type = "debug") {

    $env = ConfigUtil::get('app.deployment_state');
//    $debug = ConfigUtil::get('app.debug');
    $debug = true;

    if ($env === "production" || $debug === false || $env === "testing") {
        return;
    }

    $logger = new \Monolog\Logger('debug');

    $storageConfig = ANDS\Util\Config::get('app.storage');
    $logsPath = $storageConfig['logs']['path'];
    
    $handler = new \Monolog\Handler\StreamHandler($logsPath.'/debug.log');
    $logger->pushHandler($handler);
    if ($type == "debug") {
        $logger->debug($message);
    } elseif ($type == "error") {
        $logger->error($message);
    }

}

function ulog($message, $logger="activity", $type = "info") {
    if (!function_exists('get_instance')) {
        return;
    }
	$CI =& get_instance();

    //check if the logging class is loaded, if not, load it
    if (!class_exists('Logging')) {
        $CI->load->library('logging');
    }
    $CI->load->library('logging');

    try {
        $logger = $CI->logging->get_logger($logger);
        switch($type) {
            case 'info' : $logger->info($message);break;
            case 'debug' : $logger->debug($message);break;
            case 'warning' : $logger->warning($message);break;
            case 'error' : $logger->error($message);break;
            case 'critical' : $logger->critical($message);break;
        }
    } catch (Exception $e) {
        throw new Exception($e);
        // log_message('error', $e->getMessage());
    }
}

function ulog_email($subject='', $message='', $logger='activity', $type='info') {
	ulog($message, $logger, $type);

	$_ci =& get_instance();

	$siteAdmin = (ConfigUtil::get('app.site_admin') ? ConfigUtil::get('app.site_admin') : 'Site Admin');
	$siteInstance = (ConfigUtil::get('app.environment_name') ? ConfigUtil::get('app.environment_name') : 'Site Instance');
	$siteState = (ConfigUtil::get('app.deployment_state') ? " (".ConfigUtil::get('app.deployment_state').")" : '');

	$email = $_ci->load->library('email');
	// CC-1201 Remove site_admin_email as the sender, which should defaulted to apache@host
	// CC-1201 Default sender to noreply@host
	$email->from('noreply@researchdata.ands.org.au', $siteAdmin);
	$email->to(ConfigUtil::get('app.site_admin_email'));

	$email->subject($subject);
	$email->message($message);
	$email->send();
}

function ulog_terms($terms=array(), $logger='activity', $type='info')
{
    $msg = '';

    $CI =& get_instance();
    $msg = '';

    if (!isset($terms['ip'])) $terms['ip'] = $CI->input->ip_address();
    if (!isset($terms['user_agent'])) $terms['user_agent'] = $CI->input->user_agent();

    //check if user is logged in, then record the current user
    if ($CI->user->isLoggedIn()) {
        $terms['username'] = $CI->user->name();
        $terms['userid'] = $CI->user->localIdentifier();
    }

    foreach($terms as $key=>$term) {
        if(!is_array($key) && !is_array($term)) {
            $msg.='['.$key.':'.$term.']';
        }
    }
    ulog($msg,$logger,$type);
}

function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

if (!function_exists('dd')) {
    function dd($stuff) {
        die(var_dump($stuff));
    }
}


/**
 * Hooking the functionality for a module
 * Mainly used for ANDS\RegistryPlugin modules
 * @param  string  $action append_roapi|append_index
 * @param  registry_object $ro     Registry Object
 * @return array          Result
 */
function module_hook($action, $ro = false) {
	$modules_directory = REGISTRY_APP_PATH.'/registry_object/modules/';

	//return empty array if no modules found
	if (!file_exists($modules_directory)) return array();

    $modules = scandir($modules_directory);
    $result = array();
    foreach ($modules as $module) {
        if ($module !='.' && $module!='..') {
            $conf = parse_ini_file($modules_directory.'/'.$module.'/config.ini');
            $file = REGISTRY_APP_PATH.'/registry_object/modules/'.$module.'/models/'.$conf['module'].'.php';
            require_once($file);
            $class_name = $conf['namespace'].'\\'.$conf['module'];

            $class = new $class_name();
            if ($ro) $class->injectRo($ro);
            if (method_exists($class, $action)) {
                $result = array_merge($result, $class->$action());
            }
        }
    }
    return $result;
}

/**
 * Return an instance of the module
 *
 * @param  string              $namespace Name space of the module
 * @param  string              $class     Class name
 * @param bool|registry_object $ro        Registry Object
 * @return class
 */
function module_return($namespace, $class, $ro = false) {
    $modules_directory = REGISTRY_APP_PATH.'/registry_object/modules/';

    //return false if no modules found
	if (!file_exists($modules_directory)) return false;

    $modules = scandir($modules_directory);
    foreach ($modules as $module) {
        if ($module !='.' && $module!='..') {
            $conf = parse_ini_file($modules_directory.'/'.$module.'/config.ini');
            if ($namespace == $conf['namespace']) {
                $file = REGISTRY_APP_PATH.'/registry_object/modules/'.$module.'/models/'.$class.'.php';
                require_once($file);

                $class_name = $conf['namespace'] . '\\' . $class;
                $class = new $class_name();
                if ($class) {
                    if ($ro)
                        $class->injectRo($ro);
                    return $class;
                } else {
                    //handle error here
                }

            }
        }
    }
    return false;
}

/**
 * @return the release version
 */
function getReleaseVersion(){
    $CI =& get_instance();
    return $CI->config->item('release_version');
}

/**
 * Check if an useragent is a bot
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @param  string $useragent
 * @return bool
 */
function isbot($useragent = false)
{
	if (!$useragent) {
		$useragent = $_SERVER['HTTP_USER_AGENT'];
	}
	if (preg_match('/bot|crawl|slurp|spider/i', $useragent)) {
		return true;
	} else {
		return false;
	}
}

/***
 *
 * added MYSQL_ATTR_MAX_BUFFER_SIZE to config default value of 1MB is not enough for some registryObjects
 * CC-2691
 */
function initEloquent() {
    $dotenv = new \Dotenv\Dotenv(__DIR__ . '/../../');
    $dotenv->load();
    $two_mb = 2 * 1024 * 1024;
    $capsule = new \Illuminate\Database\Capsule\Manager;

    $databases = require (dirname(__DIR__) . '/../config/database.php');
    $default = $databases['default'];

    $options = [];
    $options[\PDO::ATTR_PERSISTENT] = True;
    // dev bamboo php runtime uses mysqlnd that doesn't need (or uses max_buffer_size)
    // build fails so need to test
    $is_mysqlnd = function_exists('mysqli_fetch_all');
    if (!$is_mysqlnd) {
        $options[\PDO::MYSQL_ATTR_MAX_BUFFER_SIZE] = $two_mb;
    }
    foreach ($databases as $key => $db) {
        $capsule->addConnection(
            [
                'driver' => 'mysql',
                'host' => array_key_exists('hostname', $db) ? $db['hostname'] : $default['hostname'],
                'database' => array_key_exists('database', $db) ? $db['database'] : $default['database'],
                'port' => array_key_exists('port', $db) ? $db['port'] : $default['port'],
                'username' => array_key_exists('username', $db) ? $db['username'] : $default['username'],
                'password' => array_key_exists('password', $db) ? $db['password'] : $default['password'],
                'charset' => 'utf8',
                'collation' => 'utf8_general_ci',
                'prefix' => '',
                'options' => $options
            ], $key
        );
    }
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
}

function tearDownEloquent() {
    $connection = \Illuminate\Database\Capsule\Manager::connection("default");
    $connection->disconnect();
}

function getSolrCountForQuery($filters) {

    if (!function_exists('get_instance')) {
        return 0;
    }

    $ci =& get_instance();
    $ci->load->library('solr');
    $result = $ci->solr->init()
        ->setFilters($filters)
        ->executeSearch(true);

    return $result['response']['numFound'];

    // TODO: map solr search to use solr client instead
//    $solr = new SolrClient(\ANDS\Util\Config::get('app.solr_url'));
//    return $solr
//        ->search(array_merge($filters, ['fl' => 'id', 'rows' => 0]))
//        ->getNumFound();
}

function constructPortalSearchQuery($queries)
{
    $query = portal_url('search');
    $query .= '#!/';
    foreach ($queries as $key=>$value) {
        if (is_array($value)) {
            foreach ($value as $v) {
                $query.= $key .'='.$v.'/';
            }
        } else {
            $query .= $key . '=' . $value . '/';
        }
    }
    return $query;
}