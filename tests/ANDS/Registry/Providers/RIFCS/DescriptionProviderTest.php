<?php

namespace ANDS\Registry\Providers\RIFCS;


class DescriptionProviderTest extends \RegistryTestClass
{
    /** @test */
    function it_get_the_brief_description()
    {
        $record = $this->ensureKeyExist("AUTCollection1");
        $descriptions = DescriptionProvider::get($record);
        $this->assertNotNull($descriptions['brief']);
        $this->assertRegexp('/brief/', $descriptions['brief']);
    }

    /** @test */
    function it_get_the_full_description()
    {
        $record = $this->ensureKeyExist("AUTCollection1");
        $descriptions = DescriptionProvider::get($record);
        $this->assertNotNull($descriptions['full']);
        $this->assertRegexp('/full/', $descriptions['full']);
    }

    /** @test */
    function it_get_the_primary_description()
    {
        $record = $this->ensureKeyExist("AUTCollection1");
        $descriptions = DescriptionProvider::get($record);
        $this->assertNotNull($descriptions['primary_description']);
        $this->assertRegexp('/brief/', $descriptions['primary_description']);
    }
}
