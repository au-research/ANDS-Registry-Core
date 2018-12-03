<?php

namespace ANDS\Registry\Providers\DCI;


use ANDS\API\Task\ImportSubTask\ProcessCoreMetadata;
use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Registry\Providers\RIFCS\CoreMetadataProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

class DataCitationIndexProviderTest extends \RegistryTestClass
{
    /** @test
     * @throws \Exception
     */
    public function it_should_produce_valid_dci()
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
    function it_should_produce_dci_with_all_required_fields()
    {
        // given a record
        $record = $this->stub(RegistryObject::class);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);
        CoreMetadataProvider::process($record);

        // when get dci
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
        $this->assertNotEmpty($sml->xpath('//BibliographicData/AuthorList/Author/AuthorName'));
        $this->assertNotEmpty($sml->xpath('//BibliographicData/Source/SourceRepository'));
        $this->assertNotEmpty($sml->xpath('//BibliographicData/Source/Version'));
        $this->assertNotEmpty($sml->xpath('//BibliographicData/LanguageList'));

        // Abstract
        $this->assertNotEmpty($sml->xpath('//Abstract'));

        // Rights & Licencing
        $this->assertNotEmpty($sml->xpath('//Rights_Licensing'));
        $this->assertNotEmpty($sml->xpath('//Rights_Licensing/RightsStatement'));

        // getParentDataRef

        // DescriptorData (spatial and temporal)
        $this->assertNotEmpty($sml->xpath('//DescriptorsData'));
        $this->assertNotEmpty($sml->xpath('//DescriptorsData/GeographicalData'));
        $this->assertNotEmpty($sml->xpath('//DescriptorsData/TimeperiodList'));


        // getCitationList
        $this->assertNotEmpty($sml->xpath('//CitationList'));
        $this->assertNotEmpty($sml->xpath('//CitationList/Citation/CitationText/CitationString'));

        // it has MethodologyList
        $this->assertNotEmpty($sml->xpath('//MethodologyList'));
        $this->assertNotEmpty($sml->xpath('//MethodologyList/Methodology'));

        // it has NamedPersonList
        $this->assertNotEmpty($sml->xpath('//NamedPersonList'));
        $this->assertNotEmpty($sml->xpath('//NamedPersonList/NamedPerson'));
    }

    /** @test */
    function author_address()
    {
        // given a record with an author (party)
        $record = $this->stub(RegistryObject::class);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_no_description.xml')
        ]);

        // with an author (party)
        $party = $this->stub(RegistryObject::class);
        $this->stub(RecordData::class, [
            'registry_object_id' => $party->id,
            'data' => Storage::disk('test')->get('rifcs/party_location_address.xml')
        ]);
        $this->stub(RegistryObject\Relationship::class,
            [
                'registry_object_id' => $record->id,
                'related_object_key' => $party->key,
                'relation_type' => 'author'
            ]
        );

        // author address with lines are present
        CoreMetadataProvider::process($record);
        CoreMetadataProvider::process($party);
        $dci = DataCitationIndexProvider::get($record);

        $sml = new \SimpleXMLElement($dci);

        // party exists
        $names = [];
        foreach ($sml->xpath('//BibliographicData/AuthorList/Author/AuthorName') as $authorName) {
            $names[] = (string) $authorName;
        }
        $this->assertContains($party->title, $names);

        // it has an Email
        $this->assertNotEmpty($sml->xpath('//BibliographicData/AuthorList/Author/AuthorEmail'));

        // it has an Address with AddressString
        $this->assertNotEmpty($sml->xpath('//BibliographicData/AuthorList/Author/AuthorAddress'));
        $this->assertNotEmpty($sml->xpath('//BibliographicData/AuthorList/Author/AuthorAddress/AddressString'));
    }

    /** @test */
    function funding_info()
    {
        // given a record with a funder
        $record = $this->stub(RegistryObject::class);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);

        // with a grants
        $activity = $this->stub(RegistryObject::class);
        $this->stub(RecordData::class, [
            'registry_object_id' => $activity->id,
            'data' => Storage::disk('test')->get('rifcs/activity_quality.xml')
        ]);
        $this->stub(RegistryObject\Relationship::class,
            [
                'registry_object_id' => $record->id,
                'related_object_key' => $activity->key,
                'relation_type' => 'isOutputOf'
            ]
        );

        // and a funder
        $funder = $this->stub(RegistryObject::class, ['class' => 'party']);
        $this->stub(RegistryObject\Relationship::class,
            [
                'registry_object_id' => $activity->id,
                'related_object_key' => $funder->key,
                'relation_type' => 'isFundedBy'
            ]
        );

        CoreMetadataProvider::process($record);
        CoreMetadataProvider::process($activity);

        // when get DCI
        $dci = DataCitationIndexProvider::get($record);
        $sml = new \SimpleXMLElement($dci);

        // it has a funding info
        $this->assertNotEmpty($sml->xpath('//FundingInfo'));

        // the FundingOrganization is present
        $names = [];
        foreach ($sml->xpath('//FundingInfo/FundingInfoList/ParsedFunding/FundingOrganisation') as $name) {
            $names[] = (string) $name;
        }
        $this->assertContains($funder->title, $names);
    }

    /** @test */
    function parent_data_ref()
    {
        // given a record
        $record = $this->stub(RegistryObject::class);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);

        // has a parent
        $parent = $this->stub(RegistryObject::class);
        $this->stub(RegistryObject\Relationship::class,
            [
                'registry_object_id' => $record->id,
                'related_object_key' => $parent->key,
                'relation_type' => 'isPartOf'
            ]
        );

        // when get dci on the record
        CoreMetadataProvider::process($record);
        $dci = DataCitationIndexProvider::get($record);
        $sml = new \SimpleXMLElement($dci);

        // it has a parent
        $this->assertNotEmpty($sml->xpath('//ParentDataRef'));
        $this->assertEquals($parent->key, (string) array_first($sml->xpath('//ParentDataRef')));
    }

    /** @test */
    function it_provides_dci_only_for_ds_that_has_the_flag()
    {
        // given a record
        $record = $this->stub(RegistryObject::class);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);
        CoreMetadataProvider::process($record);

        // when get dci it won't proceed
        $this->assertFalse(DataCitationIndexProvider::process($record));

        // but when the data source flag is turned on
        $record->datasource->setDataSourceAttribute('export_dci', DB_TRUE);

        // it returns true
        $this->assertTrue(DataCitationIndexProvider::process($record));

        // there's an entry in the database
        $dci = DCI::where('registry_object_id', $record->id);
        $this->assertNotEmpty($dci->get());
    }
}
