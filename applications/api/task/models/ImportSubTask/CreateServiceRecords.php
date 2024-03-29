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
    public $title = "SERVICE CREATION";

    public function run_task()
    {
        $service_json_file = $this->parent()->getTaskData('services_links');
        if($service_json_file == '')
        {
            $this->log("No Services to generate");
            return;
        }
        $service_discovery_service_url = \ANDS\Util\config::get('app.services_registry_url');
        $serviceProduce = new ServiceProducer($service_discovery_service_url);

        // Generate the services in the right format
        $this->log("Generating services from $service_json_file");
        $services_json = file_get_contents($service_json_file);
        $serviceProduce->processServices($services_json);
        $summary = $serviceProduce->getSummary();

        $this->log("Tested " . $summary['number_of_links_tested'] . " service links");
        $this->log("Generated " . $summary['number_of_service_created'] . " rifcs service records");
        $this->log($summary['number_of_links_failed'] . " Links Failed");
        if(sizeof($summary['error_msgs']) > 0){
            foreach ($summary['error_msgs'] as $error)
                $this->log($error);
        }

        $harvestedContentDir = \ANDS\Util\config::get('app.harvested_contents_path');

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
        $this->parent()->setTaskData("number_of_links_tested", $summary['number_of_links_tested']);
        $this->parent()->setTaskData("number_of_links_failed", $summary['number_of_links_failed']);
        $this->parent()->updateHarvest(["importer_message" => "Generated " . $summary['number_of_service_created'] . " rifcs service records"]);
    }
}