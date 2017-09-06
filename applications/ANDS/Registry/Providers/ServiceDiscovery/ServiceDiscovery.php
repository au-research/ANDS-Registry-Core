<?php


namespace ANDS\Registry\Providers\ServiceDiscovery;



use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;
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
    
    
    public static function getRegistryObjectsBylinks($url){

        $links = Links::where('link', $url)->get();
        return $links;
    }


    public static function getLinkasJson($links){
        $url = null;
        $linksArray = array();
        foreach($links as $link){
            $url = $link->link;
            if(!isset($linksArray[$url])){
                $linksArray[$url] = array("url" => $url, "registry_object_keys" => array());
            }
            $ro = RegistryObject::where('registry_object_id', $link->registry_object_id)->first();
            if($ro->status == 'PUBLISHED' and !isset($linksArray[$url]["registry_object_keys"][$ro->key])){
                array_push($linksArray[$url]["registry_object_keys"], $ro->key);
            }
        }
        return json_encode($linksArray);
    }

}