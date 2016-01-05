<?php

namespace ANDS;


class SolrTitles extends GenericSolrMigration
{
    function __construct()
    {
        parent::__construct();
        $this->setFields(
            [
                ['name' => 'title', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
                ['name' => 'title_search', 'type' => 'text_en_splitting', 'stored' => 'true', 'indexed' => true],
                ['name' => 'display_title', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
                ['name' => 'list_title', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
                ['name' => 'list_title_sort', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
                ['name' => 'simplified_title', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
                [
                    'name' => 'simplified_title_search',
                    'type' => 'text_en_splitting',
                    'stored' => 'true',
                    'indexed' => true
                ],
                ['name' => 'alt_list_title', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
                ['name' => 'alt_display_title', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
                ['name' => 'alt_title_search', 'type' => 'text_en_splitting', 'stored' => 'true', 'indexed' => true],
            ]
        );
    }
}