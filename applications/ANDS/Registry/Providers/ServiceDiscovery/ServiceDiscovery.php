<?php


namespace ANDS\Registry\Providers\ServiceDiscovery;



use ANDS\RegistryObject;
use ANDS\RegistryObject\Identifier;
use ANDS\RegistryObject\Links;

class ServiceDiscovery {


    public static function getServiceLinksForDatasource($data_source_id){

        $links = Links::where('data_source_id', $data_source_id)->get();
        return $links;
    }

    public static function getServiceLinksForRegistryObject(RegistryObject $record){

        $links = Links::where('registry_object_id', $record->id)->get();
        return $links;
    }


    public static function getServiceByRegistryObjectIds($ro_ids){

        $links = Links::wherein('registry_object_id', $ro_ids)->get();
        //dd($links);
        return $links;
    }
    
    
    public static function getServicesBylinks($url){
        $url = static::getBaseUrl($url);
        $links = Links::where('link','LIKE',"{$url}%")->get();
        return $links;
    }

    /*
     *
     * get all unique baseURLS and populate them with related collection keys and uuids
     *
     */
    public static function processLinks($incompleteLinks){
        $baseUrls = [];
        $resultLinks = [];
        foreach($incompleteLinks as $link) {
            $url = static::getBaseUrl($link->link);
            if($url != "" && !in_array($url, $baseUrls) && static::isServiceLink($link)) {
                $baseUrls[] = $url;
                $newLinks = static::getServicesBylinks($url);

                if($resultLinks)
                    $resultLinks =  $resultLinks->merge($newLinks)->unique();
                else
                    $resultLinks = $newLinks;
            }
        }

        return $resultLinks;

//        return static::getLinkasJson($resultLinks);
    }

    public static function formatLinks($links){
        $url = null;
        $linksArray = array();
        foreach($links as $link){
            $url = static::getBaseUrl($link->link);
            $ro = RegistryObject::where('registry_object_id', $link->registry_object_id)->first();
            // && $ro->class == 'collection' && $ro->status == "PUBLISHED"
            if($ro->class == 'collection' && $ro->status == "PUBLISHED"){
                if(!isset($linksArray[$url])){
                    $linksArray[$url] = array();
                }

                if(!isset($linksArray[$url][$ro->key])){
                    $linksArray[$url][$ro->key] = array("relation_types" => array(),
                                                        "full_urls" => array(),
                                                        "related_collection_uuids" => array());
                }

                if(!in_array($link->link_type, $linksArray[$url][$ro->key]["relation_types"])){
                    array_push($linksArray[$url][$ro->key]["relation_types"], $link->link_type);
                }
                if(!in_array($link->link, $linksArray[$url][$ro->key]["full_urls"])){
                    array_push($linksArray[$url][$ro->key]["full_urls"], $link->link);
                }
                $uuids = Identifier::where('registry_object_id',
                    $link->registry_object_id)->where('identifier_type', 'global')->get();
                foreach($uuids as $uuid) {
                    if(!in_array($uuid->identifier, $linksArray[$url][$ro->key]["related_collection_uuids"])){
                        array_push($linksArray[$url][$ro->key]["related_collection_uuids"], $uuid->identifier);
                    }
                }
            }
        }

        // format
        $links = [];
        foreach($linksArray as $url => $serviceLink){

            $relations = [];
            $fullURLs = [];
            foreach ($serviceLink as $key => $serviceRelation) {
                $relations[] = [
                    "key" => $key,
                    "uuid" => $serviceRelation["related_collection_uuids"][0],
                    "identifiers" => $serviceRelation["related_collection_uuids"],
                    "types" => $serviceRelation["relation_types"],
                    "full_url" => $serviceRelation["full_urls"]
                ];
                $fullURLs = array_merge($fullURLs, $serviceRelation["full_urls"]);
            }

            $links[] = [
                "url" => $url,
                "relations" => $relations,
                "full_urls" => array_values(array_unique($fullURLs))
            ];
        }

        return $links;
    }

    private static function getBaseUrl($url){
        $parsed_url = parse_url($url);

        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';

        return $scheme.$host.$port.$path;

    }

    private static function isServiceLink($link){
        $supported_services = [
            "wms",
            "wfs",
            "ogc",
            "wcs",
            "wps"
        //    ,"thredds"
        ];
        $supported_types = array("identifier_uri_link", "electronic", "relatedInfo");
        $supported = false;

        foreach($supported_types as $a) {
            if (stripos($link->link_type, $a) !== false)
                $supported = true;
        }
        if(!$supported){ return false;}

        foreach($supported_services as $a) {
            if (stripos($link->link, $a) !== false)
                return true;
        }
        return false;

    }

}