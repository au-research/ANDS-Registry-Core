<?php


namespace ANDS\OAI\Exception;


use ANDS\OAI\Exception\OAIException;

class BadVerbException extends OAIException
{
    public function getErrorName()
    {
        return "badVerb";
    }
}