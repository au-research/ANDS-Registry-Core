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
class JSONNOHEADERInterface extends FormatHandler
{

    protected $message_version = 'v1.0';

    public function __construct()
    {

    }

    /**
     * display a normal message
     * @param  mixed $payload
     * @param  array  $benchmark
     * @return application/json
     */
    public function display($payload, $benchmark = array())
    {
        $ci = &get_instance();
        $ci->output->set_header('Content-type: application/json');
        $ci->output->set_content_type('Content-type: application/json');

        if ($ci->input->get('pretty')) {
            echo json_encode($payload, JSON_PRETTY_PRINT);
        } else {
            echo json_encode($payload);
        }

        return true;
    }

    /**
     * display an error message
     * @param  mixed $payload
     * @return application/json
     */
    public function error($payload)
    {
        $ci = &get_instance();
        $ci->output->set_header('Content-type: application/json');
        $ci->output->set_content_type('Content-type: application/json');


        $ci = &get_instance();

        if (!is_array($payload) && is_string($payload)) {
            $payload = [$payload];
        }

        $terms = array_merge(array('event'=>'api_error'), $payload);
        api_log_terms($terms);

        if ($ci->input->get('pretty')) {
            echo json_encode($payload, JSON_PRETTY_PRINT);
        } else {
            echo json_encode($payload);
        }
        return false;
    }

    /**
     * Application mimetype
     * @return string
     */
    public function output_mimetype()
    {
        return 'application/json';
    }

}
