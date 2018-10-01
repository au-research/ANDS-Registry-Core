<?php


namespace ANDS\Registry\Providers\Quality\Types;


use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Repository\RegistryObjectsRepository;

class CheckRelatedService extends CheckType
{
    protected $descriptor = [
        'collection' => 'Is connected to <a target="_blank" href="https://documentation.ands.org.au/display/DOC/Service">services</a> that can be used to access or operate on the data',
        'party' => 'Is connected to any <a target="_blank" href="https://documentation.ands.org.au/display/DOC/Service">services</a> that is an output of the party',
        'service' => 'Is connected to any <a target="_blank" href="https://documentation.ands.org.au/display/DOC/Service">services</a> that is an output of the service',
        'activity' => 'Is connected to any <a target="_blank" href="https://documentation.ands.org.au/display/DOC/Service">services</a> that is an output of the activity'
    ];

    protected $message = [
        'collection' => 'Include any related parties, activities or services that provide context for, or assist discovery of, the data.',
        'activity' => 'Include any related parties, and any related collections or services that are outputs of the activity.'
    ];

    /**
     * Returns the status of the check
     *
     * @return boolean
     * @throws \Exception
     */
    public function check()
    {
        if (in_array('service', MetadataProvider::getRelatedInfoTypes($this->record, $this->simpleXML))) {
            return true;
        }

        if (RelationshipProvider::hasRelatedClass($this->record, 'service')) {
            return true;
        }

        return false;
    }
}