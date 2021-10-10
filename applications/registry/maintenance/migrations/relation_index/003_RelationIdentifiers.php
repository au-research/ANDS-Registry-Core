<?php
/**
 * Class:  RelationIdentifiers
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS;


class RelationIdentifiers extends GenericSolrMigration
{
    /**
     * InitialRelationsFields constructor.
     */
    function __construct()
    {
        parent::__construct();
        $this->setCore('relations');
        $this->setFields([
            ['name' => "relation_identifier_identifier", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "relation_identifier_type", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "relation_identifier_id", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "relation_identifier_url", 'type' => 'string', 'stored' => true, 'indexed' => true]
        ]);
    }
}