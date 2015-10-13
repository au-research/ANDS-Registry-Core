<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * The dispatcher catches all requests that pass through the (:any) filter
 * in the routes.php configuration for this application.
 *
 *
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Dispatcher extends MX_Controller
{

    private $formatter;
    private $default_format = 'json';

    public function __construct()
    {
        parent::__construct();
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');
    }

    public function _remap($method, $params = array())
    {
        // Put the method back together and try and locate a matching controller
        array_unshift($params, $method);

        //shift API if it's the first params
        if ($params[0] == "api") {
            array_shift($params);
        }

        //check for versions
        if (strpos($params[0], "v") !== false) {
            $api_version = $params[0];
            array_shift($params);
        } else {
            //use latest version from application directive
            $directives = $this->config->item('application_directives');
            if ($directives['portal'] && $directives['portal']['api_version']) {
                $api_version = $directives['portal']['api_version'];
            } else {
                $api_version = "v1.0"; //default
            }
            //overwrite with application level configuration
            $api_version = $this->config->item('api_version') ? $this->config->item('api_version') : false;
        }

        //check for formatting in GET
        $format = $this->input->get('format') ? $this->input->get('format') : false;
        if (!$format) {
            $format = isset($_GET['format']) ? $_GET['format'] : false;
        }

        //check for formatting in method
        //in the form of module.format
        //eg: registry.xml
        if (!$format) {
            if (isset($params[0]) && strpos($params[0], ".")) {
                $called = explode(".", $params[0]);
                if (sizeof($called) == 2) {
                    $format = $called[1];
                    $method = $called[0];
                }
            }
        }

        //check for formatting in global config
        //else use the default formatting option specified in this file
        if (!$format) {
            $format = $this->config->item('api_default_format') ? $this->config->item('api_default_format') : $this->default_format;
        }

        $this->formatter = $this->getFormater($format);
        // set_exception_handler($format.'_exception_handler');

        //check and require API Key only if method is not index (default)
        $api_key = $this->input->get('api_key') ? $this->input->get('api_key') : false;
        if (!$api_key) {
            $api_key = isset($_GET['api_key']) ? $_GET['api_key'] : false;
        }

        if (!$api_key && $method != 'index') {
            throw new Exception('An API Key is required to access this service');
        }

        //setting api version for the formatter for display purpose
        $this->formatter->set_api_version($api_version);

        //home page index
        if (sizeof($params) == 1 && $params[0] == 'index') {
            $this->index($api_key, $api_version, $params);
            return;
        } else {
            //finally route the request
            $this->route($api_key, $api_version, $params);
            return;
        }

    }

    public function getFormater($format)
    {
        $formatter = null;

        if ($format && ctype_alnum($format)) {
            $path = APP_PATH . '/interfaces/' . strtolower($format) . '.php';
            if (file_exists($path)) {
                require_once $path;
                $classname = $format . "interface";
                $formatter = new $classname;
            } else {
                throw new Exception("Invalid format. Could not load the formatting parser for: '" . $format . "'");
            }
        } else {
            throw new Exception("Invalid Formatter -- cannot continue");
        }
        return $formatter;
    }

    public function index($api_key, $api_version, $params)
    {
        $response = [
            'Welcome to ANDS API',
        ];
        $this->formatter->display($response);
    }

    public function route($api_key, $api_version, $params)
    {
        // set_exception_handler($this->formatter->error);
        try {
            $namespace = 'ANDS';
            $class_name = $params[0].'_api';
            $file = APP_PATH.$params[0].'/'.$class_name.'.'.$api_version.'.php';
            if (!file_exists($file)) {
                throw new Exception('Class '.$params[0].' version '.$api_version.' is not recognised as a valid method');
            }
            require_once($file);
            $class_name = $namespace.'\\'.$class_name;
            $class = new $class_name();
            $result = $class->handle($params);
            $this->formatter->display($result);
        } catch (Exception $e) {
            $this->formatter->error($e->getMessage());
            return;
        }
    }

}