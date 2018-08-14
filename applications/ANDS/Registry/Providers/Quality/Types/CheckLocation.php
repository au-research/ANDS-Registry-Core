<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckLocation extends CheckType
{
    protected $descriptor = [
        'collection' => 'Provides access to, or information about <a href="https://documentation.ands.org.au/display/DOC/Location">how to access</a>, the data being described',
        'party' => 'Provides access to, or information about <a href="https://documentation.ands.org.au/display/DOC/Location">how to access</a>, the party being described',
        'service' => 'Provides access to, or information about <a href="https://documentation.ands.org.au/display/DOC/Location">how to access</a>, the service being described',
        'activity' => 'Provides access to, or information about <a href="https://documentation.ands.org.au/display/DOC/Location">how to access</a>, the activity being described'
    ];

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