<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once APP_PATH . 'interfaces/_interface.php';

class JSONInterface extends FormatHandler
{
    public $params, $options, $formatter;
    protected $message_version = 'v1.0';

    public function __construct()
    {
        $ci = &get_instance();
        $ci->output->set_header('Content-type: application/json');
    }

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
        echo json_encode($response, true);
        return true;
    }

    public function error($payload)
    {
        $response = [
            'status' => 'ERROR',
            'code' => '404',
            'message' => [
                'message' => 'An error has occured',
                'version' => $this->api_version,
                'format' => $this->output_mimetype(),
            ],
            'data' => $payload,
        ];

        echo json_encode($response);
        return false;
    }

    public function output_mimetype()
    {
        return 'application/json';
    }

}
