<?php

use ANDS\API\Repository\DataciteClientRespository;

class DataciteClientRepositoryTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_do_stuff()
    {
        $repo = new DataciteClientRespository();
        $this->assertNotNull($repo->getFirst());
    }

    /** @test */
    public function it_should_authenticate_the_right_user_if_has_shared_secret()
    {
        $repo = new DataciteClientRespository();
        $authenticate = $repo->authenticate(
            "94c5cdfc4183eca7f836f06f1ec5b85a4932758b", "04b51aa4aa"
        );
        $this->assertTrue($authenticate);
    }

    /** @test */
    public function it_should_not_authenticate_if_wrong_shared_secret()
    {
        $repo = new DataciteClientRespository();
        $authenticate = $repo->authenticate(
            "94c5cdfc4183eca7f836f06f1ec5b85a4932758b", "asdfasdfasdf"
        );
        $this->assertFalse($authenticate);
    }

    /** @test */
    public function it_should_authenticate_user_if_ip_match_and_no_shared_secret()
    {
        $repo = new DataciteClientRespository();
        $authenticate = $repo->authenticate(
            "94c5cdfc4183eca7f836f06f1ec5b85a4932758b", null, "130.56.111.120"
        );
        // $this->assertTrue($authenticate);
    }

    public function setUp()
    {
        require_once(__DIR__.'/../../vendor/autoload.php');
    }
}