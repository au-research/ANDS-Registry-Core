<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Formatter Superclass
 * For use as a super class for all formatter support by the API application
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
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
