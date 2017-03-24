<?php


namespace ANDS\Registry\Suggestors;


use ANDS\Repository\RegistryObjectsRepository;

class UserDataSuggestorTest extends \RegistryTestClass
{
    protected $requiredKeys = [
        "AUTestingRecords5parties12323489"
    ];

    /** @test **/
    public function it_should_be_able_to_get_suggestion_for_a_given_record()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords5parties12323489");

//        $suggestor = new UserDataSuggestor();
//        $suggestions = $suggestor->suggestByView($record);
//        $this->assertNotEmpty($suggestions);
    }
}
