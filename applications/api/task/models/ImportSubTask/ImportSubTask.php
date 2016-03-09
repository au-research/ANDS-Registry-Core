<?php
/**
 * Class:  ImportSubTask
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Task\ImportSubTask;

use ANDS\API\Task\Task;

/**
 * Class ImportSubTask
 * A superclass for all ImportSubTask
 *
 * @package ANDS\API\Task\ImportSubTask
 */
class ImportSubTask extends Task
{
    public $name;
    private $requireIDs = false;
    private $affectedRecords = [];

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return boolean
     */
    public function isRequireIDs()
    {
        return $this->requireIDs;
    }

    /**
     * @param boolean $requireIDs
     */
    public function setRequireIDs($requireIDs)
    {
        $this->requireIDs = $requireIDs;
    }

    /**
     * @return array
     */
    public function getAffectedRecords()
    {
        return $this->affectedRecords;
    }

    /**
     * @param array $affectedRecords
     */
    public function setAffectedRecords($affectedRecords)
    {
        $this->affectedRecords = $affectedRecords;
    }


}