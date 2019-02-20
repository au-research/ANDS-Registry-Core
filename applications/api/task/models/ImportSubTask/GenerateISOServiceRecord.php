<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 20/2/19
 * Time: 3:52 PM
 */

namespace ANDS\API\Task\ImportSubTask;

use \ANDS\Repository\RegistryObjectsRepository as Repo;
use ANDS\API\Task\ImportTask;
use \ANDS\Registry\Providers\ISO19115\ISO19115_3Provider;

class GenerateISOServiceRecord extends ImportSubTask
{
    protected $requirePayload = true;
    protected $title = "GENERATING ISO SERVICE RECORDS";
    protected $data_source = null;

    public function run_task()
    {
        $provider = new ISO19115_3Provider();
        $ids = $this->parent()->getTaskData("imported_service_ids");
        $counter = 0;
        if(sizeof($ids) == 0 )
            return;
        foreach($ids as $id){
            $record = Repo::getRecordByID($id);
            $result = $provider->process($record);
            if($result){
                $counter++;
            }
        }
        $this->parent()->updateHarvest(["importer_message" => "ISO ServiceRecords Created: ".$counter]);
        $this->parent()->setTaskData("IsoServiceObjectsGenerated", $counter);
    }
}