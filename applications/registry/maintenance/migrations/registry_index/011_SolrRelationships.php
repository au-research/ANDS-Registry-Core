<?php
/**
 * Class:  SolrRelationships
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * Date: 5/01/2016
 * Time: 2:37 PM
 */

namespace ANDS;


class SolrRelationships extends GenericSolrMigration
{
    /**
     * SolrRelationships constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setFields([
            ['name' => 'related_collection_id', 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => 'related_collection_title', 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => 'related_collection_search', 'type' => 'text_en_splitting', 'stored' => false, 'indexed' => true, 'multiValued' => true],

            ['name' => 'related_party_one_id', 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => 'related_party_one_title', 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => 'related_party_one_search', 'type' => 'text_en_splitting', 'stored' => false, 'indexed' => true, 'multiValued' => true],

            ['name' => 'related_party_multi_id', 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => 'related_party_multi_title', 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => 'related_party_multi_search', 'type' => 'text_en_splitting', 'stored' => false, 'indexed' => true, 'multiValued' => true],

            ['name' => 'related_activity_id', 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => 'related_activity_title', 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => 'related_activity_search', 'type' => 'text_en_splitting', 'stored' => false, 'indexed' => true, 'multiValued' => true],

            ['name' => 'related_service_id', 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => 'related_service_title', 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => 'related_service_search', 'type' => 'text_en_splitting', 'stored' => false, 'indexed' => true, 'multiValued' => true],
        ]);

        $this->setCopyFields([
            ['source' => 'related_collection_title', 'dest' => ['related_collection_search']],
            ['source' => 'related_party_one_title', 'dest' => ['related_party_one_search']],
            ['source' => 'related_party_multi_title', 'dest' => ['related_party_multi_search']],
            ['source' => 'related_activity_title', 'dest' => ['related_activity_search']],
            ['source' => 'related_service_title', 'dest' => ['related_service_search']]
        ]);
    }
}