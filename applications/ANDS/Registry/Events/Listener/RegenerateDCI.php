<?php

namespace ANDS\Registry\Events\Listener;

use ANDS\Log\Log;
use ANDS\Registry\Events\Event\RegenerateMetadataEvent;
use ANDS\Registry\Providers\DCI\DataCitationIndexProvider;
use ANDS\Repository\RegistryObjectsRepository;

class RegenerateDCI
{
    public function handle(RegenerateMetadataEvent $event)
    {
        if (!$event->dci) {
            return;
        }

        Log::info(__METHOD__." Attempting to regenerate DCI for RegistryObject[id={$event->registryObjectId}]");

        if (!$event->registryObjectId) {
            throw new \Exception("RegistryObjectId prop required for this event");
        }

        $record = RegistryObjectsRepository::getRecordByID($event->registryObjectId);
        if (!$record) {
            throw new \Exception("RegistryObject[id=$event->registryObjectId] not found!");
        }

        DataCitationIndexProvider::process($record);
        Log::info(__METHOD__." Regenerated DCI for RegistryObject[id={$event->registryObjectId}]");
    }
}