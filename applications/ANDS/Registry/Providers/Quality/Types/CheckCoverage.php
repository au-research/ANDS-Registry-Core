<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckCoverage extends CheckType
{
    /**
     * Returns the status of the check
     *
     * @return boolean
     */
    public function check()
    {
        return count($this->simpleXML->xpath('//ro:coverage')) > 0;
    }
}