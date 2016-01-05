<?php

namespace ANDS;


class SolrAddSingleValueFields extends GenericSolrMigration
{

    function __construct()
    {
        parent::__construct();
        $this->setFields([
            ['name' => 'slug', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
            ['name' => 'key', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
            ['name' => 'class', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
            ['name' => 'status', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
            ['name' => 'logo', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
            ['name' => 'data_source_id', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
            ['name' => 'data_source_key', 'type' => 'string', 'stored' => 'true', 'indexed' => true],

            ['name' => 'contributor_page', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
            ['name' => 'update_timestamp', 'type' => 'date', 'stored' => 'true', 'indexed' => true],
            ['name' => 'record_created_timestamp', 'type' => 'date', 'stored' => 'true', 'indexed' => true],
            ['name' => 'record_modified_timestamp', 'type' => 'date', 'stored' => 'true', 'indexed' => true],

            ['name' => 'quality_level', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
            ['name' => 'tr_cited', 'type' => 'string', 'stored' => 'true', 'indexed' => true],

            ['name' => 'group', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
            ['name' => 'group_sort', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
            ['name' => 'group_search', 'type' => 'text_en_splitting', 'stored' => 'true', 'indexed' => true],

            ['name' => 'type', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
            ['name' => 'type_sort', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
            ['name' => 'type_search', 'type' => 'text_en_splitting', 'stored' => 'true', 'indexed' => true],

            ['name' => 'license_class', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
            ['name' => 'access_rights', 'type' => 'string', 'stored' => 'true', 'indexed' => true],

            ['name' => 'theme_page', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
            ['name' => 'matching_identifier_count', 'type' => 'int', 'stored' => 'true', 'indexed' => true],

            [
                'name' => 'text',
                'type' => 'text_en_splitting',
                'stored' => 'true',
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'fulltext',
                'type' => 'text_en_splitting',
                'stored' => 'true',
                'indexed' => true,
                'multiValued' => true
            ],
        ]);
    }

}