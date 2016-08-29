<?php

namespace ANDS\API\Task\ImportSubTask;


class PopulateImportOptions extends ImportSubTask
{
    public function run_task()
    {
        $ci = $this->parent()->getCI();

        $ci->load->model('registry/data_source/data_sources', 'ds');
        $dataSource = $ci->ds->getByID($this->parent()->dataSourceID);

        if (!$dataSource) {
            $this->stoppedWithError("Data Source ".$this->dataSourceID." Not Found");
            return;
        }

        $this->parent()->setTaskData(
            "dataSourceDefaultStatus",
            $this->getDefaultRecordStatusForDataSource($dataSource)
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
        if ($dataSource->qa_flag === DB_TRUE) {
            return 'SUBMITTED_FOR_ASSESSMENT';
        } else {
            if ($dataSource->manual_publish === DB_TRUE) {
                return 'APPROVED';
            } else {
                return 'PUBLISHED';
            }
        }
    }
}