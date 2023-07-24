<?php

namespace ANDS\DOI\Formatter;

class StringFormatter extends Formatter
{
    /**
     * Format and return the payload
     *
     * @param $payload
     * @return string
     */
    public function format($payload)
    {
        $payload = $this->fill($payload);
        return "[".$payload['responsecode']."] ".$payload['message']."<br />".$payload['verbosemessage']."<br/>".$payload['url'];

    }

}