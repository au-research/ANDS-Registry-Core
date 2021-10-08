<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\RegistryObject;

class SlugProviderTest extends \RegistryTestClass
{
    /** @test *
     * @throws \Exception
     */
    public function it_should_get_the_slug_for_long_values()
    {
        $record = $this->stub(RegistryObject::class, ['title' => "Resilience model (MTSRF Project 2.5i.4)"]);
        $slug = SlugProvider::get($record);
        $this->assertEquals("resilience-model-mtsrf-project-25i4", $slug);
    }

    /** @test *
     * @throws \Exception
     */
    public function it_should_get_the_slug_for_short_values()
    {
        $record = $this->stub(RegistryObject::class, ['title' => "Some shorter values)"]);
        $slug = SlugProvider::get($record);
        $this->assertEquals("some-shorter-values", $slug);
    }

    /** @test
     * @throws \Exception
     */
    public function it_should_fall_back_to_group_value_when_title_is_empty()
    {
        $record = $this->stub(RegistryObject::class, ['title' => "", "group" => "oceanic"]);
        $slug = SlugProvider::get($record);
        $id = $record->id;
        $this->assertEquals("oceanic-$id", $slug);
    }

    /** @test
     * @throws \Exception
     */
    public function it_should_save_the_slug()
    {
        $record = $this->stub(RegistryObject::class, ['title' => "Resilience model (MTSRF Project 2.5i.4)"]);
        SlugProvider::process($record);
        $record = $record->fresh();
        $this->assertNotNull($record->slug);
        $this->assertEquals("resilience-model-mtsrf-project-25i4", $record->slug);
    }
}
