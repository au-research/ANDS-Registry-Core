<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'interfaces/_interface.php');

class JSONInterface extends FormatHandler
{
    var $params, $options, $formatter;

    function display($payload, $benchmark = array())
    {
        $data = array(
            'status' => 'success',
            'message' => $payload,
            'benchmark' => $benchmark
        );
        echo json_encode($this->utf8ize($data), true);
//        echo json_encode(array("status" => "success", "message" => $payload, "benchmark" => $benchmark), true);
        return true;
    }

    function utf8ize($d)
    {
        if (is_array($d))
            foreach ($d as $k => $v)
                $d[$k] = $this->utf8ize($v);

        else if (is_object($d))
            foreach ($d as $k => $v)
                $d->$k = $this->utf8ize($v);

        else
            return utf8_encode($d);

        return $d;
    }

    function error($message)
    {
        echo json_encode(array("status" => "error", "message" => $message));
        return false;
    }

    function output_mimetype()
    {
        return 'application/json';
    }

}