<?php

namespace ANDS;


class SolrTitles extends GenericSolrMigration
{
    function __construct()
    {
        parent::__construct();
        $this->setFields([
            ['name' => 'title', 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => 'title_search', 'type' => 'text_en_splitting', 'stored' => false, 'indexed' => true, 'multiValued'=>true, 'termVectors' => true],
            ['name' => 'display_title', 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => 'list_title', 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => 'list_title_sort', 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => 'simplified_title', 'type' => 'string', 'stored' => true, 'indexed' => true],
            [
                'name' => 'simplified_title_search',
                'type' => 'text_en_splitting',
                'stored' => false,
                'indexed' => true
            ],
            ['name' => 'alt_list_title', 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => 'alt_display_title', 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => 'alt_title_search', 'type' => 'text_en_splitting', 'stored' => false, 'indexed' => true, 'multiValued' => true],
        ]);
        $this->setCopyFields([
            ['source' => 'display_title', 'dest' => ['title', 'title_search']],
            ['source' => 'list_title', 'dest' => ['title_search', 'list_title_sort']],
            ['source' => 'alt_list_title', 'dest' => ['title_search']],
            ['source' => 'alt_display_title', 'dest' => ['title_search']]
        ]);
    }
}