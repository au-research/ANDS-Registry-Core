<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckDescription extends CheckType
{
    public static $name = 'description';
    /**
     * Returns the status of the check
     *
     * @return boolean
     */
    public function check()
    {
        return count($this->simpleXML->xpath('//ro:description')) > 0;
    }
}