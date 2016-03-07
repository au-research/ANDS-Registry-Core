<?php
/**
 * Class:  ToFunder
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS;


class ToFunder extends GenericSolrMigration
{

    /**
     * ToFunder constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setCore('relations');
        $this->setFields([
            ['name' => "to_funder", 'type' => 'string', 'stored' => true, 'indexed' => true, 'multiValued' => false]
        ]);
    }
}