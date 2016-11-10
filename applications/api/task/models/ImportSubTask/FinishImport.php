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

class FinishImport extends ImportSubTask
{

    private $harvestStarted;

    protected $requireDataSource = true;
    protected $title = "FINISHING IMPORT";

    public function run_task()
    {
        $dataSource = $this->getDataSource();

        $this->parent()->setTaskData(
            "datasourceRecordAfterCount",
            Repo::getCountByDataSourceIDAndStatus($this->parent()->dataSourceID,
                $this->parent()->getTaskData("targetStatus")
            )
        );

        $this->updateDataSourceLogs($dataSource);
        $this->updateDataSourceStats($dataSource);

        return $this;
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
        if ($errorList = $this->parent()->getError()) {
            $message = "Import from $source COMPLETED with error(s)" . NL;
            $message .= $this->parent()->getDataSourceMessage();
            $this->parent()->setTaskData("dataSourceLog", $message);
            $dataSource->appendDataSourceLog($message, "error", "IMPORTER", "");
            return;
        }

        // not error
        $message = "Import from $source COMPLETED" . NL;

        if ($this->parent()->getTaskData("noRecords")) {
            $message = "Import from $source COMPLETED with 0 records found". NL;
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

}