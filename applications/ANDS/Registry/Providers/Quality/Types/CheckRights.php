<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckRights extends CheckType
{
    protected $descriptor = [
        'collection' => 'Includes <a target="_blank" href="https://documentation.ardc.edu.au/display/DOC/Access+rights">access rights</a> and <a target="_blank" href="https://documentation.ardc.edu.au/display/DOC/Licence">licence</a> information that specifies how the data may be reused by others',
        'service' => 'Includes <a target="_blank" href="https://documentation.ardc.edu.au/display/DOC/Access+rights">access rights</a> and <a target="_blank" href="https://documentation.ardc.edu.au/display/DOC/Licence">licence</a> information that specifies how the service may be reused by others',
    ];

    /**
     * Returns the status of the check
     *
     * @return boolean
     */
    public function check()
    {
        return count($this->simpleXML->xpath('//ro:rights')) > 0;
    }
}