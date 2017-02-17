<?php


namespace ANDS\Providers\RIFCS;


use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Repository\RegistryObjectsRepository;

class DatesProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @test **/
    public function it_should_get_the_correct_publication_date()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTCollectionToTestSearchFields37");
        $publicationDate = DatesProvider::getPublicationDate($record);
        $this->assertEquals("2001-12-12", $publicationDate);
    }
}