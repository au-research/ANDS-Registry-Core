<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckExistenceDate extends CheckType
{
    protected $descriptor = [
        'activity' => 'Includes <a href="https://documentation.ands.org.au/display/DOC/Existence+dates">existence dates</a> for the activity to allow users to narrow their search by date'
    ];

    /**
     * Returns the status of the check
     *
     * @return boolean
     */
    public function check()
    {
        return count($this->simpleXML->xpath('//ro:existenceDates')) > 0;
    }
}