<?php


namespace ANDS\Registry\Providers\RIFCS;


use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Registry\Versions;
use ANDS\RegistryObject;
use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\Registry\Providers\LinkProvider;
use ANDS\RegistryObject\RegistryObjectVersion;
use ANDS\Util\Config;
use ANDS\Util\XMLUtil;
use \ANDS\Registry\Schema;



/**
 * Class JsonLDProvider
 * @package ANDS\Registry\Providers
 */

class JsonLDProvider implements RIFCSProvider
{

    private static $schema_uri = "https://schema.org/";
    private static $origin = "REGISTRY";

    public static function base_url() {
        return Config::get('app.default_base_url');
    }

    public static function process(RegistryObject $record)
    {
        // CC-2143.
        // Do the truth test first and early return to fix super node issue
        if ($record->class <> "collection" && $record->class <> "service")
            return "";

        $allowedTypeList = ['collection','dataset','software'];
        if ($record->class == "collection" && !in_array(strtolower($record->type), $allowedTypeList))
            return "";

        $schema = Schema::get(static::$schema_uri);

        $altVersionsIDs = RegistryObjectVersion::where('registry_object_id', $record->id)->get()->pluck('version_id')->toArray();
        $existingVersion = null;
        if (count($altVersionsIDs) > 0) {
            $existingVersion = Versions::wherein('id', $altVersionsIDs)->where("schema_id", $schema->id)->first();
        }
        // don't generate one if we have an other instance from different origin eg HARVESTER, BUT  THIS MIGHT CHANGE !!
        if($existingVersion && $existingVersion->origin != static::$origin)
            return $existingVersion->data;

        $data = [];

        $data['recordData'] = $record->getCurrentData()->data;

        $json_ld = new JsonLDProvider();
        $json_ld->{'@context'} = static::$schema_uri;
        if ($record->type == 'dataset' || $record->type == 'collection') {
            $json_ld->{'@type'} = "Dataset";
            $json_ld->distribution = self::getDistribution($record, $data);

        }
        if ($record->type == 'software' && $record->class == "collection") {
            $json_ld->codeRepository = self::getCodeRepository($record, $data);
            $json_ld->{'@type'} = "SoftwareSourceCode";
        }

        if ($record->class == 'service') {
            $json_ld->{'@type'} = "Service";
            $json_ld->serviceType = $record->type;
            $json_ld->provider = self::getProvider($record, $data);
            $json_ld->termsOfService = self::getTermsOfService($record, $data);
        } elseif ($record->class == 'collection'){
            $json_ld->accountablePerson = self::getAccountablePerson($record, $data);
            $json_ld->creator = self::getCreator($record, $data);
            $json_ld->citation = self::getCitation($record);
            $json_ld->dateCreated = self::getDateCreated($record, $data);
            $json_ld->datePublished = DatesProvider::getPublicationDateForSchemadotOrg($record);
            $json_ld->alternativeHeadline = self::getAlternateName($record, $data);
            $json_ld->version = self::getVersion($record, $data);
            $json_ld->encodingFormat = self::getEncodingFormat($record, $data);
            $json_ld->funder = self::getFunder($record);
            $json_ld->hasPart = self::getRelated($record, array("hasPart"));
            $json_ld->isBasedOn = self::getRelated($record, array("isDerivedFrom"));
            $json_ld->isPartOf = self::getRelated($record, array("isPartOf"));
            $json_ld->sourceOrganization = array("@type" => "Organization", "name" => $record->group);
            $json_ld->keywords = self::getKeywords($record);
            $json_ld->license = self::getLicense($record, $data);
            $json_ld->publisher = self::getPublisher($record, $data);
            $json_ld->spatialCoverage = self::getSpatialCoverage($record, $data);
            $json_ld->temporalCoverage = self::getTemporalCoverage($record, $data);
            $json_ld->inLanguage = "en";
        }

        $json_ld->name = $record->title;
        $json_ld->description = self::getDescriptions($record, $data);
        $json_ld->alternateName = self::getAlternateName($record, $data);
        $json_ld->identifier = self::getIdentifier($record, $data);
        $json_ld->url = self::base_url() . $record->slug . "/" . $record->id;

        $json_ld = (object) array_filter((array) $json_ld);
        $record->addVersion(json_encode($json_ld), static::$schema_uri, static::$origin);

        return json_encode($json_ld);
    }

    public static function get(RegistryObject $record)
    {
        // obtaining existing versions
        $schema = Schema::get(static::$schema_uri);
        $altVersionsIDs = RegistryObjectVersion::where('registry_object_id', $record->id)->get()->pluck('version_id')->toArray();
        $existingVersion = null;

        if (count($altVersionsIDs) > 0) {
            $existingVersion = Versions::wherein('id', $altVersionsIDs)->where("schema_id", $schema->id)->first();
            if ($existingVersion) {
                if ($record->modified_at > $existingVersion->updated_at){
                    return static::process($record);
                }
                return $existingVersion->data;
            }
        }

        return static::process($record);
    }

    public static function getIdentifier(RegistryObject $record, $data = null){
        $identifiers = [];

        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:citationInfo/ro:citationMetadata/ro:identifier') AS $identifier) {
            $identifier_url = LinkProvider::getResolvedLinkForIdentifier((string)$identifier['type'],(string)$identifier);
            if($identifier_url) {
                $identifiers[] = array( "@type"=> "PropertyValue",
                    "propertyID"=> "URL",
                    "value"=> $identifier_url
                );
            }else{
                $identifiers[] = array( "@type"=> "PropertyValue",
                    "propertyID"=> (string)$identifier['type'],
                    "value"=> (string)$identifier
                );
            }
        };

        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:identifier') AS $identifier) {
            $identifier_url = LinkProvider::getResolvedLinkForIdentifier((string)$identifier['type'],(string)$identifier);
            if($identifier_url) {
                $identifiers[] = array( "@type"=> "PropertyValue",
                    "propertyID"=> "URL",
                    "value"=> $identifier_url
                );
            }else{
                $identifiers[] = array( "@type"=> "PropertyValue",
                    "propertyID"=> (string)$identifier['type'],
                    "value"=> (string)$identifier
                );
            }
         };
        return $identifiers;
    }


    public static function getSpatialCoverage(
        RegistryObject $record,
        $data = null
    ){
        $coverages = [];

        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:coverage/ro:spatial') AS $coverage) {

            $processabelTypes = ["iso19139dcmiBox", "dcmiPoint", "gmlKmlPolyCoords", "kmlPolyCoords"];

            $type = (string)$coverage['type'];
            if(in_array($type, $processabelTypes)){
                $coverage = static::processCoordinates($type, (string)$coverage);
                if($coverage != null)
                    $coverages[] = $coverage;
            }
            else{
                $coverages[] = array("@type" => "Place", "description" => $coverage['type'] . " " . (string)$coverage);
            }
        };

        return $coverages;
    }

    public static function getTemporalCoverage(
        RegistryObject $record,
        $data = null
    ){
        $coverages = [];

        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:coverage/ro:temporal/ro:date') AS $coverage) {
            $type = (string) $coverage['type'];
            $coverages[$type] = (string)$coverage;
        };


        return static::formatTempCoverages($coverages);
    }

    public static function formatTempCoverages($coverages){

        $dateFrom = "..";
        $dateTo = "..";
        foreach ($coverages as $type=>$date)
        {
            $date = DatesProvider::formatDate($date);
            if($type == 'dateFrom' && $date != null){
                    $dateFrom = $date;
            }
            if($type == "dateTo" && $date != null)
                $dateTo = $date;
        }
        $formattedDate =  $dateFrom . '/' . $dateTo;

        if($formattedDate == '../..')
            return null;

        return $formattedDate;
    }


    public static function getPublisher(RegistryObject $record, $data = null){
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:citationInfo/ro:citationMetadata/ro:publisher') AS $publisher) {

            $publishers = array("@type"=>"Organization","name"=>(string)$publisher);
            return $publishers;
        };

        $publishers = array("@type"=>"Organization","name"=>$record->group);

        return $publishers;
    }

    public static function getLicense(RegistryObject $record, $data = null){
        $licenses = [];
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:rights/ro:licence') AS $license) {
            if((string)$license != '')
                $licenses[]= (string)$license;
            if((string)$license['type'] != '')
                $licenses[]= (string)$license['type'];
            if((string)$license['rightsUri'] != '')
                $licenses[]= (string)$license['rightsUri'];
        };
        return $licenses;
    }

    public static function getTermsOfService(RegistryObject $record, $data = null){
        $termsOfService = [];

        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:rights/ro:licence') AS $right) {
            $termsOfService[]= (string)$right;
            $termsOfService[]= (string)$right['type'];
            $termsOfService[]= (string)$right['rightsUri'];
        };
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:rights/ro:accessRights') AS $right) {
            $termsOfService[]= (string)$right;
            $termsOfService[]= (string)$right['rightsUri'];
        };

        return $termsOfService;
    }

    public static function getKeywords(RegistryObject $record){
        $keywords = "";

        $subjects = SubjectProvider::processSubjects($record);
        foreach($subjects as $subject){
            $keywords .= ", ".$subject['resolved'];
        }
        return trim($keywords,",");
    }


    public static function getCitation(RegistryObject $record)
    {
        $citation = [];

        $relationships = RelationshipProvider::getRelationByClassTypeRelationType($record, null, 'publication', null);
        foreach ($relationships as $relation) {
            if($relation['to_url'] != ""){
                $citation[] = array("@type"=>"CreativeWork","name"=>$relation['to_title'],"url"=> $relation["to_url"]);
            }else{
                $identifier =array(
                    "@type"=> "PropertyValue",
                    "propertyID"=> $relation["to_identifier_type"],
                    "value"=> $relation["to_identifier"]
                );
                $citation[] = array("@type"=>"CreativeWork","name"=>$relation['to_title'],"identifier"=>[$identifier]);
            }
        }
        return $citation;

    }

    public static function processCoordinates($type, $coords){

        $geo = null;

        if($type == "iso19139dcmiBox"){
            $geo = static::getGeo($coords);
        }
        elseif($type == "dcmiPoint"){
            $geo = static::getCoordinates($coords);
        }
        elseif($type == "gmlKmlPolyCoords"|| $type == "kmlPolyCoords") {
            $geo = static::getBoxFromCoords($coords);
        }

        if($geo == null)
            return null;

        return array("@type" => "Place", "geo" =>$geo);
    }

    /*
     * JIRA  CC-2360
     * simplifies a list of lat long coordinates to produces a bounding box
     * Currently not displaying in Google Dataset Search.
     * ("box" GeoShapes display, but GeoShape "polygon" doesn't seem to display).
     * If polygon is not supported going forward we can produce a box which has the poly extents.
     * Send a query to Google to see if they will support "polygon"?
     * https://developers.google.com/search/docs/data-types/dataset
     */
    public static function getBoxFromCoords($coords){
        $north = -90;
        $south = 90;
        $west = 180;
        $east = -180;
        $coordsArray = explode(" ", $coords);
        $geo = null;

        if (sizeof($coordsArray) > 1) {
            foreach( $coordsArray as $latLon){
                $latLon = $coordsArray = explode(",", $latLon);
                if(is_numeric($latLon[1])){
                    if($north < $latLon[1])
                        $north = $latLon[1];
                    if($south > $latLon[1])
                        $south = $latLon[1];
                }
                if(is_numeric($latLon[0])){
                    if($east < $latLon[0])
                        $east = $latLon[0];
                    if($west > $latLon[0])
                        $west= $latLon[0];
                }
            }
            $geo =array("@type" => "GeoShape", "box" => $south. " " .$west. " " . $north. " " .$east );
        } else {
            $latLon = $coordsArray = explode(",", $coordsArray[0]);
            if(is_numeric($latLon[1]) && is_numeric($latLon[0]))
                $geo = array("@type" => "GeoCoordinates", "latitude" => $latLon[1], "longitude" => $latLon[0]);
        }

        return $geo;
    }

    public static function getCoordinates($coords){
        $tok = strtok($coords, ";");
        $north = null;
        $east = null;
        $geo = null;
        while ($tok !== false) {
            $keyValue = explode("=", $tok);
            if (strtolower(trim($keyValue[0])) == 'north' && is_numeric($keyValue[1])) {
                $north = floatval($keyValue[1]);
            }
            if (strtolower(trim($keyValue[0])) == 'east' && is_numeric($keyValue[1])) {
                $east = floatval($keyValue[1]);
            }
            $tok = strtok(";");
        }
        if(is_numeric($north) && is_numeric($east))
            $geo = array("@type"=>"GeoCoordinates", "latitude"=>$north, "longitude"=>$east);

        return $geo;
    }

    /*
     *  dcmiText not sure how it worked before but
     *  according to the schema.org specs the box element needs to have the actual coordinates not dcmibox attributes
     *
     */
    public static function getGeo($dcmiText){
        $tok = strtok($dcmiText, ";");
        $north = null;
        $south = null;
        $west = null;
        $east = null;
        $geo = null;
        while ($tok !== false) {
            $keyValue = explode("=", $tok);
            if (strtolower(trim($keyValue[0])) == 'northlimit' && is_numeric($keyValue[1])) {
                $north = floatval($keyValue[1]);
            }
            if (strtolower(trim($keyValue[0])) == 'southlimit' && is_numeric($keyValue[1])) {
                $south = floatval($keyValue[1]);
            }
            if (strtolower(trim($keyValue[0])) == 'westlimit' && is_numeric($keyValue[1])) {
                $west = floatval($keyValue[1]);
            }
            if (strtolower(trim($keyValue[0])) == 'eastlimit' && is_numeric($keyValue[1])) {
                $east = floatval($keyValue[1]);
            }
            $tok = strtok(";");
        }
        if(is_numeric($north) && is_numeric($east) && is_numeric($south) && is_numeric($west)){
            if ($north == $south && $east == $west) {
                $geo = array("@type"=>"GeoCoordinates","latitude"=>$north, "longitude"=>$east);
            } else {
                $geo = array("@type" => "GeoShape", "box" => $south. " " .$west. " " . $north. " " .$east );
            }
        }

        return $geo;

    }


    public static function getFunder(RegistryObject $record)
    {
        $funders = [];
        $relationships = RelationshipProvider::getRelationByClassAndType($record, 'party', ['isFundedBy']);
        var_dump($relationships);
        foreach($relationships as $relationship) {
                $type = ($relationship['to_type'] == "group") ? "Organization" : "Person";
                $funders[] = array("@type" => $type, "name" => $relationship['to_title'], "url" =>  $relationship['to_url']);

        }
        return $funders;
    }

    public static function getRelated(RegistryObject $record, $relation_type = array())
    {
        $related = [];

        $relationships = RelationshipProvider::getRelationByType($record, $relation_type);

        foreach ($relationships as $relation) {
            if($relation["to_url"] != ""){
                $related[] = array("@type"=>"CreativeWork","name"=>$relation["to_title"], "url"=> $relation["to_url"]);
            }else{
                $identifier =array(
                    "@type"=> "PropertyValue",
                    "propertyID"=> $relation["to_identifier_type"],
                    "value"=> $relation["to_identifier"]
                );
                $related[] = array("@type"=>"CreativeWork","name"=>$relation["to_title"], "identifier"=>[$identifier]);
            }
        }
        return $related;

    }

    public static function getProvider(RegistryObject $record)
    {
        $related = [];
        $provider_relationships = array("isOwnedBy", "isManagedBy");

        $relationships = RelationshipProvider::getRelationByType($record, $provider_relationships);
        var_dump($relationships);
        foreach ($relationships as $relation) {
            if ($relation["to_type"] == 'group') {
                $type = "Organization";
            } else {
                $type = "Person";
            }
            if ($relation["to_url"] != "") {
                $related[] = array("@type" => $type, "name" => $relation["to_title"], "url" => $relation["to_url"]);

            } else {
                $identifier =array(
                    "@type"=> "PropertyValue",
                    "propertyID"=> $relation["to_identifier_type"],
                    "value"=> $relation["to_identifier"]
                );
                $related[] = array("@type" => $type, "name" => $relation["to_title"], "identifier" => $identifier);
            }
        }

        if(count($related) > 0) return $related;

        $related[] =array("@type" => "Organization", "name" => $record->group);

        return $related;

    }



    public static function getDateCreated(RegistryObject $record, $data = null)
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

        return $dateCreated;

    }

    public static function getDistribution(RegistryObject $record, $data = null)
    {
        $distribution = [];
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic') AS $distribute) {
            if((string)$distribute['target']=='directDownload') {
                $notes = (trim((string)$distribute->notes)!="")? trim((string)$distribute->notes) : null;
                $distArray = [];
                $distArray["@type"] = "DataDownload";
                $distArray["contentSize"] = (string)$distribute->byteSize;
                $distArray["contentUrl"] = (string)$distribute->value;
                $distArray["encodingFormat"] = (string)$distribute->mediaType;
                if($notes) $distArray["description"] = $notes;
                $distribution[] = $distArray;
            }
        };
        return $distribution;
    }

    public static function getCodeRepository(RegistryObject $record, $data = null)
    {
        $codeRepository = [];
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic') AS $repository) {

            $codeRepository[] = (string)$repository->value;
             };
        return $codeRepository;
    }

    public static function getEncodingFormat(RegistryObject $record, $data = null)
    {
        $encodingFormat = [];
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic') AS $distribute) {
            if((string)$distribute['target']=='directDownload') {
                $encodingFormat[] = (string)$distribute->mediaType;
            }
        };
        return $encodingFormat;
    }


    public static function getDateModified(RegistryObject $record, $data)
    {
        $dateModified = [];
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class ) AS $recordAtt) {
             if((string)$recordAtt['dateModified']!='')   $dateModified[] = (string)$recordAtt['dateModified'];
        };
        return $dateModified;

    }

    public static function getCreator(RegistryObject $record, $data)
    {

        $creator = [];
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:citationInfo/ro:citationMetadata/ro:contributor') AS $contributor) {
            $names = (array)$contributor;
            if(is_array($names['namePart'])){
                $name = implode(" ",$names['namePart']);
            }else{
                $name = $names['namePart'];
            }
            $creator[] = array("@type"=>"Person","name"=>$name);
        };

        if(sizeof($creator) > 0)
            return $creator;

        $relations_types = array("hasPrincipalInvestigator","author","coInvestigator", "hasCollector");
        foreach ($relations_types as $idx=>$relation_type) {
            $relationships = RelationshipProvider::getRelationByType($record, array($relation_type));
            foreach ($relationships as $relation) {
                if ($relation["class"] != 'party') // shouldn't happen with these relationship types but to be sure
                {
                    continue;
                }
                if ($relation["to_type"] == 'group') {
                    $type = "Organization";
                } else {
                    $type = "Person";
                }
                if ($relation["to_url"] != '') {
                    $creator[] = array("@type" => $type, "name" => $relation["to_title"], "url" => $relation["to_url"]);
                } else {
                    $identifier =array(
                        "@type"=> "PropertyValue",
                        "propertyID"=> $relation["to_identifier_type"],
                        "value"=> $relation["to_identifier"]
                    );
                    $creator[] = array("@type" => $type, "name" => $relation["to_title"], "identifier" =>  $identifier);
                }
            }
            if(sizeof($creator) > 0)
                return $creator;
        }
        return $creator;
    }


    public static function getAccountablePerson(RegistryObject $record)
    {
        $relations_types = ["isOwnedBy", "isManagedBy"];
        $processedIds = [];
        $accountablePerson = [];
        foreach ($relations_types as $idx=>$relation_type) {
            $relationships = RelationshipProvider::getRelationByType($record, array($relation_type));
            foreach ($relationships as $relation) {
                // check for class == party in case shouldn't happen with these relationship types but to be sure
                if ($relation["to_class"] != 'party' || $relation["type"] == 'group' || in_array_r($relation["id"] , $processedIds)) {
                    continue;
                }
                $processedIds[] = $relation["id"];
                if ($relation["to_url"] != "") {
                    $accountablePerson[] = array("@type" => "Person", "name" => $relation["to_title"], "url" => $relation["to_url"]);
                } else {
                    $identifier =array(
                        "@type"=> "PropertyValue",
                        "propertyID"=> $relation["to_identifier_type"],
                        "value"=> $relation["to_identifier"]
                    );
                    $accountablePerson[] = array("@type" => "Person", "name" => $relation["to_title"], "identifier" => $identifier);
                }
            }
            if(sizeof($accountablePerson) > 0)
                return $accountablePerson;
        }
        return $accountablePerson;
    }

    public static function getDescriptions(RegistryObject $record, $data = null)
    {

        $types= array ("brief","full");

        $descriptions = [];
        foreach($types as $type) {
            foreach (XMLUtil::getElementsByXPath($data['recordData'],'ro:registryObject/ro:' . $record->class . '/ro:description') AS $description) {
                if ((string)$description["type"] == $type) {
                    $descriptions[] = ((string)$description);
                    return $descriptions;
                }
            }
        };

        return $descriptions;
    }

    public static function getVersion(RegistryObject $record, $data = null)
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


    public static function getAlternateName(RegistryObject $record, $data = null)
    {
        if (!$data) {
            $data = MetadataProvider::getSelective($record, ['recordData']);
        }

        $types = array("alternative","abbreviated");

        $alternateNames = [];
        foreach($types as $type) {
            foreach (XMLUtil::getElementsByXPath($data['recordData'], 'ro:registryObject/ro:' . $record->class . '/ro:name') AS $name) {
                if ((string)$name["type"] == $type ) {
                    $alternateNames[] = (implode(" ", (array)$name->namePart));
                    return $alternateNames;
                }
            };
        }

        return $alternateNames;
    }

}
?>