<?php


namespace ANDS\Test;

use ANDS\API\Task\ImportSubTask\IndexRelationship;
use ANDS\API\Task\ImportTask;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Registry\Providers\GrantsConnectionsProvider;
use ANDS\Registry\Relation;

/**
 * Class TestRelationshipProvider
 * @package ANDS\Test
 */
class TestRelationshipProvider extends UnitTest
{

    // php index.php test connections TestRelationshipProvider test_it_should_find_the_related_class
    /** @test **/
//    public function test_it_should_find_the_related_class()
//    {
//        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords3anudc:3317");
//        if ($record){
//            $hasRelatedParty = RelationshipProvider::hasRelatedClass($record, 'party');
//        $this->assertTrue($hasRelatedParty);
//        }
//    }
//
//    public function test_it_sould_delete_all_relationships(){
//        $collectionkey = 'IMOS/3ece0a18-0809-3fed-932a-021069ee911b';
//        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);
//        if($record)
//        RelationshipProvider::process($record);
//
//        // TODO
//    }
//
//    public function test_it_sould_get_correct_title_for_identifierRelationship(){
//        $collectionkey = 'AUTestingRecords3:Funder/Program12/Hub2/GrantDP0987282/Collection1';
//        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);
//        $relationships = RelationshipProvider::getIdentifierRelationship($record);
//        var_dump($relationships);
//        // TODO
//    }


    /** @test **/
    public function test_it_should_retrieve_relationships_for_duplicate_records()
    {
        initEloquent();
        $record = RegistryObjectsRepository::getPublishedByKey('AUTestingRecords3:Funder/Program12/Hub1/ProjectLP0347149/Collection3Duplicate');

        $relationships = RelationshipProvider::getImplicitRelationship($record);
        echo($record->registry_object_id.NL);
        $duplicates = $record->getDuplicateRecords();
        echo("DUPLICATES".NL);
        foreach ($duplicates as $duplicate) {
            echo("ID:" . $duplicate->registry_object_id.NL);
            echo("TITLE:". $duplicate->title.NL);
        }

        foreach($relationships as $rel) {
            var_dump($rel->getProperty('to_key'));
        }

        $affectedIds = RelationshipProvider::getAffectedIDsFromIDs([$record->registry_object_id], [$record->key]);
        echo("AFFECTED IDS".NL);
        var_dump($affectedIds);
        $mergedRelationships = RelationshipProvider::getMergedRelationships($record);
        echo("MERGED".NL);
        foreach ($mergedRelationships as $rel) {
            echo("FROM ID:" . $rel->getProperty('from_id').NL);
            echo("TO KEY:" . $rel->getProperty('to_key').NL);
        }




        echo("::::::::::::::::::::::::::::::::".NL);
        echo("::::::::::::::::::::::::::::::::".NL);

        $record = RegistryObjectsRepository::getPublishedByKey('AUTestingRecords3:Funder/Program12/Hub1/ProjectLP0347149/Collection3');

        echo($record->registry_object_id.NL);


        $duplicates = $record->getDuplicateRecords();
        echo("DUPLICATES".NL);
        foreach ($duplicates as $duplicate) {
            echo("ID:" . $duplicate->registry_object_id.NL);
            echo("TITLE:". $duplicate->title.NL);
        }


        $relationships = RelationshipProvider::getImplicitRelationship($record);


        foreach($relationships as $rel) {
            var_dump($rel->getProperty('to_key'));
        }
        $affectedIds = RelationshipProvider::getAffectedIDsFromIDs([$record->registry_object_id], [$record->key]);
        echo("AFFECTED IDS".NL);
        var_dump($affectedIds);



        $mergedRelationships = RelationshipProvider::getMergedRelationships($record);
        echo("MERGED".NL);
        foreach ($mergedRelationships as $rel) {
            echo("FROM ID:" . $rel->getProperty('from_id').NL);
            echo("TO KEY:" . $rel->getProperty('to_key').NL);

        }

    }

    public function test_it_should_give_me_relationship_index()
    {
        initEloquent();
        $record = RegistryObjectsRepository::getRecordByID(587019);
        $relationships = RelationshipProvider::getMergedRelationships($record);

        $importTask = new ImportTask();
        $importTask->init([
            'params' => http_build_query([
                'ds_id' => 210,
                'targetStatus' => 'PUBLISHED'
            ])
        ])->skipLoadingPayload();

        $this->ci->load->library('solr');
        $importTask->setCI($this->ci);
        $importTask->initialiseTask();
        $importTask->setTaskData("importedRecords", [587019]);

        $indexRelationshipTask = $importTask->getTaskByName("IndexRelationship");

        $indexRelationshipTask->run_task();
        dd("hello");

        $this->ci->solr->init()->setCore('relations')->commit();

    }


//    /** @test **/
//    public function test_it_should_find_affected_records_by_ids_for209()
//    {
//        initEloquent();
//        $ids = RegistryObject::where('data_source_id', 209)->where('status', 'PUBLISHED')->pluck('registry_object_id')->toArray();
//        $keys = RegistryObject::where('data_source_id', 209)->where('status', 'PUBLISHED')->pluck('key')->toArray();
//
//        $affectedIDs = RelationshipProvider::getAffectedIDsFromIDs($ids, $keys, true);
//
//        $record = RegistryObject::find(577960);
//
//        RelationshipProvider::processGrantsRelationship($record);
//        $relationships = RelationshipProvider::getImplicitRelationship($record);
//
//        dd($relationships);

//        var_dump($affectedIDs);

//        foreach($keys as $key){
//            $record = RegistryObjectsRepository::getPublishedByKey($key);
//            RelationshipProvider::process($record);
//            RelationshipProvider::processGrantsRelationship($record);
//        }
//
//        $affectedIDs = RelationshipProvider::getAffectedIDsFromIDs($ids, $keys, true);
//        var_dump($affectedIDs);

//   }
//
//    public function test_it_should_find_affected_records_by_ids_for211()
//    {
//        initEloquent();
//        $ids = RegistryObject::where('data_source_id', 211)->where('status', 'PUBLISHED')->pluck('registry_object_id')->toArray();
//        $keys = RegistryObject::where('data_source_id', 211)->where('status', 'PUBLISHED')->pluck('key')->toArray();
//
//        $affectedIDs = RelationshipProvider::getAffectedIDsFromIDs($ids, $keys, true);
//
//        var_dump($affectedIDs);
//
//        foreach($keys as $key){
//            $record = RegistryObjectsRepository::getPublishedByKey($key);
//            RelationshipProvider::process($record);
//        }
//
//        $affectedIDs = RelationshipProvider::getAffectedIDsFromIDs($ids, $keys, true);
//        var_dump($affectedIDs);
//
//    }

}