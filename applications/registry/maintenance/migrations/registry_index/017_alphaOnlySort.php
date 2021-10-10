<?php
/**
 * Class:  alphaOnlySort
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS;


/**
 * Class alphaOnlySort
 * SOLR migration to enable sorting alphabetically
 * even through multiValued field
 *
 * @package ANDS
 */
class alphaOnlySort extends GenericSolrMigration
{

    /**
     * alphaOnlySort constructor.
     * Setting the previously set _sort fields to the new alphaOnlySort field type
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci->solr->setCore('portal');
        $this->setFields([
            ['name' => 'list_title_sort', 'type' => 'alphaOnlySort', 'stored' => true, 'indexed' => true],
            ['name' => 'group_sort', 'type' => 'alphaOnlySort', 'stored' => true, 'indexed' => true],
            ['name' => 'subject_value_resolved_sort', 'type' => 'alphaOnlySort', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => 'tag_sort', 'type' => 'alphaOnlySort', 'stored' => true, 'indexed' => true, 'multiValued' => true],
            ['name' => 'type_sort', 'type' => 'alphaOnlySort', 'stored' => true, 'indexed' => true],
        ]);
    }

    /**
     * Adding the field type of alphaOnlySort with the required analyzers
     * that can deal with multiValued fields
     *
     * @return array
     */
    public function up()
    {
        $result = array();
        $result[] = $this->ci->solr->schema([
            'add-field-type' => [
                'name' => 'alphaOnlySort',
                'class' => 'solr.TextField',
                'sortMissingLast' => "true",
                "omitNorms" => "true",
                "analyzer" => [
                    "tokenizer" =>
                        ["class" => "solr.KeywordTokenizerFactory"]
                    ,
                    "filters" => [
                        ["class" => "solr.LowerCaseFilterFactory"],
                        ["class" => "solr.TrimFilterFactory"],
                        [
                            "class" => "solr.PatternReplaceFilterFactory",
                            "pattern" => "([^a-z])",
                            "replacement" => "",
                            "replace" => "all"
                        ]
                    ]
                ]
            ]
        ]);
        $result[] = parent::up();
        return $result;
    }

    /**
     * Removing the alphaOnlySort field in teardown
     *
     * @return array
     */
    public function down()
    {
        $result = array();
        $result[] = parent::down();
        $result[] = $this->ci->solr->schema([
            'delete-field-type' => [
                'name' => 'alphaOnlySort'
            ]
        ]);
        return $result;
    }
}