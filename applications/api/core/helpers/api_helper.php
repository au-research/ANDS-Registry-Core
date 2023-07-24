<?php
/**
 * Logging functionality for API Service
 * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 * @param $message
 * @param string $type
 */
function api_log($message, $type = 'info')
{
    $CI = &get_instance();

    //check if the logging class is loaded, if not, load it
    if (!class_exists('Logging')) {
        $CI->load->library('logging');
    } else {
        $CI->load->library('logging');
    }

    try {
        $CI->logging->add_logger(
            array(
                'type'      => 'file',
                'level'     => 'INFO',
                'name'      => 'api',
                'format'    => '[date:{date}] {message}',
                'file_path' => 'api',
            )
        );
        $logger = $CI->logging->get_logger('api');
        switch ($type) {
            case 'info':
                $logger->info($message);
                break;
            case 'debug':
                $logger->debug($message);
                break;
            case 'warning':
                $logger->warning($message);
                break;
            case 'error':
                $logger->error($message);
                break;
            case 'critical':
                $logger->critical($message);
                break;
        }
    } catch (Exception $e) {
        // throw new Exception($e);
    } catch (LoggingException $e) {
        // throw new Exception($e);
    }
}

/**
 * API Service  log array helper function
 * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 * @param array $terms
 * @param string $type
 */
function api_log_terms($terms = array(), $type = 'info')
{
    $CI  = &get_instance();
    $msg = '';

    if (!isset($terms['ip'])) {
        $terms['ip'] = $CI->input->ip_address();
    }

    if (!isset($terms['user_agent'])) {
        $terms['user_agent'] = $CI->input->user_agent();
    }

    //check if user is logged in, then record the current user
    if ($CI->user->isLoggedIn()) {
        $terms['username'] = $CI->user->name();
        $terms['userid']   = $CI->user->localIdentifier();
    }

    foreach ($terms as $key => $term) {
        if (!is_array($key) && !is_array($term)) {
            $msg .= '[' . $key . ':' . $term . ']';
        }
    }

    api_log($msg, $type);
}

function api_exception_handler($e)
{
    api_log_terms(
        array(
            'event'   => 'api_exception',
            'message' => $e->getMessage(),
        )
    );
    echo json_encode(array("status" => "ERROR", "message" => $e->getMessage()));
}

/**
 * Read a local file path and return the lines
 * @author  Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 * @param  string $file File Path Local
 * @return array()
 */
function readFileToLine($file)
{
    $lines = array();
    if (file_exists($file)) {
        $file_handle = fopen($file, "r");
        while (!feof($file_handle)) {
            $line    = fgets($file_handle);
            $lines[] = $line;
        }
        fclose($file_handle);
    }
    return $lines;
}

/**
 * Read a string in the form of [key:value]
 * and return an array of key=>value in PHP
 * @author  Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 * @param  string $string
 * @return array(key=>value)
 */
function readString($string)
{
    $result = array();
    preg_match_all("/\[([^\]]*)\]/", $string, $matches);
    foreach ($matches[1] as $match) {
        $array = explode(':', $match, 2);
        if ($array && is_array($array) && isset($array[0]) && isset($array[1])) {
            $result[$array[0]] = $array[1];
        }
    }
    return $result;
}

/**
 * Creating date collection between two dates
 *
 * <code>
 * <?php
 * # Example 1
 * date_range("2014-01-01", "2014-01-20", "+1 day", "m/d/Y");
 *
 * # Example 2. you can use even time
 * date_range("01:00:00", "23:00:00", "+1 hour", "H:i:s");
 * </code>
 *
 * @author Ali OYGUR <alioygur@gmail.com>
 * @param string since any date, time or datetime format
 * @param string until any date, time or datetime format
 * @param string step
 * @param string date of output format
 * @return array
 */
function date_range($first, $last, $step = '+1 day', $output_format = 'd/m/Y')
{

    $dates   = array();
    $current = strtotime($first);
    $last    = strtotime($last);

    while ($current <= $last) {
        $dates[] = date($output_format, $current);
        $current = strtotime($step, $current);
    }

    return $dates;
}

/**
 * Read a directory and return a list of files
 * exclude the . and .. directory
 * @param  string $directory Directory Path Local
 * @return array
 */
function readDirectory($directory)
{
    $scanned_directory = array_diff(scandir($directory), array('..', '.'));
    return $scanned_directory;
}

/**
 * Return the Spatial information for an IP Address using the ip-api api
 * @param $ip
 * @return bool|mixed|string
 */
function getIPLocation($ip)
{
    $data = @file_get_contents('http://ip-api.com/json/' . $ip);
    if ($data) {
        $data = json_decode($data, true);
        return $data;
    } else {
        return false;
    }
}


/**
 * Convert string to UTF8 encoded
 * @param $d
 * @return array|string
 */
function utf8ize($d)
{
    if (is_array($d))
        foreach ($d as $k => $v)
            $d[$k] = utf8ize($v);

    else if (is_object($d))
        foreach ($d as $k => $v)
            $d->$k = utf8ize($v);

    else
        return utf8_encode($d);

    return $d;
}