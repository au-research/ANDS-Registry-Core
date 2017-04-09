<?php


namespace ANDS\Registry\Providers;


use ANDS\Repository\RegistryObjectsRepository;

class RelationshipProviderTest extends \RegistryTestClass
{
    protected $requiredKeys = [
        // these records exist in demo
        "Collection31_demo",
        "Collection346"
    ];

    /** @test **/
    public function it_should_1()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("Collection346");
        RelationshipProvider::process($record);
        RelationshipProvider::processGrantsRelationship($record);

        $implicitRelationships = RelationshipProvider::getImplicitRelationship($record);
    }

    /** @test **/
    public function it_should_find_all_implicit_links()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("Collection31_demo");

        $parentCollections = GrantsConnectionsProvider::create()->getParentsCollections($record);
        $this->assertGreaterThan(0, count($parentCollections));

        $parentActivities = GrantsConnectionsProvider::create()->getParentsActivities($record);
        $this->assertEquals(0, count($parentActivities));

        $funder = GrantsConnectionsProvider::create()->getFunder($record);
        // TODO: verify funder exists, this one has a relatedInfo (reverse) isFundedBy a party
    }
}
