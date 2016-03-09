<?php
/**
 * Class:  extractRelationships
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Task\ImportSubTask;


/**
 * Class ExtractRelationships
 * An ImportSubTask that extracts the direct relationships
 * and put them into the database
 *
 * @package ANDS\API\Task\ImportSubTask
 */
class ExtractRelationships extends ImportSubTask
{

    public function run_task()
    {
        $this->log('Extract Relationships for ' . join(',', $this->getAffectedRecords()));
    }

    /**
     * extractRelationships constructor.
     */
    public function __construct()
    {
        $this->setName('ExtractRelationships');
        $this->setRequireIDs(true);
    }


}