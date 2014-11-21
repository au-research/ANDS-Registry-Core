<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @author sjwood25890 https://github.com/sjwood25890/codeigniter-logging
 * Replaced default Codeigniter logging functionality
 */
class Logging
{
    const CRITICAL = 0;
    const ERROR = 1;
    const WARNING = 2;
    const INFO = 3;
    const DEBUG = 4;

    private $_loggers;

    public function __construct($config = array())
    {
        $this->_loggers = array();

        foreach ($config as $name => $logger)
        {
            $logger_class_name = ucfirst($logger['type'] . 'Logger');
            if (class_exists($logger_class_name))
            {

                // First check to make sure that the logger's level exists
                if (!is_null(@constant('self::' . $logger['level'])))
                {
                    // Create the logger
                    $logger_object = new $logger_class_name;

                    // Initialise and set up the logger
                    $logger_object->set_name($name);
                    $logger_object->set_level($logger['level']);
                    $logger_object->initialise($logger);

                    // Finally store it, keyed by the name assigned to it
                    $this->_loggers[$name] = $logger_object;
                }
            }
        }
    }

    public function get_logger($logger)
    {
        if (!array_key_exists($logger, $this->_loggers))
        {
            throw new LoggingException('Logger with name ' . $logger . ' does not exist.');
        }

        return $this->_loggers[$logger];
    }
}

class LoggingException extends Exception
{

}

abstract class Logger
{
    protected $name = '';

    protected $CI;
    protected $enabled = FALSE;

    private $_format = '{message}';
    private $_level;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function set_name($name)
    {
        $this->name = $name;
    }

    public function get_name()
    {
        return $this->name;
    }

    public function set_level($level)
    {
        $this->_level = $level;
    }

    public function get_level()
    {
        return $this->_level;
    }

    public function initialise($params)
    {
        $this->_format = $params['format'];
    }

    protected function log($level, $message)
    {
        if ($this->within_log_level($level) && $this->enabled)
        {
            $this->do_log(
                $this->parse_log_message(
                    $this->_format,
                    $this->collect_parameters($level, $message)
                )
            );
        }

        return $this;
    }

    private function collect_parameters($level, $message)
    {
        return array(
            'message' => $message,
            'level' => $level,
            'date' => date('Y-m-d H:i:s'),

            'logger_level' => $this->get_level(),
            'logger_name' => $this->get_name()
        );
    }

    protected function within_log_level($level)
    {
        return constant('Logging::' . $this->get_level()) >= constant('Logging::' . $level);
    }

    protected function parse_log_message($message, $params)
    {
        $this->CI->load->library('parser');

        return $this->CI->parser->parse_string($message, $params, TRUE);
    }

    protected abstract function do_log($message);

    public function critical($message)
    {
        return $this->log('CRITICAL', $message);
    }

    public function error($message)
    {
        return $this->log('ERROR', $message);
    }

    public function warning($message)
    {
        return $this->log('WARNING', $message);
    }

    public function info($message)
    {
        return $this->log('INFO', $message);
    }

    public function debug($message)
    {
        return $this->log('DEBUG', $message);
    }
}

class FileLogger extends Logger
{
    private $_file_path;
    private $_file_name;

    public function initialise($params)
    {
        parent::initialise($params);

        $params = array_merge(array(
            'file_path' => '',
        ), $params);

        $this->set_file_path($params['file_path']);

        $this->check_file_path_exists_and_is_writeable();

        $this->set_file_name();

        $this->enabled = TRUE;
    }

    protected function do_log($log_entry)
    {
        file_put_contents($this->_file_path . $this->_file_name, $log_entry . PHP_EOL, FILE_APPEND);
    }

    private function set_file_path($file_path)
    {
        $this->_file_path = $file_path;

        $config_log_path = $this->CI->config->item('log_path');

        if ($this->_file_path == '')
        {
            $this->_file_path = ($config_log_path != '') ? $config_log_path : APPPATH . 'logs/';
        }
        else if ($this->_file_path[0] != '/')
        {
            $this->_file_path = (($config_log_path != '') ? $config_log_path : APPPATH.'logs/') . $this->_file_path;
        }

        $this->_file_path = rtrim($this->_file_path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    private function check_file_path_exists_and_is_writeable()
    {
        if(!is_dir($this->_file_path)) {
            // First try to create the directory
            @mkdir($this->_file_path, 0777, TRUE);
        }
       

        if (!is_dir($this->_file_path) OR !is_really_writable($this->_file_path))
        {
            throw new LoggingException($this->_file_path . ' is not a directory to which logs can be written, and the directory could not be created. Please check your logging settings.');
        }
    }

    private function set_file_name()
    {
        $this->_file_name = 'log-' . $this->get_name() . '-' . date('Y-m-d') . '.php';
    }
}

class NullLogger extends Logger
{
    public function initialise($params)
    {
        parent::initialise($params);
    }

    protected function do_log($log_entry)
    {

    }
}

class EmailLogger extends Logger
{
    private $_from;
    private $_to;
    private $_subject;

    public function set_to($to)
    {
        $this->_to = $to;
    }

    public function set_subject($subject)
    {
        $this->_subject = $subject;
    }

    public function set_from($from)
    {
        $this->_from = $from;
    }

    public function get_from()
    {
        return $this->_from;
    }

    public function get_subject()
    {
        return $this->_subject;
    }

    public function get_to()
    {
        return $this->_to;
    }

    public function initialise($params)
    {
        if (!array_key_exists('from', $params))
        {
            throw new LoggingException('The email logger ' . $this->get_name() . ' needs a from address in its config.');
        }
        else if (!array_key_exists('to', $params))
        {
            throw new LoggingException('The email logger ' . $this->get_name() . ' needs a to address in its config.');
        }
        else if (!array_key_exists('subject', $params))
        {
            throw new LoggingException('The email logger ' . $this->get_name() . ' needs a subject in its config.');
        }
        else
        {
            $this->enabled = TRUE;

            $this->set_from($params['from']);
            $this->set_to($params['to']);
            $this->set_subject($params['subject']);
        }
    }

    protected function do_log($log_entry)
    {
        $this->CI->load->library('email');

        $this->CI->email->from($this->get_from());
        $this->CI->email->to($this->get_to());
        $this->CI->email->subject($this->get_subject());

        $this->CI->email->message($log_entry);

        $this->CI->email->send();
    }
}

/* End of file Logging.php */