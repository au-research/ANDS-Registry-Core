<?php

namespace ANDS\Registry\Providers\DublinCore;


use ANDS\File\File;
use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\Registry\Providers\RIFCS\CoreMetadataProvider;
use ANDS\Registry\Providers\RIFCS\TitleProvider;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;
use DOMDocument;

class DublinCoreProviderTest extends \MyceliumTestClass
{
    /** @test
     * @throws \Exception
     *
     * public function get_dc_for_record_should_provide_dc()
     * {
     * $record = $this->stub(RegistryObject::class);
     * $this->stub(RecordData::class, [
     * 'registry_object_id' => $record->id,
     * 'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
     * ]);
     *
     * $dc = DublinCoreProvider::get($record);
     *
     * $dom = new DOMDocument;
     * $dom->loadXML($dc);
     * $this->assertEquals("oai_dc:dc", $dom->firstChild->tagName);
     * }
     */
    /** @test
     * @throws \Exception
     * /
     * function it_should_have_required_elements()
     * {
     * // given a collection record
     * $record = $this->stub(RegistryObject::class);
     * $this->stub(RecordData::class, [
     * 'registry_object_id' => $record->id,
     * 'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
     * ]);
     * CoreMetadataProvider::process($record);
     * TitleProvider::process($record);
     *
     * // when get dc
     * $dc = DublinCoreProvider::get($record);
     *
     * // it has title, publisher, type and source
     * $this->assertContains("<dc:title>Collection with all RIF v1.6 elements (primaryName)</dc:title>", $dc);
     * $this->assertContains("<dc:publisher>AUTestingRecords</dc:publisher>", $dc);
     * $this->assertContains("<dc:type>collection</dc:type>", $dc);
     * $this->assertContains("<dc:source>http://demo.ands.org.au/registry/orca/register_my_data</dc:source>", $dc);
     * }
     *
     * /** @test
     * @throws \Exception
     *
     * function it_should_have_the_right_identifiers()
     * {
     * // given a collection record
     * $record = $this->stub(RegistryObject::class);
     * $this->stub(RecordData::class, [
     * 'registry_object_id' => $record->id,
     * 'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
     * ]);
     * CoreMetadataProvider::process($record);
     * TitleProvider::process($record);
     *
     * // when get dc
     * $dc = DublinCoreProvider::get($record);
     *
     * $sml = new \SimpleXMLElement($dc);
     * $sml->registerXPathNamespace("dc", DublinCoreDocument::$DCNamespace);
     *
     * $identifiers = [];
     * foreach ($sml->xpath("//dc:identifier") as $identifier) {
     * $identifiers[] = (string) $identifier;
     * }
     *
     * // has the url as an identifier
     * $this->assertContains($record->portal_url, $identifiers);
     *
     * // has the first local identifier
     * $this->assertContains("nla.AUTCollection1", $identifiers);
     *
     * // has the second local identifier
     * $this->assertContains("nla.part.12345", $identifiers);
     * }
     */
    /** @test
     * @throws \Exception
     *
     * function it_should_have_descriptions()
     * {
     * // given a collection record
     * $record = $this->stub(RegistryObject::class);
     * $this->stub(RecordData::class, [
     * 'registry_object_id' => $record->id,
     * 'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
     * ]);
     *
     * // when get dc
     * $dc = DublinCoreProvider::get($record);
     *
     * $sml = new \SimpleXMLElement($dc);
     * $sml->registerXPathNamespace("dc", DublinCoreDocument::$DCNamespace);
     *
     * // there are descriptions
     * $descriptions = [];
     * foreach ($sml->xpath("//dc:description") as $description) {
     * $descriptions[] = (string) $description;
     * }
     * $this->assertNotEmpty($descriptions);
     * }
     */
    /** @test
     * @throws \Exception
     *
     * function rights_can_come_from_description_of_type_rights()
     * {
     * // given a collection record with rights description
     * $record = $this->stub(RegistryObject::class);
     * $this->stub(RecordData::class, [
     * 'registry_object_id' => $record->id,
     * 'data' => Storage::disk('test')->get('rifcs/collection_with_rights_descriptions.xml')
     * ]);
     *
     * // when get dc
     * $dc = DublinCoreProvider::get($record);
     *
     * $sml = new \SimpleXMLElement($dc);
     * $sml->registerXPathNamespace("dc", DublinCoreDocument::$DCNamespace);
     *
     * // there are rights
     * $descriptions = [];
     * foreach ($sml->xpath("//dc:rights") as $description) {
     * $descriptions[] = (string) $description;
     * }
     * $this->assertNotEmpty($descriptions);
     * }
     */
    /** TODO test
     * @throws \Exception
     */
    function rights_can_come_from_rights_element()
    {
        // given a collection record with rights description
        $record = $this->stub(RegistryObject::class);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_with_rights.xml')
        ]);

        // when get dc
        $dc = DublinCoreProvider::get($record);

        $sml = new \SimpleXMLElement($dc);
        $sml->registerXPathNamespace("dc", DublinCoreDocument::$DCNamespace);

        // there are rights
        $descriptions = [];
        foreach ($sml->xpath("//dc:rights") as $description) {
            $descriptions[] = (string)$description;
        }
        $this->assertNotEmpty($descriptions);
    }


    /** @test
     * @throws \Exception
     /
    function it_should_have_coverages()
    {
        // given a collection record
        $record = $this->stub(RegistryObject::class);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);

        // when get dc
        $dc = DublinCoreProvider::get($record);

        $sml = new \SimpleXMLElement($dc);
        $sml->registerXPathNamespace("dc", DublinCoreDocument::$DCNamespace);

        // there are coverages
        $coverages = [];
        foreach ($sml->xpath("//dc:coverage") as $coverage) {
            $coverages[] = (string) $coverage;
        }
        $this->assertNotEmpty($coverages);

        // has spatial coverages
        $this->assertNotEmpty(collect($coverages)->filter(function($coverage){
            return substr($coverage, 0, strlen("Spatial")) === "Spatial";
        })->toArray());

        // has temporal coverages
        $this->assertNotEmpty(collect($coverages)->filter(function($coverage){
            return substr($coverage, 0, strlen("Temporal")) === "Temporal";
        })->toArray());
    }
*/
    /** @test
     * @throws \Exception

    function it_should_have_contributor_as_related_parties()
    {
        // given a record with an author (party)
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'AUT_DCI_COLLECTION']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_DCI.xml')
        ]);
        $this->myceliumInsert($record);

        // with an author (party)
        $party = $this->stub(RegistryObject::class, ['class' => 'party','type' => 'person','key' => 'AUT_DCI_PARTY']);

        $this->stub(RecordData::class, [
            'registry_object_id' => $party->id,
            'data' => Storage::disk('test')->get('rifcs/party_DCI.xml')
        ]);

        $this->myceliumInsert($party);

        // author address with lines are present
        CoreMetadataProvider::process($record);
        CoreMetadataProvider::process($party);

        // when get dc
        $dc = DublinCoreProvider::get($record);
        // has a contributor in the form of title (relation)
        $sml = new \SimpleXMLElement($dc);
        $sml->registerXPathNamespace("dc", DublinCoreDocument::$DCNamespace);
        $actual = (string) array_first($sml->xpath('//dc:contributor'));
        $this->assertContains("(isFundedBy)", $actual);
    }
*/
    /** @test
     * @throws \Exception
     /
    function it_should_have_all_subjects()
    {
        // given a collection record
        $record = $this->stub(RegistryObject::class);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);

        // when get dc
        $dc = DublinCoreProvider::get($record);

        $sml = new \SimpleXMLElement($dc);
        $sml->registerXPathNamespace("dc", DublinCoreDocument::$DCNamespace);

        // there are subjects and they are resolved
        $subjects = [];
        foreach ($sml->xpath("//dc:subject") as $subject) {
            $subjects[] = (string) $subject;
        }
        $this->assertNotEmpty($subjects);
        $this->assertContains("localSubject", $subjects);
        $this->assertContains("830201", $subjects);
    }
     * */
}
