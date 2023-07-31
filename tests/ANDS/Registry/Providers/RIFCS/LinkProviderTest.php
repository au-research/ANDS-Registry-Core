<?php


use ANDS\Registry\Providers\LinkProvider;
use ANDS\RegistryObject;
use ANDS\RegistryObject\Links;
use ANDS\Repository\RegistryObjectsRepository;

class LinkProviderTest extends \RegistryTestClass
{

    protected $requiredKeys = [
        'https://redbox.rmit.edu.au/redbox/published/detail/3f138d4b3c73f5643'
    ];

    /** @test **/
    public function test_it_sould_have_3_links() {
        // $collectionkey = 'AUTestingRecords3RelatedCollectionDatasetRelObj1';

        $collectionkey = 'https://redbox.rmit.edu.au/redbox/published/detail/3f138d4b3c73f5643';
        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);

        $existringLinks = Links::where('registry_object_id', $record->registry_object_id)->get();

        $this->assertEquals(count($existringLinks), 11);
    }

    /** @test **/
    public function test_it_sould_create_Links() {
        //$collectionkey = 'OdPos6tCNy5Zw0WVfKwkZGpDOsdfdscRNImDnpjCIfsssHWwt16PcW';
        $collectionkey = 'https://redbox.rmit.edu.au/redbox/published/detail/3f138d4b3c73f5643';
        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);
        LinkProvider::process($record);

        $links = Links::where('registry_object_id', $record->registry_object_id)->get();
        $this->assertEquals(count($links), 11);
    }

    /** @test **/
    public function test_it_sould_clean_urls() {
        $dirtyUrls  = array(
            "http://google.com...." => "http://google.com",
            "(([[http://google.com,.,.,.,]" => "http://google.com",
            "(([[http://google.com]]]]]" => "http://google.com",
            "(([[http://google.com?1=2&amp;4=5]]]]]" => "http://google.com?1=2&4=5",
            "
                                (([[http://google.com" => "http://google.com",
            "(([[http://google.com          
            " => "http://google.com",
            "
                                (([[http://google.com
                                            " => "http://google.com",
        );
        foreach($dirtyUrls as $dirty=>$clean)
        {
            $this->assertEquals($clean, LinkProvider::cleanUrl($dirty));
        }
    }

}