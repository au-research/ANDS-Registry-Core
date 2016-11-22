<?php


namespace ANDS\Test;

use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Registry\Providers\GrantsConnectionsProvider;

/**
 * Class TestRelationshipProvider
 * @package ANDS\Test
 */
class TestRelationshipProvider extends UnitTest
{
    /** @test **/
    public function test_it_should_find_and_save_the_funder()
    {
        $activityKey = 'http://purl.org/au-research/grants/arc/DP0664065';
        $record = RegistryObjectsRepository::getPublishedByKey($activityKey);

        // process
        RelationshipProvider::processGrantsRelationship($record);

        // make sure it's in the database
        $metadata = $record->getRegistryObjectMetadata('funder_id');
        $this->assertTrue($metadata);

        // get it
        $grantsRelationship =  RelationshipProvider::getGrantsRelationship($record);
        $funder = $grantsRelationship['funder'];

        // make sure it's the same as the generated ones
        $generatedFunder = GrantsConnectionsProvider::create()->getDirectFunder($record);

        $this->assertEquals($funder, $generatedFunder);
    }

    /** @test **/
    public function test_it_should_find_and_save_the_parent_activities_nested()
    {
        $activityKey = 'http://purl.org/au-research/grants/doe/nesp/caul/air';
        $record = RegistryObjectsRepository::getPublishedByKey($activityKey);

        // process
        RelationshipProvider::processGrantsRelationship($record);

        // get it
        $metadata = $record->getRegistryObjectMetadata('parents_activity_ids');
        $this->assertTrue($metadata);

        // there are at least 2
        $this->assertGreaterThanOrEqual(count(explode(',', $metadata)), 2);
    }

    /** @test **/
    public function test_it_should_find_and_save_parent_collections()
    {
        $collectionkey = 'abctb.org.au 11';
        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);

        // process
        RelationshipProvider::processGrantsRelationship($record);

        // get it
        $metadata = $record->getRegistryObjectMetadata('parents_collection_ids');
        $this->assertTrue($metadata);

        // there are at least 2
        $this->assertGreaterThanOrEqual(count(explode(',', $metadata)), 2);

    }

    public function test_it_sould_delete_all_relationships(){
        $collectionkey = 'IMOS/3ece0a18-0809-3fed-932a-021069ee911b';
        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);

        RelationshipProvider::process($record);
    }
}