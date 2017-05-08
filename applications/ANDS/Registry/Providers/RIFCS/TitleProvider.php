<?php


namespace ANDS\Registry\Providers;


use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;

class TitleProvider implements RIFCSProvider
{
    protected static $maxLength = 512;
    protected static $partyNamePartOrder = [
        'title',
        'given',
        'initial',
        'family',
        'suffix'
    ];

    /**
     * Process displayTitle and listTitle for the given record
     * and store them in the database for retrieval
     * TODO: UnitTest
     *
     * @param RegistryObject $record
     */
    public static function process(RegistryObject $record)
    {
        // get the raw ones
        $names = self::getRaw($record);

        // get Titles
        $displayTitle = null;
        $listTitle = null;

        // take the first primary found
        $name = collect($names)->first(function ($key, $item) {
            return $item['@attributes']['type'] == 'primary';
        });

        //get the value of the first namePart
        $displayTitle = is_array($name['value']) ? collect($name['value'])->get('value') : $name['value'];

        $listTitle = $displayTitle;


        // special rules for party
        if ($record->class === "party") {
            $displayTitle = self::constructTitleByOrder(
                $names,
                self::$partyNamePartOrder
            );

            $listTitle = self::constructTitleByOrder(
                $names,
                self::$partyNamePartOrder,
                $suffix = true
            );
        }

        // if none is found, get the first name
        if ($displayTitle === null || trim($displayTitle) == "") {
            $first = collect($names)->first();
            $displayTitle = $first['value'];

            // first found name
            $displayTitle = is_array($displayTitle) ? $displayTitle[0] : $displayTitle;

            // first found namePart
            $displayTitle = is_array($displayTitle) && array_key_exists('value', $displayTitle) ? $displayTitle['value'] : $displayTitle;

            $listTitle = $displayTitle;
        }

        // truncate to length
        $displayTitle = mb_strimwidth($displayTitle, 0, self::$maxLength,
            "...");
        $listTitle = mb_strimwidth($listTitle, 0, self::$maxLength, "...");

        // saving
        $record->title = $displayTitle;
        $record->setRegistryObjectAttribute('list_title', $listTitle);

        $record->save();
    }

    /**
     * Get all available titles for a given record
     * TODO: UnitTest
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function get(RegistryObject $record)
    {
        return [
            "display_title" => $record->title,
            "list_title" => $record->getRegistryObjectAttributeValue("list_title"),
            "raw" => self::getRaw($record)
        ];
    }

    /**
     * Return the raw form of nameParts from RIFCS
     * TODO: UnitTest
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function getRaw(RegistryObject $record)
    {
        $xml = $record->getCurrentData()->data;
        return self::getRawForXML($xml, $record->class);
    }

    /**
     * Separated from getRaw to ease unit testing
     *
     * TODO: UnitTest
     * @param $xml
     * @param $class
     * @return array
     */
    public static function getRawForXML($xml, $class)
    {
        $rawNames = [];
        foreach (XMLUtil::getElementsByXPath($xml,
            'ro:registryObject/ro:' . $class . '/ro:name') as $fragment) {
            $name = ['value' => null];
            if ($type = (string)$fragment['type']) {
                $name['@attributes'] = ['type' => $type];
            }

            foreach ($fragment->namePart as $namePartFragment) {
                $namePart = [];
                if ($type = (string)$namePartFragment['type']) {
                    $namePart['@attributes'] = ['type' => $type];
                }
                $namePart['value'] = (string)$namePartFragment;

                // flatten it if there's only 1 namePart
                if (count($namePart) === 1) {
                    $namePart = $namePart['value'];
                }

                $name['value'][] = $namePart;
            }
            $rawNames[] = $name;
        }

        // clean up nested value
        $rawNames = collect($rawNames)->map(function ($item) {
            if (is_array($item['value']) && count($item['value']) === 1) {
                $item['value'] = $item['value'][0];
            }
            return $item;
        })->toArray();

        return $rawNames;
    }

    /**
     * ANDS Business Rule
     * return a title following a given order
     * TODO: UnitTest
     *
     * @param  array $names an Array of raw names
     * @param  array $order an Array of order of name types
     * @param  boolean $suffix would list_title business rule suffix apply
     * @return string          Title based on business rule
     */
    public static function constructTitleByOrder(
        $names,
        $order,
        $suffix = false
    ) {
        $titleFragments = [];
        $names = collect($names);

        // take the first primary found
        $name = $names->first(function ($key, $item) {
            return $item['@attributes']['type'] == 'primary';
        });

        // add up the part on order
        $nameParts = collect($name['value']);
        foreach ($order as $o) {
            $part = $nameParts->where('@attributes', ['type' => $o]);
            if ($part->count() > 0) {
                $titleFragments[$o] = $part->pluck('value')->flatten()->all();
            }
        }

        // join the nameparts together
        $titleFragments = collect($titleFragments);
        $titleFragments = $titleFragments->map(function ($item, $key) {
            return is_array($item) ? implode(' ', $item) : $item;
        });

        // business logic for list title suffix
        if ($suffix) {
            $titleFragments = $titleFragments->map(function ($item, $key) {
                if ($key == 'initial' || $key == 'given') {
                    $item .= ".";
                }
                return $item;
            });
            return implode(', ', $titleFragments->all());
        }

        return implode(' ', $titleFragments->all());
    }
}