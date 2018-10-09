<?php

namespace ANDS\Registry\Providers\DCI;


use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\RegistryObject;

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
    function it_has_headers()
    {
        $record = $this->stub(RegistryObject::class);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);

        $dci = DataCitationIndexProvider::get($record);

        $dom = new \DOMDocument;
        $dom->loadXML($dci);

        $sml = new \SimpleXMLElement($dci);

        $results = [];
        foreach ($sml->xpath("//Header") as $actual) {
            $results[] = (string) $actual;
        }
        $this->assertNotEmpty($results);
    }

    /** @test */
    function it_test_for_bibliographic_data()
    {
        $record = $this->stub(RegistryObject::class);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);

        $dci = DataCitationIndexProvider::get($record);

        $dom = new \DOMDocument;
        $dom->loadXML($dci);

        // TODO: it has bibliographic data
    }
}
