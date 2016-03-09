<?php
/**
 * Class:  ProcessQuality
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Task\ImportSubTask;


/**
 * Class ProcessQuality
 * Process Quality Level and provide quality_html for
 * all affected records
 *
 * @package ANDS\API\Task\ImportSubTask
 */
class ProcessQuality extends ImportSubTask
{

    public function run_task()
    {
        $this->log('Process Quality Metadata for ' . join(',', $this->getAffectedRecords()));
    }

    /**
     * ProcessQuality constructor.
     */
    public function __construct()
    {
        $this->setName('ProcessQuality');
        $this->setRequireIDs(true);
    }
}