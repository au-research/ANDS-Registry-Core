<?php
/**
 * Return the vocabulary configuration for a particular config
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @param $item
 * @return bool
 */
function get_vocab_config($item)
{
    $vocab_configs = \ANDS\Util\config::get('vocab.vocab_config');
    if (isset($vocab_configs[$item])) {
        return $vocab_configs[$item];
    } else return false;
}

/**
 * Return the vocabulary uploaded url for a file
 * Used in the view page to generate the file URL
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @param $name
 * @return string
 */
function vocab_uploaded_url($name)
{
    $path = get_vocab_config('upload_path') . $name;
    return $path;
}

/**
 * Logging functionality for vocabs
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @param $message
 * @param string $type
 */
function vocab_log($message, $type = 'info')
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
                // 'type' => 'database',
                // 'database_group' => 'vocabs',
                // 'table' => 'log',
                'level' => 'INFO',
                'name' => 'vocab',
                'format' => '[date:{date}] {message}',
                'file_path' => 'vocab'
            )
        );
        $logger = $CI->logging->get_logger('vocab');
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
 * Vocab log array
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @param array $terms
 * @param string $type
 */
function vocab_log_terms($terms = array(), $type = 'info')
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

    vocab_log($msg, $type);
}

/**
 * Helper method to format language
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @param $term
 * @return string
 */
function readable_lang($term)
{
    $match = strtolower($term);
    switch ($match) {
        case 'en':
            return 'English';
            break;
        case 'zh':
            return 'Chinese';
            break;
        case 'fr':
            return 'French';
            break;
        case 'de':
            return 'German';
            break;
        case 'it':
            return 'Italian';
            break;
        case 'ja':
            return 'Japanese';
            break;
        case 'mi':
            return 'Māori';
            break;
        case 'ru':
            return 'Russian';
            break;
        case 'es':
            return 'Spanish';
            break;
        default:
            return $term;
            break;
    }
}

/**
 * Helper method to format access point type
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @param $term
 * @return string
 */
function vocab_readable($term)
{
    $match = strtolower($term);
    switch ($match) {
        case 'webpage' :
            return 'Online';
            break;
        case 'apisparql' :
            return 'API/SPARQL';
            break;
        case 'file' :
            return 'Direct Download';
            break;
        default:
            return $term;
            break;
    }
}