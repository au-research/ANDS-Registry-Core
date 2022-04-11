<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once APP_PATH . 'interfaces/_interface.php';

/**
 * Formatter for JSON interface
 * for use with the API application
 *
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class JSONPInterface extends FormatHandler
{

    protected $message_version = 'v1.0';

    public function __construct()
    {
        header("Content: ". $this->output_mimetype());
        header("Access-Control-Allow-Origin: *");
    }

    /**
     * display a normal message
     * @param  mixed $payload
     * @param  array  $benchmark
     * @return application/json
     */
    public function display($payload, $benchmark = array())
    {
        $callback = (isset($_GET['callback'])? $_GET['callback']: '?');
        echo ($callback) . '(' . json_encode($payload) . ')';
        return true;
    }

    /**
     * display an error message
     * @param  mixed $payload
     * @return application/json
     */
    public function error($payload)
    {
        $callback = (isset($_GET['callback'])? $_GET['callback']: '?');
        echo ($callback) . '(' . json_encode($payload) . ')';
        return false;
    }

    /**
     * Application mimetype
     * @return string
     */
    public function output_mimetype()
    {
        return 'application/javascript';
    }

}
