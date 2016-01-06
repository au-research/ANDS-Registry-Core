<?php

namespace ANDS;


class SolrDescriptions extends GenericSolrMigration
{

    /**
     * SolrDescriptions constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setFields([
            ['name' => 'description', 'type' => 'text_en_splitting', 'stored' => 'true', 'indexed' => true],
            ['name' => 'list_description', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
            [
                'name' => 'description_value',
                'type' => 'text_en_splitting',
                'stored' => 'true',
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'description_type',
                'type' => 'string',
                'stored' => 'true',
                'indexed' => true,
                'multiValued' => true
            ],
        ]);
    }
}