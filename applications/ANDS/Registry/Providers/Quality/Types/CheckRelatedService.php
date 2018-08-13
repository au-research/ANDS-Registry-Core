<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckRelatedService extends CheckType
{
    public static $name = "relatedServices";

    /**
     * Returns the status of the check
     *
     * @return boolean
     */
    public function check()
    {
        $relatedInfoTypes = [];
        foreach ($this->simpleXML->xpath("//ro:relatedInfo/@type") as $type) {
            $relatedInfoTypes[] = (string) $type;
        }

        $hasRelatedInfoService = in_array("party", $relatedInfoTypes);
        $hasRelatedObjectServices = $this->record->relationshipViews->where('to_class', 'service')->count() > 0;

        return $hasRelatedInfoService || $hasRelatedObjectServices;
    }
}