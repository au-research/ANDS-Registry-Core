<?php

namespace ANDS\Registry;

use ANDS\RegistryObject\Tag;
use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    /** @test */
    public function tag_can_be_obtained_from_database()
    {
        // given a tag
        Tag::create([
            'key' => 'testkey',
            'tag' => 'testtag',
            'user' => 'automated-test-user',
            'user_from' => 'phpunit'
        ]);

        // when try to get tag
        $tags = Tag::where('key', 'testkey')->get();
        $this->assertCount(1, $tags);

        // clean up
        Tag::where('key', 'testkey')->delete();
    }

}
