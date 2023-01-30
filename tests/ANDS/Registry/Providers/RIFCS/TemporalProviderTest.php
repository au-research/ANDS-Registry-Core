<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\RecordData;
use ANDS\RegistryObject;
use ANDS\File\Storage;

class TemporalProviderTest extends \RegistryTestClass
{

    /** @test */
    public function test_it_gets_the_dates()
    {
        // given a record
        /** @var RegistryObject */
        //test get of date_from, date_to, earliest_year, latest_year
        $record = $this->stub(RegistryObject::class,
            ['class' => 'collection', 'type' => 'dataset', 'key' => 'AUTESTING_ALL_ELEMENTS_TEST']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);

        $dates = TemporalProvider::getIndexableArray($record);
        $this->assertNotNull($dates);
        $this->assertNotNull($dates['date_from']);
        $this->assertNotNull($dates['date_to']);
        $this->assertNotNull($dates['earliest_year']);
        $this->assertNotNull($dates['latest_year']);

    }

    /** @test TODO stubs**/
    public function it_should_get_the_correct_pub_date()
    {
        $record = $this->stub(RegistryObject::class);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/rif_date_test.xml')
        ]);
        $dates = TemporalProvider::getIndexableArray($record);
        $this->assertNotContains(false, $dates["date_from"]);
        $this->assertNotContains(false, $dates["date_to"]);
    }


}
