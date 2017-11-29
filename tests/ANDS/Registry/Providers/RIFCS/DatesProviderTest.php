<?php


namespace ANDS\Providers\RIFCS;


use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

class DatesProviderTest extends \RegistryTestClass
{

    /** @test **/
    public function it_should_get_the_correct_pub_date()
    {
        $key = "AUTestingRecords2ScholixRecords44";
        $this->ensureKeyExist($key);
        $record = RegistryObject::where('key', $key)->first();
        $publicationDate = DatesProvider::getPublicationDate($record);
        $this->assertEquals("2001-03-05", $publicationDate);
    }

    /** @test **/
    public function it_should_get_the_correct_publication_date()
    {
        $key = "AUTCollectionToTestSearchFields37";
        $this->ensureKeyExist($key);
        $record = RegistryObjectsRepository::getPublishedByKey($key);
        $publicationDate = DatesProvider::getPublicationDate($record);
        $this->assertEquals("2001-12-12", $publicationDate);
    }

    /** @test */
    function it_should_get_correct_publciation_date_2()
    {
        $key = "AUTORCIDWizCollection2";
        $this->ensureKeyExist($key);
        $record = RegistryObjectsRepository::getPublishedByKey($key);
        $publicationDate = DatesProvider::getPublicationDate($record);
        $this->assertEquals("2011-02-07", $publicationDate);
    }
}