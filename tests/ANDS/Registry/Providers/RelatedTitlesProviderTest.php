<?php


namespace ANDS\Registry\Providers;


use ANDS\Repository\RegistryObjectsRepository;

class RelatedTitlesProviderTest extends \RegistryTestClass
{

    /** @test **/
    public function it_should_find_related_class()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("C1_46");
        $related_party_multi = RelatedTitlesProvider::getIndexableArray($record);
        var_dump($related_party_multi);
    }


}