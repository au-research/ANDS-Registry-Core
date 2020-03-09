<?php


namespace ANDS\Registry\Providers\Quality\Types;


use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Repository\RegistryObjectsRepository;

class CheckRelatedActivity extends CheckType
{
    protected $descriptor = [
        'collection' => 'Is connected to <a target="_blank" href="https://documentation.ardc.edu.au/display/DOC/Activity">projects</a> associated with the data to improve discovery and provide context',
        'party' => 'Is connected to <a target="_blank" href="https://documentation.ardc.edu.au/display/DOC/Activity">activities</a> associated with the party',
        'service' => 'Is connected to <a target="_blank" href="https://documentation.ardc.edu.au/display/DOC/Activity">activities</a> associated with the service',
        'activity' => 'Is connected to <a target="_blank" href="https://documentation.ardc.edu.au/display/DOC/Activity">activities</a> associated with the activity'
    ];

    protected $message = [
        'collection' => 'Include any related parties, activities or services that provide context for, or assist discovery of, the data.',
        'party' => 'Include any related activities or collections that are associated with the party.'
    ];

    /**
     * Returns the status of the check
     *
     * @return boolean
     * @throws \Exception
     */
    public function check()
    {
        if (in_array('activity', MetadataProvider::getRelatedInfoTypes($this->record, $this->simpleXML))) {
            return true;
        }

        if (RelationshipProvider::hasRelatedClass($this->record, 'activity')) {
            return true;
        }

        return false;
    }
}