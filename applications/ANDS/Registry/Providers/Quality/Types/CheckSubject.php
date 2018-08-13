<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckSubject extends CheckType
{
    public static $name = "subject";

    /**
     * Returns the status of the check
     *
     * @return boolean
     */
    public function check()
    {
        return count($this->simpleXML->xpath('//ro:subject')) > 0;
    }
}