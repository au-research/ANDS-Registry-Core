<?php
namespace ANDS\API\Log;

class Indexer {
    public $params;
    public $ci;

    function __construct($params = false) {
        $this->params = $params;
        $this->ci =& get_instance();
    }
}