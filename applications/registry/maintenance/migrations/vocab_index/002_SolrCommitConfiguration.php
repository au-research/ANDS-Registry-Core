<?php
/**
 * Class:  SolrCommitConfiguration
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS;


class SolrCommitConfiguration extends GenericSolrMigration
{
    public function up()
    {
        $this->setCore('vocabs');
        return $this->ci->solr->config([
            'set-property' => [
                'updateHandler.autoSoftCommit.maxTime' => "10000"
            ]
        ]);
    }

    public function down(){
        $this->setCore('vocabs');
        return $this->ci->solr->config([
            'set-property' => [
                'updateHandler.autoSoftCommit.maxTime' => "-1"
            ]
        ]);
    }
}