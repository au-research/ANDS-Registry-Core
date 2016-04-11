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
            ['name' => 'funding_scheme_search', 'type' => 'text_en_splitting', 'stored' => false, 'indexed' => true],
            ['name' => 'researchers', 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => 'researchers_search', 'type' => 'text_en_splitting', 'stored' => false, 'indexed' => true, 'multiValued' => true],
            ['name' => 'administering_institution', 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => 'administering_institution_search', 'type' => 'text_en_splitting', 'stored' => false, 'indexed' => true, 'multiValued' => true],
            ['name' => 'funders', 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => 'funders_search', 'type' => 'text_en_splitting', 'stored' => false, 'indexed' => true, 'multiValued' => true],
            ['name' => 'principal_investigator', 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => 'principal_investigator_search', 'type' => 'text_en_splitting', 'stored' => false, 'indexed' => true, 'multiValued' => true],
        ]);

        $this->setCopyFields([
            ['source' => 'administering_institution', 'dest' => ['administering_institution_search']],
            ['source' => 'researchers', 'dest' => ['researchers_search']],
            ['source' => 'funders', 'dest' => ['funders_search']],
            ['source' => 'principal_investigator', 'dest' => ['principal_investigator_search']]
        ]);
    }
}