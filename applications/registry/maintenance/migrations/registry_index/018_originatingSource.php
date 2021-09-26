<?php
/**
 * Class:  originatingSource
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS;


class originatingSource extends GenericSolrMigration
{

    /**
     * originatingSource constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setFields([
            ['name' => 'originating_source', 'type' => 'string', 'stored' => 'true', 'indexed' => true],
        ]);
    }
}