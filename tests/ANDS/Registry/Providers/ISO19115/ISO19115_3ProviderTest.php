<?php

namespace ANDS\Registry\Providers\ORCID;
use \ANDS\Registry\Providers\ISO19115\ISO19115_3Provider;
use ANDS\Util\XMLUtil;

class ISO19115_3ProviderTest extends \RegistryTestClass
{
    /** @test */
    public function test_iso_transform()
    {
        //$record = $this->ensureKeyExist("c126e9f1-b072-9fee-6e04-d131383d9bea");
        $record = $this->ensureKeyExist("6ac30542-4805-f2bf-e5e4-45b73b15221d");
        $provider = new ISO19115_3Provider();
      //  $result = $provider->process($record);
      //  $this->assertTrue($result);
        $success =  $provider->process($record);
        $this->assertTrue($success);
        $iso = $provider->get($record);
        $rootNode = XMLUtil::getElementsByName($iso,'MD_Metadata',"http://standards.iso.org/iso/19115/-3/mdb/1.0");
        $this->assertEquals(sizeof($rootNode), 1);
        //$provider->validateContent($iso);
    }

    /** @test */
    public function test_get_existing_iso19115()
    {
        //$record = $this->ensureKeyExist("c126e9f1-b072-9fee-6e04-d131383d9bea");
        $record = $this->ensureKeyExist("31ac49f3-d391-5553-a10e-8eb880c12613");

        $provider = new ISO19115_3Provider();
        //  $result = $provider->process($record);
        //  $this->assertTrue($result);
        $iso = $provider->get($record);
        $this->assertContains("31ac49f3-d391-5553-a10e-8eb880c12613", $iso);
        $rootNode = XMLUtil::getElementsByName($iso,'MD_Metadata',"http://standards.iso.org/iso/19115/-3/mdb/1.0");
        $this->assertEquals(sizeof($rootNode), 1);
        //$provider->validateContent($iso);
    }
    /** @test **/
    public function test_dont_overwrite_harvester_iso19115()
    {
        $record = $this->ensureKeyExist("be564722-0e19-5b14-bf66-adf1d676ebc2ZZZ");
        $provider = new ISO19115_3Provider();
        $result = $provider->process($record);
        $this->assertContains("be564722-0e19-5b14-bf66-adf1d676ebc2", $result);
        $iso = $provider->get($record);
        $rootNode = XMLUtil::getElementsByName($iso,'MD_Metadata',"http://standards.iso.org/iso/19115/-3/mdb/1.0");
        $this->assertEquals(sizeof($rootNode), 1);
        //$provider->validateContent($iso);
    }



}
