<?php


namespace ANDS\API\Task\ImportSubTask;


use ANDS\DataSource\Harvest;
use Carbon\Carbon;

class WriteImportSummary extends ImportSubTask
{
    protected $requireDataSource = true;
    protected $title = "WRITING IMPORT SUMMARY";

    public function run_task()
    {
        $dataSource = $this->getDataSource();
        $parentTaskData = collect($this->parent()->getTaskData());
        $parentBenchmarkData = collect($this->parent()->getBenchmarkData());

        $started = Carbon::parse($this->parent()->dateAdded);
        $end = Carbon::now();

        // TODO started should be harvest started if there's a harvest
        $harvestSummary = null;
        if ($harvestID = $this->parent()->getHarvestID()) {
            $harvest = Harvest::find($harvestID);
            $harvestSummary = json_decode($harvest->summary, true);
            $started = Carbon::parse($harvestSummary['start']);
        }

        $payload = [
            'event' => 'import',

            'source' => $parentTaskData->get('source', 'unknown'),
            'pipeline' => $parentTaskData->get('pipeline', 'unknown'),

            'started' => $started->toDateTimeString(),
            'finished' => $end->toDateTimeString(),
            'duration' => $end->diffInSeconds($started),

            'batchID' => $parentTaskData['batchID'],

            'harvest' => $harvestSummary,

            'datasource' => [
                'id' => $dataSource->id,
                'title' => $dataSource->title,
                'key' => $dataSource->key,
                'owner' => $dataSource->record_owner ?: ''
            ],

            'import' => [
                'duration_total' => collect($parentBenchmarkData)->map(function($bench) {
                    return $bench['duration_seconds'];
                })->sum(),
                'duration' => collect($parentBenchmarkData)->map(function($bench) {
                    return $bench['duration_seconds'];
                })->toArray(),
                'memory' =>  collect($parentBenchmarkData)->map(function($bench) {
                    return $bench['memory_mb'];
                })->toArray(),
                'errors' => collect($this->parent()->getError())->implode("\n\n"),
            ],

            'counts' => [
                'imported' => $parentTaskData->get('importedRecords', false)
                    ? count($parentTaskData->get('importedRecords')) : 0,
                'deleted' => $parentTaskData->get('deletedRecords', 0)
                    ? count($parentTaskData->get('deletedRecords')) : 0,
                'inFeed' => $parentTaskData->get('recordsInFeedCount', 0),
                'created' => $parentTaskData->get('recordsCreatedCount', 0),
                'updated' => $parentTaskData->get('recordsUpdatedCount', 0),
                'unchanged' => $parentTaskData->get('recordsNotUpdatedCount', 0),
                'invalid' => $parentTaskData->get('invalidRegistryObjectsCount', 0),
                'duplicate_key' => $parentTaskData->get('duplicateKeyinFeedCount', 0),
                'key_in_other_ds' => $parentTaskData->get('recordsExistOtherDataSourceCount', 0),
                'missing_key' => $parentTaskData->get('missingRegistryObjectKeyCount', 0),
                'missing_originating_source' => $parentTaskData->get('missingOriginatingSourceCount', 0),
                'missing_group' => $parentTaskData->get('missingGroupAttributeCount', 0),
                'missing_title' => $parentTaskData->get('missingTitleCount', 0),
                'missing_type' => $parentTaskData->get('missingTypeCount', 0),
                'missing_collection_description' => $parentTaskData->get('missingDescriptionCollectionCount', 0),
                'errors' => count($this->parent()->getError()),
            ]
        ];

        monolog($payload, 'import', 'info', true);
    }
}