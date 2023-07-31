<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\RegistryObject;

class ThemePageProvider implements RIFCSProvider
{

    public static function process(RegistryObject $record)
    {
        // TODO: Implement process() method.
    }

    public static function get(RegistryObject $record)
    {
        $secretTags = collect(TagProvider::get($record))
            ->where('type', RegistryObject\Tag::$TAG_TYPE_SECRET)
            ->pluck('tag')
            ->toArray();

        return RegistryObject\ThemePage::whereIn('secret_tag', $secretTags)->get()->toArray();
    }

    public static function getIndexableArray(RegistryObject $record)
    {
        $themePages = static::get($record);

        return [
            'theme_page' => collect($themePages)->pluck('slug')
        ];
    }
}