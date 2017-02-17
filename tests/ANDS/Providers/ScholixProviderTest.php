<?php

use ANDS\Registry\Providers\ScholixProvider;
use ANDS\Repository\RegistryObjectsRepository;

class ScholixProviderTest extends RegistryTestClass
{
    protected $requiredKeys = [
        "AUTestingRecordsu/collection/enmasse/1248",
        "AUTCollectionToTestSearchFields37",
        "AUTestingRecordsQualityLevelsCollection8_demo",
        "AUTestingRecordsQualityLevelsParty7_demo"
    ];

    /** @test **/
    public function it_should_return_true_for_scholixable_record()
    {
        // should pass
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecordsu/collection/enmasse/1248");
        $result = ScholixProvider::isScholixable($record);
        $this->assertTrue($result);

        // should pass, provided relationships
        $record = RegistryObjectsRepository::getPublishedByKey("AUTCollectionToTestSearchFields37");
        $relationships = \ANDS\Registry\Providers\RelationshipProvider::getMergedRelationships($record);
        $result = ScholixProvider::isScholixable($record, $relationships);
        $this->assertTrue($result);
    }

    /** @test **/
    public function it_should_fail_for_nonscholixable_records()
    {
        // should fail, is a collection, not related to a publication
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecordsQualityLevelsCollection8_demo");
        $result = ScholixProvider::isScholixable($record);
        $this->assertFalse($result);

        // should fail, is a party
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecordsQualityLevelsParty7_demo");
        $result = ScholixProvider::isScholixable($record);
        $this->assertFalse($result);
    }

    /** @test **/
    public function it_should_process_scholixable_correctly()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTCollectionToTestSearchFields37");
        ScholixProvider::process($record);
        $scholixable = (bool) $record->getRegistryObjectAttributeValue("scholixable");
        $this->assertTrue($scholixable);

        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecordsQualityLevelsCollection8_demo");
        ScholixProvider::process($record);
        $scholixable = (bool) $record->getRegistryObjectAttributeValue("scholixable");
        $this->assertFalse($scholixable);
    }

    /** @test **/
    public function it_should_get_the_correct_scholix_doc()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTCollectionToTestSearchFields37");
        $scholix = ScholixProvider::get($record);
        $arrayForm = $scholix->toArray();

        $this->assertArrayHasKey('link', $arrayForm);
        $this->assertArrayHasKey('publicationDate', $arrayForm['link']);
    }

    /** @test **/
    public function it_should_get_the_correct_relationships_format()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTCollectionToTestSearchFields37");
        $relationships = ScholixProvider::getRelationships($record);
        $this->assertEmpty($relationships);
    }

    /** @test **/
    public function it_should_get_the_right_publication_relationships()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTCollectionToTestSearchFields37");
        $relationships = ScholixProvider::getRelatedPublications($record);
        $this->assertEquals(2, count($relationships));
    }

    /** @test **/
    public function it_should_get_the_correct_identifiers_format()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTCollectionToTestSearchFields37");
        $identifiers = ScholixProvider::getIdentifiers($record);
        $this->assertNotEmpty($identifiers);
        $this->assertEquals(2, count($identifiers));
    }

}