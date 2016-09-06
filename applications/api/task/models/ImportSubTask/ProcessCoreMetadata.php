<?php


namespace ANDS\API\Task\ImportSubTask;


use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;

class ProcessCoreMetadata extends ImportSubTask
{
    public function run_task()
    {
        if ($this->parent()->getTaskData("importedRecords") === false) {
            $this->log("No imported records found");
            return;
        }

        foreach ($this->parent()->getTaskData("importedRecords") as $roID) {
            $record = RegistryObject::find($roID);
            $recordData = $record->getCurrentData();

            // determine class, type and group in the record data
            $classes = ['collection', 'party', 'service', 'activity'];
            foreach ($classes as $class) {
                $registryObjectsElement = XMLUtil::getSimpleXMLFromString($recordData->data);
                $element = $registryObjectsElement->xpath('//ro:registryObject/ro:'.$class);
                $registryObjectElement = array_first(
                    $registryObjectsElement->xpath('//ro:registryObject')
                );
                if (count($element) > 0) {
                    $element = array_first($element);
                    $record->class = $class;
                    $record->type = (string) $element['type'];
                    $record->group = (string) $registryObjectElement['group'];
                    $record->save();
                    break;
                }
            }

            //determine harvest_id
            $record->setRegistryObjectAttribute('harvest_id', $this->parent()->batchID);

            // TODO: record_owner on RegistryObject model and RegistryObjectAttribute (as created_who)
            $record->record_owner = "SYSTEM";
            $record->save();

            // titles and slug require the ro object
            $this->parent()->getCI()->load->model('registry/registry_object/registry_objects', 'ro');
            $ro = $this->parent()->getCI()->ro->getByID($roID);
            $ro->updateTitles();

            // TODO only update slug if the defaultStatus is PUBLISHED
            $ro->generateSlug();

            // TODO manually_assessed

            unset($ro);
        }
    }
}