<?php
namespace ANDS\API;

use \Exception as Exception;



/**
 * ANDS\Registry_api
 * for use with the ANDS API application
 *
 * Returns response for localhost/api/registry/ based requests
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Registry_api
{
    private $ci;
    private $params;
    private $version = "2.0";

    public function __construct()
    {
        $this->ci = &get_instance();
        require_once BASE . 'vendor/autoload.php';
    }

    /**
     * Primary handle function
     * @param  array  $method list of URL parameters
     * @return array          response
     */
    public function handle($method = array())
    {
        $this->params = array(
            'submodule' => isset($method[1]) ? $method[1] : false,
            'identifier' => isset($method[2]) ? $method[2] : false,
            'object_module' => isset($method[3]) ? $method[3] : false,
            'object_submodule' => isset($method[4]) ? $method[4] : false,
        );

        if (!$this->params['submodule']) {
            return $this->index();
        }

        if ($this->params['submodule']) {

            // backward compatible with grants api v2 only
            if ($this->params['submodule'] === "grants") {
                $this->params['submodule'] = "activities";
            }

            try {
                $class_name = 'ANDS\API\Registry\Handler\\' . ucfirst($this->params['submodule']) . 'Handler';
                $v2_class_name = $class_name.'V2';
                if (class_exists($v2_class_name)) {
                    $class_name = $v2_class_name;
                }
                if (!class_exists($class_name)) {
                    throw new Exception("Method " . $this->params['submodule'] . " is not supported (Version = ".$this->version.")");
                }
                $handler = new $class_name($this->params);
                return $handler->handle();
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        } else {
            return "Method " . $this->params['submodule'] . " is not supported";
        }
    }

    /**
     * Default Index Method
     * @todo   populate with useful information about this API
     * @return array
     */
    public function index()
    {
        return array(
            'Registry Index',
        );
    }

    /**
     * Default Status Method
     * @todo populate with useful status about the registry
     * @return  array
     */
    public function status()
    {
        return ['status returned'];
    }
}
