<?php
/**
 * Class:  SolrIndexRelations
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Task\ImportSubTask;


/**
 * Class SolrIndexRelations
 * Index the affected records to the relations core
 * Make the retrieval of relationships faster and more efficient
 *
 * @package ANDS\API\Task\ImportSubTask
 */
class SolrIndexRelations extends ImportSubTask
{
    public function run_task()
    {
        $this->log('Index Relationships for ' . join(',', $this->getAffectedRecords()));
    }

    /**
     * SolrIndexRelations constructor.
     */
    public function __construct()
    {
        $this->setName('SolrIndexRelations');
        $this->setRequireIDs(true);
    }
}