<?php


namespace ANDS\Registry\Providers\Quality\Types;


use ANDS\Repository\RegistryObjectsRepository;

class CheckRelatedParties extends CheckType
{
    protected $descriptor = [
        'collection' => 'Is connected to <a target="_blank" href="https://documentation.ands.org.au/display/DOC/Party">people and organisations</a> associated with the data to improve discovery',
        'party' => 'Is connected to <a target="_blank" href="https://documentation.ands.org.au/display/DOC/Party">parties</a> associated with the party',
        'service' => 'Is connected to <a target="_blank" href="https://documentation.ands.org.au/display/DOC/Party">parties</a> associated with the service',
        'activity' => 'Is connected to <a target="_blank" href="https://documentation.ands.org.au/display/DOC/Party">people</a> associated with the activity'
    ];

    protected $message = [
        'collection' => 'Include any related parties, activities or services that provide context for, or assist discovery of, the data.',
        'activity' => 'Include any related parties, and any related collections or services that are outputs of the activity.',
        'service' => 'Include any related parties or collections that can be accessed through, or acted upon by, the service.'
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

        $hasRelatedInfoParties = in_array("party", $relatedInfoTypes);
        $hasRelatedObjectParties = $this->record->relationshipViews->where('to_class', 'party')->count() > 0;

        if ($this->record->status === "DRAFT") {
            $draftHasRelatedParties = collect($this->simpleXML->xpath("//ro:relatedObject/ro:key"))
                ->map(function($keyField){
                    return (string) $keyField;
                })
                ->map(function($key) {
                    if ($record = RegistryObjectsRepository::getPublishedByKey($key)) {
                        return $record->class;
                    }
                    return null;
                })->contains('party');

            return $draftHasRelatedParties || $hasRelatedInfoParties || $hasRelatedObjectParties;
        }

        return $hasRelatedInfoParties || $hasRelatedObjectParties;
    }
}