<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckRights extends CheckType
{
    protected $descriptor = [
        'collection' => 'Includes <a href="https://documentation.ands.org.au/display/DOC/Access+rights">access rights</a> and <a href="https://documentation.ands.org.au/display/DOC/Licence">licence</a> information that specifies how the data may be reused by others',
        'service' => 'Includes <a href="https://documentation.ands.org.au/display/DOC/Access+rights">access rights</a> and <a href="https://documentation.ands.org.au/display/DOC/Licence">licence</a> information that specifies how the service may be reused by others',
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