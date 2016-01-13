<?php
/**
 * Class:  SolrDynamicSubjects
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS;


class SolrDynamicSubjects extends GenericSolrMigration
{
    public function up(){
        $this->ci->load->library('solr');
        $this->ci->solr->setCore('portal');
        return $this->ci->solr->schema([
            'add-dynamic-field' => [
                ['name' => 'tsubject_*', 'type' => 'string', 'stored' => 'true', 'indexed' => true, 'multiValued' => true]
            ]
        ]);
    }

    public function down(){
        $this->ci->load->library('solr');
        $this->ci->solr->setCore('portal');
        return $this->ci->solr->schema([
            'delete-dynamic-field' => [
                ['name' => 'tsubject_*']
            ]
        ]);
    }
}