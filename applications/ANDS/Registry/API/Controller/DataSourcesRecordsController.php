<?php

namespace ANDS\Registry\API\Controller;


use ANDS\Payload;
use ANDS\Registry\API\Middleware\IPRestrictionMiddleware;
use ANDS\Registry\API\Request;
use ANDS\Registry\Importer;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository;

class DataSourcesRecordsController extends HTTPController implements RestfulController
{

    public function index($dsAny = null)
    {
        $dataSource = DataSourceRepository::getByAny($dsAny);
        $records = $this->getRecordsWithFilters($dataSource);
        return $records;
    }

    private function getRecordsWithFilters($dataSource)
    {
        $limit = Request::value('limit', 10);
        $offset = Request::value('offset', 0);

        $records = RegistryObjectsRepository::getRecordsByDataSource(
            $dataSource, $limit, $offset,
            Request::only(['status', 'class', 'type', 'group'])
        );

        return $records;
    }

    public function show()
    {
        throw new \Exception("Not implemented");
    }

    public function update()
    {
        throw new \Exception("Not implemented");
    }

    public function delete($dsAny = null)
    {
        $this->middlewares([IPRestrictionMiddleware::class]);
        $dataSource = DataSourceRepository::getByAny($dsAny);
        $task = Importer::instantDeleteRecords($dataSource, [
            'ids' => $this->getRecordsWithFilters($dataSource)
                ->pluck('registry_object_id')
        ]);
        return $task ? $task->toArray() : [];
    }

    public function destroy()
    {
        throw new \Exception("Not implemented");
    }

    public function add($dsAny = null)
    {
        $dataSource = DataSourceRepository::getByAny($dsAny);

        $this->validate(['url']);
        $this->middlewares([IPRestrictionMiddleware::class]);

        $url = Request::value('url');

        $content = @file_get_contents($url);

        $batchID = "MANUAL-URL-".str_slug($url).'-'.time();
        Payload::write($dataSource->data_source_id, $batchID, $content);
        $task =  Importer::instantImportRecordFromBatchID($dataSource, $batchID);
        return $task->toArray();
    }
}