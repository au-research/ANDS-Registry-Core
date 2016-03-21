<?php
/**
 * Class:  Subjects
 * Enhancements to subjects, for Release 19
 */

namespace ANDS;

class Subjects extends GenericSolrMigration
{

    /**
     * Subjects constructor.
     * Specify the new fields for subjects.
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci->solr->setCore('vocabs');
        $this->setFields([
            ['name' => 'subject_types',
             'type' => 'string', 'stored' => true, 'indexed' => true,
             'multiValued' => true],
            ['name' => 'subject_labels',
             'type' => 'string', 'stored' => true, 'indexed' => true,
             'multiValued' => true],
            ['name' => 'subject_notations',
             'type' => 'string', 'stored' => true, 'indexed' => true,
             'multiValued' => true],
            ['name' => 'subject_uris',
             'type' => 'string', 'stored' => true, 'indexed' => true,
             'multiValued' => true],
        ]);
        $this->setCopyFields([
            ['source' => 'subject_labels', 'dest' => ['subject_search']],
            ['source' => 'subject_notations', 'dest' => ['subject_search']],
        ]);
    }

    /**
     * Remove "legacy" subjects field.
     *
     * @return array
     */
    public function up()
    {
        $this->setCore('vocabs');
        $result = array();
        $result[] = $this->ci->solr->schema([
            'delete-copy-field' => [
                'source' => 'subjects', 'dest' => 'subject_search'
            ],
            'delete-field' => [
                'name' => 'subjects'
            ]
        ]);
        $result[] = parent::up();
        return $result;
    }

    /**
     * Restore "legacy" subjects field.
     *
     * @return array
     */
    public function down(){
        $this->setCore('vocabs');
        $result = array();
        $result[] = $this->ci->solr->schema([
            'add-field' => [
                'name' => 'subjects',
                'type' => 'string', 'stored' => true, 'indexed' => true,
                'multiValued' => true
            ],
            'add-copy-field' => [
                'source' => 'subjects', 'dest' => 'subject_search'
            ]
        ]);
        $result[] = parent::down();
        return $result;
    }
}
