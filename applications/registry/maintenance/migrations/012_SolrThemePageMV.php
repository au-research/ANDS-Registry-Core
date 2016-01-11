<?php
/**
 * Class:  SolrThemePageMV
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS;


class SolrThemePageMV extends GenericSolrMigration
{
    /**
     * @Override
     * @return mixed
     */
    function up()
    {
        $this->ci->load->library('solr');

        return $this->ci->solr->schema([
            'replace-field' => [
                ['name' => 'theme_page', 'type' => 'string', 'stored' => 'true', 'indexed' => true, 'multiValued'=>true],
            ]
        ]);
    }

    /**
     * @Override
     * @return mixed
     */
    function down()
    {
        $this->ci->load->library('solr');
        return $this->ci->solr->schema([
            'replace-field' => [
                ['name' => 'theme_page', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
            ]
        ]);
    }
}