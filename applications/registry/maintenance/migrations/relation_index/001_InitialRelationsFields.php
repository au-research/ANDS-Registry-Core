<?php
/**
 * Class:  InitialRelationsFields
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS;


/**
 * Class InitialRelationsFields
 *
 * @package ANDS
 */
class InitialRelationsFields extends GenericSolrMigration
{
    /**
     * InitialRelationsFields constructor.
     */
    function __construct()
    {
        parent::__construct();
        $this->setCore('relations');
        $this->setFields([

            // from
            ['name' => "from_id", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "from_key", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "from_status", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "from_title", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "from_class", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "from_type", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "from_slug", 'type' => 'string', 'stored' => true, 'indexed' => true],

            // to
            ['name' => "to_id", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "to_key", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "to_class", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "to_type", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "to_title", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "to_slug", 'type' => 'string', 'stored' => true, 'indexed' => true],

            // relation
            ['name' => "relation", 'type' => 'string', 'stored' => true, 'indexed' => true, "multiValued" => true],
            ['name' => "relation_description", 'type' => 'text_en_splitting', 'stored' => true, 'indexed' => true, "multiValued" => true],
            ['name' => "relation_url", 'type' => 'string', 'stored' => true, 'indexed' => true, "multiValued" => true],
            ['name' => "relation_origin", 'type' => 'string', 'stored' => true, 'indexed' => true, "multiValued" => true],

        ]);
    }
}