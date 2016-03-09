<?php
/**
 * Class:  extractIdentifiers
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Task\ImportSubTask;


/**
 * Class ExtractIdentifiers
 * An ImportSubTask that will extract the identifiers from the affected records
 * and put them into the database
 *
 * @package ANDS\API\Task\ImportSubTask
 */
class ExtractIdentifiers extends ImportSubTask
{

    public function run_task()
    {
        $this->log('Ran extract Identifiers');
        $this->log('Extract Identifiers for ' . join(',', $this->getAffectedRecords()));
    }

    /**
     * Insert constructor.
     */
    public function __construct()
    {
        $this->setName('ExtractIdentifiers');
        $this->setRequireIDs(true);
    }
}