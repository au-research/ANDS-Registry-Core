<?php


namespace ANDS\API\Task\ImportSubTask;

class IndexPortal extends ImportSubTask
{
    protected $requireImportedRecords = true;

    public function run_task()
    {
        $this->parent()->getCI()->load->model('registry/registry_object/registry_objects', 'ro');
        // TODO: MAJORLY REFACTOR THIS
        foreach ($this->parent()->getTaskData("importedRecords") as $roID) {
            $ro = $this->parent()->getCI()->ro->getByID($roID);
            $index = $ro->indexable_json();
            $ro->setMetadata('solr_doc', json_encode($index));
            $ro->sync();
        }
    }
}