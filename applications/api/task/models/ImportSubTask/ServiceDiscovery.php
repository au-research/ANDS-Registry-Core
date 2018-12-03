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
    protected $requireImportedCollections = true;
    protected $requireDataSource = true;
    protected $requirePayload = false;
    protected $title = "SERVICE DISCOVERY";

    public function run_task()
    {
        $dataSource = $this->getDataSource();
        //$flag = $dataSource->getDataSourceAttributeValue('service_discovery_enabled');
        //if (!$flag || $flag == "0") {
        //    $this->log("Data source service discovery is disabled for {$dataSource->title} ({$dataSource->id})");
        //    return;
        //}

        // only deal with collection records
        $ids = $this->parent()->getTaskData("imported_collection_ids");
        if (!$ids || count($ids) == 0) {
            $this->log("No imported collection ids found");
            return;
        }

        // Generate the services in the right format
        $this->log("Generating services links for " . count($ids) . " records");
        $links = ServiceDiscoveryProvider::getServiceByRegistryObjectIds($ids);
        $links = ServiceDiscoveryProvider::processLinks($links);
        if (count($links) == 0) {
            $this->log("No links found");
            return;
        }
        $links = ServiceDiscoveryProvider::formatLinks($links);
        $this->log("Discovered " . count($links) . " links");

        $harvestedContentDir = get_config_item('harvested_contents_path');

        $harvestedContentDir = rtrim($harvestedContentDir, '/') . '/';
        $batchID = $this->parent()->getTaskData("batchID");
        $directoryPath = $harvestedContentDir . $this->parent()->getTaskData('dataSourceID') . '/' . $batchID;

        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0775, true);
        }

        $filePath = "{$directoryPath}/services.json";
        $this->log("Writing link to {$filePath}");
        file_put_contents($filePath, json_encode($links, true));
        $this->parent()->setTaskData('services_links', $filePath);

        $this->parent()->updateHarvest(["importer_message" => "Service Discovery"]);
    }
}