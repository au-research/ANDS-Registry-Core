<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 20/09/2016
 * Time: 1:33 PM
 */

namespace ANDS\API\Task\ImportSubTask;

use ANDS\DataSource;

class FinishImport extends ImportSubTask
{

    public function run_task()
    {
        
        $this->parent()->getCI()->load->model('registry/data_source/data_sources', 'ds');
        $dataSource = $this->parent()->getCI()->ds->getByID($this->parent()->dataSourceID);
        if (!$dataSource) {
            $this->stoppedWithError("Data Source ".$this->parent()->dataSourceID." Not Found");
            return;
        }

        $advancedHarvestMode = $dataSource->advanced_harvest_mode;

        if($advancedHarvestMode == 'INCREMENTAL')
        {
            date_default_timezone_set('UTC');
            $dataSource->setAttribute("last_harvest_run_date",date("Y-m-d\TH:i:s\Z", time()));
            date_default_timezone_set('Australia/Canberra');
            $dataSource->setNextHarvestRun($this->parent()->harvestID);
        }
        else
        {
            $dataSource->setAttribute("last_harvest_run_date",'');
            $dataSource->updateHarvestStatus($this->parent()->harvestID, 'COMPLETED');
        }
        
        $this->handleAdvancedHarvest();
        
        return $this;
    }

    public function handleAdvancedHarvest()
    {
        // INCREMENTAL -> reschedule        
    }
    

}