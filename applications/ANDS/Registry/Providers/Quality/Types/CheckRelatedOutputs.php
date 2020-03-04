<?php


namespace ANDS\Registry\Providers\Quality\Types;


use ANDS\Registry\Providers\MetadataProvider;

class CheckRelatedOutputs extends CheckType
{
    protected $descriptor = [
        'collection' => 'Is connected to <a target="_blank" href="https://documentation.ardc.edu.au/display/DOC/Related+information">related outputs</a>, such as publications, that give context to the data'
    ];

    protected $message = [
        'collection' => 'Include related information, such as publications, to provide context for the data.'
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
            "dataQualityInformation",
            "metadata",
            "publication",
            "provenance",
            "reuseInformation",
            "website"
        ];
        $relatedInfoTypes = MetadataProvider::getRelatedInfoTypes($this->record, $this->simpleXML);
        $intersect = array_intersect($validRelatedInfoType, $relatedInfoTypes);

        return count($intersect) > 0;
    }
}