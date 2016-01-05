<?php
/**
 * Class: SolrSubjects
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * Date: 5/01/2016
 * Time: 11:53 AM
 */

namespace ANDS;


class SolrSubjects extends GenericSolrMigration
{

    /**
     * SolrSubjects constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setFields([
            [
                'name' => 'subject_value_resolved',
                'type' => 'text_en_splitting',
                'stored' => 'true',
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'subject_value_resolved_search',
                'type' => 'text_en_splitting',
                'stored' => 'true',
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 's_subject_value_resolved',
                'type' => 'text_en_splitting',
                'stored' => 'true',
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'subject_value_resolved_sort',
                'type' => 'text_en_splitting',
                'stored' => 'true',
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'subject_value_unresolved',
                'type' => 'text_en_splitting',
                'stored' => 'true',
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'subject_type',
                'type' => 'text_en_splitting',
                'stored' => 'true',
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'subject_vocab_uri',
                'type' => 'text_en_splitting',
                'stored' => 'true',
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'subject_anzsrcfor',
                'type' => 'text_en_splitting',
                'stored' => 'true',
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'subject_anzsrcseo',
                'type' => 'text_en_splitting',
                'stored' => 'true',
                'indexed' => true,
                'multiValued' => true
            ],
        ]);
    }
}