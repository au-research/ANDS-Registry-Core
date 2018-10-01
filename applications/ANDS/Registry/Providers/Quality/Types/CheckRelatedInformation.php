<?php


namespace ANDS\Registry\Providers\Quality\Types;


use ANDS\Registry\Providers\MetadataProvider;

class CheckRelatedInformation extends CheckType
{
    protected $descriptor = [
        'service' => 'Is connected to <a target="_blank" href="https://documentation.ands.org.au/display/DOC/Related+information">related information</a> that supports use of the service, such as additional protocol information'
    ];

    protected $message = [
        'service' => 'Include related information that supports use of the service, such as additional protocol information.'
    ];

    /**
     * Returns the status of the check
     *
     * @return boolean
     * @throws \Exception
     */
    public function check()
    {
        $validRelatedInfoType = [
            "provenance",
            "reuseInformation",
            "website"
        ];
        $relatedInfoTypes = MetadataProvider::getRelatedInfoTypes($this->record, $this->simpleXML);
        $intersect = array_intersect($validRelatedInfoType, $relatedInfoTypes);

        return count($intersect) > 0;
    }
}