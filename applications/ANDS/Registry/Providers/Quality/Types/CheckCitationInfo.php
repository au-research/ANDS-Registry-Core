<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckCitationInfo extends CheckType
{
    /**
     * Returns the status of the check
     *
     * @return boolean
     */
    public function check()
    {
        return count($this->simpleXML->xpath('//ro:citationInfo')) > 0;
    }
}