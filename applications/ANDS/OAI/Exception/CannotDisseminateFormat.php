<?php


namespace ANDS\OAI\Exception;


use ANDS\OAI\Exception\OAIException;

class CannotDisseminateFormat extends OAIException
{
    public function getErrorName()
    {
        return "cannotDisseminateFormat";
    }
}