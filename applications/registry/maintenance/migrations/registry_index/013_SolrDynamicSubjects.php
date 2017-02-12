<?php
/**
 * Class:  SolrDynamicSubjects
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS;


class SolrDynamicSubjects extends GenericSolrMigration
{

    /**
     * SolrDynamicSubjects constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setDynamicFields([
            ['name' => 'tsubject_*', 'type' => 'string', 'stored' => 'true', 'indexed' => true, 'multiValued' => true]
        ]);
    }
}