<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\RegistryObject;
use ANDS\RegistryObject\Tag;
use ANDS\RegistryObject\ThemePage;
use PHPUnit\Framework\TestCase;

class ThemePageProviderTest extends \RegistryTestClass
{

    public function testGetEmpty()
    {
        // given a record with a secret tag and some other tag
        $record = $this->stub(RegistryObject::class);
        $this->stub(Tag::class, [
            'key' => $record->key,
            'type' => Tag::$TAG_TYPE_SECRET
        ]);
        $this->stub(Tag::class, [
            'key' => $record->key,
            'type' => Tag::$TAG_TYPE_PUBLIC
        ]);

        // when get theme pages, nothing should appear
        $this->assertEmpty(ThemePageProvider::get($record));
    }

    public function testGetThemePage()
    {
        // given a record with a secret tag
        $record = $this->stub(RegistryObject::class);
        $secretTag = $this->stub(Tag::class, [
            'key' => $record->key,
            'type' => Tag::$TAG_TYPE_SECRET
        ]);

        // and a theme page with that tag
        $themePage = $this->stub(ThemePage::class, [
            'secret_tag' => $secretTag->tag
        ]);

        // when get theme pages, the theme page turns up
        $themePages = ThemePageProvider::get($record);
        $this->assertCount(1, $themePages);
        $this->assertContains($themePage->slug, collect($themePages)->pluck('slug'));

        // tearDown
        $themePage->delete();
    }

    public function testGetIndexableArray()
    {
        // given a record with 2 secret tag
        $record = $this->stub(RegistryObject::class);
        $secretTag1 = $this->stub(Tag::class, [
            'key' => $record->key,
            'type' => Tag::$TAG_TYPE_SECRET
        ]);
        $secretTag2 = $this->stub(Tag::class, [
            'key' => $record->key,
            'type' => Tag::$TAG_TYPE_SECRET
        ]);

        // and 2 theme pages with those tags
        $themePage1 = $this->stub(ThemePage::class, [
            'secret_tag' => $secretTag1->tag
        ]);
        $themePage2 = $this->stub(ThemePage::class, [
            'secret_tag' => $secretTag2->tag
        ]);

        // and 1 without any secret tags
        $themePage3 = $this->stub(ThemePage::class);

        // when get theme pages for the record, 1 and 2 should show up
        $index = ThemePageProvider::getIndexableArray($record);
        $this->assertArrayHasKey('theme_page', $index);
        $this->assertCount(2, $index['theme_page']);
        $this->assertContains($themePage1->slug, $index['theme_page']);
        $this->assertContains($themePage2->slug, $index['theme_page']);
        $this->assertNotContains($themePage3->slug, $index['theme_page']);

        // clean up
        $themePage1->delete();
        $themePage2->delete();
        $themePage3->delete();
    }
}
