<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once APP_PATH . 'interfaces/_interface.php';

class NopInterface extends FormatHandler
{
    public function __construct()
    {
        $ci = &get_instance();
    }

    public function display($payload) {
        echo print_r($payload, 1);
    }

    public function error($payload) {
        echo print_r($payload, 1);
    }
}