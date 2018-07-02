<?php
namespace ANDS\API\Registry\Handler;
use \Exception as Exception;

class Handler {
    public $params;
    public $ci;
    public $parentAPI;

    function __construct($params = false, $parentAPI = null) {
        $this->params = $params;
        $this->parentAPI = $parentAPI;
        $this->ci =& get_instance();
    }

    public function getParentAPI()
    {
        return $this->parentAPI;
    }
}