<?php


namespace ANDS\API\Task\ImportSubTask;


use ANDS\API\Task\ImportTask;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Repository\DataSourceRepository;

class HandleStatusChange extends ImportSubTask
{
    protected $title = "HANDLING STATUS CHANGES";
    protected $requireDataSource = true;

    public function run_task()
    {
        $ids = explode(',', $this->parent()->getTaskData('ro_id'));
        if ($this->parent()->getTaskData('importedRecords')) {
            $ids = array_merge($ids, $this->parent()->getTaskData('importedRecords'));
        }
        $ids = array_filter($ids, function($id){
           return trim($id) != "";
        });

        $targetStatus = $this->parent()->getTaskData('targetStatus');

        $this->log('Changing status of '.count($ids). ' records to '.$targetStatus);
        $this->parent()->updateHarvest([
            "importer_message" => 'Changing status of '.count($ids). ' records to '.$targetStatus
        ]);

        foreach ($ids as $id) {
            $this->log('Processing '. $id);
            $record = RegistryObject::find($id);
            if ($record) {
                if (RegistryObjectsRepository::isPublishedStatus($record->status)
                    && RegistryObjectsRepository::isDraftStatus($targetStatus)) {
                    $this->log('Cloning record to DRAFT');
                    $draftRecord = $this->cloneToDraft($record, $targetStatus);
                    $this->log('DRAFT record ID:'.$draftRecord->registry_object_id.' has been created');
                } elseif(RegistryObjectsRepository::isDraftStatus($record->status)
                    && RegistryObjectsRepository::isPublishedStatus($targetStatus)) {
                    $this->log('Publishing record '.$record->registry_object_id);

                    $publishedRecord = $this->publishRecord($record);

                    $this->log('Published Record '.$publishedRecord->registry_object_id);

                    //delete the draft
                    RegistryObjectsRepository::completelyEraseRecordByID($record->registry_object_id);
                } else {
                    $record->status = $targetStatus;
                    $record->save();
                }

            } else {
                $this->addError("Record with ID:".$id. " not found!");
            }
        }
    }

    /**
     * Creates a new ImportTask with default pipeline
     * with targetStatus = PUBLISHED
     *
     * @param $record
     * @return mixed
     */
    public function publishRecord($record)
    {
        $recordData = $record->getCurrentData()->data;

        //save this to file
        $batchID = 'PUBLISH-' . time();
        $path = get_config_item('harvested_contents_path') . '/' . $record->data_source_id . '/' . $batchID . '.xml';
        file_put_contents($path, $recordData);

        $importTask = new ImportTask();
        $importTask->init([
            'params'=>'ds_id='.$record->data_source_id.'&batch_id='.$batchID.'&targetStatus=PUBLISHED'
        ])->setCI($this->parent()->getCI())->enableRunAllSubTask()->initialiseTask();

        $importTask->run();

        return RegistryObjectsRepository::getPublishedByKey($record->key);
    }

    public function cloneToDraft($record, $targetStatus = "DRAFT")
    {
        $recordData = $record->getCurrentData()->data;

        // save this to file
        // TODO: use payload write instead
        $batchID = 'CLONE-' . time();
        $path = get_config_item('harvested_contents_path') . '/' . $record->data_source_id . '/' . $batchID . '.xml';
        file_put_contents($path, $recordData);

        $importTask = new ImportTask();
        $importTask->init([
            'params'=>'ds_id='.$record->data_source_id.'&batch_id='.$batchID.'&targetStatus='.$targetStatus
        ])->setCI($this->parent()->getCI())->initialiseTask();
        $importTask->enableRunAllSubTask()->run();

        $importedRecord = array_first($importTask->getTaskData('importedRecords'));
        return RegistryObject::find($importedRecord);
    }
}