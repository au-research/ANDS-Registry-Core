<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckRelatedOutputs extends CheckType
{
    public static $name = "relatedOutputs";

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
            $relatedInfoTypes[] = (string) $type;
        }
        $intersect = array_intersect($validRelatedInfoType, $relatedInfoTypes);

        return count($intersect) > 0;
    }
}