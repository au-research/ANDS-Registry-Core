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
        $allSubjects = array();
        foreach($links as $link){
            $url = static::getBaseUrl($link->link);
            $ro = RegistryObject::where('registry_object_id', $link->registry_object_id)->first();
            // && $ro->class == 'collection' && $ro->status == "PUBLISHED"
            if($ro->class == 'collection' && $ro->status == "PUBLISHED"){
                if(!isset($linksArray[$url])){
                    $linksArray[$url] = array();
                }

                $subjects = \ANDS\Registry\Providers\RIFCS\SubjectProvider::getSubjects($ro);
                $allSubjects = static::unique_multidim_array(array_merge($allSubjects, $subjects), "value");

                if(!isset($linksArray[$url][$ro->key])){
                    $linksArray[$url][$ro->key] = array(
                        "key" => $ro->key,
                        "title" => $ro->title,
                        "relation_types" => array(),
                        "relation"=>array(),
                        "full_urls" => array(),
                        "related_collection_uuids" => array());
                }

                if(!in_array($link->link_type, $linksArray[$url][$ro->key]["relation_types"])){
                    array_push($linksArray[$url][$ro->key]["relation_types"], $link->link_type);
                }
                if(!in_array($link->link, $linksArray[$url][$ro->key]["full_urls"])){
                    array_push($linksArray[$url][$ro->key]["full_urls"], $link->link);
                }

                $relType = static::getRelitionType($link->link_type);
                array_push($linksArray[$url][$ro->key]["relation"], array("type"=>$relType, "full_url"=>$link->link));

                $identifiers = Identifier::where('registry_object_id',
                    $link->registry_object_id)->get();
                $linksArray[$url][$ro->key]["related_collection_id"] = static::getPreferredIdentifier($identifiers);
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
                    "title" => $serviceRelation["title"],
                    "identifier" => $serviceRelation["related_collection_id"],
                    "types" => $serviceRelation["relation_types"],
                    "relation" => $serviceRelation["relation"]
                ];
                $fullURLs = array_merge($fullURLs, $serviceRelation["full_urls"]);
            }

            $links[] = [
                "url" => $url,
                "relations" => $relations,
                "full_urls" => array_values(array_unique($fullURLs)),
                "subjects" => $allSubjects
            ];
        }

        return $links;
    }
//sourced from http://php.net/manual/en/function.array-unique.php
    private static function unique_multidim_array($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }


    private static function getPreferredIdentifier($identifiers)
    {

        if(sizeof($identifiers) === 0)
            return array();

        foreach ($identifiers as $id) {
            if ($id->identifier_type === "global")
                return array("type" => $id->identifier_type, "identifier" => $id->identifier);
        }

        foreach ($identifiers as $id) {
            if ($id->identifier_type === "local")
                return array("type" => $id->identifier_type, "identifier" => $id->identifier);
        }

        return array("type"=>$identifiers[0]->identifier_type, "identifier"=>$identifiers[0]->identifier);
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
            "wps",
            "wmts"
           // "geonetwork",
          //  "geoserver"
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

    private static function getRelitionType($link_type){

    if(strpos($link_type, "relatedInfo_relation_") === 0){
        $tokens = explode("_", $link_type);
        $type = $tokens[2];
        if($type != ""){
            return getReverseRelationshipString($type);
        }
    }

    switch ($link_type) {
        case "electronic_url":
            return "makesAvailable";
            break;
        default:
            return "hasAssociationWith";
    }

}


}