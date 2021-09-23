<?php


namespace ANDS\Registry\Providers;


use ANDS\Repository\RegistryObjectsRepository;

class GrantsConnectionsProviderTest extends \RegistryTestClass
{
    protected $requiredKeys = [
        "AUTestingRecords3:Funder/Program12/Hub1/ProjectLP0347149/Collection3/Collection3.1",
        "AUTestingRecords3:Funder",
        "AUTestingRecords3:Funder/Program12/Hub1/ProjectLP0347149/Collection3",
        "AUTestingRecords2ala.org.au/dr931",
        "AUTestingRecords3:Funder/Program12/Hub1/ProjectLP0347149",
        "AUTestingRecords3:Funder/Program12/Hub1",
        "AUTestingRecords3:Funder/Program12"
    ];

    /** @test **/
    public function it_should_find_funder_for_a_collection()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords3:Funder/Program12/Hub1/ProjectLP0347149/Collection3/Collection3.1");

        $funder = GrantsConnectionsProvider::create()->getFunder($record);
        $this->assertEquals("AUTestingRecords3:Funder", $funder->key);
    }

    /** @test **/
    public function it_should_find_funder_for_an_activity()
    {
        $activities = [
            "AUTestingRecords3:Funder/Program12",
            "AUTestingRecords3:Funder/Program12/Hub1",
            "AUTestingRecords3:Funder/Program12/Hub1/ProjectLP0347149"
        ];

        foreach ($activities as $key) {
            $record = RegistryObjectsRepository::getPublishedByKey($key);
            $funder = GrantsConnectionsProvider::create()->getFunder($record);
            $this->assertEquals("AUTestingRecords3:Funder", $funder->key);
        }
    }

    /** @test **/
    public function it_should_find_all_parent_activities_for_an_activity()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords3:Funder/Program12/Hub1/ProjectLP0347149");

        $parentActivities = GrantsConnectionsProvider::create()->getParentsActivities($record);
        $keys = collect($parentActivities)->unique()->pluck('key');
        $this->assertContains("AUTestingRecords3:Funder/Program12/Hub1", $keys);
        $this->assertContains("AUTestingRecords3:Funder/Program12", $keys);
        $this->assertEquals(2, count($keys));
    }

    /** @test **/
    public function it_should_find_all_parent_collections()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords3:Funder/Program12/Hub1/ProjectLP0347149/Collection3/Collection3.1");

        $parentCollections = GrantsConnectionsProvider::create()->getParentsCollections($record);
        $keys = collect($parentCollections)->unique()->pluck('key');
        $this->assertContains("AUTestingRecords3:Funder/Program12/Hub1/ProjectLP0347149/Collection3", $keys);
        $this->assertContains("AUTestingRecords2ala.org.au/dr931", $keys);
        $this->assertEquals(2, count($keys));
    }

    /** @test **/
    public function it_should_find_parent_activities()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords3:Funder/Program12/Hub1/ProjectLP0347149/Collection3/Collection3.1");

        $parentActivities = GrantsConnectionsProvider::create()->getParentsActivities($record);
        $keys = collect($parentActivities)->unique()->pluck('key');
        $this->assertEquals(3, count($keys));
    }

    public function setUp()
    {
        parent::setUp();
//        foreach ($this->requiredKeys as $key) {
//            $record = RegistryObjectsRepository::getPublishedByKey($key);
//            RelationshipProvider::process($record);
//            RelationshipProvider::processGrantsRelationship($record);
//        }
    }
}
