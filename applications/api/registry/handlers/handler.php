<?php
namespace ANDS\API\Registry\Handler;
use \Exception as Exception;

class Handler {
    public $params;
    public $ci;

    function __construct($params = false) {
        $this->params = $params;
        $this->ci =& get_instance();
    }
}