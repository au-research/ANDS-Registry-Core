<?php

namespace ANDS\Registry\API\Controller;


use ANDS\RegistryObject;
use ANDS\Repository\DataSourceRepository;
use ANDS\Registry\Providers\ServiceDiscovery\ServiceDiscovery as ServiceDiscoveryProvider;

class DataSourcesServiceController extends HTTPController
{
    public function index($dsAny = null)
    {
        $dataSource = DataSourceRepository::getByAny($dsAny);

        $ids = RegistryObject::where("status", "PUBLISHED")
            ->where('data_source_id', $dataSource->data_source_id)
            ->where('class', 'collection')->pluck('registry_object_id');

        if (count($ids) === 0) {
            throw new \Exception("No collection found");
        }

        $links = ServiceDiscoveryProvider::getServiceByRegistryObjectIds($ids);
        $links = ServiceDiscoveryProvider::processLinks($links);
        $links = ServiceDiscoveryProvider::formatLinks($links);

        if (count($links) === 0) {
            throw new \Exception("No links found for {$ids->count()} collections");
        }

        $acronym = $dataSource->acronym ? : "ACRONYM";
        $batchID = "MANUAL";
        $directoryPath = "/var/ands/data/{$acronym}";

        try {
            if (!is_dir($directoryPath)) {
                mkdir($directoryPath, 0775, true);
            }
            $filePath = "{$directoryPath}/services_{$batchID}.json";

            file_put_contents($filePath, json_encode($links, true));

            return [
                'count' => count($links),
                'path' => $filePath
            ];
        } catch (Exception $e) {
            return get_exception_msg($e);
        }
    }
}