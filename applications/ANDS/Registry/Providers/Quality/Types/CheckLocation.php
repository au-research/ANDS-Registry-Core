<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckLocation extends CheckType
{
    public static $name = 'location';
    protected $msg = 'Provides access to, or information about how to access, the data being described';

    /**
     * Returns the status of the check
     *
     * @return boolean
     */
    public function check()
    {
        return count($this->simpleXML->xpath('//ro:location')) > 0;
    }
}