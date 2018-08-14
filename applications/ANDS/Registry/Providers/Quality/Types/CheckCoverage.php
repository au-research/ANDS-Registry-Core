<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckCoverage extends CheckType
{
    protected $descriptor = [
        'collection' => 'Where relevant, provides <a href="https://documentation.ands.org.au/display/DOC/Coverage">spatial and/or temporal coverage</a> information that helps researchers find data that relates to a geographical area or time period of interest',
        'party' => '',
        'service' => '',
        'activity' => ''
    ];

    protected $message = [
        'collection' => 'Where relevant, include spatial and/or temporal coverage information for the data.'
    ];

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