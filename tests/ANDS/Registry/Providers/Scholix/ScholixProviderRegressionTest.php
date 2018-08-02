<?php

use ANDS\Registry\Providers\Scholix\ScholixDocument;
use ANDS\Registry\Providers\ScholixProvider;
use ANDS\Repository\RegistryObjectsRepository;


class ScholixProviderRegressionTest extends \RegistryTestClass
{
    // TODO: refactor to not require keys existence

    protected $requiredKeys = [
        "AUTestingRecords2ScholixRecords1", // regression
        "AUTestingRecords2ScholixRecords2", // regression
        "AUTestingRecords2ScholixRecords9", // regression
        "AUTestingRecords2ScholixRecords12", // regression
        "AUTestingRecords2ScholixRecords18", // regression
        "AUTestingRecords2ScholixRecords25", // regression
        "AUTestingRecords2ScholixRecords33", // regression
        "AUTestingRecords2ScholixRecords37", // regression
        "AUTestingRecords2ScholixRecords41", // regression
        "AUTestingRecords2ScholixRecords42", // regression
        "AUTestingRecords2ScholixRecords43", // regression
        "AUTestingRecords2ScholixRecords44", // regression
        "AUTestingRecords2ScholixRecords46", // regression
        "AUTestingRecords3:Funder/Program13/Collection4",
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
        $this->assertEquals('url', $link['target']['identifier'][0]['schema']);

        // check xml
        $this->checkXML($scholix);
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
        $this->assertContains('url', $targetIdentifiers);
        $this->assertContains('hdl', $targetIdentifiers);

        // check xml
        $this->checkXML($scholix);
    }

    /** @test **/
    public function it_should_regression_9()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords2ScholixRecords9");
        $scholix = ScholixProvider::get($record);
        $links = $scholix->toArray();

        // (AUTestingRecords2) Simple Scholix Source Collection With 1 relatedObject hasCollector creator, a Single Identifier and Single RelatedInfo Publication with 2x relations.

        $this->assertEquals(1, count($links));

        $sourceIdentifiers = collect($links)->pluck('link')->pluck('source')->pluck('identifier')->flatten();
        $targetIdentifiers = collect($links)->pluck('link')->pluck('target')->pluck('identifier')->flatten();

        // from doi to uri
        $this->assertContains('doi', $sourceIdentifiers);
        $this->assertContains('url', $targetIdentifiers);

        // 1 creator
        $creator = collect($links)->pluck('link')->pluck('source')->pluck('creator')->flatten(1)->toArray();
        $this->assertEquals(1, count($creator));

        // 2 relations
        $relations = collect($links)->pluck('link')->pluck('relationship')->flatten(1)->toArray();
        $this->assertEquals(2, count($relations));

        // check xml
        $this->checkXML($scholix);
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
        $this->assertContains('url', $targetIdentifiers);

        $creators = collect($links)->pluck('link')->pluck('source')->pluck('creator')->flatten(1)->toArray();
        $this->assertEquals(2, count($creators));

        // check xml
        $this->checkXML($scholix);
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
        $this->assertContains('url', $targetIdentifiers);

        $creators = collect($links)->pluck('link')->pluck('source')->pluck('creator')->flatten(1);
        // TODO: Check Creators count is 3

        // check xml
        $this->checkXML($scholix);
    }

    /** @test **/
    public function it_should_regression_25()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords2ScholixRecords25");
        $scholix = ScholixProvider::get($record);
        $links = $scholix->toArray();

        // should have 6 creators
        $creators = $links[0]['link']['source']['creator'];
        $this->assertEquals(6, count($creators));
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

        // check xml
        $this->checkXML($scholix);
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

        $targetIdentifiers = collect($links)->pluck('link')->pluck('target')->pluck('identifier')->flatten(1)->pluck('identifier')->first();
        $this->assertRegExp("/view\?key=/", $targetIdentifiers);

        $creators = collect($links)->pluck('link')->pluck('source')->pluck('creator')->flatten(1);
        $this->assertEquals(2, count($creators));

        // check xml
        $this->checkXML($scholix);
    }

    /** @test **/
    public function it_should_regression_41()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords2ScholixRecords41");
        $scholix = ScholixProvider::get($record);
        $links = $scholix->toArray();

        // (AUTestingRecords2) Scholix Source Collection With a identifier only in citationMetadata and relationship to 4x RelatedInfo Publication 1x reverse RelatedObject collection/publication. 3 with relations 1 without. 2xdates 1 in citationMetadata and 1

        $this->assertEquals(5, count($links));

        $this->assertTrue(true);
    }

    /** @test **/
    public function it_should_regression_42()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords2ScholixRecords42");
        $scholix = ScholixProvider::get($record);
        $links = $scholix->toArray();

        // TODO: Verify 42

        $this->assertTrue(true);
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

        // check xml
        $this->checkXML($scholix);
    }

    /** @test **/
    public function it_should_regression_46()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords2ScholixRecords46");
        $scholix = ScholixProvider::get($record);
        $links = $scholix->toArray();

        //(AUTestingRecords2) Scholix Source Collection With a Single Identifier, 1x not supported Date and 1 RelatedInfo Publication and 1 relatedObject collection/publication. 3 creators all reverse. 2 x relatedInfo 1 x RelatedObject

        // 3 creators per link
        foreach ($links as $link) {
            $creators = collect($link['link'])['source']['creator'];
            $this->assertEquals(3, count($creators));
        }
    }

    /** @test **/
    public function it_should_regression_collection_4()
    {
        $key = "AUTestingRecords3:Funder/Program13/Collection4";
        $this->ensureKeyExist($key);
        $record = RegistryObjectsRepository::getPublishedByKey($key);
        $scholix = ScholixProvider::get($record);
        $links = $scholix->toArray();

        $this->assertGreaterThan(0, count($links));
        $dateCreated = \ANDS\Registry\Providers\RIFCS\DatesProvider::getCreatedDate($record);

        $link = $links[0]['link'];
        $publicationDate = $link['publicationDate'];

        $this->assertEquals($dateCreated, $publicationDate);

        // should have a creator
    }

    private function checkXML(ScholixDocument $scholix)
    {
        // TODO Removed once the Schema has been updated
        // PR: https://github.com/scholix/schema/pull/5
        return true;

        $xmls = [];
        foreach ($scholix->getLinks() as $link) {
            $xmls[] = $scholix->json2xml($link['link']);
        }
        foreach ($xmls as $xml) {
            $util = \ANDS\Util\XMLUtil::create();
            $result = $util->validateSchema("scholix", $xml);
            if (!$result) {
                print_r($xml);
                $this->fail($util->getValidationMessage());
            }
            $this->assertTrue($result);
        }
    }


}
