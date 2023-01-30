<?php

namespace ANDS\Registry\Events\Listener;

use ANDS\Log\Log;
use ANDS\Registry\Events\Event\RegenerateMetadataEvent;
use ANDS\Registry\Providers\Scholix\ScholixProvider;
use ANDS\Repository\RegistryObjectsRepository;

class RegenerateScholix
{
    public function handle(RegenerateMetadataEvent $event)
    {
        if (!$event->scholix) {
            return;
        }

        Log::info(__METHOD__." Attempting to regenerate Scholix for RegistryObject[id={$event->registryObjectId}]");

        if (!$event->registryObjectId) {
            throw new \Exception("RegistryObjectId prop required for this event");
        }

        $record = RegistryObjectsRepository::getRecordByID($event->registryObjectId);
        if (!$record) {
            throw new \Exception("RegistryObject[id=$event->registryObjectId] not found!");
        }

        ScholixProvider::process($record);
        Log::info(__METHOD__." Scholix regenerated for RegistryObject[id={$event->registryObjectId}]");
    }
}