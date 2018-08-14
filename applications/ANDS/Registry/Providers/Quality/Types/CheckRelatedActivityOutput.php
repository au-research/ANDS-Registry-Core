<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckRelatedActivityOutput extends CheckType
{
    protected $descriptor = [
        'activity' => 'Is connected to any related <a href="https://documentation.ands.org.au/display/DOC/Collection">collection</a> or <a href="https://documentation.ands.org.au/display/DOC/Service">service</a> that is an output of the activity'
    ];

    protected $message = [
        'activity' => 'Include any related parties, and any related collections or services that are outputs of the activity.'
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

        $hasRelatedInfoCollection = in_array("collection", $relatedInfoTypes);
        $hasRelatedObjectCollection = $this->record->relationshipViews->where('to_class', 'collection')->count() > 0;
        $hasRelatedInfoService = in_array("service", $relatedInfoTypes);
        $hasRelatedObjectService = $this->record->relationshipViews->where('to_class', 'service')->count() > 0;

        return $hasRelatedInfoCollection || $hasRelatedObjectCollection || $hasRelatedInfoService || $hasRelatedObjectService;
    }
}