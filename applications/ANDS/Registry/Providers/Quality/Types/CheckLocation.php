<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckLocation extends CheckType
{
    protected $descriptor = [
        'collection' => 'Provides access to, or information about <a target="_blank" href="https://documentation.ands.org.au/display/DOC/Location">how to access</a>, the data being described',
        'party' => 'Provides access to, or information about <a target="_blank" href="https://documentation.ands.org.au/display/DOC/Location">how to access</a>, the party being described',
        'service' => 'Provides access to, or information about <a target="_blank" href="https://documentation.ands.org.au/display/DOC/Location">how to access</a>, the service being described',
        'activity' => 'Provides access to, or information about <a target="_blank" href="https://documentation.ands.org.au/display/DOC/Location">how to access</a>, the activity being described'
    ];

    protected $message = [
        'collection' => 'Include a location address to provide access to, or information about how to access, the data.',
        'activity' => 'Include a location address for the activity, such as a URL to a project web page.'
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