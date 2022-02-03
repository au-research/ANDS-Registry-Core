<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\RegistryObject;

class TagProvider implements RIFCSProvider
{

    public static function process(RegistryObject $record)
    {
        // TODO: Implement process() method.
    }

    public static function get(RegistryObject $record)
    {
        $tags = RegistryObject\Tag::where('key', $record->key)->get();
        return collect($tags)->map(function($tag) {
            return [
                'tag' => $tag->tag,
                'type' => $tag->type
            ];
        });
    }

    /**
     * Obtain an associative array for the indexable fields
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function getIndexableArray(RegistryObject $record) {
        $tags = static::get($record);

        // only contains tag, no tag_type required
        return [
            'tag' => collect($tags)->pluck('tag')->toArray()
        ];
    }
}