<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckLocationAddress extends CheckType
{
    protected $descriptor = [
        'party' => 'Includes <a href="https://documentation.ands.org.au/display/DOC/Location">contact details</a> for a person or organisation',
        'activity' => 'Includes a <a href="https://documentation.ands.org.au/display/DOC/Location">location address</a> for an activity such as a URL to a project web page'
    ];

    protected $message = [
        'party' => 'Include contact details for the party.',
        'service' => 'Include a location address to provide access to, or information about how to access, the service.'
    ];

    /**
     * Returns the status of the check
     *
     * @return boolean
     */
    public function check()
    {
        return count($this->simpleXML->xpath('//ro:location/ro:address')) > 0;
    }
}