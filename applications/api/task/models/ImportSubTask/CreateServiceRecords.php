<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 10/8/18
 * Time: 3:25 PM
 */
namespace ANDS\API\Task\ImportSubTask;

use ANDS\Registry\Providers\ServiceDiscovery\ServiceProducer as ServiceProducer;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class ServiceDiscovery
 * @package ANDS\API\Task\ImportSubTask
 */
class CreateServiceRecords extends ImportSubTask
{
    protected $requireImportedCollections = true;
    protected $requireDataSource = true;
    protected $requirePayload = false;
    protected $title = "SERVICE CREATION";

    public function run_task()
    {
        $service_json_file = $this->parent()->getTaskData('services_links');
        $service_discovery_service_url = get_config_item('SERVICES_DISCOVERY_SERVICE_URL');
        $serviceProduce = new ServiceProducer($service_discovery_service_url);

        // Generate the services in the right format
        $this->log("Generating services from $service_json_file");
        $services_json = file_get_contents($service_json_file);
        $serviceProduce->processServices($services_json);
        $serviceCount = $serviceProduce->getServiceCount();
        if ($serviceCount == 0) {
            $this->log("No Services generated");
            return;
        }
        $this->log("Generated $serviceCount rifcs service records");

        $harvestedContentDir = get_config_item('harvested_contents_path');

        $harvestedContentDir = rtrim($harvestedContentDir, '/') . '/';
        $batchID = $this->parent()->getTaskData("batchID");
        $directoryPath = $harvestedContentDir . $this->parent()->getTaskData('dataSourceID') . '/' . $batchID;

        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0775, true);
        }

        $filePath = "{$directoryPath}/services.xml";
        $this->log("Writing RIFCS Services to {$filePath}");
        file_put_contents($filePath, $serviceProduce->getRegistryObjects());
        $this->parent()->loadPayload();
        $this->parent()->setTaskData('payload', $filePath);


    }
}