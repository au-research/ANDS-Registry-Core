<?php


namespace ANDS\Test;

use ANDS\Registry\Connections as Connections;
use ANDS\Registry\Connections\Relation as Relation;
use ANDS\Registry\Connections\CIActiveRecordConnectionsRepository as Repository;

class TestMergingConnectionsRelations extends UnitTest
{

    public function test_merging_multiple_relation_type()
    {
        $conn = new Connections(new Repository($this->ci->db));
        $links = $conn
            ->setFlag(['from_key', 'to_key', 'relation_type'])
            ->setFilter('from_key', 'eAtlas/a289dc20-85b9-11dc-8e98-00008a07204e')
            ->setFilter('to_key', 'eAtlas/AustralianInstituteofMarineScience(AIMS)')
            ->get();
        $this->assertEquals(1, sizeof($links));
        $relation = array_values($links)[0];
        $this->assertEquals(3, sizeof($relation->getProperty('relation_type')));
    }

    public function setUp()
    {
        require_once(REGISTRY_APP_PATH.'connections/Connections.php');
        require_once(REGISTRY_APP_PATH.'connections/Relation.php');
        require_once(REGISTRY_APP_PATH.'connections/CIActiveRecordConnectionsRepository.php');
    }
}
