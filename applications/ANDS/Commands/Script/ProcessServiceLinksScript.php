<?php
namespace ANDS\Commands\Script;

use ANDS\DataSource;
use ANDS\RegistryObject;
use ANDS\Registry\Providers\ServiceDiscovery\ServiceDiscovery as ServiceDiscoveryProvider;

class ProcessServiceLinksScript extends GenericScript
{
    public function run()
    {
        $dataSourceID = $this->getInput()->getOption('params');
        if (!$dataSourceID) {
            $this->log("You have to specify -p dataSourceID");
            return;
        }

        $dataSource = DataSource::find($dataSourceID);
        if (!$dataSource) {
            $this->log("Datasource $dataSourceID not found");
            return;
        }

        // get all published collection id from the data source and run service discovery on them
        $ids = RegistryObject::where("status", "PUBLISHED")
            ->where('data_source_id', $dataSourceID)
            ->where('class', 'collection')->pluck('registry_object_id');

        $this->log("Generating services links for " . count($ids) . " collection records for {$dataSource->title} ($dataSource->id)");
        $links = ServiceDiscoveryProvider::getServiceByRegistryObjectIds($ids);
        $links = ServiceDiscoveryProvider::processLinks($links);
        $links = ServiceDiscoveryProvider::formatLinks($links);
        $this->log("Generated " . count($links) . " links");

        $acronym = $dataSource->acronym ? : "ACRONYM";
        $batchID = "MANUAL";
        $directoryPath = "/var/ands/data/{$acronym}";
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0775, true);
        }
        $filePath = "{$directoryPath}/services_{$batchID}.json";
        $this->log("Writing link to {$filePath}");
        file_put_contents($filePath, json_encode($links, true));
    }
}