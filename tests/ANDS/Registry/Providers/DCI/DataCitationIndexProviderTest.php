<?php

namespace ANDS\Registry\Providers\DCI;


use ANDS\API\Task\ImportSubTask\ProcessCoreMetadata;
use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RIFCS\CoreMetadataProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

class DataCitationIndexProviderTest extends \RegistryTestClass
{
    /** @test
     * @throws \Exception
     */
    public function get_dci_for_a_record_should_return_dci()
    {
        $record = $this->stub(RegistryObject::class);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);

        $dci = DataCitationIndexProvider::get($record);

        $dom = new \DOMDocument;
        $dom->loadXML($dci);

        $this->assertEquals("DataRecord", $dom->firstChild->tagName);
    }

    /** @test */
    function all_the_required_dci_fields_are_available()
    {
        $record = $this->stub(RegistryObject::class);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);
        CoreMetadataProvider::process($record);

        $dci = DataCitationIndexProvider::get($record);

        $sml = new \SimpleXMLElement($dci);

        // it has a Header element
        $this->assertNotEmpty($sml->xpath('//Header'));
        $this->assertNotEmpty($sml->xpath('//Header/DateProvided'));
        $this->assertNotEmpty($sml->xpath('//Header/RepositoryName'));
        $this->assertNotEmpty($sml->xpath('//Header/RecordIdentifier'));

        // BibliographicData
        $this->assertNotEmpty($sml->xpath('//BibliographicData'));
        $this->assertNotEmpty($sml->xpath('//BibliographicData/TitleList'));
        $this->assertNotEmpty($sml->xpath('//BibliographicData/Source'));
        $this->assertNotEmpty($sml->xpath('//BibliographicData/Source/SourceURL'));
        $this->assertNotEmpty($sml->xpath('//BibliographicData/Source/CreatedDate'));
        $this->assertNotEmpty($sml->xpath('//BibliographicData/AuthorList'));
        $this->assertNotEmpty($sml->xpath('//BibliographicData/Source/SourceRepository'));
        $this->assertNotEmpty($sml->xpath('//BibliographicData/Source/Version'));
        $this->assertNotEmpty($sml->xpath('//BibliographicData/LanguageList'));

        // Abstract
        $this->assertNotEmpty($sml->xpath('//Abstract'));
    }

    /** @test */
    function it_something()
    {
        $this->markTestSkipped("This is a data test for refactoring");
        $record = RegistryObjectsRepository::getPublishedByKey("10.4225/06/565E7702F1E12");
        $dci = DataCitationIndexProvider::get($record);
        $sml = new \SimpleXMLElement($dci);
        dd($sml->xpath('//AuthorList/Author'));
    }
}
