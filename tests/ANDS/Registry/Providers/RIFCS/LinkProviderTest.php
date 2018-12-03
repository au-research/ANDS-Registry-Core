<?php


use ANDS\Registry\Providers\LinkProvider;
use ANDS\RegistryObject;
use ANDS\RegistryObject\Links;
use ANDS\Repository\RegistryObjectsRepository;

class LinkProviderTest extends \RegistryTestClass
{

    protected $requiredKeys = [
        'Collection2_demo'
    ];

    /** @test **/
    public function test_it_sould_have_3_links() {
        // $collectionkey = 'AUTestingRecords3RelatedCollectionDatasetRelObj1';

        $collectionkey = 'Collection2_demo';
        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);

        $existringLinks = Links::where('registry_object_id', $record->registry_object_id)->get();

        $this->assertEquals(count($existringLinks), 13);
    }

    /** @test **/
    public function test_it_sould_create_Links() {
        //$collectionkey = 'OdPos6tCNy5Zw0WVfKwkZGpDOsdfdscRNImDnpjCIfsssHWwt16PcW';
        $collectionkey = 'Collection2_demo';
        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);
        LinkProvider::process($record);

        $links = Links::where('registry_object_id', $record->registry_object_id)->get();
        $this->assertEquals(count($links), 13);
    }

    /** @test **/
    public function test_it_sould_clean_urls() {
        $dirtyUrls  = array(
            "http://google.com...." => "http://google.com",
            "(([[http://google.com,.,.,.,]" => "http://google.com",
            "(([[http://google.com]]]]]" => "http://google.com",
            "(([[http://google.com?1=2&amp;4=5]]]]]" => "http://google.com?1=2&4=5"
        );
        foreach($dirtyUrls as $dirty=>$clean)
        {
            $this->assertEquals($clean, LinkProvider::cleanUrl($dirty));
        }
    }

}