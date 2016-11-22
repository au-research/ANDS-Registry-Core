<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\RegistryObject\Relationship;
use ANDS\Repository\RegistryObjectsRepository;

class ProcessRelationships extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "PROCESSING RELATIONSHIPS";

    public function run_task()
    {
        // addRelationships to all importedRecords
        $this->parent()->getCI()->load->model('registry/registry_object/registry_objects', 'ro');
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);

        // TODO: Order importedRecords by class


        foreach ($importedRecords as $index => $roID) {
            $ro = $this->parent()->getCI()->ro->getByID($roID);
            $ro->addRelationships();

            $record = RegistryObjectsRepository::getRecordByID($roID);
            RelationshipProvider::processGrantsRelationship($record);

            $this->updateProgress($index, $total, "Processed ($index/$total) $ro->title($roID)");
        }

        // Delete all relationship from all deletedRecords
        $deletedRecords = $this->parent()->getTaskData("deletedRecords");
        if ($deletedRecords === null || $deletedRecords === false) {
            return;
        }
        $total = count($deletedRecords);
        foreach ($deletedRecords as $index => $roID) {
            Relationship::where('registry_object_id', $roID)->delete();
            $this->updateProgress($index, $total, "Deleting Relationship ($index/$total) $roID");
        }
    }
}