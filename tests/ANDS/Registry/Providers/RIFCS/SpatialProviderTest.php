<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\RegistryObject;

class SpatialProviderTest extends \RegistryTestClass
{

    public function testGetIndexableArray()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);

        $index = SpatialProvider::getIndexableArray($record);
        $this->assertNotEmpty($index);
        $this->assertArrayHasKey('spatial_coverage_polygons', $index);
        $this->assertArrayHasKey('spatial_coverage_extents', $index);
        $this->assertArrayHasKey('spatial_coverage_extents_wkt', $index);
        $this->assertArrayHasKey('spatial_coverage_centres', $index);
        $this->assertArrayHasKey('spatial_coverage_area_sum', $index);
    }
}
