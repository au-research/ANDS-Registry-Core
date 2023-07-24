<?php


namespace ANDS\OAI\Exception;


use ANDS\OAI\Exception\OAIException;

class BadArgumentException extends OAIException
{
    public function getErrorName()
    {
        return "badArgument";
    }
}