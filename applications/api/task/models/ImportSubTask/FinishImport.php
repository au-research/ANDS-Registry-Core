<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 20/09/2016
 * Time: 1:33 PM
 */

namespace ANDS\API\Task\ImportSubTask;

use ANDS\DataSource;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository as Repo;
use Carbon\Carbon;

class FinishImport extends ImportSubTask
{

    private $harvestStarted;
    private $addToDatasourceLog = true;

    protected $requireDataSource = true;
    protected $title = "FINISHING IMPORT";

    public function run_task()
    {
        $this->writeImportSummaryLog();

        $dataSource = $this->getDataSource();

        $this->parent()->setTaskData(
            "datasourceRecordAfterCount",
            Repo::getCountByDataSourceIDAndStatus($this->parent()->dataSourceID,
                $this->parent()->getTaskData("targetStatus")
            )
        );

        if ($this->addToDatasourceLog) {
            $this->updateDataSourceLogs($dataSource);
        }

        $this->updateDataSourceStats($dataSource);

        return $this;
    }

    public function disableLoggingToDatasourceLogs()
    {
        $this->addToDatasourceLog = false;
    }

    /**
     * Add a data_source_log
     *
     * @param $dataSource
     */
    public function updateDataSourceLogs($dataSource)
    {
        $source = $this->parent()->getTaskData("source");
        if ($source === null) {
            $source = "harvester";
        }

        // in case of error
        $noRecords = $this->parent()->getTaskData('noRecords');
        if (count($this->parent()->getError()) > 0 && !$noRecords ) {
            $message = $this->parent()->name . " Completed with error(s)" . NL;
            $message .= $this->parent()->getDataSourceMessage();
            $this->parent()->setTaskData("dataSourceLog", $message);
            $dataSource->appendDataSourceLog($message, "error", "IMPORTER", "");
            return;
        }

        // not error
        $message = $this->parent()->name . " Completed" . NL;

        if ($noRecords) {
            $message = $this->parent()->name . " Completed with 0 records found". NL;
        }

        $message .= $this->parent()->getDataSourceMessage();
        $this->parent()->setTaskData("dataSourceLog", $message);

        $dataSource->appendDataSourceLog($message, "info", "IMPORTER", "");
        return;
    }

    public function updateDataSourceStats($dataSource)
    {
        // update count_total
        $dataSource->setDataSourceAttribute(
            'count_total',
            RegistryObject::where('data_source_id', $dataSource->data_source_id)->count()
        );

        // count_$status
        $validStatuses = ["MORE_WORK_REQUIRED", "DRAFT", "SUBMITTED_FOR_ASSESSMENT", "ASSESSMENT_IN_PROGRESS", "APPROVED", "PUBLISHED"];
        foreach ($validStatuses as $status) {
            $dataSource->setDataSourceAttribute(
                'count_'.$status,
                RegistryObject::where('data_source_id', $dataSource->data_source_id)
                    ->where('status', $status)->count()
            );
        }

        // TODO :update count_ql
    }

    private function writeImportSummaryLog()
    {
        $dataSource = $this->getDataSource();
        $parentTaskData = collect($this->parent()->getTaskData());
        $parentBenchmarkData = collect($this->parent()->getBenchmarkData());

        $started = Carbon::parse($this->parent()->dateAdded);
        $end = Carbon::now();

        // TODO started should be harvest started if there's a harvest

        $payload = [
            'source' => $parentTaskData->get('source', 'unknown'),
            'pipeline' => $parentTaskData->get('pipeline', 'unknown'),
            'status' => $this->parent()->getStatus(),

            'started' => $started->toDateTimeString(),
            'finished' => $end->toDateTimeString(),
            'duration' => $end->diffInSeconds($started),

            'datasource' => [
                'id' => $dataSource->id,
                'title' => $dataSource->title,
                'key' => $dataSource->key,
                'owner' => $dataSource->record_owner ?: ''
            ],

            'batchID' => $parentTaskData['batchID'],
            'harvest' => [
                'id' => $parentTaskData->get('harvest_id'),
                // TODO harvest benchmark read from summary file
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
                })->toArray()
            ],
            'errors' => collect($this->parent()->getError())->implode("\n\n"),
            'counts' => [
                'imported' => count($parentTaskData['importedRecords']),
                'deleted' => count($parentTaskData['deletedRecords']),
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

    }

}