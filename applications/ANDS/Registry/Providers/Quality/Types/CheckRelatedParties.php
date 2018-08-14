<?php


namespace ANDS\Registry\Providers\Quality\Types;


class CheckRelatedParties extends CheckType
{
    protected $descriptor = [
        'collection' => 'Is connected to <a href="https://documentation.ands.org.au/display/DOC/Party">people and organisations</a> associated with the data to improve discovery',
        'party' => 'Is connected to <a href="https://documentation.ands.org.au/display/DOC/Party">parties</a> associated with the party',
        'service' => 'Is connected to <a href="https://documentation.ands.org.au/display/DOC/Party">parties</a> associated with the service',
        'activity' => 'Is connected to <a href="https://documentation.ands.org.au/display/DOC/Party">people</a> associated with the activity'
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

        return $hasRelatedInfoParties || $hasRelatedObjectParties;
    }
}