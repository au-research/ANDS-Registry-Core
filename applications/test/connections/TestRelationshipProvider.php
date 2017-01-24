<?php


namespace ANDS\Test;

use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Registry\Providers\GrantsConnectionsProvider;

/**
 * Class TestRelationshipProvider
 * @package ANDS\Test
 */
class TestRelationshipProvider extends UnitTest
{

    // php index.php test connections TestRelationshipProvider test_it_should_find_the_related_class
    /** @test **/
    public function test_it_should_find_the_related_class()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords3anudc:3317");

        $hasRelatedParty = RelationshipProvider::hasRelatedClass($record, 'party');
        $this->assertTrue($hasRelatedParty);
    }

    public function test_it_sould_delete_all_relationships(){
        $collectionkey = 'IMOS/3ece0a18-0809-3fed-932a-021069ee911b';
        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);
        RelationshipProvider::process($record);

        // TODO
    }

    public function test_it_sould_get_correct_title_for_identifierRelationship(){
        $collectionkey = 'AUTestingRecords3:Funder/Program12/Hub2/GrantDP0987282/Collection1';
        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);
        $relationships = RelationshipProvider::getIdentifierRelationship($record);
        var_dump($relationships);
        // TODO
    }



    /** @test **/
    public function test_it_should_find_affected_records_by_ids()
    {
        initEloquent();
        $ids = RegistryObject::where('data_source_id', 205)->where('status', 'PUBLISHED')->pluck('registry_object_id')->toArray();

        $affectedIDs = RelationshipProvider::getAffectedIDsFromIDs($ids);

        dd(count($affectedIDs));
        dd($affectedIDs);
    }
}