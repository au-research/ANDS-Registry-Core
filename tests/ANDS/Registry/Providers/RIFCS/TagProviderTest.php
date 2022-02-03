<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\RegistryObject;

class TagProviderTest extends \RegistryTestClass
{

    /** @test */
    public function test_it_gets_tags()
    {
        // given a record
        /** @var RegistryObject */
        $record = $this->stub(RegistryObject::class, ['title' => 'test record']);
        $key = $record->key;

        // with 2 tags
        $this->stub(RegistryObject\Tag::class, ['key' => $record->key, 'tag' => 'tag1']);
        $this->stub(RegistryObject\Tag::class, ['key' => $record->key, 'tag' => 'tag2']);

        $tags = TagProvider::get($record);
        $this->assertNotNull($tags);
        $this->assertCount(2, $tags);
    }

    /** @test */
    public function test_it_can_index() {
        // given a record
        /** @var RegistryObject */
        $record = $this->stub(RegistryObject::class, ['title' => 'test record']);
        $key = $record->key;

        // with 2 tags
        $this->stub(RegistryObject\Tag::class, ['key' => $record->key, 'tag' => 'tag1']);
        $this->stub(RegistryObject\Tag::class, ['key' => $record->key, 'tag' => 'tag2']);

        $index = TagProvider::getIndexableArray($record);
        $this->assertNotNull($index);
        $this->assertArrayHasKey('tag', $index);
        $this->assertCount(2, $index['tag']);
    }
}
