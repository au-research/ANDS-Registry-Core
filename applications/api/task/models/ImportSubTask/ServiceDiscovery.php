<?php
namespace ANDS\API\Task\ImportSubTask;

use ANDS\Registry\Providers\ServiceDiscovery\ServiceDiscovery as ServiceDiscoveryProvider;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class ServiceDiscovery
 * @package ANDS\API\Task\ImportSubTask
 */
class ServiceDiscovery extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "SERVICE DISCOVERY";

    public function run_task()
    {
        // TODO: check for DataSource for flag

        // TODO: check only for collection, can be optimised
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $ids = [];
        foreach ($importedRecords  as $index => $roID) {
            $record = RegistryObjectsRepository::getRecordByID($roID);
            if (strtolower($record->class) === "collection") {
                $ids[] = $record->id;
            }
        }

        $links = ServiceDiscoveryProvider::getServiceByRegistryObjectIds($ids);
        $links = ServiceDiscoveryProvider::processLinks($links);

        $links = ServiceDiscoveryProvider::formatLinks($links);

        // TODO: save the links
    }
}