<?php

use ANDS\Registry\Providers\ScholixProvider;
use ANDS\Repository\RegistryObjectsRepository;


class ScholixProviderRegressionTest extends \RegistryTestClass
{
    protected $requiredKeys = [
        "AUTestingRecords2ScholixRecords1", // regression
        "AUTestingRecords2ScholixRecords2", // regression
        "AUTestingRecords2ScholixRecords9", // regression
        "AUTestingRecords2ScholixRecords12", // regression
        "AUTestingRecords2ScholixRecords18", // regression
        "AUTestingRecords2ScholixRecords33", // regression
        "AUTestingRecords2ScholixRecords37", // regression
        "AUTestingRecords2ScholixRecords43", // regression
        "AUTestingRecords2ScholixRecords44", // regression
    ];

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
    public function it_should_regression_18()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords2ScholixRecords18");
        $scholix = ScholixProvider::get($record);
        $links = $scholix->toArray();


        /**
         * Expected Result: 1 Link Info Package with a link from the collection doi identifier to the publication uri identifier.
        Source publication date, creator and identifier shall be taken from citationMetadata.
        CitationMetadata Creator name is display name for contributor (all nameParts Firstname, Surname)
        2nd creator taken from relatedInfo isOwnedBy party.
        3rd creator taken from reverse relatedInfo isCollectorOf party.
         */

        $this->assertEquals(1, count($links));

        $sourceIdentifiers = collect($links)->pluck('link')->pluck('source')->pluck('identifier')->flatten();
        $targetIdentifiers = collect($links)->pluck('link')->pluck('target')->pluck('identifier')->flatten();

        $this->assertContains('doi', $sourceIdentifiers);
        $this->assertContains('uri', $targetIdentifiers);

        $creators = collect($links)->pluck('link')->pluck('source')->pluck('creator')->flatten(1);
        // TODO: Check Creators count is 3
    }

    /** @test **/
    public function it_should_regression_33()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords2ScholixRecords33");
        $scholix = ScholixProvider::get($record);
        $links = $scholix->toArray();

        /**
         * Expected Result: 2 Link Info Packages with a link from the collection doi identifier to each of the collection/publication identifiers.
        Source publication date, creator and identifier shall be taken from citationMetadata.
        Creator name is display name for contributor (all nameParts)
         */
        $this->assertEquals(2, count($links));

        $creators = collect($links)->pluck('link')->pluck('source')->pluck('creator')->flatten(1)->pluck('name')->toArray();
        $this->assertContains("Contributor, Scholix", $creators);
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
