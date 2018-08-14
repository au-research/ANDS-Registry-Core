<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckRelatedCollection extends CheckType
{

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

        $hasRelatedInfoCollection = in_array("collection", $relatedInfoTypes);
        $hasRelatedObjectCollection = $this->record->relationshipViews->where('to_class', 'collection')->count() > 0;

        return $hasRelatedObjectCollection || $hasRelatedInfoCollection;
    }
}