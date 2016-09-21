<?php

namespace ANDS\API\Task\ImportSubTask;


use ANDS\DataSource;

class PopulateImportOptions extends ImportSubTask
{
    public function run_task()
    {
        $dataSource = DataSource::find($this->parent()->dataSourceID);

        if (!$dataSource) {
            $this->stoppedWithError("Data Source ".$this->dataSourceID." Not Found");
            return;
        }

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
         * @todo importDefaultStatus
         * @todo datasourceRecordCountBefore
         */

        $this->parent()->setTaskData("recordsCreatedCount", 0);
        $this->parent()->setTaskData("recordsDeletedCount", 0);
        $this->parent()->setTaskData("recordsInFeedCount", 0);
        // calculate this $this->parent()->setTaskData("recordsIngestedCount", 0);
        $this->parent()->setTaskData("recordsUpdatedCount", 0);
        $this->parent()->setTaskData("datasourceRecordBeforeCount", 0);
        $this->parent()->setTaskData("datasourceRecordAfterCount", 0);

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
        if ($dataSource->attr('qa_flag') === DB_TRUE) {
            return 'SUBMITTED_FOR_ASSESSMENT';
        } else {
            if ($dataSource->attr('manual_publish') === DB_TRUE) {
                return 'APPROVED';
            } else {
                return 'PUBLISHED';
            }
        }
    }
}