<?php
/**
 * Logging functionality for API Service
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @param $message
 * @param string $type
 */
function api_log($message, $type = 'info')
{
    $CI =& get_instance();

    //check if the logging class is loaded, if not, load it
    if (!class_exists('Logging')) {
        $CI->load->library('logging');
    } else {
        $CI->load->library('logging');
    }

    try {
        $CI->logging->add_logger(
            array(
                'type' => 'file',
                'level' => 'INFO',
                'name' => 'api',
                'format' => '[date:{date}] {message}',
                'file_path' => 'api'
            )
        );
        $logger = $CI->logging->get_logger('api');
        switch ($type) {
            case 'info' :
                $logger->info($message);
                break;
            case 'debug' :
                $logger->debug($message);
                break;
            case 'warning' :
                $logger->warning($message);
                break;
            case 'error' :
                $logger->error($message);
                break;
            case 'critical' :
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
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @param array $terms
 * @param string $type
 */
function api_log_terms($terms = array(), $type = 'info')
{
    $CI =& get_instance();
    $msg = '';

    if (!isset($terms['ip'])) $terms['ip'] = $CI->input->ip_address();
    if (!isset($terms['user_agent'])) $terms['user_agent'] = $CI->input->user_agent();

    //check if user is logged in, then record the current user
    if ($CI->user->isLoggedIn()) {
        $terms['username'] = $CI->user->name();
        $terms['userid'] = $CI->user->localIdentifier();
    }

    foreach ($terms as $key => $term) {
        if (!is_array($key) && !is_array($term)) {
            $msg .= '[' . $key . ':' . $term . ']';
        }
    }

    api_log($msg, $type);
}

function api_exception_handler($e) {
    api_log_terms(
        array(
            'event' => 'api_exception',
            'message' => $e->getMessage()
        )
    );
    echo json_encode(array("status"=>"ERROR", "message"=> $e->getMessage()));
}