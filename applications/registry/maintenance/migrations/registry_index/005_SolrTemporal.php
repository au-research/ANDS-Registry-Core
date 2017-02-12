<?php

namespace ANDS;


class SolrTemporal extends GenericSolrMigration
{
    public function __construct()
    {
        parent::__construct();
        $this->setFields(
            [
                ['name' => 'date_to', 'type' => 'tdate', 'stored' => 'true', 'indexed' => true, 'multiValued' => true],
                ['name' => 'date_from', 'type' => 'tdate', 'stored' => 'true', 'indexed' => true, 'multiValued' => true],
                ['name' => 'earliest_year', 'type' => 'int', 'stored' => 'true', 'indexed' => true],
                ['name' => 'latest_year', 'type' => 'int', 'stored' => 'true', 'indexed' => true]
            ]
        );
    }
}