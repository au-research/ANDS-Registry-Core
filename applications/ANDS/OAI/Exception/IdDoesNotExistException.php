<?php


namespace ANDS\OAI\Exception;


use ANDS\OAI\Exception\OAIException;

class IdDoesNotExistException extends OAIException
{
    public function getErrorName()
    {
        return "idDoesNotExist";
    }
}