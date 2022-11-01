<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Formatter Superclass
 * For use as a super class for all formatter support by the API application
 * @todo psr-4 all format interface handlers
 * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 */
class FormatHandler
{
    protected $api_version = "v1.0";

    public function set_api_version($version)
    {
        $this->api_version = $version;
    }

    public function display($payload) {}
    public function error($message) {}
    public function output_mimetype() {}
}
