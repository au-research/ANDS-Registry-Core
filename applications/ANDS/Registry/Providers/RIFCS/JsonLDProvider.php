<?php


namespace ANDS\Registry\Providers\RIFCS;


use ANDS\RecordData;
use ANDS\RegistryObject;
use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
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
        $json_ld->creator = self::getCreator($record,$data);
        $json_ld->citation = self::getCitation($record,$data);
        $json_ld->dateCreated = self::getDateCreated($record,$data);
        $json_ld->dateModified = self::getDateModified($record,$data);
        $json_ld->datePublished = DatesProvider::getPublicationDate($record);
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


    public static function getCitation(
        RegistryObject $record,
        $data = null
    )
    {
    $citation = [];

        $relationships = $data['relationships'];

        foreach ($relationships as $relation) {
            if (($relation->prop("to_class") == "collection" && $relation->prop("to_type")=="publication") || $relation->prop("to_related_info_type") == "publication"
            ) {
                if($relation->prop("to_title") != ""){
                    $citation[] = array("@type"=>"CreativeWork","name"=>$relation->prop("to_title"),"url"=>self::base_url() ."view?key=".$relation->prop("to_key"));
                }else{
                    $identifier =array(
                        "@type"=> "PropertyValue",
                        "propertyID"=> $relation->prop("to_identifier_type"),
                        "value"=> $relation->prop("to_identifier")
                    );
                    $citation[] = array("@type"=>"CreativeWork","name"=>$relation->prop("relation_to_title"),"identifier"=>$identifier);
                }
            }
        }

        return $citation;

    }

    public static function getDateCreated(
        RegistryObject $record,
        $data = null
    )
    {
        $dateCreated = [];
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:citationInfo/ro:citationMetadata/ro:date') AS $date) {
            if((string)$date['type']=='created') {
                $dateCreated[] = (string)$date;
                return $dateCreated;
            }
        };

        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:dates') AS $date) {
            if((string)$date['type']=='dc.created') {
                $dateCreated[] = (string)$date->date;
                return $dateCreated;
            }
        };

       // $dateCreated[] = DatesProvider::getCreatedDate($record);
        return $dateCreated;

    }

    public static function getDatePublished(
        RegistryObject $record,
        $data = null
    )
    {
        $datePublished[] = DatesProvider::getPublicationDate($record);

        return $datePublished;

    }


    public static function getDateModified(
        RegistryObject $record,
        $data = null
    )
    {
        $dateModified = [];
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class ) AS $recordAtt) {
             if((string)$recordAtt['dateModified']!='')   $dateModified[] = (string)$recordAtt['dateModified'];
        };

        return $dateModified;

    }

    public static function getCreator(
        RegistryObject $record,
        $data = null
    )
    {
        $creatorArray = array("IsPrincipalInvestigatorOf","author","coInvestigator","isOwnedBy","hasCollector");

        if (!$data) {
            $data = MetadataProvider::getSelective($record, ['recordData']);
        }
        $creator = [];

        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:citationInfo/ro:citationMetadata/ro:contributor') AS $contributor) {
            $name = (array)$contributor;
            $creator[]= array("@type"=>"Person","name"=>(implode(" ",$name['namePart'])));
        };

        if(count($creator)>0) return $creator;

        $relationships = $data['relationships'];
        foreach ($relationships as $relation) {
            foreach ($creatorArray as $creatorType)
            if (($relation->prop("to_class") == "party" || $relation->prop("to_related_info_type") == "party")
                && ($relation->prop('relation_type') == $creatorType) || in_array($creatorType, $relation->prop('relation_type'))
            ) {
                if($relation->prop("to_type")=='group'|| $relation->prop("to_related_info_type")=='group'){
                    $type = "Organization";
                } else{
                    $type = "Person";
                }

                if($relation->prop("to_title") != ""){
                    $creator[] = array("@type"=>$type,"name"=>$relation->prop("to_title"),"url"=>self::base_url() ."view?key=".$relation->prop("to_key"));
                }else{
                    $identifier =array(
                        "@type"=> "PropertyValue",
                        "propertyID"=> $relation->prop("to_identifier_type"),
                        "value"=> $relation->prop("to_identifier")
                    );
                    $creator[] = array("@type"=>$type,"name"=>$relation->prop("relation_to_title"),"identifier"=>$identifier);
                }

                return $creator;

            }
        }
        return $creator;
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

        if(count($author)>0) return $author;

        $relationships = $data['relationships'];

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

        if(count($author)>0) return $author;

        foreach ($relationships as $relation) {
            foreach($authorArray2 as $authorType)
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

        $relationships = $data['relationships'];

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