<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckRelatedParties extends CheckType
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

        $hasRelatedInfoParties = in_array("party", $relatedInfoTypes);
        $hasRelatedObjectParties = $this->record->relationshipViews->where('to_class', 'party')->count() > 0;

        return $hasRelatedInfoParties || $hasRelatedObjectParties;
    }
}