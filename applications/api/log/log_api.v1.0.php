<?php
namespace ANDS\API;
use \Exception as Exception;
class Log_api
{

    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
        require_once BASE . 'vendor/autoload.php';
    }

    public function handle($method = array())
    {
        $this->params = array(
            'submodule' => isset($method[1]) ? $method[1] : false,
            'identifier' => isset($method[2]) ? $method[2] : false,
            'method_module' => isset($method[3]) ? $method[3] : false,
        );

        if ($this->params['submodule']) {
            try {
                $class_name = 'ANDS\API\Log\Handler\\' . ucfirst($this->params['submodule']) . 'Handler';
                if (!class_exists($class_name)) {
                    throw new Exception("Method " . $this->params['submodule'] . " is not supported");
                }
                $handler = new $class_name($this->params);
                return $handler->handle();
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        } else {
            return $this->detail();
        }
    }

    private function detail() {
        return 'log';
    }

}
