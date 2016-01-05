<?php
/**
 * Class: SolrActivitySpecifics
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * Date: 5/01/2016
 * Time: 11:47 AM
 */

namespace ANDS;


class SolrActivitySpecifics extends GenericSolrMigration
{

    /**
     * SolrActivitySpecifics constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setFields([
            ['name' => 'activity_status', 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => 'funding_amount', 'type' => 'float', 'stored' => true, 'indexed' => true],
            ['name' => 'funding_scheme', 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => 'funding_scheme_search', 'type' => 'text_en_splitting', 'stored' => true, 'indexed' => true],
            ['name' => 'researcher', 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => 'researchers_search', 'type' => 'text_en_splitting', 'stored' => true, 'indexed' => true],
            ['name' => 'administering_institution', 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => 'administering_institution_search', 'type' => 'text_en_splitting', 'stored' => true, 'indexed' => true],
            ['name' => 'funder', 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => 'funders_search', 'type' => 'text_en_splitting', 'stored' => true, 'indexed' => true],
        ]);

        $this->setCopyFields([
            ['source' => 'administering_institution', 'dest' => ['administering_institution_search']],
            ['source' => 'researcher', 'dest' => ['researchers_search']],
            ['source' => 'funder', 'dest' => ['funders_search']]
        ]);
    }
}