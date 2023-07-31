<?php

use ANDS\DOI\Validator\IPValidator;

class IPValidatorTest extends PHPUnit_Framework_TestCase
{
    /** @test **/
    public function it_should_validates_ip_range()
    {
        $this->assertTrue(IPValidator::validate('192.168.10.14', '192.168.10.1,192.168.10.15,192.168.10.14'));
        $this->assertTrue(IPValidator::validate('192.168.10.14', '168.168.10.1-192.168.10.15'));
        $this->assertTrue(IPValidator::validate('130.56.111.71', '130.56.60.97-130.56.111.71'));
        $this->assertTrue(IPValidator::validate('127.0.0.1', '127.0.0.1-194.123.123.123'));
    }

    /** @test **/
    public function it_should_valiodates_host_names()
    {

        $this->assertFalse(IPValidator::validate('130.56.60.128', 'researchdata.ands.org.au'));
      //  $this->assertTrue(IPValidator::validate('130.56.60.133', 'researchdata.ands.org.au'));
        $this->assertFalse(IPValidator::validate('hello world!#$%$#%', '130.56.62.129'));
    }

    /** @test **/
    public function it_should_validates_exact_matching()
    {
        $this->assertTrue(IPValidator::validate('127.0.0.1', '127.0.0.1'));
        $this->assertTrue(IPValidator::validate('1.2.3.5', '1.2.3.5'));
        $this->assertFalse(IPValidator::validate('1.x.x.x', 'test+str'));
        $this->assertFalse(IPValidator::validate('127.0.0.1', '127.0.0.2'));
    }

    /** @test **/
    public function it_should_validates_cidr_range()
    {
        $this->assertTrue(IPValidator::validate('192.168.1.23', '192.168.1.0/24'));
        $this->assertTrue(IPValidator::validate('192.168.1.4', '192.168.1.4/32'));
        $this->assertTrue(IPValidator::validate('192.168.1.23', '192.168.1.0/24'));
        $this->assertFalse(IPValidator::validate('192.168.1.5', '192.168.1.4/32'));
        $this->assertFalse(IPValidator::validate('92.168.4.23', '192.162.1.0/24'));
        $this->assertFalse(IPValidator::validate('192.168.1.23', '192.162.1.0/24'));
    }
}