<?php
/**
 * Class:  highlightFields
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS;


class institutions extends GenericSolrMigration
{

    /**
     * highlightFields constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setFields([
            [
                'name' => 'institutions',
                'type' => 'string',
                'stored' => true,
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'institutions_search',
                'type' => 'text_en_splitting',
                'stored' => true,
                'indexed' => true,
                'multiValued' => true
            ]
        ]);

        $this->setCopyFields([
            ['source' => 'institutions', 'dest' => ['institutions_search']]
        ]);
    }
}