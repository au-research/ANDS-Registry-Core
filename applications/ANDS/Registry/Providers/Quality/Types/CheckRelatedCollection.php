<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckRelatedCollection extends CheckType
{
    protected $descriptor = [
        'collection' => '',
        'party' => 'Is connected to <a target="_blank" href="https://documentation.ands.org.au/display/DOC/Collection">collections</a> associated with the party',
        'service' => 'Is connected to <a target="_blank" href="https://documentation.ands.org.au/display/DOC/Collection">collections</a> that can be accessed through, or acted upon by, the service',
        'activity' => ''
    ];

    protected $message = [
        'activity' => 'Include any related parties, and any related collections or services that are outputs of the activity.',
        'party' => 'Include any related activities or collections that are associated with the party.',
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

        $hasRelatedInfoCollection = in_array("collection", $relatedInfoTypes);
        $hasRelatedObjectCollection = $this->record->relationshipViews->where('to_class', 'collection')->count() > 0;

        return $hasRelatedObjectCollection || $hasRelatedInfoCollection;
    }
}