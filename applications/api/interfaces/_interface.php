<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class FormatHandler
{
    protected $api_version = "v1.0";

    function set_api_version($version) {
        $this->api_version = $version;
    }

    function display($payload){}
    function error($message){}
    function output_mimetype(){}
}
