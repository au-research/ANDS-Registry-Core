<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckRelatedActivity extends CheckType
{
    protected $descriptor = [
        'collection' => 'Is connected to <a href="https://documentation.ands.org.au/display/DOC/Activity">projects</a> associated with the data to improve discovery and provide context',
        'party' => 'Is connected to <a href="https://documentation.ands.org.au/display/DOC/Activity">activities</a> associated with the party',
        'service' => 'Is connected to <a href="https://documentation.ands.org.au/display/DOC/Activity">activities</a> associated with the service',
        'activity' => 'Is connected to <a href="https://documentation.ands.org.au/display/DOC/Activity">activities</a> associated with the activity'
    ];

    protected $message = [
        'collection' => 'Include any related parties, activities or services that provide context for, or assist discovery of, the data.',
        'party' => 'Include any related activities or collections that are associated with the party.'
    ];

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