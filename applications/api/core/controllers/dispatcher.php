<?php use ANDS\Registry\API\Router;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * The dispatcher catches all requests that pass through the (:any) filter
 * in the routes.php configuration for this application.
 *
 * This controller is primarily used for the API application of the software
 * and should not be reused for any other purpose
 *
 * Support the following URL structure
 * http://api.ands.org.au/
 * {optional:api}/
 * {optional:version}/
 * {module}/
 * {submodule}/
 * {object_identifier}/
 * {object_module}/
 * {optional:object_submodule}/
 * ?api_key={api_key}&{PARAMS}
 *
 * eg:
 * http://localhost/
 * http://localhost/v1.0/
 * http://localhost/registry/
 * http://localhost/v1.0/registry/
 * http://localhost/registry/object/1234
 * http://localhost/registry/object/1234/relationships
 *
 * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 * @link https://intranet.ands.org.au/display/~mnguyen/api.ands.org.au
 */
class Dispatcher extends MX_Controller
{

    private $formatter;
    private $default_format = 'json';

    /**
     * Class construction
     * Sets the default header to the default formatting
     */
    public function __construct()
    {
        parent::__construct();
        $this->output->set_content_type('application/json');
        set_exception_handler('api_exception_handler');
    }

    /**
     * _remap magic function
     * Deal with the URL parameters and direct the request to the right
     * handler
     * @todo    API Key restriction and authenticating
     * @todo    Logging
     * @param   string $method The primary method called
     * @param   array  $params The URL parameters
     * @return  response
     */
    public function _remap($method, $params = array())
    {
        // Put the method back together and try and locate a matching controller
        array_unshift($params, $method);

        //shift API if it's the first params
        if ($params[0] == "api") {
            array_shift($params);
        }

        //check for versions
        /**
         * @todo regex for matching version number may need improving
         */

        if(preg_match('/v[0-9].[0-9]/',$params[0])){
            $api_version = $params[0];
            array_shift($params);

        } else {
            //use latest version from application directive
            $directives = $this->config->item('application_directives');
            if ($directives['portal'] && isset($directives['portal']['api_version'])) {
                $api_version = $directives['portal']['api_version'];
            } else {
                $api_version = "v1.0"; //default
            }
            //overwrite with application level configuration
            $api_version = $this->config->item('api_version') ? $this->config->item('api_version') : $api_version;
        }

        //check for formatting in GET
        $format = $this->input->get('format') ? $this->input->get('format') : false;
        if (!$format) {
            $format = isset($_GET['format']) ? $_GET['format'] : false;
        }

        /**
         * check for formatting in method
         * in the form of module.format
         * eg: registry.xml
         */
        if (!$format) {
            if (isset($params[0]) && strpos($params[0], ".")) {
                $called = explode(".", $params[0]);
                if (sizeof($called) == 2) {
                    $format = $called[1];
                    $method = $called[0];
                    $params[0] = $called[0];
                }
            }
        }

        /**
         * check for formatting in global config
         * else use the default formatting option specified in this file
         */
        if (!$format) {
            $format = $this->config->item('api_default_format') ? $this->config->item('api_default_format') : $this->default_format;
        }

        //obtain the formatter
        $this->formatter = $this->getFormater($format);
        $this->output->set_content_type($this->formatter->output_mimetype());
        set_exception_handler(function($exception) {
            $this->formatter->error($exception->getMessage());
        });

        //check and require API Key only if method is not index (default)
        $api_key = $this->input->get('api_key') ? $this->input->get('api_key') : false;
        if (!$api_key) {
            $api_key = isset($_GET['api_key']) ? $_GET['api_key'] : false;
        }

        if ($this->input->is_cli_request()) {
            $api_key = 'api';
        }

        /**
         * If no api_key is presented, api_key is set to public
         */
        if (!$api_key && $method != 'index') {
            $api_key = 'public';
            // throw new Exception('An API Key is required to access this service');
        }

        //setting api version for the formatter for display purpose
        $this->formatter->set_api_version($api_version);

        //SETUP FINISHED
        //Time to do some real routing process
        ob_start();
        //home page index
        if (sizeof($params) == 1 && $params[0] == 'index') {
            $this->index($api_key, $api_version, $params);
        } else {
            //finally route the request
            $this->route($api_key, $api_version, $params);
        }
        $this->output->set_output(ob_get_clean());
    }

    /**
     * Returns the formatter for use with the response
     * @param  string $format json|xml
     * @return formatter
     * @throws Exception
     */
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

    /**
     * Primary index function for use when access the root
     * Welcome messages and instructions
     * @param  string $api_key
     * @param  string $api_version
     * @param  array $params
     * @return response
     */
    public function index($api_key, $api_version, $params)
    {
        $response = [
            'Welcome to ANDS API',
        ];
        $this->formatter->display($response);
    }

    /**
     * Primary routing function to call the handler located in directories
     * eg registry handler is located in /applications/api/registry/
     * All handler must have ANDS as a namespace
     *
     * @todo make ANDS\API class to handle routing instead of the dispatcher
     * @param  string $api_key
     * @param  string $api_version
     * @param  array $params
     * @return response
     */
    public function route($api_key, $api_version, $params)
    {
        try {

            $this->benchmark->mark('code_start');
            $namespace = 'ANDS\API';

            $class_name = $params[0].'_api';
            $file = APP_PATH.$params[0].'/'.$class_name.'.'.$api_version.'.php';
            if (!file_exists($file)) {
                throw new Exception('Class '.$params[0].' version '.$api_version.' is not recognised as a valid method');
            }
            require_once($file);
            $class_name = $namespace.'\\'.$class_name;
            $class = new $class_name();
            $result = $class->handle($params);

            $this->benchmark->mark('code_end');
            $elapsed = $this->benchmark->elapsed_time('code_start', 'code_end');

            $terms = array (
                'event' => 'api_hit',
                'api_key' => $api_key,
                'api_version' => $api_version,
                'path' => implode('/', $params),
                'elapsed' => $elapsed
            );

            api_log_terms($terms);

            if (property_exists($class, 'providesOwnResponse') && $class->isProvidingOwnResponse() === true) {
                $this->output->set_content_type($class->outputFormat);
                print_r($result);
            } else {
                $this->formatter->display($result);
            }

        } catch (Exception $e) {
            $message = get_exception_msg($e);
            $this->formatter->error($message);
            return;
        }
    }

}