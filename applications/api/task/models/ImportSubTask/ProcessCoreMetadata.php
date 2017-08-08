<?php


namespace ANDS\API\Task\ImportSubTask;


use ANDS\Registry\Group;
use ANDS\Registry\Providers\RIFCS\CoreMetadataProvider;
use ANDS\Registry\Providers\ScholixProvider;
use ANDS\Registry\Providers\TitleProvider;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;

/**
 * Class ProcessCoreMetadata
 * @package ANDS\API\Task\ImportSubTask
 */
class ProcessCoreMetadata extends ImportSubTask
{
    protected $requireHarvestedOrImportedRecords = true;
    protected $title = "PROCESSING CORE METADATA";

    public function run_task()
    {
        $this->processUpdatedRecords();
    }

    /**
     * Update importedRecords core metadata
     *
     */
    public function processUpdatedRecords()
    {
        $importedRecords = $this->parent()->getTaskData("importedRecords");

        if ($importedRecords === false || $importedRecords === null) {
            return;
        }

        $total = count($importedRecords);

        foreach ($importedRecords as $index => $roID) {

            $record = RegistryObject::find($roID);

            // ProcessCoreMetadata, class, group, type, ANDS\Group get set
            CoreMetadataProvider::process($record);

            //determine harvest_id
            $record->setRegistryObjectAttribute('harvest_id',
                $this->parent()->batchID);
            
            $record->status = $this->parent()->getTaskData("targetStatus");

            // process Title
            TitleProvider::process($record);
            $record->save();

            // titles and slug require the ro object
            $this->parent()->getCI()->load->model(
                'registry/registry_object/registry_objects', 'ro'
            );
            $ro = $this->parent()->getCI()->ro->getByID($roID);

            // TODO: SlugProvider::process($record);
            $ro->generateSlug();

            // TODO: Remove CodeIgniter RO dependency
            $ro->save();

            $this->updateProgress($index, $total, "Processed ($index/$total) $ro->title($roID)");
            unset($ro);
        }
    }


}