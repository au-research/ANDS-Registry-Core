<?php

namespace ANDS\Log;

use ANDS\Util\Config;
use Exception;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as Monolog;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Log
{
    /** @var \Psr\Log\LoggerInterface[] */
    protected static $channels = [];

    // the default channel to be set during init()
    protected static $defaultChannel = null;

    protected static $levels = [
        'debug' => Monolog::DEBUG,
        'info' => Monolog::INFO,
        'notice' => Monolog::NOTICE,
        'warning' => Monolog::WARNING,
        'error' => Monolog::ERROR,
        'critical' => Monolog::CRITICAL,
        'alert' => Monolog::ALERT,
        'emergency' => Monolog::EMERGENCY,
    ];

    /**
     * Log::init() must be run before logging can occur
     *
     * @return void
     */
    public static function init()
    {
        $loggingConfiguration = Config::get('logging');

        self::$defaultChannel = $loggingConfiguration['default'];

        // build loggers
        foreach ($loggingConfiguration['channels'] as $channel => $config) {
            $logger = self::createDriver($channel, $config);
            if ($logger instanceof LoggerInterface) {
                self::$channels[$channel] = $logger;
            }
        }
    }

    /**
     * @param $channel
     * @param array $config
     * @return \Monolog\Logger|\Psr\Log\LoggerInterface|null
     */
    public static function createDriver($channel, array $config)
    {
        if ($config['driver'] == 'single') {
            return self::createSingleDriver($channel, $config);
        } else if ($config['driver'] == 'daily') {
            return self::createDailyDriver($channel, $config);
        }

        return null;
    }

    /**
     * @param $channel
     * @param array $config
     * @return \Psr\Log\LoggerInterface|null
     */
    private static function createSingleDriver($channel, array $config)
    {
        $logger = new Monolog($channel);
        try {
            $path = rtrim($config['path'], '/') . '/' . $config['file'];
            $handler = new StreamHandler($path, self::parseLevel($config['level']));

            // formatter
            $format = array_key_exists('format', $config) ? $config['format'] : LineFormatter::SIMPLE_FORMAT;
            $formatter = new LineFormatter($format);
            $handler->setFormatter($formatter);

            $logger->pushHandler($handler);
            return $logger;
        } catch (Exception $e) {
            // directory is not buildable
            return null;
        }
    }

    /**
     * @param $channel
     * @param array $config
     * @return \Psr\Log\LoggerInterface|null
     */
    private static function createDailyDriver($channel, array $config)
    {
        $logger = new Monolog($channel);
        $path = rtrim($config['path'], '/') . '/' . $config['file'];
        $handler = new RotatingFileHandler($path, $config['days'], self::parseLevel($config['level']));

        // formatter
        $format = array_key_exists('format', $config) ? $config['format'] : LineFormatter::SIMPLE_FORMAT;
        $formatter = new LineFormatter($format);
        $handler->setFormatter($formatter);

        $logger->pushHandler($handler);

        return $logger;
    }

    /**
     * parse log level from text to numeric values
     *
     * @param $level
     * @return mixed|null
     */
    public static function parseLevel($level)
    {
        $level = strtolower($level);
        return array_key_exists($level, self::$levels) ? self::$levels[$level] : null;
    }

    /**
     * send a log message to a logger
     *
     * @param $logger
     * @param $method
     * @param $message
     * @param $context
     * @return void
     */
    public static function writeLog($logger, $method, $message, $context)
    {
        if ($logger != null && method_exists($logger, $method)) {
            $logger->$method($message, $context);
        }
    }

    /**
     * @return \Psr\Log\LoggerInterface|null
     */
    public static function getDefaultChannel()
    {
        return self::channel(self::$defaultChannel);
    }

    /**
     * @param $channel
     * @return \Psr\Log\LoggerInterface|null
     */
    public static function channel($channel = null)
    {
        return array_key_exists($channel, self::$channels)
            ? self::$channels[$channel]
            : new NullLogger();
    }

    public static function emergency($message, array $context = array())
    {
        self::writeLog(self::getDefaultChannel(), __FUNCTION__, $message, $context);
    }

    public static function alert($message, array $context = array())
    {
        self::writeLog(self::getDefaultChannel(), __FUNCTION__, $message, $context);
    }

    public static function critical($message, array $context = array())
    {
        self::writeLog(self::getDefaultChannel(), __FUNCTION__, $message, $context);
    }

    public static function error($message, array $context = array())
    {
        self::writeLog(self::getDefaultChannel(), __FUNCTION__, $message, $context);
    }

    public static function warning($message, array $context = array())
    {
        self::writeLog(self::getDefaultChannel(), __FUNCTION__, $message, $context);
    }

    public static function notice($message, array $context = array())
    {
        self::writeLog(self::getDefaultChannel(), __FUNCTION__, $message, $context);
    }

    public static function info($message, $context = [])
    {
        self::writeLog(self::getDefaultChannel(), __FUNCTION__, $message, $context);
    }

    public static function debug($message, $context = [])
    {
        self::writeLog(self::getDefaultChannel(), __FUNCTION__, $message, $context);
    }

}