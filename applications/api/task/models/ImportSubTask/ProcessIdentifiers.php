<?php


namespace ANDS\API\Task\ImportSubTask;
use ANDS\Repository\DataSourceRepository;

class ProcessIdentifiers extends ImportSubTask
{
    protected $requirePayload = true;
    protected $requireImportedRecords = true;

    public function run_task()
    {

        $dataSource = DataSourceRepository::getByID($this->parent()->dataSourceID);
        if (!$dataSource) {
            $this->stoppedWithError("Data Source ".$this->parent()->dataSourceID." Not Found");
            return;
        }
        $dataSource->updateHarvest($this->parent()->harvestID, ['status'=>'PROCESSING IDENTIFIERS']);

        $this->parent()->getCI()->load->model('registry/registry_object/registry_objects', 'ro');
        foreach ($this->parent()->getTaskData("importedRecords") as $roID) {
            $ro = $this->parent()->getCI()->ro->getByID($roID);
            $ro->processIdentifiers();
        }
    }
}