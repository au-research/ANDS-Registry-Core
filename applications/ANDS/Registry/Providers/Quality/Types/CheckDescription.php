<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckDescription extends CheckType
{
    protected $descriptor = [
        'service' => 'Includes a <a href="https://documentation.ands.org.au/display/DOC/Description">description</a> of the service for potential users',
        'activity' => 'Includes a <a href="https://documentation.ands.org.au/display/DOC/Description">description</a> of the activity to provide context for related collections'
    ];

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