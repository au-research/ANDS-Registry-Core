<?php


namespace ANDS\API\Task\ImportSubTask;


use ANDS\Registry\Group;
use ANDS\Registry\Providers\RIFCS\CoreMetadataProvider;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\RIFCS\SlugProvider;
use ANDS\Registry\Providers\RIFCS\TitleProvider;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;

/**
 * Class ProcessCoreMetadata
 * @package ANDS\API\Task\ImportSubTask
 */
class ProcessCoreMetadata extends ImportSubTask
{
    protected $requireHarvestedOrImportedRecords = true;
    public $title = "PROCESSING CORE METADATA";

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
            try {
                CoreMetadataProvider::process($record);
            } catch (\Exception $e) {
                $this->addError("Failed CoreMetadataProvider process on record $roID: ". get_exception_msg($e));
                continue;
            }

            // process Title
            try {
                TitleProvider::process($record);
            } catch (\Exception $e) {
                $this->addError("Failed Title processing on record $roID: ". get_exception_msg($e));
                continue;
            }

            // process dates
            try {
                DatesProvider::process($record);
            } catch (\Exception $e) {
                $this->addError("Failed Date processing on record $roID: ". get_exception_msg($e));
                continue;
            }

            //determine harvest_id
            $record->setRegistryObjectAttribute('harvest_id',
                $this->parent()->batchID);
            
            $record->status = $this->parent()->getTaskData("targetStatus");

            $record->save();

            // generate and save the slug
            try {
                SlugProvider::process($record);
            } catch (\Exception $e) {
                $this->addError("Failed to process slug on record $roID: " . get_exception_msg($e));
            }

            $this->updateProgress($index, $total, "Processed ($index/$total) $record->title($roID)");
            unset($ro);
        }
    }


}