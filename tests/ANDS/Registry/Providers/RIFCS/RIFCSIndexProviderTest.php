<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\File\Storage;
use ANDS\Log\Log;
use ANDS\RecordData;
use ANDS\RegistryObject;
use ANDS\Util\SolrIndex;
use MinhD\SolrClient\SolrClient;

class RIFCSIndexProviderTest extends \MyceliumTestClass
{

    public function test_getIndexCollection()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','key' => 'AUTESTING_ALL_ELEMENTS_TEST']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);
        $this->myceliumInsert($record);

        // processing that should happen prior
        DatesProvider::process($record);
        TitleProvider::process($record);

        $index = RIFCSIndexProvider::get($record);

        $this->assertNotNull($index);
        $this->assertNotEmpty($index);
        $this->assertArrayHasKey('id', $index);
        $this->assertArrayHasKey('slug', $index);
        $this->assertArrayHasKey('key', $index);
        $this->assertArrayHasKey('display_title', $index);
        $this->assertArrayHasKey('description', $index);
        $this->assertArrayHasKey('identifier_type', $index);
        $this->assertArrayHasKey('identifier_value', $index);
        $this->assertArrayHasKey('identical_record_ids', $index);
        $this->assertArrayHasKey('access_methods', $index);
        $this->assertArrayHasKey('access_rights', $index);
        $this->assertArrayHasKey('license_class', $index);
        $this->assertArrayHasKey('date_from', $index);
        $this->assertArrayHasKey('date_to', $index);
        $this->assertArrayHasKey('earliest_year', $index);
        $this->assertArrayHasKey('latest_year', $index);
        $this->assertArrayHasKey('related_info_search', $index);
        $this->assertArrayHasKey('citation_info_search', $index);
        $this->assertArrayHasKey('spatial_coverage_extents_wkt', $index);

        $this->myceliumDelete($record);
    }

    public function test_getIndexActivity()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'activity', 'type' => 'project','key' => 'ACTIVITY_GRANT_NETWORK']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/activity_grant_network.xml')
        ]);
        $this->myceliumInsert($record);

        // processing that should happen prior
        DatesProvider::process($record);
        TitleProvider::process($record);

        $index = RIFCSIndexProvider::get($record);

        $this->assertNotNull($index);
        $this->assertNotEmpty($index);
        $this->assertArrayHasKey('id', $index);
        $this->assertArrayHasKey('slug', $index);
        $this->assertArrayHasKey('key', $index);
        $this->assertArrayHasKey('display_title', $index);
        $this->assertArrayHasKey('description', $index);
        $this->assertArrayHasKey('identifier_type', $index);
        $this->assertArrayHasKey('identifier_value', $index);
        $this->assertArrayHasKey('identical_record_ids', $index);
        $this->assertArrayHasKey('activity_status', $index);
        $this->assertArrayHasKey('funding_amount', $index);
        $this->assertArrayHasKey('funding_scheme', $index);
        $this->assertArrayHasKey('funding_scheme_search', $index);
        $this->assertArrayHasKey('administering_institution', $index);
        $this->assertArrayHasKey('institutions', $index);
        $this->assertArrayHasKey('funders', $index);
        $this->assertArrayHasKey('researchers', $index);
        $this->assertArrayHasKey('principal_investigator', $index);

        $this->myceliumDelete($record);
    }

    public function test_isIndexable()
    {
        // PUBLISHED record is indexable
        $this->assertTrue(
            RIFCSIndexProvider::isIndexable($this->stub(RegistryObject::class, ['status' => 'PUBLISHED']))
        );

        // DRAFT record is not indexable
        $this->assertFalse(
            RIFCSIndexProvider::isIndexable($this->stub(RegistryObject::class, ['status' => 'DRAFT']))
        );

        // PUBLISHED record that is an activity is indexable
        $this->assertTrue(
            RIFCSIndexProvider::isIndexable($this->stub(RegistryObject::class, [
                'status' => 'PUBLISHED',
                'class' => 'activity'
            ]))
        );

        // PUBLISHED activity record that belongs to PROV is not indexable
        $this->assertFalse(
            RIFCSIndexProvider::isIndexable($this->stub(RegistryObject::class, [
                'status' => 'PUBLISHED',
                'class' => 'activity',
                'group' => 'Public Record Office Victoria'
            ]))
        );
    }

    public function testIndexRecord()
    {
        $solrClient = SolrIndex::getClient("portal");

        // given a record
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','key' => 'AUTESTING_ALL_ELEMENTS_TEST']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);

        // when index and commit
        RIFCSIndexProvider::indexRecord($record);
        $solrClient->commit();

        // the document exists in SOLR
        $doc = $solrClient->get($record->id)->toArray();
        $this->assertNotNull($doc);
        $this->assertEquals($record->key, $doc['key']);
    }

    public function testUpdateFieldTags()
    {
        $solrClient = SolrIndex::getClient("portal");

        // given a record
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','key' => 'AUTESTING_ALL_ELEMENTS_TEST']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);
        $this->stub(RegistryObject\Tag::class, [
            'key' => $record->key,
            'tag' => 'AutomatedTestTag'
        ]);

        // when index and commit
        RIFCSIndexProvider::indexRecord($record);
        $solrClient->commit();

        // the document exists in SOLR and have the right tag, not the new one
        $doc = $solrClient->get($record->id)->toArray();
        $this->assertNotNull($doc);
        $this->assertContains("AutomatedTestTag", $doc['tag']);
        $this->assertNotContains("AutomatedTestTagNew", $doc['tag']);

        // when add a new tag and regenerateField
        $this->stub(RegistryObject\Tag::class, [
            'key' => $record->key,
            'tag' => 'AutomatedTestTagNew'
        ]);
        RIFCSIndexProvider::regenerateField($record, 'tags');
        $solrClient->commit();

        // doc should now have the new tag too
        $doc = $solrClient->get($record->id)->toArray();
        $this->assertNotNull($doc);
        $this->assertArrayHasKey('id', $doc);
        $this->assertArrayHasKey('key', $doc);
        $this->assertContains("AutomatedTestTag", $doc['tag']);
        $this->assertContains("AutomatedTestTagNew", $doc['tag']);
    }


}
