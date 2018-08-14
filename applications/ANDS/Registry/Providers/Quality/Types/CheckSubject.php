<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckSubject extends CheckType
{
    protected $descriptor = [
        'collection' => 'Contains <a href="https://documentation.ands.org.au/display/DOC/Subject">subject</a> information to enhance discovery',
        'service' => 'Includes <a href="https://documentation.ands.org.au/display/DOC/Subject">subject</a> terms that describe the research focus of the service',
        'activity' => 'Contains <a href="https://documentation.ands.org.au/display/DOC/Subject">subject</a> information to associate an activity with collections in the same field'
    ];

    /**
     * Returns the status of the check
     *
     * @return boolean
     */
    public function check()
    {
        return count($this->simpleXML->xpath('//ro:subject')) > 0;
    }
}