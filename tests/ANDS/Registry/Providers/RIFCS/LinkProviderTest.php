<?php


use ANDS\Registry\Providers\LinkProvider;
use ANDS\RegistryObject;
use ANDS\RegistryObject\Links;
use ANDS\Repository\RegistryObjectsRepository;

class LinkProviderTest extends \RegistryTestClass
{

    protected $requiredKeys = [
        'http://hdl.handle.net/10536/DRO/DU:30036766'
    ];

    /** @test **/
    public function test_it_sould_have_3_links() {
        // $collectionkey = 'AUTestingRecords3RelatedCollectionDatasetRelObj1';

        $collectionkey = 'http://hdl.handle.net/10536/DRO/DU:30036766';
        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);

        $existringLinks = Links::where('registry_object_id', $record->registry_object_id)->get();

        $this->assertEquals(count($existringLinks), 2);
    }

    /** @test **/
    public function test_it_sould_create_Links() {
        // $collectionkey = 'AUTestingRecords3RelatedCollectionDatasetRelObj1';
        $collectionkey = 'http://hdl.handle.net/10536/DRO/DU:30042980';
        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);
        LinkProvider::process($record);

        $links = Links::where('registry_object_id', $record->registry_object_id)->get();
        $this->assertEquals(count($links), 2);
    }

}