<?php


namespace ANDS\Registry\Providers\Quality\Types;


use ANDS\Repository\RegistryObjectsRepository;

class CheckRelatedActivityOutput extends CheckType
{
    protected $descriptor = [
        'activity' => 'Is connected to any related <a target="_blank" href="https://documentation.ands.org.au/display/DOC/Collection">collection</a> or <a target="_blank"  href="https://documentation.ands.org.au/display/DOC/Service">service</a> that is an output of the activity'
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

        if ($this->record->status === "DRAFT") {
            $relatedClasses = collect($this->simpleXML->xpath("//ro:relatedObject/ro:key"))
                ->map(function($keyField){
                    return (string) $keyField;
                })
                ->map(function($key) {
                    if ($record = RegistryObjectsRepository::getPublishedByKey($key)) {
                        return $record->class;
                    }
                    return null;
                });

            $draftHasRelatedCollection = $relatedClasses->contains('collection');
            $draftHasRelatedService = $relatedClasses->contains('service');

            return $draftHasRelatedCollection || $draftHasRelatedService
                || $hasRelatedInfoCollection || $hasRelatedObjectCollection
                || $hasRelatedInfoService || $hasRelatedObjectService;
        }

        return $hasRelatedInfoCollection || $hasRelatedObjectCollection
            || $hasRelatedInfoService || $hasRelatedObjectService;
    }
}