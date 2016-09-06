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

        // the import status is what should be used if it is set
        $this->parent()->setTaskData(
            "importStatus",
            $this->parent()->getTaskData("dataSourceDefaultStatus")
        );

        /**
         * @todo datasourceHarvestMode
         * @todo importDefaultStatus
         * @todo datasourceRecordCountBefore
         */

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