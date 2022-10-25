<?php

namespace ANDS\Util;

use function React\Promise\resolve;

class RelationUtil
{
    public static $relations = [];

    public static function load() {
        if (! is_array_empty(static::$relations)) {
            return;
        }
        $rows = array_map('str_getcsv', file(__DIR__.'./../../../etc/relations.csv'));
        $header = array_shift($rows);
        $csv = array();
        foreach ($rows as $row) {
            $csv[$row[0]] = array_combine($header, $row);
        }
        static::$relations = $csv;
    }

    public static function resolve($relationType) {
        self::load();
        return static::$relations[$relationType];
    }

    public static function contains($relationType) {
        self::load();
        return isset(static::$relations[$relationType]);
    }

    public static function getDisplayText($relationType, $fromClass = null) {
        self::load();
        if (! self::contains($relationType)) {
            return null;
        }

        $relation = self::resolve($relationType);

        switch ($fromClass) {
            case "collection":
                return $relation['collectionText'] !== '' ? $relation['collectionText'] : $relation['defaultText'];
            case "party":
                return $relation['partyText'] !== '' ? $relation['partyText']: $relation['defaultText'];
            case "activity":
                return $relation['activityText'] !== '' ? $relation['activityText']: $relation['defaultText'];
            case "service":
                return $relation['serviceText'] !== '' ? $relation['serviceText']: $relation['defaultText'];
            case null:
            default:
                return $relation['defaultText'];
        }
    }

    public static function getReverse($relationType) {
        self::load();

        if (self::contains($relationType)) {
            $relation = self::resolve($relationType);
            return $relation['reverseRelationType'];
        }

        return $relationType;
    }
}

