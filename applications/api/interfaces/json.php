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
class JSONInterface extends FormatHandler
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

        $response = [
            'status' => 'OK',
            'code' => '200',
            'message' => [
                'message_version' => $this->message_version,
                'api_version' => $this->api_version,
                'format' => $this->output_mimetype(),
            ],
            'data' => $payload,
        ];
        if (is_array($benchmark) && sizeof($benchmark) > 0) {
            $response['benchmark'] = $benchmark;
        }

        if ($ci->input->get('pretty')) {
            echo json_encode($response, JSON_PRETTY_PRINT);
        } else {
            echo json_encode($response);
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
        $response = [
            'status' => 'ERROR',
            'code' => '400',
            'message' => [
                'message' => 'An error has occured',
                'version' => $this->api_version,
                'format' => $this->output_mimetype(),
            ],
            'data' => $payload,
        ];

        $ci = &get_instance();

        if ($ci->input->get('pretty')) {
            echo json_encode($response, JSON_PRETTY_PRINT);
        } else {
            echo json_encode($response);
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
