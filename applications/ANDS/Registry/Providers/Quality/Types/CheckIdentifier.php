<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckIdentifier extends CheckType
{
    /**
     * Returns the status of the check
     *
     * @return bool
     */
    public function check()
    {
        return count($this->simpleXML->xpath('//ro:identifier')) > 0;
    }
}