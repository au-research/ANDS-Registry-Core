<?php
namespace ANDS;


class SolrTags extends GenericSolrMigration
{

    function __construct()
    {
        parent::__construct();
        $this->setFields([
            ['name' => 'tag', 'type' => 'string', 'stored' => 'true', 'indexed' => true, 'multiValued' => true],
            ['name' => 'tag_type', 'type' => 'string', 'stored' => 'true', 'indexed' => true, 'multiValued' => true],
            ['name' => 'tag_sort', 'type' => 'string', 'stored' => 'true', 'indexed' => true, 'multiValued' => true],
            [
                'name' => 'tag_search',
                'type' => 'text_en_splitting',
                'stored' => 'true',
                'indexed' => true,
                'multiValued' => true
            ]
        ]);
    }
}