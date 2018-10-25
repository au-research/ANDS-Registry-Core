<?php


namespace ANDS\OAI\Exception;


class OAIException extends \Exception
{
    public function getErrorName()
    {
        return "OAI Exception";
    }
}