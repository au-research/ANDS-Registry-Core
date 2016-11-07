<?php


namespace ANDS\API\Task\ImportSubTask;


use ANDS\API\Task\ImportTask;
use ANDS\Payload;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Repository\DataSourceRepository;
use ANDS\Util\XMLUtil;

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

        $message = 'Changing status of '.count($ids). ' records to '.$targetStatus;
        $this->log($message);
        $this->parent()->updateHarvest([
            "importer_message" => $message
        ]);

        $recordIDsToPublished = [];

        foreach ($ids as $id) {
            $this->log('Processing '. $id);
            $record = RegistryObject::find($id);
            if ($record) {
                if (RegistryObjectsRepository::isPublishedStatus($record->status)
                    && RegistryObjectsRepository::isDraftStatus($targetStatus)) {
                    // from published to draft, cloning record
                    $this->log('Cloning record to DRAFT');
                    $draftRecord = $this->cloneToDraft($record, $targetStatus);
                    $this->log('DRAFT record ID:'.$draftRecord->registry_object_id.' has been created');
                } elseif(RegistryObjectsRepository::isDraftStatus($record->status)
                    && RegistryObjectsRepository::isPublishedStatus($targetStatus)) {
                    // from draft to published, put in a queue to process all at once
                    $recordIDsToPublished[] = $record->registry_object_id;
                } else {
                    // from draft to draft, standard status change
                    $record->status = $targetStatus;
                    $record->save();
                }
            } else {
                $this->addError("Record with ID:".$id. " not found!");
            }
        }

        if (count($recordIDsToPublished) > 0) {
            $this->publishRecords($recordIDsToPublished);
            $this->deleteDrafts($recordIDsToPublished);
        }
    }

    /**
     * Delete an array of draft ids
     *
     * @param $ids
     */
    public function deleteDrafts($ids)
    {
        foreach ($ids as $id) {
            RegistryObjectsRepository::completelyEraseRecordByID($id);
        }
    }

    /**
     * Publish an array of records
     * Using a sub pipeline
     *
     * @param $ids
     */
    public function publishRecords($ids)
    {
        $data = [];
        foreach ($ids as $id) {
            $record = RegistryObject::find($id);
            $recordData = $record->getCurrentData()->data;
            $data[] = XMLUtil::unwrapRegistryObject($recordData);
        }

        $data = trim(implode(NL, $data));
        $xml = XMLUtil::wrapRegistryObject($data);

        $dataSource = $this->getDataSource();

        //save this to file
        $batchID = "PUBLISH-".count($ids).'-'.time();
        Payload::write($dataSource->data_source_id, $batchID, $xml);

        //start a new ImportTask
        $importTask = new ImportTask;
        $importTask->init([
           'name' => "Publishing ".count($ids). " records for $dataSource->title($dataSource->data_source_id)",
            'params' => http_build_query([
                'ds_id' => $dataSource->data_source_id,
                'batch_id' => $batchID,
                'targetStatus' => 'PUBLISHED',
                'source' => 'mmr'
            ])
        ])->setCI($this->parent()->getCI())->enableRunAllSubTask()->initialiseTask();

        // don't handle refresh harvest in this workflow
        $importTask->removeSubtaskByname("HandleRefreshHarvest");

        $importTask->run();

        if ($importTask->hasError()) {
            foreach ($importTask->getError() as $error) {
                $this->addError($error);
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