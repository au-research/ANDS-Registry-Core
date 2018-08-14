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

    protected $message = [
        'collection' => 'Include an identifier, such as a DOI, to uniquely identify the data.',
        'party' => 'Include an identifier, such as an ORCID, that uniquely identifies the party.',
        'service' => 'Include an identifier, such as a handle, that uniquely identifies the service.',
        'activity' => 'Include an identifier, such as a PURL, that uniquely identifies the activity.'
    ];

    /**
     * Returns the status of the check
     *
     * @return bool
     */
    public function check()
    {
        $class = $this->record->class;
        return count($this->simpleXML->xpath('//ro:'.$class.'/ro:identifier')) > 0;
    }
}