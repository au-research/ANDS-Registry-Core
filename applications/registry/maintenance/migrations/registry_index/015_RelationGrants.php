<?php
/**
 * Class:  RelationGrants
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS;


class RelationGrants extends GenericSolrMigration
{
    /**
     * RelationGrants constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setDynamicFields([
            ['name' => 'relation_grants_*', 'type' => 'string', 'stored' => 'true', 'indexed' => true, 'multiValued' => true]
        ]);
    }
}