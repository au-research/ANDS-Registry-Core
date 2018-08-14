<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckRelatedService extends CheckType
{
    protected $descriptor = [
        'collection' => 'Is connected to <a href="https://documentation.ands.org.au/display/DOC/Service">services</a> that can be used to access or operate on the data',
        'party' => 'Is connected to any service that is an output of the party',
        'service' => 'Is connected to any service that is an output of the service',
        'activity' => 'Is connected to any service that is an output of the activity'
    ];

    protected $message = [
        'collection' => 'Include any related parties, activities or services that provide context for, or assist discovery of, the data.',
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

        $hasRelatedInfoService = in_array("party", $relatedInfoTypes);
        $hasRelatedObjectServices = $this->record->relationshipViews->where('to_class', 'service')->count() > 0;

        return $hasRelatedInfoService || $hasRelatedObjectServices;
    }
}