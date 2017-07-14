<?php

namespace ANDS\Registry\Providers\RIFCS;

class SlugProviderTest extends \RegistryTestClass
{
    /** @test **/
    public function it_should_get_the_right_slug()
    {
        $record = $this->ensureKeyExist("AIMS/ced0bef3-5ae6-47ec-a1fb-2fe83f67b023");
        $slug = SlugProvider::get($record);
        $this->assertEquals("resilience-model-mtsrf-project-25i4", $slug);
    }
}
