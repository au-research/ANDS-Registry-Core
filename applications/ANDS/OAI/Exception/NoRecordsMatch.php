<?php


namespace ANDS\OAI\Exception;

use ANDS\OAI\Exception\OAIException;

class NoRecordsMatch extends OAIException
{
    public function getErrorName()
    {
        return "noRecordsMatch";
    }
}