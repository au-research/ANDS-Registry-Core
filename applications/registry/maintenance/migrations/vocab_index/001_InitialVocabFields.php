<?php
/**
 * Class:  InitialVocabFields
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS;


class InitialVocabFields extends GenericSolrMigration
{
    function __construct()
    {
        parent::__construct();
        $this->setCore('vocabs');
        $this->setFields([

            //single values
            ['name' => "slug", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "title", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "title_sort", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "description", 'type' => 'text_en_splitting', 'stored' => true, 'indexed' => true],
            ['name' => "licence", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "pool_party_id", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "owner", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "acronym", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "status", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "sissvoc_end_point", 'type' => 'string', 'stored' => true, 'indexed' => true],
            ['name' => "widgetable", 'type' => 'boolean', 'stored' => true, 'indexed' => true],

            //multiValued
            ['name' => "subjects", 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => "top_concept", 'type' => 'text_en_splitting', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => "language", 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => "concept", 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => "publisher", 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => "access", 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => "format", 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => true],

            ['name'=> "concept_search", 'type' => 'text_en_splitting', 'stored' => false, 'indexed' => true, 'multiValued' => true],
            ['name'=> "title_search", 'type' => 'text_en_splitting', 'stored' => false, 'indexed' => true, 'multiValued' => true],
            ['name'=> "subject_search", 'type' => 'text_en_splitting', 'stored' => false, 'indexed' => true, 'multiValued' => true],
            ['name'=> "publisher_search", 'type' => 'text_en_splitting', 'stored' => false, 'indexed' => true, 'multiValued' => true],
            ['name'=> "fulltext", 'type' => 'text_en_splitting', 'stored' => false, 'indexed' => true, 'multiValued' => true],

        ]);
        $this->setCopyFields([
            ['source' => '*', 'dest' => 'fulltext'],
            ['source' => 'title', 'dest' => ['title_search', 'title_sort']],
            ['source' => 'subjects', 'dest' => ['subject_search']],
            ['source' => 'concept', 'dest' => ['concept_search']],
            ['source' => 'top_concept', 'dest' => ['subject_search']]
        ]);
    }

    public function up(){
        $this->ci->solr->setCore($this->getCore());
        $this->ci->solr->schema([
            'delete-dynamic-field' => [
                ['name' => '*_point']
            ]
        ]);
        return parent::up();
    }
}