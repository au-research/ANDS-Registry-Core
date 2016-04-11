<?php
/**
 * Class:  SolrUpdateConfiguration
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS;


class SolrUpdateConfiguration extends GenericSolrMigration
{
    public function up()
    {
        $this->setCore('portal');
        return $this->ci->solr->config([
             'set-property' => [
                 'updateHandler.autoSoftCommit.maxTime' => "10000"
             ]
        ]);
    }

    public function down(){
        $this->setCore('portal');
        return $this->ci->solr->config([
            'set-property' => [
                'updateHandler.autoSoftCommit.maxTime' => "-1"
            ]
        ]);
    }
}