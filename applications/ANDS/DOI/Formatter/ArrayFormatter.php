<?php


namespace ANDS\DOI\Formatter;


class ArrayFormatter extends Formatter
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
        return $payload;
    }
}