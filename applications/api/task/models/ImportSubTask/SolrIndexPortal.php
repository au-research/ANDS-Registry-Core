<?php
/**
 * Class:  SolrIndexPortal
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Task\ImportSubTask;


/**
 * Class SolrIndexPortal
 * Index the affected records into the portal core
 * Available for searching in the portal
 *
 * @package ANDS\API\Task\ImportSubTask
 */
class SolrIndexPortal extends ImportSubTask
{
    public function run_task()
    {
        $this->log('Indexing Portal for ' . join(',', $this->getAffectedRecords()));
    }

    /**
     * SolrIndexPortal constructor.
     */
    public function __construct()
    {
        $this->setName('SolrIndexPortal');
        $this->setRequireIDs(true);
    }
}