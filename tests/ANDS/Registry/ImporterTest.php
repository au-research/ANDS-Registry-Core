<?php

namespace ANDS\Registry;

use ANDS\DataSource;
use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\RegistryObject;
use PHPUnit\Framework\TestCase;

class ImporterTest extends \RegistryTestClass
{

    public function testWipeDataSourceRecords()
    {
        // given a data source, a record & a record data
        $dataSource = $this->stub(DataSource::class);
        $record = $this->stub(RegistryObject::class, ['data_source_id' => $dataSource->id]);
        $recordData = $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_minimal.xml')
        ]);

        // when wipe
        Importer::wipeDataSourceRecords($dataSource);

        // the record data is gone
        $this->assertEquals(0, RecordData::where('registry_object_id', $record->id)->count());

        // clean up
        $dataSource->delete();
    }
}
