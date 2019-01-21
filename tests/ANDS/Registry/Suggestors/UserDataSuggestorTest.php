<?php


namespace ANDS\Registry\Suggestors;


use ANDS\Repository\RegistryObjectsRepository;

class UserDataSuggestorTest extends \RegistryTestClass
{
//    protected $requiredKeys = [
//        "AUTestingRecords5parties12323489"
//    ];

    /** @test **/
    public function it_should_be_able_to_get_suggestion_for_a_given_record()
    {
        $key = "102.100.100/26735";
        $key = "AODN/0ad834fe-ea26-478a-a2d9-8bd8a47be842";
        $this->ensureKeyExist($key);
        $record = RegistryObjectsRepository::getPublishedByKey($key);

        $suggestor = new UserDataSuggestor();
        $suggestions = $suggestor->suggestByView($record);
        $this->assertNotEmpty($suggestions);
    }
}
