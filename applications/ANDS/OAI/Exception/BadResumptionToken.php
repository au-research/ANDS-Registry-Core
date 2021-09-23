<?php


namespace ANDS\OAI\Exception;


use ANDS\OAI\Exception\OAIException;

class BadResumptionToken extends OAIException
{
    public function getErrorName()
    {
        return "Bad resumptionToken";
    }
}