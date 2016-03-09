<?php
/**
 * Class:  ProcessRelationships
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Task\ImportSubTask;


/**
 * Class ProcessRelationships
 * Process the available relationships for all affected records
 * Requires extractIdentifiers and extractRelationships to be finished
 *
 * @package ANDS\API\Task\ImportSubTask
 */
class ProcessRelationships extends ImportSubTask
{
    public function run_task()
    {
        $this->log('Process Relationships for ' . join(',', $this->getAffectedRecords()));
    }

    /**
     * ProcessRelationships constructor.
     */
    public function __construct()
    {
        $this->setName('ProcessRelationships');
        $this->setRequireIDs(true);
    }
}