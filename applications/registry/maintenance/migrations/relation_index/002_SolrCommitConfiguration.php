<?php
/**
 * Class:  SolrCommitConfiguration
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS;


/**
 * Class SolrCommitConfiguration
 *
 * @package ANDS
 */
class SolrCommitConfiguration extends GenericSolrMigration
{
    /**
     * @return mixed
     */
    public function up()
    {
        $this->setCore('relations');
        return $this->ci->solr->config([
            'set-property' => [
                'updateHandler.autoSoftCommit.maxTime' => "10000"
            ]
        ]);
    }

    /**
     * @return mixed
     */
    public function down(){
        $this->setCore('relations');
        return $this->ci->solr->config([
            'set-property' => [
                'updateHandler.autoSoftCommit.maxTime' => "-1"
            ]
        ]);
    }
}