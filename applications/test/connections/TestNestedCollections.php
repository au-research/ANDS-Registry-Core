<?php


namespace ANDS\Test;

use ANDS\Registry\Connections;
use ANDS\Registry\Providers\NestedConnectionsProvider;
use ANDS\Registry\Relation;
//use ANDS\Repository\CIActiveRecordConnectionsRepository as Repository;
use ANDS\Repository\EloquentConnectionsRepository as Repository;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class TestNestedCollections
 * @package ANDS\Test
 */
class TestNestedCollections extends UnitTest
{

    /** @test **/
    public function test_it_should_sample()
    {
        $conn = new NestedConnectionsProvider(new Repository);

        $record = RegistryObjectsRepository::getRecordByID(124829);
        $links = $conn->getNestedCollectionsFromChild($record->key, 3);

        $links = array_values($links);
        foreach ($links as &$link) {
            $link = $link->format([
                'from_id' => 'registry_object_id',
                'from_title' => 'title',
                'from_class' => 'class',
                'from_slug' => 'slug',
                'children' => 'children'
            ], true);
        }
    }

    /**
     * @test
     * @name Test get nested collection for UrbanWater:Collection
     */
    public function get_nested_collections_multiple_layer()
    {
        $conn = new NestedConnectionsProvider(new Repository);
        $conn->setFlag(['to_title', 'to_class', 'to_slug', 'to_id', 'to_key']);
        $links = $conn->getNestedCollections('UrbanWater:Collection');

        // ensure 1 layer nested collection
        $testObjects = array_filter($links, function($relation) {
            return $relation->getProperty('to_title') == "Australian Water Resources Assessment 2010"
                    && $relation->getProperty('to_slug') == "australian-water-resources-assessment-2010";
        });
        $this->assertTrue(sizeof($testObjects) === 1);

        $testObject = array_shift(array_values($testObjects));
        $this->assertTrue(sizeof($testObject->getProperty('children')) === 13);

        // ensure 2 layer nested collections
        $testObject = array_filter($links, function($relation) {
            return $relation->getProperty('to_title') == "Urban Water Security Research Alliance Collection"
            && $relation->getProperty('to_slug') == "urban-water-security-research-alliance-collection";
        });

        $this->assertTrue(sizeof($testObject) === 1);

        // go through the children and make sure that 1 specific record exists
        $testObject = array_shift(array_values($testObject));
        $this->assertTrue(sizeof($testObject->getProperty('children')) === 41);
        $testObjectSecondLayer = array_filter($testObject->getProperty('children'), function($relation) {
            return $relation->getProperty('to_title') == "Urban Water Security Research Alliance Bioassays and Risk Communication, Subproject 1d : Extension of battery of bioanalytical screening tools to additional modes of toxic action."
            && $relation->getProperty('to_slug') == "urban-water-security-research-alliance-bioassays-and-risk-communication-subproject-1d-extension-of-battery-of-bioanalytical-screening-tools-to-additional-modes-of-toxic-action";
        });
        $this->assertTrue(sizeof($testObjectSecondLayer) === 1);

        $this->assertTrue(is_array($links));
    }

    /**
     * @test
     * @name Test test parent exists from a child
     */
    public function test_it_should_give_me_the_same_as_the_parent()
    {
        $conn = new NestedConnectionsProvider(new Repository);
        $links = $conn->init()->getNestedCollectionsFromChild('AWRA_Murray_Darling_Basin');
        $parentLinks = $conn->init()->getNestedCollections("UrbanWater:Collection");
        $this->assertTrue(sizeof($links) == sizeof($parentLinks));
        $this->assertTrue($links == $parentLinks);
    }

    /**
     * @test
     * @name Test get all parents
     */
    public function test_it_should_give_me_2_parent()
    {
        $conn = new NestedConnectionsProvider(new Repository);
        $conn->setFlag(['from_title', 'from_class', 'from_slug', 'from_id', 'from_key']);
        $links = $conn->getParentNestedCollections('AWRA_Murray_Darling_Basin');
        $this->assertTrue(sizeof($links) === 2);
    }

    public function test_it_should_give_me_the_right_formatting()
    {
        $conn = new NestedConnectionsProvider(new Repository);

        $connectionTree = $conn
            ->init()
            ->getNestedCollectionsFromChild('AUTestingRecordsCollectionRelationsid_757');

//        dd($connectionTree);
    }
}
