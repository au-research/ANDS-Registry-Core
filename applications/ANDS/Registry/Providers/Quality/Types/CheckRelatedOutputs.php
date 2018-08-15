<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckRelatedOutputs extends CheckType
{
    protected $descriptor = [
        'collection' => 'Is connected to <a target="_blank" href="https://documentation.ands.org.au/display/DOC/Related+information">related outputs</a>, such as publications, that give context to the data'
    ];

    protected $message = [
        'collection' => 'Include related information, such as publications, to provide context for the data.'
    ];

    /**
     * Returns the status of the check
     *
     * @return boolean
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
        $relatedInfoTypes = [];
        foreach ($this->simpleXML->xpath("//ro:relatedInfo/@type") as $type) {
            $relatedInfoTypes[] = (string)$type;
        }
        $intersect = array_intersect($validRelatedInfoType, $relatedInfoTypes);

        return count($intersect) > 0;
    }
}