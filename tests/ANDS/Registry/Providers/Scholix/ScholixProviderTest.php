<?php

use ANDS\Registry\Providers\ScholixProvider;
use ANDS\Repository\RegistryObjectsRepository;

class ScholixProviderTest extends RegistryTestClass
{
    protected $requiredKeys = [
        "AUTestingRecordsu/collection/enmasse/1248",
        "AUTCollectionToTestSearchFields37",
        "AUTestingRecordsQualityLevelsCollection8_demo",
        "AUTestingRecordsQualityLevelsParty7_demo",
        "AUTestingRecords2ScholixRecords16",
        "AUTestingRecords2ScholixRecords14",
        "AUTestingRecords2ScholixRecords15",
        "AUTestingRecords2ScholixRecords18",
        "AUTestingRecords2ScholixGroupRecord1",
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
    public function it_should_get_the_right_identifier()
    {
        $partyRecord = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords2ScholixGroupRecord1");
        $partyRecordIdentifiers = \ANDS\Registry\Providers\IdentifierProvider::get($partyRecord);

        $shouldHave = [
            "AUTestingRecords2ScholixRecords16",
            "AUTestingRecords2ScholixRecords14",
            "AUTestingRecords2ScholixRecords18"
        ];

        $shouldNotHave = [
            "AUTestingRecords2ScholixRecords15"
        ];

        foreach ($shouldHave as $key) {
            $record = RegistryObjectsRepository::getPublishedByKey($key);
            $identifiers = ScholixProvider::getIdentifiers($record);
            $this->assertEquals(count($identifiers), count($partyRecordIdentifiers));
        }

        foreach ($shouldNotHave as $key) {
            $record = RegistryObjectsRepository::getPublishedByKey($key);
            $identifiers = ScholixProvider::getIdentifiers($record);
            $this->assertNotEquals(count($identifiers), count($partyRecordIdentifiers));
        }

        $this->assertTrue(true);
    }

    /** @test **/
    public function it_should_get_the_right_publication_format()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTCollectionToTestSearchFields37");
        $scholix = ScholixProvider::get($record);

        $links = $scholix->toArray();

        $this->assertGreaterThan(0, count($links));

        // each link has publicationDate, publisher and linkProvider, source and target
        foreach ($links as $link) {
            $this->assertArrayHasKey('link', $link);
            $this->assertArrayHasKey('publicationDate', $link['link']);
            $this->assertArrayHasKey('publisher', $link['link']);
            $this->assertArrayHasKey('linkProvider', $link['link']);
            $this->assertArrayHasKey('source', $link['link']);
            $this->assertArrayHasKey('target', $link['link']);

            // each publisher has a name
            $publisher = $link['link']['publisher'];
            $this->assertArrayHasKey('name', $publisher);

            // name is group
            $this->assertEquals($record->group, $publisher['name']);

            // linkProvider
            $linkProvider = $link['link']['linkProvider'];
            $this->assertArrayHasKey('name', $linkProvider);
            $this->assertArrayHasKey('objectType', $linkProvider);
            $this->assertArrayHasKey('title', $linkProvider);
            $this->assertArrayHasKey('identifier', $linkProvider);
        }
    }

    /** @test **/
    public function it_should_has_all_identifiers_as_source()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTCollectionToTestSearchFields37");
        $scholix = ScholixProvider::get($record);

        $links = $scholix->toArray();

        $sourcesIdentifiers = collect($links)->pluck('link')->pluck('source')->pluck('identifier')->flatten();

        // each identifier has a source
        $identifiers = collect(\ANDS\Registry\Providers\IdentifierProvider::get($record))->flatten();
        foreach ($identifiers as $identifier) {
            $this->assertContains($identifier, $sourcesIdentifiers);
        }

        // each citationMetadata identifier also has a source
        $citationIdentifiers = \ANDS\Registry\Providers\IdentifierProvider::getCitationMetadataIdentifiers($record);
        $citationIdentifiers = collect($citationIdentifiers)->flatten();
        foreach ($citationIdentifiers as $identifier) {
            $this->assertContains($identifier, $sourcesIdentifiers);
        }
    }

}