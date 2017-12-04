<?php
namespace ANDS\Registry\API\Middleware;


abstract class Middleware
{
    private $message;

    abstract public function pass();

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}