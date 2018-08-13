<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckRelatedActivity extends CheckType
{
    public static $name = "relatedActivities";

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

        $hasRelatedActivities = in_array("party", $relatedInfoTypes);
        $relatedActivities = $this->record->relationshipViews->where('to_class', 'activity')->count() > 0;

        return $hasRelatedActivities || $relatedActivities;
    }
}