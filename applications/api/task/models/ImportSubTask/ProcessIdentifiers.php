<?php


namespace ANDS\API\Task\ImportSubTask;
use ANDS\Repository\DataSourceRepository;

class ProcessIdentifiers extends ImportSubTask
{
    protected $requirePayload = true;
    protected $requireImportedRecords = true;
    protected $title = "PROCESSING IDENTIFIERS";

    public function run_task()
    {
        // TODO: Refactor to RIFCS\IdentifierProvider
        $this->parent()->getCI()->load->model('registry/registry_object/registry_objects', 'ro');
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        foreach ( $importedRecords as $index=>$roID) {
            $this->updateProgress( $index, count($importedRecords), "Processing Record " . $roID );
            $ro = $this->parent()->getCI()->ro->getByID($roID);
            $ro->processIdentifiers();
        }
    }
}