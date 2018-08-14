<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckRelatedInformation extends CheckType
{
    protected $descriptor = [
        'service' => 'Is connected to <a href="https://documentation.ands.org.au/display/DOC/Related+information">related information</a> that supports use of the service, such as additional protocol information'
    ];

    protected $message = [
        'service' => 'Include related information that supports use of the service, such as additional protocol information.'
    ];

    /**
     * Returns the status of the check
     *
     * @return boolean
     */
    public function check()
    {
        $validRelatedInfoType = [
            "provenance",
            "reuseInformation",
            "website"
        ];
        $relatedInfoTypes = [];
        foreach ($this->simpleXML->xpath("//ro:relatedInfo/@type") as $type) {
            $relatedInfoTypes[] = (string) $type;
        }
        $intersect = array_intersect($validRelatedInfoType, $relatedInfoTypes);

        return count($intersect) > 0;
    }
}