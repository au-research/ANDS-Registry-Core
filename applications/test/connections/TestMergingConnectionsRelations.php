<?php


namespace ANDS\Test;

use ANDS\Registry\PreMyceliumConnections;
use ANDS\Registry\Relation;
use ANDS\Repository\EloquentConnectionsRepository as Repository;

/**
 * Class TestMergingConnectionsRelations
 * @package ANDS\Test
 */
class TestMergingConnectionsRelations extends UnitTest
{
    public function test_merging_multiple_relation_type()
    {
        $conn = new PreMyceliumConnections(new Repository);
        $links = $conn
            ->setFlag(['from_key', 'to_key', 'relation_type'])
            ->setFilter('from_key', 'eAtlas/a289dc20-85b9-11dc-8e98-00008a07204e')
            ->setFilter('to_key', 'eAtlas/AustralianInstituteofMarineScience(AIMS)')
            ->get();
        $this->assertEquals(1, sizeof($links));
        $relation = array_values($links)[0];
        $this->assertEquals(3, sizeof($relation->getProperty('relation_type')));
    }
}
