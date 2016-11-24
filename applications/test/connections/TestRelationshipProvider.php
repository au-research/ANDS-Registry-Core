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

    public function test_it_sould_delete_all_relationships(){
        $collectionkey = 'IMOS/3ece0a18-0809-3fed-932a-021069ee911b';
        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);
        RelationshipProvider::process($record);

        // TODO
    }

    /** @test **/
    public function test_it_should_find_affected_records()
    {
        initEloquent();
        $record = RegistryObjectsRepository::getRecordByID(574582);
        $affectedIDs = RelationshipProvider::getAffectedIDs($record);
        dd($affectedIDs);
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