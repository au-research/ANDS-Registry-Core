<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckRelatedActivity extends CheckType
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

        $hasRelatedActivities = in_array("activity", $relatedInfoTypes);
        $relatedActivities = $this->record->relationshipViews->where('to_class', 'activity')->count() > 0;

        return $hasRelatedActivities || $relatedActivities;
    }
}