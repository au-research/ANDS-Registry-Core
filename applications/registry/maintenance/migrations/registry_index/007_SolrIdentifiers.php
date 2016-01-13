<?php
/**
 * Class: SolrIdentifiers
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * Date: 5/01/2016
 * Time: 11:46 AM
 */

namespace ANDS;


class SolrIdentifiers extends GenericSolrMigration
{

    /**
     * SolrIdentifiers constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setFields([
            [
                'name' => 'identifier_value',
                'type' => 'string',
                'stored' => 'true',
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'identifier_type',
                'type' => 'string',
                'stored' => 'true',
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'identifier_value_search',
                'type' => 'text_en_splitting',
                'stored' => 'false',
                'indexed' => true,
                'multiValued' => true
            ],
        ]);

        $this->setCopyFields([
            ['source' => 'identifier_value', 'dest' => ['identifier_value_search']]
        ]);
    }
}