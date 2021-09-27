<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckSubject extends CheckType
{
    protected $descriptor = [
        'collection' => 'Contains <a target="_blank" href="https://documentation.ardc.edu.au/display/DOC/Subject">subject</a> information to enhance discovery',
        'service' => 'Includes <a target="_blank" href="https://documentation.ardc.edu.au/display/DOC/Subject">subject</a> terms that describe the research focus of the service',
        'activity' => 'Contains <a target="_blank" href="https://documentation.ardc.edu.au/display/DOC/Subject">subject</a> information to associate an activity with collections in the same field'
    ];

    protected $message = [
        'collection' => 'Include subject information (e.g. an anzsrc-for code) to enhance discovery.',
        'party' => 'Include subject information (e.g. an anzsrc-for code) to enhance discovery.',
        'service' => 'Include subject terms (e.g. an anzsrc-for code) that describe the research focus of the service.',
        'activity' => 'Include subject information (e.g. an anzsrc-for code) to enhance discovery.'
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