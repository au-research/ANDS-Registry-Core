<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckIdentifier extends CheckType
{
    protected $descriptor = [
        'collection' => 'Includes an <a href="https://documentation.ands.org.au/display/DOC/Identifier">identifier</a> such as a DOI, that uniquely identifies the data',
        'service' => 'Includes an <a href="https://documentation.ands.org.au/display/DOC/Identifier">identifier</a> such as a handle, that uniquely identifies the service',
        'activity' => 'Includes an <a href="https://documentation.ands.org.au/display/DOC/Identifier">identifier</a> such as a PURL, that uniquely identifies the activity',
        'party' => 'Includes an <a href="https://documentation.ands.org.au/display/DOC/Identifier">identifier</a> such as an ORCID, that uniquely identifies the party'
    ];

    /**
     * Returns the status of the check
     *
     * @return bool
     */
    public function check()
    {
        return count($this->simpleXML->xpath('//ro:identifier')) > 0;
    }
}