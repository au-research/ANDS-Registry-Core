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
    
    
    public static function getRegistryObjectsBylinks($url){

        $links = Links::where('link', $url)->get();
        return $links;
    }


    public static function getLinkasJson($links){
        $url = null;
        $linksArray = array();
        foreach($links as $link){
            $url = $link->link;
            $type = $link->link_type;

            $ro = RegistryObject::where('registry_object_id', $link->registry_object_id)->first();
            if($ro->status == 'PUBLISHED' and $ro->class == 'collection' and
                !isset($linksArray[$url]["related_object_keys"][$ro->key])){
                if(!isset($linksArray[$url.'####'.$type])){
                    $linksArray[$url.'####'.$type] = array("url" => $url, "type" => $type,
                        "related_collection_keys" => array(), "related_collection_uuids" => array());
                }

                array_push($linksArray[$url.'####'.$type]["related_collection_keys"], $ro->key);

                $uuids = Identifier::where('registry_object_id',
                    $link->registry_object_id)->where('identifier_type', 'global')->get();
                foreach($uuids as $uuid) {
                    if(!isset($linksArray[$url.'####'.$type]["related_collection_uuids"][$uuid->identifier])){
                        array_push($linksArray[$url.'####'.$type]["related_collection_uuids"], $uuid->identifier);
                    }
                }
            }
        }
        return json_encode(array("services"=>$linksArray));
    }

}