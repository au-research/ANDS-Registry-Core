<?php


namespace ANDS\Registry\Providers\RIFCS;


use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;

class LocationProvider implements RIFCSProvider
{

    public static function process(RegistryObject $record)
    {
        // TODO: Implement process() method.
    }

    public static function get(RegistryObject $record, $xml = null)
    {
        if (!$xml) {
            $xml = $record->getCurrentData()->data;
        }

        return [
            'urls' => self::getElectronicUrl($record, $xml)
        ];
    }

    /**
     * class>/location/address/electronic[@type='url']
     *
     * @param RegistryObject $record
     * @param null $xml
     * @return array
     */
    public static function getElectronicUrl(RegistryObject $record, $xml = null)
    {
        if (!$xml) {
            $xml = $record->getCurrentData()->data;
        }

        $urls = [];
        foreach (XMLUtil::getElementsByXPath($xml,
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic[@type=\'url\']') AS $url) {
            $urls[] = trim((string) $url->value);
        }
        return $urls;
    }
}