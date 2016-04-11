<?php

namespace ANDS;


class SolrReplaceId extends GenericSolrMigration
{

    /**
     * @Override
     * @return mixed
     */
    function up()
    {
        $this->ci->load->library('solr');

        return $this->ci->solr->schema(
            [
                'replace-field' => [
                    ['name' => 'id', 'type' => 'string', 'stored' => 'true', 'indexed' => true, 'required' => true]
                ]
            ]
        );
    }

    /**
     * @Override
     * @return mixed
     */
    function down()
    {
        $this->ci->load->library('solr');
        return $this->ci->solr->schema(
            [
                'replace-field' => [
                    ['name' => 'id', 'type' => 'int', 'stored' => 'true', 'indexed' => true, 'required' => true]
                ]
            ]
        );
    }
}