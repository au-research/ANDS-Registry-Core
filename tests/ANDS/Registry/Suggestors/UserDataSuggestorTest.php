<?php


namespace ANDS\Registry\Suggestors;


class UserDataSuggestorTest extends \PHPUnit_Framework_TestCase
{
    /** @test **/
    public function it_should_get_stuff()
    {
        $suggestor = new UserDataSuggestor();
        dd($suggestor->suggestByView());
    }

    public function setUp()
    {
        restore_error_handler();
    }
}
