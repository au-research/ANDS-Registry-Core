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
        "AUTestingRecords2ScholixRecords1", // regression
        "AUTestingRecords2ScholixRecords2", // regression
        "AUTestingRecords2ScholixRecords9", // regression
        "AUTestingRecords2ScholixRecords12", // regression
        "AUTestingRecords2ScholixRecords37", // regression
        "AUTestingRecords2ScholixRecords43", // regression
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

        foreach ($arrayForm as $link) {
            $this->assertArrayHasKey('link', $link);
            $this->assertArrayHasKey('publicationDate', $link['link']);
        }

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

            // each publisher has a name, and identifiers
            $publisher = $link['link']['publisher'];
            $this->assertArrayHasKey('name', $publisher);
            $this->assertArrayHasKey('identifier', $publisher);

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



    /** @test **/
    public function it_should_regression_1()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords2ScholixRecords1");
        $scholix = ScholixProvider::get($record);
        $links = $scholix->toArray();

        // Expected Result: A single Link Info Package with a link from the collection doi identifier to the publication uri identifier
        $this->assertEquals(1, count($links));

        $link = $links[0]['link'];
        $this->assertEquals('doi', $link['source']['identifier'][0]['schema']);
        $this->assertEquals('uri', $link['target']['identifier'][0]['schema']);
    }

    /** @test **/
    public function it_should_regression_2()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords2ScholixRecords2");
        $scholix = ScholixProvider::get($record);
        $links = $scholix->toArray();

        /**
         * Expected Result: 2 Link Info Packages:
        1 with a link from the collection doi identifier to the 1st publication uri identifier
        1 with a link from the collection doi identifier to the 2nd publication handle identifier
         */
        $this->assertEquals(2, count($links));

        $sourceIdentifiers = collect($links)->pluck('link')->pluck('source')->pluck('identifier')->flatten();
        $targetIdentifiers = collect($links)->pluck('link')->pluck('target')->pluck('identifier')->flatten();

        $this->assertContains('doi', $sourceIdentifiers);
        $this->assertContains('uri', $targetIdentifiers);
        $this->assertContains('handle', $targetIdentifiers);
    }

    /** @test **/
    public function it_should_regression_9()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords2ScholixRecords9");
        $scholix = ScholixProvider::get($record);
        $links = $scholix->toArray();

        // Expected Result: A single Link Info Package with a link from the collection doi identifier to the publication uri identifier. Single creator.

        $this->assertEquals(1, count($links));

        $sourceIdentifiers = collect($links)->pluck('link')->pluck('source')->pluck('identifier')->flatten();
        $targetIdentifiers = collect($links)->pluck('link')->pluck('target')->pluck('identifier')->flatten();

        $this->assertContains('doi', $sourceIdentifiers);
        $this->assertContains('uri', $targetIdentifiers);

        $creator = collect($links)->pluck('link')->pluck('source')->pluck('creator')->flatten(1)->toArray();

        $this->assertEquals(1, count($creator));
    }

    /** @test **/
    public function it_should_regression_12()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords2ScholixRecords12");
        $scholix = ScholixProvider::get($record);
        $links = $scholix->toArray();

        /**
         * Expected Result: A single Link Info Package with a link from the collection doi identifier to the publication uri identifier. 2 creators.
         */
        $sourceIdentifiers = collect($links)->pluck('link')->pluck('source')->pluck('identifier')->flatten();
        $targetIdentifiers = collect($links)->pluck('link')->pluck('target')->pluck('identifier')->flatten();

        $this->assertContains('doi', $sourceIdentifiers);
        $this->assertContains('uri', $targetIdentifiers);

        $creators = collect($links)->pluck('link')->pluck('source')->pluck('creator')->flatten(1)->toArray();
        $this->assertEquals(2, count($creators));
    }

    /** @test **/
    public function it_should_regression_37()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords2ScholixRecords37");
        $scholix = ScholixProvider::get($record);
        $links = $scholix->toArray();

        /**
         * Expected Result: 1 Link Info Package with a link from the collection doi identifier to the collection/publication key(has no identifier).
        Source publication date, creator and identifier shall be taken from citationMetadata.
        CitationMetadata Creator name is display name for contributor (all nameParts Firstname, Surname)
        2nd creator taken from relatedInfo isOwnedBy party.
         */

        $this->assertEquals(1, count($links));

        $sourceIdentifiers = collect($links)->pluck('link')->pluck('source')->pluck('identifier')->flatten(1)->pluck('schema')->toArray();
        $this->assertContains('doi', $sourceIdentifiers);

        $targetIdentifiers = collect($links)->pluck('link')->pluck('target')->pluck('identifier')->flatten(1)->pluck('schema')->toArray();
        $this->assertContains('Research Data Australia', $targetIdentifiers);

        $targetIdentifiers = collect($links)->pluck('link')->pluck('target')->pluck('identifier')->flatten(1)->pluck('identifier')->first();
        $this->assertRegExp("/view\?key=/", $targetIdentifiers);

        $creators = collect($links)->pluck('link')->pluck('source')->pluck('creator')->flatten(1);
        $this->assertEquals(2, count($creators));

    }

    /** @test **/
    public function it_should_regression_43()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords2ScholixRecords43");
        $scholix = ScholixProvider::get($record);
        $links = $scholix->toArray();

        /**
         * Expected Result: 34 Link Info Packages with a link from the collection doi identifier to each publication identifier. And from the collection local identifier to each publication identifier.
        Source publication date, creator and identifier shall be taken from citationMetadata.
        CitationMetadata Creator name is display name for contributor (all nameParts Firstname, Surname)
        2nd creator taken from relatedInfo isOwnedBy party.
        3rd creator taken from reverse relatedInfo isCollectorOf party.
         */

        $this->assertEquals(34, count($links));
    }

    /** @test **/
    public function it_should_regression_44()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords2ScholixRecords44");
        $scholix = ScholixProvider::get($record);
        $links = $scholix->toArray();

        /**
         * Expected Result: 2 Link Info Package with a link from the collection doi identifier to each of the collection/publication identifiers.
        Source publication date, creator and identifier shall be taken from citationMetadata.
        CitationMetadata Creator name is display name for contributor (all nameParts Firstname, Surname)
        2nd creator taken from relatedInfo isOwnedBy party.
        3rd creator taken from reverse relatedInfo isCollectorOf party.
         */

        $this->assertEquals(2, count($links));
    }


}