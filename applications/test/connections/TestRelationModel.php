<?php


namespace ANDS\Test;

use ANDS\Registry\Connections;
use ANDS\Registry\Relation;

/**
 * Class TestRelationModel
 * @package ANDS\Test
 */
class TestRelationModel extends UnitTest
{
    public function test_mergeWith()
    {
//        $relation = new Relation;
//        $relation->setProperty('relation_type', 'isManagedBy');
//        $relation->setProperty('from_key', 'from_KEY');
//        $row = [
//            'from_key' => 'from_KEY',
//            'relation_type' => 'isPartOf'
//        ];
//        $relation->mergeWith($row);
//        $this->assertTrue(is_array($relation->getProperty('relation_type')));
//        $this->assertTrue(count($relation->getProperty('relation_type')) == 2);
    }

    public function test_mergeWith_array()
    {
        $relation = new Relation;
        $relation->setProperty('from_key', 'from_KEY');
        $relation->setProperty('relation_type', 'hasAssociationWith');
        $relation->setProperty('relation_type', 'pointOfContact');

        $row = [
            'from_key' => 'from_KEY',
            'relation_type' => 'isPartOf'
        ];

        $relation->mergeWith($row);
        $this->assertTrue(is_array($relation->getProperty('relation_type')));
        $this->assertTrue(count($relation->getProperty('relation_type')) == 3);
    }

    public function test_hasProperty() {
        $relation = new Relation;
        $this->assertFalse($relation->hasProperty('relation_type'));
        $relation->setProperty('relation_type', 'something');
        $this->assertTrue($relation->hasProperty('relation_type'));
        $relation->setProperty('relation_type', 'something');
        $this->assertEquals("something", $relation->getProperty('relation_type'));
        $relation->setProperty('relation_type', 'something else');
        $this->assertEquals(["something", "something else"], $relation->getProperty('relation_type'));
        $relation->setProperty('relation_type', 'something');
        $this->assertEquals(["something", "something else"], $relation->getProperty('relation_type'));
    }
}
