<?php

use ANDS\Registry\Providers\ScholixProvider;
use ANDS\Repository\RegistryObjectsRepository;

class ScholixProviderTest extends \PHPUnit_Framework_TestCase
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
    public function it_should_get_the_correct_publication_date()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTCollectionToTestSearchFields37");
        $publicationDate = ScholixProvider::getPublicationDate($record);
        $this->assertEquals("2001-12-12", $publicationDate);
    }

    /** @test **/
    public function it_should_get_the_correct_identifiers()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTCollectionToTestSearchFields37");
        $identifiers = ScholixProvider::getIdentifiers($record);
        $this->assertNotEmpty($identifiers);
        $this->assertEquals(2, count($identifiers));
    }

    public function setUp()
    {
        restore_error_handler();
        foreach ($this->requiredKeys as $key) {
            $record = RegistryObjectsRepository::getPublishedByKey($key);
            if ($record === null) {
                $this->markTestSkipped("The record with $key is not available. Skipping tests");
            }
        }
    }
}