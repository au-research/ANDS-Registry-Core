<?php

namespace ANDS\API\Task\ImportSubTask;


class PopulateImportOptions extends ImportSubTask
{
    public function run_task()
    {
        $ci = $this->parent()->getCI();

        $this->log('Sub Task ran');
        $ci->load->model('registry/data_source/data_sources', 'ds');
        $dataSource = $ci->ds->getByID($this->parent()->dataSourceID);
        if (!$dataSource) {
            $this->stoppedWithError("Data Source ".$this->dataSourceID." Not Found");
        }

        $this->parent()->setTaskData(
            "dataSourceDefaultStatus",
            $this->getDefaultRecordStatusForDataSource($dataSource)
        );

        return $this;
    }

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