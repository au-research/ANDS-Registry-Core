<?php


namespace ANDS\API\Task\ImportSubTask;


use ANDS\Registry\Group;
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
        debug("Processing Core Metadata for $total records");
        foreach ($importedRecords as $index => $roID) {
            // $this->log('Processing (updated) record: ' . $roID);

            $record = RegistryObject::find($roID);
            $recordData = $record->getCurrentData();

            // determine class, type and group in the record data
            $classes = ['collection', 'party', 'service', 'activity'];
            foreach ($classes as $class) {
                $registryObjectsElement = XMLUtil::getSimpleXMLFromString($recordData->data);
                $element = $registryObjectsElement->xpath('//ro:registryObject/ro:' . $class);
                $registryObjectElement = array_first(
                    $registryObjectsElement->xpath('//ro:registryObject')
                );
                if (count($element) > 0) {
                    $element = array_first($element);
                    $record->class = $class;
                    $record->type = (string)$element['type'];
                    $group = (string)$registryObjectElement['group'];
                    $record->group = (string)$registryObjectElement['group'];

                    // added group if not exists
                    $exist = Group::where('title', $group)->first();
                    if (!$exist) {
                        $group = new Group;
                        $group->title = $title;
                        $group->slug = str_slug($title);
                        $group->save();
                    }

                    $record->save();
                    break;
                }
            }

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

            /**
             * Process Scholixable records
             * TODO: Move to it's own ImportSubTask
             */
            if ($this->parent()->getTaskData("targetStatus") == "PUBLISHED") {
                ScholixProvider::process($record);
            }


            $this->updateProgress($index, $total, "Processed ($index/$total) $ro->title($roID)");
            unset($ro);
        }
        debug("Finished Processing Core Metadata for $total records");
    }


}