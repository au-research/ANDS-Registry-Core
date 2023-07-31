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
    public $title = "HANDLING STATUS CHANGES";
    protected $requireDataSource = true;

    public function run_task()
    {
        $ids = explode(',', $this->parent()->getTaskData('ro_id'));
        if ($this->parent()->getTaskData('affectedRecords')) {
            $ids = array_merge($ids, $this->parent()->getTaskData('affectedRecords'));
        }
        $ids = array_filter($ids, function($id){
           return trim($id) != "";
        });

        $targetStatus = $this->parent()->getTaskData('targetStatus');

        if ($targetStatus == "" || count($ids) == 0) {
            $this->log("Status: $targetStatus or affectedRecords size is ".count($ids). ". abort");
            return;
        }

        $message = 'Changing status of '.count($ids). ' records to '.$targetStatus;
        $this->log($message);
        $this->parent()->updateHarvest([
            "importer_message" => $message
        ]);

        $recordIDsToPublished = [];
        $data_source = DataSourceRepository::getByID($this->parent()->dataSourceID);
        foreach ($ids as $id) {
            $this->log('Processing '. $id);
            $record = RegistryObject::find($id);
            if ($record) {
                if (RegistryObjectsRepository::isPublishedStatus($record->status)
                    && RegistryObjectsRepository::isDraftStatus($targetStatus)) {
                    // from published to draft, cloning record
                    $this->log('Cloning record to DRAFT');
                    $draftRecord = $this->cloneToDraft($record, $targetStatus);
                    if($data_source->getDataSourceAttributeValue('qa_flag') == 1){
                        $draftRecord->setRegistryObjectAttribute('manually_assessed', 'no');
                    }
                    $this->log('DRAFT record ID:'.$draftRecord->registry_object_id.' has been created');
                } elseif(RegistryObjectsRepository::isDraftStatus($record->status)
                    && RegistryObjectsRepository::isPublishedStatus($targetStatus)) {
                    // from draft to published, put in a queue to process all at once
                    $recordIDsToPublished[] = $record->registry_object_id;
                } else {
                    // from draft to draft, standard status change
                    
                    if( $targetStatus == 'APPROVED' && $data_source->getDataSourceAttributeValue('qa_flag') == 1){
                        $record->setRegistryObjectAttribute('manually_assessed', 'yes');
                    }
                    
                    elseif($data_source->getDataSourceAttributeValue('qa_flag') == 1){
                        $record->setRegistryObjectAttribute('manually_assessed', 'no');
                    }

                    $record->status = $targetStatus;

                    $record->save();
                }
            } else {
                $this->addError("Record with ID:".$id. " not found!");
            }
        }

        if (count($recordIDsToPublished) > 0) {
            $this->publishRecords($recordIDsToPublished);
            $this->parent()->setTaskData("deletedRecords", $ids);
        }

        // reset the affectedRecords, so as to not count deleted records among them
        $this->parent()->setTaskData('affectedRecords', []);
    }

    /**
     * Publish an array of records
     * Using a sub pipeline
     *
     * @param $ids
     */
    public function publishRecords($ids)
    {
        debug("Publishing: ". implode(', ', $ids));
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
        $batchID = "MANUAL-MMR-".count($ids).'-'.time();
        Payload::write($dataSource->data_source_id, $batchID, $xml);

        $this->parent()->setBatchID($batchID);
        $this->parent()->loadPayload();
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
        $path = \ANDS\Util\config::get('app.harvested_contents_path') . '/' . $record->data_source_id . '/' . $batchID . '.xml';
        file_put_contents($path, $recordData);

        $importTask = new ImportTask();
        $importTask->init([
            'params'=>'ds_id='.$record->data_source_id.'&batch_id='.$batchID.'&targetStatus=PUBLISHED'
        ])->enableRunAllSubTask()->initialiseTask();

        $importTask->run();

        return RegistryObjectsRepository::getPublishedByKey($record->key);
    }

    public function cloneToDraft($record, $targetStatus = "DRAFT")
    {
        $recordData = $record->getCurrentData()->data;

        // save this to file
        // TODO: use payload write instead
        $batchID = 'CLONE-' . time();
        $path = \ANDS\Util\config::get('app.harvested_contents_path') . '/' . $record->data_source_id . '/' . $batchID . '.xml';
        file_put_contents($path, $recordData);

        $importTask = new ImportTask();
        $importTask->init([
            'params'=>'ds_id='.$record->data_source_id.'&batch_id='.$batchID.'&targetStatus='.$targetStatus
        ])->initialiseTask();
        $importTask->enableRunAllSubTask()->run();

        $importedRecord = array_first($importTask->getTaskData('importedRecords'));
        return RegistryObject::find($importedRecord);
    }
}