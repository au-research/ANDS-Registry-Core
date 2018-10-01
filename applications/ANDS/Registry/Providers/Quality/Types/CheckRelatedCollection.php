<?php


namespace ANDS\Registry\Providers\Quality\Types;


use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Repository\RegistryObjectsRepository;

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
     * @throws \Exception
     */
    public function check()
    {
        if (in_array('collection', MetadataProvider::getRelatedInfoTypes($this->record, $this->simpleXML))) {
            return true;
        }

        if (RelationshipProvider::hasRelatedClass($this->record, 'collection')) {
            return true;
        }

        return false;
    }
}