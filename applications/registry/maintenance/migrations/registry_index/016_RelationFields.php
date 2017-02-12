<?php
/**
 * Class:  RelationFields
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS;


/**
 * Class RelationFields
 *
 * @package ANDS
 */
class RelationFields extends GenericSolrMigration
{
    /**
     * RelationGrants constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setDynamicFields([
            ['name' => 'relationType_*', 'type' => 'string', 'stored' => 'true', 'indexed' => true, 'multiValued' => true]
        ]);
    }
}