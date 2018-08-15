<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckCitationInfo extends CheckType
{
    protected $descriptor = [
        'collection' => 'Includes <a target="_blank" href="https://documentation.ands.org.au/display/DOC/Citation+information">citation information</a> that clearly indicates how the data should be cited when reused'
    ];

    protected $message = [
        'collection' => 'Include citation information to clearly indicate how the data should be cited.'
    ];

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