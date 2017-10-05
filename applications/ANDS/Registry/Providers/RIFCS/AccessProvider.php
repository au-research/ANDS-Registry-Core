<?php

namespace ANDS\Registry\Providers\RIFCS;


use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\Registry\Relation;
use ANDS\RegistryObject;
use ANDS\RegistryObject\Access;
use ANDS\Util\XMLUtil;

class AccessProvider implements RIFCSProvider
{
    protected static $methods = ["directDownload", "landingPage", "OGC:WMS", "OGC:WCS", "OGC:WFS", "OGC:WPS", "GeoServer", "THREDDS", "THREDDS:WCS", "THREDDS:WMS", "THREDDS:OPeNDAP"];

    /**
     * Process all the available methods
     * @param RegistryObject $record
     */
    public static function process(RegistryObject $record)
    {
        // TODO: Implement. Possibly store the access methods to a retrievable cache?
    }

    /**
     * Return all the available methods and their information
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function get(RegistryObject $record)
    {
        $result = [];
        $data = MetadataProvider::get($record);
        foreach (static::$methods as $method) {
            if ($accessMethod = static::getMethod($method, $record, $data)) {
                $result[$method] = $accessMethod;
            }
        }

        // does not contain any access
        if (empty($result)) {
            if ($contactCustodian = static::getContactCustodian($record, $data)) {
                $result['contactCustodian'] = $contactCustodian;
            }

            if ($other = static::getOther($record, $data)) {
                $result['other'] = $other;
            }
        }

        return $result;
    }

    /**
     * Return a particular access based on a known method
     *
     * @param $method
     * @param RegistryObject $record
     * @param $data
     * @return mixed|null
     */
    private static function getMethod($method, RegistryObject $record, $data)
    {
        $data = $data ?: MetadataProvider::get($record);

        switch ($method) {
            case "directDownload":
                return static::getDirectDownload($record, $data);
            case "landingPage":
                return static::getLandingPage($record, $data);
            case "THREDDS":
                return static::getTHREDDS($record, $data);
            case "GeoServer":
                return static::getGeoServer($record, $data);
            case "OGC:WFS":
                return static::getOGCWFS($record, $data);
            case "OGC:WMS":
                return static::getOGCWMS($record, $data);
            case "OGC:WCS":
                return static::getOGCWCS($record, $data);
            case "OGC:WPS":
                return static::getOGCWPS($record, $data);
            case "THREDDS:WMS":
                return static::getTHREDDSWMS($record, $data);
            case "THREDDS:OPeNDAP":
                return static::getTHREDDSOPeNDAP($record, $data);
            case "THREDDS:WCS":
                return static::getTHREDDSWCS($record, $data);
            default:
                return null;
        }
    }

    /**
     * location/address/electronic @type="url" AND the electronic @target=’directDownload’
     * OR location/address/electronic @type="url" AND the URL contains ‘thredds’ AND the URL contains ‘fileServer’
     * OR relatedObject | relatedInfo = service AND relation @type="supports" | "isPresentedBy" AND the relation/url contains ‘thredds’ AND the relation/url contains ‘fileServer’
     * OR When the collection is related to via a service registry object AND relation @type="isSupportedBy" | "presents" | “makesAvailable” AND the relation/url contains ‘thredds’ AND the relation/url contains ‘fileServer’
     *
     * @param RegistryObject $record
     * @return null
     */
    public static function getDirectDownload(RegistryObject $record, $data)
    {
        $result = [];
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic') AS $loc) {
            $type = (string)$loc['type'];
            $target = (string)$loc['target'];
            $value = (string)$loc->value;

            // setup the access, only add if the values match
            $access = new Access($value);
            $access->setTitle((string)$loc->title);
            $access->setMediaType((string)$loc->mediaType);
            $access->setByteSize((string)$loc->byteSize);
            $access->setNotes((string)$loc->notes);

            // location/address/electronic @type="url" AND the electronic @target=’directDownload’
            if ($type == "url" && $target == "directDownload") {
                $result[] = $access;
                continue;
            }

            // location/address/electronic @type="url" AND the URL contains ‘thredds’ AND the URL contains ‘fileServer’
            if ($type == "url" && str_contains($value, "thredds") && str_contains($value, "fileServer")) {
                $result[] = $access;
                continue;
            }
        }

        // relationship
        /* @var $relation Relation */
        foreach ($data["relationships"] as $relation) {

            // relatedObject | relatedInfo = service AND relation @type="supports" | "isPresentedBy" AND the relation/url contains ‘thredds’ AND the relation/url contains ‘fileServer’
            if (
                ($relation->prop('to_class') == "service" || $relation->prop("to_related_info_type") == "service")
                && (in_array($relation->prop('relation_type'), ["supports", "isPresentedBy"]))
                && str_contains(strtolower($relation->prop('relation_url')), "thredds")
                && str_contains(strtolower($relation->prop('relation_url')), "fileserver")
            ) {
                $result[] = new Access($relation->prop("relation_url"));
                continue;
            }

            // When the collection is related to via a service registry object AND relation @type="isSupportedBy" | "presents" | “makesAvailable” AND the relation/url contains ‘thredds’ AND the relation/url contains ‘fileServer’
            if (
                ($relation->prop('to_class') == "service")
                && (in_array($relation->prop('relation_type'), ["isSupportedBy", "presents", "makesAvailable"]))
                && str_contains(strtolower($relation->prop('relation_url')), "thredds")
                && str_contains(strtolower($relation->prop('relation_url')), "fileserver")
            ) {
                $result[] = new Access($relation->prop("relation_url"));
                continue;
            }

        }

        return $result;
    }

    /**
     * location/address/electronic @type="url" AND the electronic @target=’landingPage’
     *
     * @param RegistryObject $record
     * @param $data
     * @return null
     */
    public static function getLandingPage(RegistryObject $record, $data)
    {
        $result = [];
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic') AS $loc) {
            $type = (string)$loc['type'];
            $target = (string)$loc['target'];
            $value = (string)$loc->value;
            if ($type == "url" && $target == "landingPage") {
                $result[] = new Access($value);
            }
        }
        return $result;
    }

    /**
     *
     * When the collection contains a: location/address/electronic @type="url" AND the URL contains ‘wms’
     * OR relatedObject | relatedInfo = service AND relation @type="supports" | "isPresentedBy" AND the relation/url contains ‘wms’
     * OR When the collection is related to via a service registry object AND relation @type="isSupportedBy" | "presents" | “makesAvailable” AND the relation/url contains ‘wms’
     *
     * @param RegistryObject $record
     * @return null
     */
    public static function getOGCWMS(RegistryObject $record, $data)
    {
        $result = [];

        // location/address/electronic @type="url" AND the URL contains ‘wms’
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic') AS $loc) {
            $type = (string)$loc['type'];
            $value = (string)$loc->value;
            if ($type == "url" && str_contains(strtolower($value), "wms")) {
                $result[] = new Access($value);
            }
        }

        // relationships
        /* @var $relation Relation */
        foreach ($data["relationships"] as $relation) {
            if (
                ($relation->prop("to_class") == "service" || $relation->prop("to_related_info_type") == "service")
                && str_contains(strtolower($relation->prop("relation_url")), "wms")
                && in_array($relation->prop("relation_type"), ["supports", "isPresentedBy"])
            ) {
                $result[] = new Access($relation->prop('relation_url'));
            }

            if (
                $relation->prop("to_class") == "service"
                && str_contains(strtolower($relation->prop("relation_url")), "wms")
                && in_array($relation->prop("relation_type"), ["isSupportedBy", "presents", "makesAvailable"])
            ) {
                $result[] = new Access($relation->prop('relation_url'));
            }
        }

        return $result;
    }

    public static function getOGCWPS(RegistryObject $record, $data)
    {
        $result = [];

        // location/address/electronic @type="url" AND the URL contains ‘wms’
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic') AS $loc) {
            $type = (string)$loc['type'];
            $value = (string)$loc->value;
            if ($type == "url" && str_contains(strtolower($value), "wps")) {
                $result[] = new Access($value);
            }
        }

        // relationships
        /* @var $relation Relation */
        foreach ($data["relationships"] as $relation) {
            if (
                ($relation->prop("to_class") == "service" || $relation->prop("to_related_info_type") == "service")
                && str_contains(strtolower($relation->prop("relation_url")), "wps")
                && in_array($relation->prop("relation_type"), ["supports", "isPresentedBy"])
            ) {
                $result[] = new Access($relation->prop('relation_url'));
            }

            if (
                $relation->prop("to_class") == "service"
                && str_contains(strtolower($relation->prop("relation_url")), "wps")
                && in_array($relation->prop("relation_type"), ["isSupportedBy", "presents", "makesAvailable"])
            ) {
                $result[] = new Access($relation->prop('relation_url'));
            }
        }

        return $result;
    }

    /**
     * When the collection contains a: location/address/electronic @type="url" AND the URL contains ‘wcs’
     * OR relatedObject | relatedInfo = service AND relation @type="supports" | "isPresentedBy" AND the relation/url contains ‘wcs’
     * OR  When the collection is related to via a service registry object AND relation @type="isSupportedBy" | "presents" | “makesAvailable” AND the relation/url contains ‘wcs’
     *
     * @param RegistryObject $record
     * @return null
     */
    public static function getOGCWCS(RegistryObject $record, $data)
    {
        $result = [];
        // location/address/electronic @type="url" AND the URL contains ‘wcs’
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic') AS $loc) {
            $type = (string)$loc['type'];
            $value = (string)$loc->value;
            if ($type == "url" && str_contains(strtolower($value), "wcs")) {
                $result[] = new Access($value);
            }
        }

        // relationships
        /* @var $relation Relation */
        foreach ($data["relationships"] as $relation) {
            if (
                ($relation->prop("to_class") == "service" || $relation->prop("to_related_info_type") == "service")
                && str_contains(strtolower($relation->prop("relation_url")), "wcs")
                && in_array($relation->prop("relation_type"), ["supports", "isPresentedBy"])
            ) {
                $result[] = new Access($relation->prop('relation_url'));
            }

            if (
                $relation->prop("to_class") == "service"
                && str_contains(strtolower($relation->prop("relation_url")), "wcs")
                && in_array($relation->prop("relation_type"), ["isSupportedBy", "presents", "makesAvailable"])
            ) {
                $result[] = new Access($relation->prop('relation_url'));
            }
        }

        return $result;
    }

    /**
     * location/address/electronic @type="url" AND the URL contains ‘wfs’
     * OR relatedObject | relatedInfo = service AND relation @type="supports" | "isPresentedBy" AND the URL contains ‘wfs’
     * OR When the collection is related to via a service registry object AND relation @type="isSupportedBy" | "presents" | “makesAvailable” AND the relation/url contains ‘wfs’
     *
     * @param RegistryObject $record
     * @param $data
     * @return null
     */
    public static function getOGCWFS(RegistryObject $record, $data)
    {
        $result = [];
        // location/address/electronic @type="url" AND the URL contains ‘wfs’
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic') AS $loc) {
            $type = (string)$loc['type'];
            $value = (string)$loc->value;
            if ($type == "url" && str_contains(strtolower($value), "wfs")) {
                $result[] = new Access($value);
            }
        }

        // relationship
        /* @var $relation Relation */
        foreach ($data["relationships"] as $relation) {

            // relatedObject | relatedInfo = service AND relation @type="supports" | "isPresentedBy" AND the relation/url contains ‘geoserver’
            if (
                ($relation->prop('to_class') == "service" || $relation->prop("to_related_info_type") == "service")
                && (in_array($relation->prop('relation_type'), ["supports", "isPresentedBy"]))
                && str_contains(strtolower($relation->prop('relation_url')), "wfs")
            ) {
                $result[] = new Access($relation->prop("relation_url"));
                continue;
            }

            // When the collection is related to via a service registry object AND relation @type="isSupportedBy" | "presents" | “makesAvailable” AND the relation/url contains ‘geoserver’
            if (
                $relation->prop('to_class') == "service"
                && (in_array($relation->prop('relation_type'), ["isSupportedBy", "presents", "makesAvailable"]))
                && str_contains(strtolower($relation->prop('relation_url')), "wfs")
            ) {
                $result[] = new Access($relation->prop("relation_url"));
                continue;
            }
        }

        return $result;
    }

    /**
     * location/address/electronic @type="url" AND the URL contains ‘geoserver’
     * OR  relatedObject | relatedInfo = service AND relation @type="supports" | "isPresentedBy" AND the relation/url contains ‘geoserver’
     * OR When the collection is related to via a service registry object AND relation @type="isSupportedBy" | "presents" | “makesAvailable” AND the relation/url contains ‘geoserver’
     *
     * @param RegistryObject $record
     * @param $data
     * @return null
     */
    public static function getGeoServer(RegistryObject $record, $data)
    {
        $result = [];

        // location/address/electronic @type="url" AND the URL contains ‘geoserver’
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic') AS $loc) {
            $type = (string)$loc['type'];
            $value = (string)$loc->value;
            if ($type == "url" && str_contains($value, "geoserver")) {
                $result[] = new Access($value);
            }
        }

        // relationships
        /* @var $relation Relation */
        foreach ($data["relationships"] as $relation) {

            // relatedObject | relatedInfo = service AND relation @type="supports" | "isPresentedBy" AND the relation/url contains ‘geoserver’
            if (
                ($relation->prop('to_class') == "service" || $relation->prop("to_related_info_type") == "service")
                && (in_array($relation->prop('relation_type'), ["supports", "isPresentedBy"]))
                && str_contains($relation->prop('relation_url'), "geoserver")
            ) {
                $result[] = new Access($relation->prop("relation_url"));
                continue;
            }

            // When the collection is related to via a service registry object AND relation @type="isSupportedBy" | "presents" | “makesAvailable” AND the relation/url contains ‘geoserver’
            if (
                $relation->prop('to_class') == "service"
                && (in_array($relation->prop('relation_type'), ["isSupportedBy", "presents", "makesAvailable"]))
                && str_contains($relation->prop('relation_url'), "geoserver")
            ) {
                $result[] = new Access($relation->prop("relation_url"));
                continue;
            }
        }

        return $result;
    }

    /**
     * location/address/electronic @type="url" AND the URL contains ‘thredds’ AND the URL contains ‘catalog.html’ | ‘catalog.xml’
     * relatedObject | relatedInfo = service AND relation @type="supports" | "isPresentedBy" AND relation/url != null AND the relation/url contains ‘thredds’ AND the relation/url contains ‘catalog.html’ | ‘catalog.xml’
     * When the collection is related to via a service registry object AND relation @type="isSupportedBy" | "presents" | “makesAvailable” AND relation/url != null AND the relation/url contains ‘thredds’ AND the relation/url contains ‘catalog.html’ | ‘catalog.xml’
     *
     * @param RegistryObject $record
     * @param $data
     * @return array
     */
    public static function getTHREDDS(RegistryObject $record, $data)
    {
        $result = [];

        // location/address/electronic @type="url" AND the URL contains ‘thredds’ AND the URL contains ‘catalog.html’ | ‘catalog.xml’
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic') AS $loc) {
            $type = (string)$loc['type'];
            $value = (string)$loc->value;
            if ($type == "url" && str_contains($value, "thredds") && (str_contains($value, "catalog.html") || str_contains($value, "catalog.xml"))) {
                $result[] = new Access($value);
            }
        }

        // relationship
        /* @var $relation Relation */
        foreach ($data["relationships"] as $relation) {
            $relationURL = $relation->prop("relation_url");
            if (!$relationURL) continue;

            // relatedObject | relatedInfo = service AND relation @type="supports" | "isPresentedBy" AND relation/url != null AND the relation/url contains ‘thredds’ AND the relation/url contains ‘catalog.html’ | ‘catalog.xml’
            if (
                ($relation->prop("to_class") == "service" || $relation->prop("to_related_info_type") == "service")
                && ($relation->prop("relation_type") == "supports" || $relation->prop("relation_type") == "isPresentedBy")
                && str_contains($relationURL, "thredds")
                && (str_contains($relationURL, "catalog.html") || str_contains($relationURL, "catalog.xml"))
            ) {
                $result[] = new Access($relationURL);
                continue;
            }

            // related to via a service registry object AND relation @type="isSupportedBy" | "presents" | “makesAvailable” AND relation/url != null AND the relation/url contains ‘thredds’ AND the relation/url contains ‘catalog.html’ | ‘catalog.xml’
            if (
                $relation->prop("to_class") == "service"
                && in_array($relation->prop('relation_type'), ["isSupportedBy", "presents", "makesAvailable"])
                && str_contains($relationURL, "thredds")
                && (str_contains($relationURL, "catalog.html") || str_contains($relationURL, "catalog.xml"))
            ) {
                $result[] = new Access($relationURL);
                continue;
            }
        }

        return $result;
    }

    /**
     * @param RegistryObject $record
     * @param $data
     * @return array
     */
    public static function getTHREDDSWCS(RegistryObject $record, $data)
    {
        $result = [];

        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic') AS $loc) {
            $type = (string)$loc['type'];
            $value = (string)$loc->value;

            // location/address/electronic @type="url" AND the URL contains ‘thredds’ AND the URL contains ‘wcs’
            if ($type == "url" && str_contains($value, "thredds") && str_contains(strtolower($value), "wcs")) {
                $result[] = new Access($value);
            }
        }

        // relationship
        /* @var $relation Relation */
        foreach ($data["relationships"] as $relation) {
            $relationURL = $relation->prop("relation_url");
            if (!$relationURL) continue;

            if (
                ($relation->prop("to_class") == "service" || $relation->prop("to_related_info_type") == "service")
                && ($relation->prop("relation_type") == "supports" || $relation->prop("relation_type") == "isPresentedBy")
                && str_contains($relationURL, "thredds")
                && str_contains(strtolower($relationURL), "wcs")
            ) {
                $result[] = new Access($relationURL);
                continue;
            }

            if (
                $relation->prop("to_class") == "service"
                && in_array($relation->prop('relation_type'), ["isSupportedBy", "presents", "makesAvailable"])
                && str_contains($relationURL, "thredds")
                && str_contains(strtolower($relationURL), "wcs")
            ) {
                $result[] = new Access($relationURL);
                continue;
            }
        }

        return $result;
    }

    /**
     * location/address/electronic @type="url" AND the URL contains ‘thredds’ AND the URL contains ‘wms’
     * OR relatedObject | relatedInfo = service AND relation @type="supports" | "isPresentedBy" AND relation/url != null AND the relation/url contains ‘thredds’ AND the Urelation/urlRL contains ‘wms’
     * OR location/address/electronic @type="url" AND the URL contains ‘thredds’ AND the URL contains ‘catalog.html’ AND we can determine that some of the data in the catalog is available via WMS (use catalog.xml)
     * OR relatedObject | relatedInfo = service AND relation @type="supports" | "isPresentedBy" AND relation/url != null AND the relation/url contains ‘thredds’ AND the relation/url contains ‘catalog.html’ AND we can determine that some of the data in the catalog is available via WMS (use catalog.xml)
     * OR When the collection is related to via a service registry object AND relation @type="isSupportedBy" | "presents" | “makesAvailable” AND relation/url != null AND the relation/url contains ‘thredds’ AND the relation/url contains ‘wms’
     * OR When the collection is related to via a service registry object AND relation @type="isSupportedBy" | "presents" | “makesAvailable” AND relation/url != null AND the relation/url contains ‘thredds’ AND the relation/url contains ‘catalog.html’ AND we can determine that some of the data in the catalog is available via WMS (use catalog.xml)
     *
     * @param RegistryObject $record
     * @return null
     */
    public static function getTHREDDSWMS(RegistryObject $record, $data)
    {
        $result = [];

        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic') AS $loc) {
            $type = (string)$loc['type'];
            $value = (string)$loc->value;

            // location/address/electronic @type="url" AND the URL contains ‘thredds’ AND the URL contains ‘wms’
            if ($type == "url" && str_contains($value, "thredds") && str_contains(strtolower($value), "wms")) {
                $result[] = new Access($value);
            }
            // location/address/electronic @type="url" AND the URL contains ‘thredds’ AND the URL contains ‘catalog.html’ AND we can determine that some of the data in the catalog is available via WMS (use catalog.xml)
        }

        // relationship
        /* @var $relation Relation */
        foreach ($data["relationships"] as $relation) {
            $relationURL = $relation->prop("relation_url");
            if (!$relationURL) continue;

            // relatedObject | relatedInfo = service AND relation @type="supports" | "isPresentedBy" AND relation/url != null AND the relation/url contains ‘thredds’ AND the Urelation/urlRL contains ‘wms’
            if (
                ($relation->prop("to_class") == "service" || $relation->prop("to_related_info_type") == "service")
                && ($relation->prop("relation_type") == "supports" || $relation->prop("relation_type") == "isPresentedBy")
                && str_contains($relationURL, "thredds")
                && str_contains(strtolower($relationURL), "wms")
            ) {
                $result[] = new Access($relationURL);
                continue;
            }

            // relatedObject|relatedInfo = service AND relation @type="supports" | "isPresentedBy" AND relation/url != null AND the relation/url contains ‘thredds’ AND the relation/url contains ‘catalog.html’ AND we can determine that some of the data in the catalog is available via WMS (use catalog.xml)

            // When the collection is related to via a service registry object AND relation @type="isSupportedBy" | "presents" | “makesAvailable” AND relation/url != null AND the relation/url contains ‘thredds’ AND the relation/url contains ‘wms’
            if (
                $relation->prop("to_class") == "service"
                && in_array($relation->prop('relation_type'), ["isSupportedBy", "presents", "makesAvailable"])
                && str_contains($relationURL, "thredds")
                && str_contains(strtolower($relationURL), "wms")
            ) {
                $result[] = new Access($relationURL);
                continue;
            }
        }

        return $result;
    }

    /**
     * location/address/electronic @type="url" AND the URL contains ‘thredds’ AND the URL contains ‘dodsC’
     * OR relatedObject | relatedInfo = service AND relation @type="supports" | "isPresentedBy" AND relation/url != null AND the relation/url contains ‘thredds’ AND the relation/url contains ‘dodsC’
     * OR location/address/electronic @type="url" AND the URL contains ‘thredds’ AND the URL contains ‘catalog.html’ AND we can determine that some of the data in the catalog is available via OPeNDAP (use catalog.xml)
     * OR relatedObject|relatedInfo = service AND relation @type="supports" | "isPresentedBy" AND relation/url != null AND the relation/url contains ‘thredds’ AND the relation/url contains ‘catalog.html’ AND we can determine that some of the data in the catalog is available via OPeNDAP (use catalog.xml)
     * OR When the collection is related to via a service registry object AND relation @type="isSupportedBy" | "presents" | “makesAvailable” AND relation/url != null AND the relation/url contains ‘thredds’ AND the relation/url contains ‘dodsC’
     * OR When the collection is related to via a service registry object AND relation @type="isSupportedBy" | "presents" | “makesAvailable” AND relation/url != null AND the relation/url contains ‘thredds’ AND the relation/url contains ‘catalog.html’ AND we can determine that some of the data in the catalog is available via OPeNDAP (use catalog.xml)
     *
     * @param RegistryObject $record
     * @param $data
     * @return null
     */
    public static function getTHREDDSOPeNDAP(RegistryObject $record, $data)
    {
        $result = [];

        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic') AS $loc) {
            $type = (string)$loc['type'];
            $value = (string)$loc->value;

            // location/address/electronic @type="url" AND the URL contains ‘thredds’ AND the URL contains ‘dodsC’
            if ($type == "url" && str_contains($value, "thredds") && str_contains($value, "dodsC")) {
                $result[] = new Access($value);
            }
            // location/address/electronic @type="url" AND the URL contains ‘thredds’ AND the URL contains ‘catalog.html’ AND we can determine that some of the data in the catalog is available via OPeNDAP (use catalog.xml)
        }

        // relationship
        /* @var $relation Relation */
        foreach ($data["relationships"] as $relation) {
            $relationURL = $relation->prop("relation_url");
            if (!$relationURL) continue;

            // relatedObject | relatedInfo = service AND relation @type="supports" | "isPresentedBy" AND relation/url != null AND the relation/url contains ‘thredds’ AND the relation/url contains ‘dodsC’
            if (
                ($relation->prop("to_class") == "service" || $relation->prop("to_related_info_type") == "service")
                && ($relation->prop("relation_type") == "supports" || $relation->prop("relation_type") == "isPresentedBy")
                && str_contains($relationURL, "thredds")
                && str_contains($relationURL, "dodsC")
            ) {
                $result[] = new Access($relationURL);
                continue;
            }

            // relatedObject|relatedInfo = service AND relation @type="supports" | "isPresentedBy" AND relation/url != null AND the relation/url contains ‘thredds’ AND the relation/url contains ‘catalog.html’ AND we can determine that some of the data in the catalog is available via OPeNDAP (use catalog.xml)

            // the collection is related to via a service registry object AND relation @type="isSupportedBy" | "presents" | “makesAvailable” AND relation/url != null AND the relation/url contains ‘thredds’ AND the relation/url contains ‘dodsC’
            if (
                $relation->prop("to_class") == "service"
                && in_array($relation->prop('relation_type'), ["isSupportedBy", "presents", "makesAvailable"])
                && str_contains($relationURL, "thredds")
                && (str_contains($relationURL, "catalog.html") || str_contains($relationURL, "dodsC"))
            ) {
                $result[] = new Access($relationURL);
                continue;
            }
        }

        return $result;
    }

    /**
     * When the collection does not contain an access URL, but does contain:
     * Collection/location/address/electronic/@email ,OR
     * Collection/location/address/physical/@streetAddress
     * Collection/location/address/physical/@PostalAddress
     * ONLY IF no access url and no related service
     *
     * @param RegistryObject $record
     * @param $data
     * @return array
     */
    public static function getContactCustodian(RegistryObject $record, $data)
    {
        $result = [];

        // check for no access url
        $urls = [];
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic') AS $loc) {
            $type = (string)$loc['type'];
            $value = (string)$loc->value;
            if ($type == "url") {
                $urls[] = $value;
            }
        }

        if (count($urls) > 0) {
            return [];
        }

        // check for no service
        /* @var $relation Relation */
        $services = [];
        foreach ($data['relationships'] as $relation) {
            if ($relation->prop("to_class") == "service" || $relation->prop("to_related_info_type") == "service") {
                $services[] = $relation;
            }
        }
        if (count($services) > 0) {
            return [];
        }

        // Collection/location/address/electronic/@email
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic') AS $loc) {
            $type = (string)$loc['type'];
            $value = (string)$loc->value;
            if (in_array($type, ['email'])) {
                $result[] = (new Access($value))->setNotes("Contact Custodian");
            }
        }

        // Collection/location/address/physical/@streetAddress OR Collection/location/address/physical/@PostalAddress
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:physical') AS $loc) {
            $value = (string)$loc->value;
            $result[] = (new Access($value))->setNotes("Contact Custodian");
        }
        return $result;
    }

    /**
     * location/address/electronic @type="url" AND the target has not been provided AND we cannot determine the service type.
     * OR relatedObject | relatedInfo = service AND relation @type="supports" | "isPresentedBy" AND relation/url != null AND we cannot determine the service type.
     * OR When the collection is related to via a service registry object AND relation @type="isSupportedBy" | "presents" | “makesAvailable” AND relation/url != null AND we cannot determine the service type.
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function getOther(RegistryObject $record, $data)
    {
        $result = [];
        // location/address/electronic @type="url"
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic') AS $loc) {
            $type = (string)$loc['type'];
            $value = (string)$loc->value;
            if (in_array($type, ['url'])) {
                $result[] = (new Access($value))->setNotes("Contact Custodian");
            }
        }

        /* @var $relation Relation */
        foreach ($data["relationships"] as $relation) {
            $relationURL = $relation->prop("relation_url");
            if (!$relationURL) continue;

            // relatedObject | relatedInfo = service AND relation @type="supports" | "isPresentedBy" AND relation/url != null
            if (
                ($relation->prop("to_class") == "service" || $relation->prop("to_related_info_type") == "service")
                && ($relation->prop("relation_type") == "supports" || $relation->prop("relation_type") == "isPresentedBy")
                && $relationURL
            ) {
                $result[] = new Access($relationURL);
                continue;
            }

            // When the collection is related to via a service registry object AND relation @type="isSupportedBy" | "presents" | “makesAvailable” AND relation/url != null
            if (
                $relation->prop("to_class") == "service"
                && in_array($relation->prop('relation_type'), ["isSupportedBy", "presents", "makesAvailable"])
                && $relationURL
            ) {
                $result[] = new Access($relationURL);
                continue;
            }
        }
        return $result;
    }
}