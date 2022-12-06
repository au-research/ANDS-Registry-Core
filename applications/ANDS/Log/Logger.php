<?php

namespace ANDS\Log;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{
    /**
     * The underlying logger implementation.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    protected $context = [];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function emergency($message, array $context = array())
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    public function alert($message, array $context = array())
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    public function critical($message, array $context = array())
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    public function error($message, array $context = array())
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    public function warning($message, array $context = array())
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    public function notice($message, array $context = array())
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    public function info($message, array $context = array())
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    public function debug($message, array $context = array())
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    public function log($level, $message, array $context = array())
    {
        $this->writeLog($level, $message, $context);
    }

    protected function writeLog($level, $message, $context)
    {
        $this->logger->{$level}(
            $this->formatMessage($message),
            array_merge($this->context, $context)
        );
    }

    /**
     * Add context to all future logs.
     *
     * @param array $context
     * @return $this
     */
    public function withContext(array $context = [])
    {
        $this->context = array_merge($this->context, $context);

        return $this;
    }

    /**
     * Flush the existing context array.
     *
     * @return $this
     */
    public function withoutContext()
    {
        $this->context = [];

        return $this;
    }

    protected function formatMessage($message)
    {
        if (is_array($message)) {
            return var_export($message, true);
        } elseif ($message instanceof Jsonable) {
            return $message->toJson();
        } elseif ($message instanceof Arrayable && method_exists($message, 'toArray')) {
            return var_export($message->toArray(), true);
        }

        return (string)$message;
    }

    /**
     * Dynamically proxy method calls to the underlying logger.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->logger->{$method}(...$parameters);
    }
}