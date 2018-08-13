<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckRelatedInformation extends CheckType
{
    public static $name = 'relatedinfomation';

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