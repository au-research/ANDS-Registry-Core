<?php


namespace ANDS\Registry\Providers\Quality\Types;


use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RelationshipProvider;
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

        if (in_array('service', MetadataProvider::getRelatedInfoTypes($this->record, $this->simpleXML))) {
            return true;
        }

        if (RelationshipProvider::hasRelatedClass($this->record, 'service')) {
            return true;
        }

        return false;
    }
}