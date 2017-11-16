<?php


namespace ANDS\Registry\Providers\RIFCS;


use ANDS\RecordData;
use ANDS\RegistryObject;
use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Util\Config;
use MinhD\SolrClient\SolrClient;
use MinhD\SolrClient\SolrSearchResult;
use ANDS\Util\XMLUtil;


/**
 * Class JsonLDProvider
 * @package ANDS\Registry\Providers
 */

class JsonLDProvider implements RIFCSProvider
{

    public function base_url() {
        return Config::get('app.default_base_url');
    }

    public static function process(RegistryObject $record){

        $base_url = Config::get('app.default_base_url');

        $data = MetadataProvider::get($record);
        $json_ld = new JsonLDProvider();
        $json_ld->{'@context'} = "http://schema.org/";
        if($record->type=='dataset'|| $record->type=='collection') {
            $json_ld->{'@type'} = "Dataset";
        }
        $json_ld->name = $record->title;
        $json_ld->accountablePerson = self::getAccountablePerson($record,$data);
        $json_ld->author = self::getAuthor($record,$data);
        $json_ld->alternateName = self::getAlternateName($record,$data);
        $json_ld->alternativeHeadline = self::getAlternateName($record,$data);
        $json_ld->version = self::getVersion($record,$data);
        $json_ld->description = self::getDescriptions($record,$data);
        $json_ld->sourceOrganization = array("@type"=>"Organization","name"=>$record->group);
        $json_ld->url = self::base_url() ."view?key=".$record->key;
        $json_ld = (object) array_filter((array) $json_ld);
        return '<script type="application/ld+json">'.json_encode($json_ld).'<script>';
    }

    public static function get(RegistryObject $record){
        return ;
    }

    public static function getAuthor(
        RegistryObject $record,
        $data = null
    )
    {
        $authorArray = array("IsPrincipalInvestigatorOf","author","coInvestigator");
        $authorArray2 = array("isOwnedBy","hasCollector");

        if (!$data) {
            $data = MetadataProvider::getSelective($record, ['recordData']);
        }
        $author = [];

        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:citationInfo/ro:citationMetadata/ro:contributor') AS $contributor) {
            $name = (array)$contributor;
            $author[]= array("@type"=>"Person","name"=>(implode(" ",$name['namePart'])));
        };

       // if(count($author)>0) return $author;

        $relationships = RelationshipProvider::getMergedRelationships($record);

        foreach ($relationships as $relation) {
            if (($relation->prop("to_class") == "party" || $relation->prop("to_related_info_type") == "party")
                && (in_array($relation->prop('relation_type'),$authorArray) || count(array_intersect($authorArray, $relation->prop('relation_type')))>0)
            ) {
                if($relation->prop("to_type")=='group'|| $relation->prop("to_related_info_type")=='group'){
                    $type = "Organization";
                } else{
                    $type = "Person";
                }

                if($relation->prop("to_title") != ""){
                    $author[] = array("@type"=>$type,"name"=>$relation->prop("to_title"),"url"=>self::base_url() ."view?key=".$relation->prop("to_key"));
                }else{
                    $identifier =array(
                        "@type"=> "PropertyValue",
                        "propertyID"=> $relation->prop("to_identifier_type"),
                        "value"=> $relation->prop("to_identifier")
                    );
                    $author[] = array("@type"=>$type,"name"=>$relation->prop("relation_to_title"),"identifier"=>$identifier);
                }
            }
        }

     //   if(count($author)>0) return $author;

        foreach ($relationships as $relation) {
            if (($relation->prop("to_class") == "party" || $relation->prop("to_related_info_type") == "party")
                && (in_array($relation->prop('relation_type'),$authorArray2) || count(array_intersect($authorArray2, $relation->prop('relation_type')))>0)
            ) {
                if($relation->prop("to_type")=='group'||$relation->prop("to_related_info_type")=='group'){
                    $type = "Organization";
                } else{
                    $type = "Person";
                }

                if($relation->prop("to_title") != ""){
                    $author[] = array("@type"=>$type,"name"=>$relation->prop("to_title"),"url"=>self::base_url() ."view?key=".$relation->prop("to_key"));
                }else{
                    $identifier =array(
                        "@type"=> "PropertyValue",
                        "propertyID"=> $relation->prop("to_identifier_type"),
                        "value"=> $relation->prop("to_identifier")
                    );
                    $author[] = array("@type"=>$type,"name"=>$relation->prop("relation_to_title").$relation->prop('relation_type'),"identifier"=>$identifier);
                }
            }
        }

        return $author;

    }


    public static function getAccountablePerson(
        RegistryObject $record,
        $data = null
    )
    {
        if (!$data) {
            $data = MetadataProvider::getSelective($record, ['recordData']);
        }
        $accountablePerson = [];
        // TODO: fix RelationshipProvider getMerged for Reverse overriding direct relationships
        $relationships = RelationshipProvider::getDirectRelationship($record);

        foreach ($relationships as $relation) {
            if ($relation->prop("to_class") == "party"
                && ($relation->prop("relation_type") == "isOwnedBy" || in_array("isOwnedBy", $relation->prop('relation_type')))
            ) {
                $accountablePerson[] = array("@type"=>"Person","name"=>$relation->prop("to_title"));
            }
        }

        return $accountablePerson;
    }

    public static function getDescriptions(
        RegistryObject $record,
        $data = null
    )
    {
        if (!$data) {
            $data = MetadataProvider::getSelective($record, ['recordData']);
        }

        $descriptions = [];
        foreach (XMLUtil::getElementsByXPath($data['recordData'],'ro:registryObject/ro:' . $record->class . '/ro:description') AS $description) {
            if((string)$description["type"]=="full" || (string)$description["type"]=="brief") {
                $descriptions[] = ((string)$description);
            }
        };

        return $descriptions;
    }

    public static function getVersion(
        RegistryObject $record,
        $data = null
    )
    {
        if (!$data) {
            $data = MetadataProvider::getSelective($record, ['recordData']);
        }

        $versions = '';
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:citationInfo/ro:citationMetadata/ro:version') AS $version) {
            $versions[]= ((string)$version);
        };

       return $versions;
    }


    public static function getAlternateName(
        RegistryObject $record,
        $data = null
    )
    {
        if (!$data) {
            $data = MetadataProvider::getSelective($record, ['recordData']);
        }

        $alternateNames = [];
        foreach (XMLUtil::getElementsByXPath($data['recordData'],'ro:registryObject/ro:' . $record->class . '/ro:name') AS $name) {
            if((string)$name["type"]=="alternative" || (string)$name["type"]=="abbreviated") {
                $alternateNames[] = (implode(" ",(array)$name->namePart));
            }
        };

        return $alternateNames;
    }
}
?>