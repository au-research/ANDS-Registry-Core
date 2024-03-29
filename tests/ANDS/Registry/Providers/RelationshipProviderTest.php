<?php


namespace ANDS\Registry\Providers;


use ANDS\Repository\RegistryObjectsRepository;

class RelationshipProviderTest extends \RegistryTestClass
{

    public function test_it_should_find_related_records()
    {
        $this->ensureKeyExist("C1_46");
        $record = RegistryObjectsRepository::getPublishedByKey("C1_46");
        $relatedRecords = RelationshipProvider::get($record);
        $this->assertGreaterThan(1, sizeof($relatedRecords));
    }


    public function test_it_should_find_related_class()
    {
        $this->ensureKeyExist("C1_46");
        $record = RegistryObjectsRepository::getPublishedByKey("C1_46");
        $hasRelatedParty = RelationshipProvider::hasRelatedClass($record, "party");
        $this->assertTrue($hasRelatedParty);
    }

    public function test_it_should_find_related_by_types()
    {
        $this->ensureKeyExist("C1_46");
        $record = RegistryObjectsRepository::getPublishedByKey("C1_46");
        $relatedRecords = RelationshipProvider::getRelationByType($record, ["hasAssociationWith", "hasCollector"]);
        $this->assertGreaterThan(1, sizeof($relatedRecords));
    }

}
