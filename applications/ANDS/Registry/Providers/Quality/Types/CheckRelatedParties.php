<?php


namespace ANDS\Registry\Providers\Quality\Types;


use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RelationshipProvider;
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
     * @throws \Exception
     */
    public function check()
    {
        if (in_array("party", MetadataProvider::getRelatedInfoTypes($this->record, $this->simpleXML))) {
            return true;
        }

        if (RelationshipProvider::hasRelatedClass($this->record, 'party')) {
            return true;
        }

        return false;
    }
}