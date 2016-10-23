<?php

namespace ANDS\API\Task\ImportSubTask;

use ANDS\DataSource;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository as Repo;

class PopulateImportOptions extends ImportSubTask
{
    protected $title = "POPULATING IMPORT OPTIONS";
    protected $requireDataSource = true;

    public function run_task()
    {
        $dataSource = $this->getDataSource();

        // save the Task ID to the harvest itself
        $harvest = $dataSource->harvest()->first();
        $harvest->task_id = $this->parent()->getId();
        $harvest->save();

        $dataSource->appendDataSourceLog(
            "Import Started". NL. "Task ID: ".$this->parent()->getId(),
            "info", "IMPORTER"
        );

        $this->parent()->setTaskData(
            "dataSourceDefaultStatus",
            $this->getDefaultRecordStatusForDataSource($dataSource)
        );

        // the targetStatus is the status that the records will go in
        // if set by the task param itself, it will override the dataSourceDefaultStatus
        if ($this->parent()->getTaskData("targetStatus") == null) {
            $this->parent()->setTaskData(
                "targetStatus",
                $this->parent()->getTaskData("dataSourceDefaultStatus")
            );
        }

        /**
         * @todo datasourceHarvestMode
         */

        // records thaqt are deleted in task by either OAI deleted or REFRESH mode
        $this->parent()->setTaskData("recordsDeletedCount", 0);
        // all registry objects in feed that are valid
        $this->parent()->setTaskData("recordsInFeedCount", 0);
        // records that exist in other datasource with the same key
        $this->parent()->setTaskData("recordsExistOtherDataSourceCount", 0);
        // NEW registry Objects Created
        $this->parent()->setTaskData("recordsCreatedCount", 0);
        // Exist Registry Objects Updated
        $this->parent()->setTaskData("recordsUpdatedCount", 0);
        // Existing records content already has matching content in feed
        $this->parent()->setTaskData("recordsNotUpdatedCount", 0);
        // Record count before harvest
        $this->parent()->setTaskData("datasourceRecordBeforeCount",
            Repo::getCountByDataSourceIDAndStatus($this->parent()->dataSourceID,
            $this->parent()->getTaskData("dataSourceDefaultStatus")
            ));
        // record count after harvest
        $this->parent()->setTaskData("datasourceRecordAfterCount", 0);
        $this->parent()->setTaskData("missingRegistryObjectKeyCount", 0);
        $this->parent()->setTaskData("duplicateKeyinFeedCount", 0);
        $this->parent()->setTaskData("missingOriginatingSourceCount", 0);
        $this->parent()->setTaskData("missingGroupAttributeCount", 0);
        $this->parent()->setTaskData("invalidRegistryObjectsCount", 0);
        // record count if REFRESH mode was applied (delete records from previous harvest)
        return $this;
    }

    /**
     * Business Rule
     * Return the default status for the given data source
     *
     * @param $dataSource
     * @return string
     */
    private function getDefaultRecordStatusForDataSource($dataSource)
    {
        if ($dataSource->attr('qa_flag') == DB_TRUE) {
            return 'SUBMITTED_FOR_ASSESSMENT';
        } else {
            if ($dataSource->attr('manual_publish') == DB_TRUE) {
                return 'APPROVED';
            } else {
                return 'PUBLISHED';
            }
        }
    }
}