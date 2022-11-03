<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once APP_PATH . 'interfaces/_interface.php';

/**
 * Formatter for JSON interface
 * for use with the API application
 *
 * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 */
class JSONInterface extends FormatHandler
{

    protected $message_version = 'v1.0';

    /**
     * Display a normal response
     * @param $payload
     * @param array $benchmark
     */
    public function display($payload, $benchmark = array())
    {
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

        echo $this->formatResponse($response);
    }

    /**
     * Error message
     * @param $payload
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

        $terms = array_merge(array('event' => 'api_error'), $response);
        api_log_terms($terms);
        echo $this->formatResponse($response, "400");
    }

    /**
     * Format the response along with the header information
     * @param $response
     * @param string $status
     * @return string
     */
    public function formatResponse($response, $status = "200")
    {
        $ci = &get_instance();
        header('Content-type: application/json');
        $ci->output->set_content_type('application/json');
        $ci->output->set_status_header($status);

        $response = $ci->input->get('pretty')
            ?  json_encode($response, JSON_PRETTY_PRINT)
            : json_encode($response);

        if (!$response) {
            monolog("Failed encoding string: ". json_last_error_msg(), "error");
        }

        return $response;
    }

    /**
     * Application mimetype
     * @return string
     */
    public function output_mimetype()
    {
        return 'application/json';
    }

    /**
     * JSONInterface constructor.
     */
    public function __construct()
    {

    }

}
