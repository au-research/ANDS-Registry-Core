<?php


namespace ANDS\Test;


use ANDS\Registry\Providers\GrantsConnectionsProvider;
use ANDS\RegistryObject\ImplicitRelationship;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class TestGrantsConnectionsProvider
 * @package ANDS\Test
 */
class TestGrantsConnectionsProvider extends UnitTest
{
    /** @test **/
    public function test_it_should_find_a_direct_funder()
    {
        $activityKey = 'http://purl.org/au-research/grants/arc/DP0664065';
        $record = RegistryObjectsRepository::getPublishedByKey($activityKey);

        $funder = GrantsConnectionsProvider::create()->getDirectFunder($record);

        $this->assertEquals($funder->key, "http://dx.doi.org/10.13039/501100000923");
        $this->assertEquals($funder->title, "Australian Research Council");
    }

    /** @test **/
    public function test_it_should_find_parent_activity()
    {
        $activityKey = 'ncris.innovation.gov.au/activity/19';
        $activityKey = 'AUTestingRecords3:Funder/Program12';

        $record = RegistryObjectsRepository::getPublishedByKey($activityKey);

        $directActivities = GrantsConnectionsProvider::create()
            ->getDirectGrantActivities($record);

    }

    /** @test **/
    public function test_it_should_get_funder_from_a_nested_activity_node()
    {
        $activityKey = 'http://purl.org/au-research/grants/doe/nesp/caul/air';

        $record = RegistryObjectsRepository::getPublishedByKey($activityKey);

        $funder = GrantsConnectionsProvider::create()
            ->getFunder($record);

        $this->assertEquals($funder->title, 'Department of the Environment');
        $this->assertEquals($funder->key, 'http://dx.doi.org/10.13039/501100003531');
    }

    /** @test **/
    public function test_it_should_get_funder_from_a_nested_collection_node()
    {
        $collectionkey = 'hdl:1959.4/004_340';
        $collectionkey = 'abctb.org.au 11';
        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);

        $funder = GrantsConnectionsProvider::create()
            ->getFunder($record);

        $this->assertEquals($funder->title, 'Department of the Environment');
        $this->assertEquals($funder->key, 'http://dx.doi.org/10.13039/501100003531');
    }

    /** @test **/
    public function test_it_should_get_funder_from_nested_identifier_node()
    {
        $collectionkey = 'hdl:1959.4/004_340';
        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);
        ImplicitRelationship::where('from_id', $record->registry_object_id)->delete();
        $funder = GrantsConnectionsProvider::create()
            ->getFunder($record);
        $this->assertEquals($funder->title, 'Department of the Environment');
        $this->assertEquals($funder->key, 'http://dx.doi.org/10.13039/501100003531');
    }
    
    /** @test **/
    public function test_processed_list(){
        $collectionkey = "https://researchhub.research.uwa.edu.au/vivo/individual/dataset981";
        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);
        $funder = GrantsConnectionsProvider::create()
            ->getFunder($record);
    }

}