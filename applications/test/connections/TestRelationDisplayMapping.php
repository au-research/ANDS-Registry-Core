<?php

namespace ANDS\Test;
use ANDS\Registry\Relation;

/**
 * Class TestRelationDisplayMapping
 * @package ANDS\Test
 */
class TestRelationDisplayMapping extends UnitTest
{

    /**
     * @name test_general_mapping
     */
    public function test_it_should_map()
    {
        $relation = new Relation();

        $relation->setProperty('to_title',
            '(AUTestingRecords)Outwards Letter Books, Primary Schools');
        $relation->setProperty('to_id', '716083');
        $relation->setProperty('to_class', 'collection');
        $relation->setProperty('to_slug',
            "autestingrecordsoutwards-letter-books-primary-schools");
        $relation->setProperty('relation_type', "hasPart");

        $mapping = [
            'to_title' => 'title',
            'to_id' => 'registry_object_id',
            'to_slug' => 'slug',
            'to_class' => 'class'
        ];

        $display = $relation->format($mapping);
        $this->assertTrue($display['title'] == '(AUTestingRecords)Outwards Letter Books, Primary Schools');
        $this->assertTrue($display['class'] == 'collection');
        $this->assertTrue($display['registry_object_id'] == '716083');
        $this->assertTrue($display['slug'] == 'autestingrecordsoutwards-letter-books-primary-schools');
        $this->assertTrue($display['relation_type'] == 'hasPart');
    }

    public function test_it_should_return_the_same_without_mapping()
    {
        $relation = new Relation();

        $relation->setProperty('to_title',
            '(AUTestingRecords)Outwards Letter Books, Primary Schools');
        $relation->setProperty('to_id', '716083');
        $relation->setProperty('to_class', 'collection');
        $relation->setProperty('to_slug',
            "autestingrecordsoutwards-letter-books-primary-schools");
        $relation->setProperty('relation_type', "hasPart");

        $display = $relation->format();
        $this->assertTrue(!array_key_exists('title', $display));
        $this->assertTrue($display['relation_type'] == 'hasPart');
        $this->assertTrue($display['to_title'] == '(AUTestingRecords)Outwards Letter Books, Primary Schools');
    }

    public function test_it_should_flip()
    {
        $relation = new Relation();
        $relation->setProperty('to_id', '716083');
        $relation->setProperty('to_class', 'collection');
        $relation->setProperty('to_slug',
            "autestingrecordsoutwards-letter-books-primary-schools");

        $relation->setProperty('from_id', '7160855');
        $relation->setProperty('from_class', 'party');

        $relation = $relation->flip();

        $this->assertTrue($relation->getProperty('from_id') == '716083');
        $this->assertTrue($relation->getProperty('from_class') == 'collection');
    }

}