<?php
namespace ANDS\API\Task\ImportSubTask;


use ANDS\API\Task\ImportTask;
use ANDS\API\Task\Task;
use ANDS\DataSource;

class ImportSubTask extends Task
{
    private $parentTask;
    protected $requirePayload = false;
    protected $requireImportedRecords = false;
    protected $requireDeletedRecords = false;
    protected $requireAffectedRecords = false;
    protected $requireHarvestedRecords = false;
    protected $requireHarvestedOrImportedRecords = false;
    protected $requireDataSource = false;
    protected $requireImportedCollections = false;
    public $title = "Import SubTask";

    private $dataSource = null;

    public function run()
    {

        $this->parent()->updateHarvest([
            'status' => 'IMPORT - '. $this->title,
            'importer_message' => "",
            'message' => ""
        ]);

        if ($this->requirePayload && $this->parent()->hasPayload() === false) {
            $this->log("Payload require for this task");
            $this->setStatus("COMPLETED");
            return;
        }

        if ($this->requireImportedRecords) {
            $importedRecords = $this->parent()->getTaskData("importedRecords");
            if ($importedRecords === false || $importedRecords === null) {
                $this->log("Imported Records require for this task");
                $this->setStatus("COMPLETED");
                return;
            }
        }

        if ($this->requireImportedCollections) {
            $importedRecords = $this->parent()->getTaskData("imported_collection_ids");
            if ($importedRecords === false || $importedRecords === null) {
                $this->log("Imported Collection Records require for this task");
                $this->setStatus("COMPLETED");
                return;
            }
        }
        
        if ($this->requireAffectedRecords) {
            $affectedRecords = $this->parent()->getTaskData("affectedRecords");
            if ($affectedRecords === false || $affectedRecords === null) {
                $this->log("Affected Records require for this task");
                $this->setStatus("COMPLETED");
                return;
            }
        }

        if ($this->requireHarvestedRecords) {
            $harvestedRecords = $this->parent()->getTaskData("harvestedRecordIDs");
            if ($harvestedRecords === false || $harvestedRecords === null) {
                $this->log("Harvested Records require for this task");
                $this->setStatus("COMPLETED");
                return;
            }
        }

        if($this->requireHarvestedOrImportedRecords){
            $importedRecords = $this->parent()->getTaskData("importedRecords");
            $harvestedRecords = $this->parent()->getTaskData("harvestedRecordIDs");
            if (($importedRecords === false || $importedRecords === null) &&
                ($harvestedRecords === false || $harvestedRecords === null)) {
                $this->log("Imported or Harvested Records require for this task");
                $this->setStatus("COMPLETED");
                return;
            }
        }

        if ($this->requireDeletedRecords) {
            $deletedRecords = $this->parent()->getTaskData("deletedRecords");
            if ($deletedRecords === false || $deletedRecords === null) {
                $this->log("Deleted Records require for this task");
                $this->setStatus("COMPLETED");
                return;
            }
        }

        if ($this->requireDataSource) {
            $dataSource = DataSource::find($this->parent()->dataSourceID);
            if (!$dataSource) {
                $this->parent()->stoppedWithError("Data Source ".$this->parent()->dataSourceID." Not Found");
                return;
            }
            $this->dataSource = $dataSource;
        }

        return parent::run();
    }

    /**
     * @param $task
     * @return $this
     */
    public function setParentTask($task)
    {
        $this->parentTask = $task;
        return $this;
    }

    /**
     * @return ImportTask
     */
    public function getParentTask()
    {
        return $this->parentTask;
    }

    /**
     * Alias for getParentTask
     * for simpler usage
     *
     * @return ImportTask
     */
    public function parent()
    {
        return $this->getParentTask();
    }

    public function log($log)
    {
//        $this->message['log'][] = $log;
        $this->parent()->log(get_class($this) . ": " . $log);
        return $this;
    }

    public function addError($log)
    {
        // add error to self as per
        parent::addError($log);

        // parent task should also have this error
        $this->parent()->addError($log);

        // add importer error to the harvests
        $this->parent()->updateHarvest(["importer_message" => get_class($this) . "(ERROR) " . $log]);
        return $this;
    }

    /**
     * @return DataSource|null
     */
    public function getDataSource()
    {
        if ($this->dataSource) {
            return $this->dataSource;
        }

        if ($id = $this->parent()->getDataSourceID()) {
            return DataSource::find($id);
        }

        return null;
    }

    public function updateProgress($index, $total, $message)
    {
        $this->parent()->updateHarvest([
            'message' => json_encode([
                'progress' => [
                    'total' => $total,
                    'current' => $index
                ]
            ], true),
            'importer_message'=> $message
        ]);
    }


}

//@todo move to ANDS\API\Task\Exception?
class NonFatalException extends \Exception
{

}