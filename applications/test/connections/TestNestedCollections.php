<?php


namespace ANDS\Test;

use ANDS\Registry\Connections as Connections;
use ANDS\Registry\Connections\Relation as Relation;
use ANDS\Registry\Connections\CIActiveRecordConnectionsRepository as Repository;

class TestNestedCollections extends UnitTest
{

    public function test_get_nested_collections()
    {
        $conn = new Connections(new Repository($this->ci->db));
        $links = $conn->getNestedCollections('UrbanWater:Collection');
        $this->assertTrue(is_array($links));
    }

    public function setUp()
    {
        require_once(REGISTRY_APP_PATH.'connections/Connections.php');
        require_once(REGISTRY_APP_PATH.'connections/Relation.php');
        require_once(REGISTRY_APP_PATH.'connections/CIActiveRecordConnectionsRepository.php');
    }
}
