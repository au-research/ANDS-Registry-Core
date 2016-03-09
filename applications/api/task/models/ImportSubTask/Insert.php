<?php
namespace ANDS\API\Task\ImportSubTask;

/**
 * Class:  Insert
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Insert extends ImportSubTask
{

    public function run_task()
    {
        $this->log('Ran Insert');
        $this->setAffectedRecords([1, 2, 3]);
    }

    /**
     * Insert constructor.
     */
    public function __construct()
    {
        $this->setName('Insert');
    }
}