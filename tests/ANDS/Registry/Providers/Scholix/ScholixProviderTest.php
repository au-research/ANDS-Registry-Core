<?php

use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\Registry\Providers\RIFCS\IdentifierProvider;
use ANDS\Registry\Providers\Scholix\ScholixProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

class ScholixProviderTest extends MyceliumTestClass
{

    /**
     * @test
     */
    public function test_until_we_get_solr_8_in_ci_machine(){
        $this->assertTrue(true);
    }


    /** @test
    public function it_should_return_true_for_scholixable_record()
    {
        // should pass
        // given a record with a related publication (via relatedInfo)
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'AUT_SCHOLIX_COLLECTION']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_scholix.xml')
        ]);
        $this->myceliumInsert($record);

        $result = ScholixProvider::isScholixable($record);
        $this->assertTrue($result);

        $this->myceliumDelete($record);
    }
*/
    /** @test /
    public function it_should_fail_for_nonscholixable_records()
    {
        // should fail, no related publication
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'AUTESTING_MINIMAL_COLLECTION']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_minimal.xml')
        ]);
        $this->myceliumInsert($record);
        $result = ScholixProvider::isScholixable($record);
        $this->assertFalse($result);
        $this->myceliumDelete($record);

        // should fail, is a party
        $record2 = $this->stub(RegistryObject::class, ['class' => 'party','type' => 'person','key' => 'AODN/Aalbersberg,BillAUT3bb']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record2->id,
            'data' => Storage::disk('test')->get('rifcs/party_quality.xml')
        ]);
        $this->myceliumInsert($record2);
        $result = ScholixProvider::isScholixable($record2);
        $this->assertFalse($result);
        $this->myceliumDelete($record2);

    }
*/
    /** @test
    public function it_should_create_link_to_related_object_publication()
    {
        // has a related object of type publication
        $record2 = $this->stub(RegistryObject::class, [
            'class' => 'collection',
            'type' => 'publication',
            'key' => 'AUTestingRecords2ScholixRecords4test',
            'title' => 'The related publication record']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record2->id,
            'title' => 'The related publication record',
            'data' => Storage::disk('test')->get('rifcs/collection_scholix_4.xml')
        ]);
        $this->myceliumInsert($record2);

        // is the related object of type publication that record2 is related to
        $record1 = $this->stub(RegistryObject::class, [
            'class' => 'collection',
            'type' => 'collection',
            'key' => 'AUTestingRecords2ScholixRecords39test']
        );
        $this->stub(RecordData::class, [
            'registry_object_id' => $record1->id,
            'data' => Storage::disk('test')->get('rifcs/collection_scholix_39.xml')
        ]);
        $this->myceliumInsert($record1);
        $result = ScholixProvider::isScholixable($record1);

        $this->assertTrue($result);

        $resultArray = ScholixProvider::process($record1);

        $this->assertArrayHasKey('created',$resultArray);
        $this->assertNotEmpty($resultArray['created']);

        $this->myceliumDelete($record1);

        $this->myceliumDelete($record2);
    }*/
    /** @test
    public function it_should_get_the_right_identifier()
    {
        $party = $this->stub(RegistryObject::class, [
            'class' => 'party',
            'type' => 'group',
            'key' => 'AUTestingRecords2ScholixGroupRecord1'
        ]);
        $this->stub(RecordData::class, [
            'registry_object_id' => $party->id,
            'data' => Storage::disk('test')->get('rifcs/party_scholix_group.xml')
        ]);
        $this->myceliumInsert($party);

        $partyRecordIdentifiers = IdentifierProvider::get($party);

        $partyRecordIdentifiers = collect($partyRecordIdentifiers)->pluck('value')->toArray();

        // related party by reverse relationship
        $record14 = $this->stub(RegistryObject::class, [
            'class' => 'collection',
            'type' => 'dataset',
            'key' => 'AUTestingRecords2ScholixRecords14',
            'group' => $party->title
        ]);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record14->id,
            'data' => Storage::disk('test')->get('rifcs/collection_scholix_14.xml')
        ]);
        $this->myceliumInsert($record14);

        $identifiers = ScholixProvider::getIdentifiers($record14);
        $this->assertNotEmpty($identifiers);
        foreach ($identifiers as $id) {
            $this->assertContains($id['identifier'], $partyRecordIdentifiers);
        }

        // related party by related Object
        $record16 = $this->stub(RegistryObject::class, [
            'class' => 'collection',
            'type' => 'dataset',
            'key' => 'AUTestingRecords2ScholixRecords16',
            'group' => $party->title
        ]);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record16->id,
            'data' => Storage::disk('test')->get('rifcs/collection_scholix_16.xml')
        ]);
        $this->myceliumInsert($record16);

        $identifiers = ScholixProvider::getIdentifiers($record16);
        $this->assertNotEmpty($identifiers);
        foreach ($identifiers as $id) {
            $this->assertContains($id['identifier'], $partyRecordIdentifiers);
        }

        $this->myceliumDelete($party);
        $this->myceliumDelete($record14);
        $this->myceliumDelete($record16);

    }*/

    /** @test
    public function it_should_get_the_non_normalised_identifier()
    {
        $party = $this->stub(RegistryObject::class, [
            'class' => 'party',
            'type' => 'group',
            'key' => 'AUTestingRecords2ScholixGroupRecord1'
        ]);
        $this->stub(RecordData::class, [
            'registry_object_id' => $party->id,
            'data' => Storage::disk('test')->get('rifcs/party_scholix_group.xml')
        ]);
        $this->myceliumInsert($party);

        // related party by reverse relationship
        $record4 = $this->stub(RegistryObject::class, [
            'class' => 'collection',
            'type' => 'dataset',
            'key' => 'AUTestingRecords2ScholixRecords4test',
            'group' => $party->title
        ]);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record4->id,
            'data' => Storage::disk('test')->get('rifcs/collection_scholix_4.xml')
        ]);
        $this->myceliumInsert($record4);

        $scholix = ScholixProvider::get($record4);
        $scholixArray = $scholix->toArray();
        $this->assertNotEmpty($scholix);
        // ensure that the doi value has not been upper-cased and is returned as provided in rif-cs
        $this->assertEquals($scholixArray[0]['link']['target']['identifier'][0]['identifier'],"10.4567.dd");
        $this->myceliumDelete($party);
        $this->myceliumDelete($record4);
    }*/
    /** @test /
    public function it_should_get_the_right_publication_format()
    {
        $party = $this->stub(RegistryObject::class, [
            'class' => 'party',
            'type' => 'group',
            'key' => 'AUTestingRecords2ScholixGroupRecord1'
        ]);
        $this->stub(RecordData::class, [
            'registry_object_id' => $party->id,
            'data' => Storage::disk('test')->get('rifcs/party_scholix_group.xml')
        ]);
        $this->myceliumInsert($party);

        // related party by relatedInfo
        $record18 = $this->stub(RegistryObject::class, [
            'class' => 'collection',
            'type' => 'dataset',
            'key' => 'AUTestingRecords2ScholixRecords18',
            'group' => $party->title
        ]);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record18->id,
            'data' => Storage::disk('test')->get('rifcs/collection_scholix_18.xml')
        ]);
        $this->myceliumInsert($record18);

        // record 18 has related object 54 which is a collection of type publication
        $record54 = $this->stub(RegistryObject::class, [
            'class' => 'collection',
            'type' => 'publication',
            'key' => 'AUTestingRecords2ScholixRecords54',
            'group' => $party->title
        ]);

        $this->stub(RecordData::class, [
            'registry_object_id' => $record54->id,
            'data' => Storage::disk('test')->get('rifcs/collection_scholix_54.xml')
        ]);
        $this->myceliumInsert($record54);

        $scholixRelatedPublications = ScholixProvider::getRelatedPublications($record18);

        $this->assertNotEmpty($scholixRelatedPublications);
        foreach ($scholixRelatedPublications as $publication) {
            $this->assertEquals($publication['to_type'], 'publication');
        }

        $scholix = ScholixProvider::get($record18);


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
            $this->assertEquals($record18->group, $publisher['name']);

            // linkProvider
            $linkProvider = $link['link']['linkProvider'];
            $this->assertArrayHasKey('name', $linkProvider);
            $this->assertArrayHasKey('objectType', $linkProvider);
            $this->assertArrayHasKey('title', $linkProvider);
            $this->assertArrayHasKey('identifier', $linkProvider);
        }

        // test source identifier
        $sourceIdentiferTypes = collect($scholix->getLinks())
            ->pluck('link')->pluck('source')->pluck('identifier')->flatten(1)->pluck('schema');
        foreach ($sourceIdentiferTypes as $type) {
            $this->assertContains($type, array_values(ScholixProvider::$validSourceIdentifierTypes));
        }

        // test target identifier
        $targetIdentifierTypes = collect($scholix->getLinks())
            ->pluck('link')->pluck('target')->pluck('identifier')->flatten(1)->pluck('schema');
        foreach ($targetIdentifierTypes as $type) {
            $this->assertContains($type, array_values(ScholixProvider::$validTargetIdentifierTypes));
        }

        $this->myceliumDelete($party);
        $this->myceliumDelete($record18);
        $this->myceliumDelete($record54);
    }
     * */

    /** test **/
    public function it_should_has_all_identifiers_as_source()
    {
        $record = $this->ensureKeyExist("AUTCollectionToTestSearchFields37");
        ScholixProvider::process($record);
        $scholix = ScholixProvider::get($record);

        $links = $scholix->toArray();

        $sourcesIdentifiers = collect($links)->pluck('link.source.identifier')->flatten();

        // each identifier has a source
        $identifiers = collect(IdentifierProvider::get($record))->filter(function($identifier) {
            return in_array($identifier['type'], ScholixProvider::$validSourceIdentifierTypes);
        })->flatten();

        foreach ($identifiers as $identifier) {
            $this->assertContains($identifier, $sourcesIdentifiers);
        }

        // each citationMetadata identifier also has a source
        $citationIdentifiers = \ANDS\Registry\Providers\RIFCS\IdentifierProvider::getCitationMetadataIdentifiers($record);
        $citationIdentifiers = collect($citationIdentifiers)->flatten();
        foreach ($citationIdentifiers as $identifier) {
            $this->assertContains($identifier, $sourcesIdentifiers);
        }
    }

    /** (AUTestingRecords2) Simple Scholix Source Collection With No supported identifiers,
     * 1 relatedObject principalInvestigator creator,
     * 1 relatedInfo hasCollector creator and
     * 5 x RelatedInfo Publication with no supported identifiers.
     * 1x relatedObject collection type pub with no identifier.
     * Source is related to party via relatedObject which has the same name as group attribute.
     * Publisher id should be that of the related party.
     * test
     **/
    public function it_should_AUTestingRecords2ScholixRecords60()
    {
        $record = $this->ensureKeyExist("AUTestingRecords2ScholixRecords60");
        $scholix = ScholixProvider::get($record);
        $this->assertNotEmpty($scholix->getLinks());
    }

    /** test **/
    public function it_should_AUTestingRecords2ScholixRecords55()
    {
        $record = $this->ensureKeyExist("AUTestingRecords2ScholixRecords55");
        $scholix = ScholixProvider::get($record);
        $this->assertNotEmpty($scholix->getLinks());

        $electronicUrls = \ANDS\Registry\Providers\RIFCS\LocationProvider::getElectronicUrl($record);
        $url = array_pop($electronicUrls);

        // first source
        $links = $scholix->getLinks();
        $this->assertEquals(
          $url,
          $links[0]['link']['source']['identifier'][0]['identifier']
        );

    }

    /** test **/
    public function it_should_have_the_right_identifier_type()
    {
        $keys = [
            "AUTestingRecords2ScholixRecords57",
            "AUTestingRecords2ScholixRecords59",
            "AUTestingRecords2ScholixRecords62",
            "AUTestingRecords2ScholixRecords60",
        ];
        foreach ($keys as $key) {
            $record = RegistryObjectsRepository::getPublishedByKey($key);
            if (!$record) {
                continue;
            }
            $scholix = ScholixProvider::get($record);

            $this->assertNotEmpty($scholix->getLinks());

            // test source identifier
            $sourceIdentiferTypes = collect($scholix->getLinks())
                ->pluck('link')->pluck('source')->pluck('identifier')->flatten(1)->pluck('schema');
            foreach ($sourceIdentiferTypes as $type) {
                $this->assertContains($type, array_values(ScholixProvider::$validSourceIdentifierTypes));
            }

            // test target identifier
            $targetIdentifierTypes = collect($scholix->getLinks())
                ->pluck('link')->pluck('target')->pluck('identifier')->flatten(1)->pluck('schema');
            foreach ($targetIdentifierTypes as $type) {
                $this->assertContains($type, array_values(ScholixProvider::$validTargetIdentifierTypes));
            }
        }
    }

    /** @test
    public function it_should_not_limit_links(){
        $need_to_delete = false;
        $record43 = RegistryObjectsRepository::getPublishedByKey('AUTestingRecords2ScholixRecords43');
        if($record43 === null) {
            $record43 = $this->stub(RegistryObject::class, [
                'class' => 'collection',
                'type' => 'dataset',
                'key' => 'AUTestingRecords2ScholixRecords43',
                'group' => 'AUTestingRecords2'
            ]);
            $this->stub(RecordData::class, [
                'registry_object_id' => $record43->id,
                'data' => Storage::disk('test')->get('rifcs/collection_scholix_43.xml')
            ]);
            $need_to_delete = true;
        }

        $record43 = RegistryObjectsRepository::getPublishedByKey('AUTestingRecords2ScholixRecords43');
        $this->myceliumInsert($record43);
        $scholix = ScholixProvider::get($record43);
        $this->assertEquals('17',count($scholix->toArray()));
        if($need_to_delete){
            $this->myceliumDelete($record43);
        }

    }
*/
    /** @test
    public function it_should_not_return_source_slug(){
        $need_to_delete = false;
        $record16 = RegistryObjectsRepository::getPublishedByKey('AUTestingRecords2ScholixRecords16');
        if($record16 === null) {
            $record16 = $this->stub(RegistryObject::class, [
                'class' => 'collection',
                'type' => 'dataset',
                'key' => 'AUTestingRecords2ScholixRecords16',
                'group' => 'AUTestingRecords2'
            ]);
            $this->stub(RecordData::class, [
                'registry_object_id' => $record16->id,
                'data' => Storage::disk('test')->get('rifcs/collection_scholix_16.xml')
            ]);
            $need_to_delete = true;
            $record16 = RegistryObjectsRepository::getPublishedByKey('AUTestingRecords2ScholixRecords16');
            $this->myceliumInsert($record16);
        }

        $scholix = ScholixProvider::get($record16);
        $scholix_array = $scholix->toArray();
        $expected = baseUrl('view?key=') . $record16->key;
        $actual = $scholix_array[0]['link']['source']['identifier'][0]['identifier'];
        $this->assertEquals($expected,$actual);
        if($need_to_delete){
            $this->myceliumDelete($record16);
        }

    }
*/
    /** @test
    public function it_should_not_return_target_slug(){
        $need_to_delete = false;
        $record39 = RegistryObjectsRepository::getPublishedByKey('AUTestingRecords2ScholixRecords39');
        if($record39 === null) {
            $record39 = $this->stub(RegistryObject::class, [
                'class' => 'collection',
                'type' => 'dataset',
                'key' => 'AUTestingRecords2ScholixRecords16',
                'group' => 'AUTestingRecords'
            ]);
            $this->stub(RecordData::class, [
                'registry_object_id' => $record39->id,
                'data' => Storage::disk('test')->get('rifcs/collection_scholix_16.xml')
            ]);
            $need_to_delete = true;
            $record8 = RegistryObjectsRepository::getPublishedByKey('AUTestingRecords2ScholixRecords39');
            $this->myceliumInsert($record39);
        }

        $scholix = ScholixProvider::get($record39);
        $scholix_array = $scholix->toArray();
        $expected = baseUrl('view?key=AUTestingRecords3ScholixPublicationRecords8');
        $actual = $scholix_array[0]['link']['target']['identifier'][0]['identifier'];
        $this->assertEquals($expected,$actual);
        if($need_to_delete){
            $this->myceliumDelete($record39);
        }

    }*/

}