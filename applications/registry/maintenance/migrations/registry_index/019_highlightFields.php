<?php
/**
 * Class:  highlightFields
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS;


class highlightFields extends GenericSolrMigration
{

    /**
     * highlightFields constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setFields([
            [
                'name' => 'identifier_value_search',
                'type' => 'text_en_splitting',
                'stored' => true,
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'related_party_one_search',
                'type' => 'text_en_splitting',
                'stored' => true,
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'related_collection_search',
                'type' => 'text_en_splitting',
                'stored' => true,
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'related_party_multi_search',
                'type' => 'text_en_splitting',
                'stored' => true,
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'related_activity_search',
                'type' => 'text_en_splitting',
                'stored' => true,
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'related_service_search',
                'type' => 'text_en_splitting',
                'stored' => true,
                'indexed' => true,
                'multiValued' => true
            ],
            ['name' => 'group_search', 'type' => 'text_en_splitting', 'stored' => true, 'indexed' => true],
            [
                'name' => 'related_info_search',
                'type' => 'text_en_splitting',
                'stored' => true,
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'subject_value_resolved_search',
                'type' => 'text_en_splitting',
                'stored' => true,
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'citation_info_search',
                'type' => 'text_en_splitting',
                'stored' => true,
                'indexed' => true,
                'multiValued' => true
            ],
        ]);
    }
}