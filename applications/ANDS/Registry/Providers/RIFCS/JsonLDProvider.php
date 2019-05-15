<?php


namespace ANDS\Registry\Providers\RIFCS;


use ANDS\RecordData;
use ANDS\RegistryObject;
use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\Registry\Providers\GrantsConnectionsProvider;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\RIFCS\SubjectProvider;
use ANDS\Registry\Providers\LinkProvider;
use ANDS\Util\Config;
use ANDS\Util\XMLUtil;


/**
 * Class JsonLDProvider
 * @package ANDS\Registry\Providers
 */

class JsonLDProvider implements RIFCSProvider
{

    public static function base_url() {
        return Config::get('app.default_base_url');
    }

    public static function process(RegistryObject $record)
    {
        // CC-2143.
        // Do the truth test first and early return to fix super node issue
        if ($record->class <> "collection" && $record->class <> "service") return "";
        if ($record->class == "collection" && $record->type <> "collection" && $record->type <> "dataset" && $record->type <> "software") return "";

        $base_url = Config::get('app.default_base_url');

        $data = MetadataProvider::get($record);

        $json_ld = new JsonLDProvider();
        $json_ld->{'@context'} = "http://schema.org/";
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
            $json_ld->citation = self::getCitation($data);
            $json_ld->dateCreated = self::getDateCreated($record, $data);
            $json_ld->datePublished = DatesProvider::getPublicationDateForSchemadotOrg($record);
            $json_ld->alternativeHeadline = self::getAlternateName($record, $data);
            $json_ld->version = self::getVersion($record, $data);
            $json_ld->fileFormat = self::getFileFormat($record, $data);
            $json_ld->funder = self::getFunder($record);
            $json_ld->hasPart = self::getRelated($data, "hasPart");
            $json_ld->isBasedOn = self::getRelated($data, "isDerivedFrom");
            $json_ld->isPartOf = self::getRelated($data, "isPartOf");
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
        $json_ld->url = self::base_url() . "view?key=" . $record->key;

        $json_ld = (object) array_filter((array) $json_ld);
        return json_encode($json_ld);
    }

    public static function get(RegistryObject $record){
        return ;
    }

    public static function getIdentifier(
        RegistryObject $record,
        $data = null
    ){
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
       return $dateFrom . '/' . $dateTo;
    }


    public static function getPublisher(
        RegistryObject $record,
        $data = null
    ){
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:citationInfo/ro:citationMetadata/ro:publisher') AS $publisher) {

            $publishers = array("@type"=>"Organization","name"=>(string)$publisher);
            return $publishers;
        };

        $publishers = array("@type"=>"Organization","name"=>$record->group);

        return $publishers;
    }

    public static function getLicense(
        RegistryObject $record,
        $data = null
    ){
        $licenses = [];

        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:rights/ro:licence') AS $license) {
            $licenses[]= (string)$license;
            $licenses[]= (string)$license['type'];
            $licenses[]= (string)$license['rightsUri'];
        };

        return $licenses;
    }

    public static function getTermsOfService(
        RegistryObject $record,
        $data = null
    ){
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

    public static function getKeywords(
        RegistryObject $record
    ){
        $keywords = "";

        $subjects = SubjectProvider::processSubjects($record);
        foreach($subjects as $subject){
            $keywords .= ", ".$subject['resolved'];
        }
        return trim($keywords,",");
    }


    public static function getCitation(
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
                    $citation[] = array("@type"=>"CreativeWork","name"=>$relation->prop("relation_to_title"),"identifier"=>[$identifier]);
                }
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
            $geo = static::GeoCoordinates($coords);
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
     *
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
        $provider = GrantsConnectionsProvider::create();
        $unprocessed = $provider->getFunder($record);
        if($unprocessed) {
            $type = ($unprocessed->type=="group") ? "Organization" : "Person";
            $funders[] = array("@type" => $type, "name"=>$unprocessed->title, "url"=>self::base_url() ."view?key=".$unprocessed->key);
        }
        return $funders;

    }

    public static function getRelated(
        $data = null,
        $relation_type = null
    ){
        $related = [];
        $relationships = $data['relationships'];


        foreach ($relationships as $relation) {
            $relation_types = [];
            $relation_origins = [];

            if(is_array($relation->prop('relation_type'))){
                $relation_types = $relation->prop('relation_type');
            }else{
                $relation_types[] = $relation->prop('relation_type');
            }
            if(is_array($relation->prop('relation_type'))){
                $relation_origins = $relation->prop('relation_origin');
            }else{
                $relation_origins[] = $relation->prop('relation_origin');
            }

            for($i=0;$i<count($relation_origins);$i++){
                if(str_replace("REVERSE_GRANTS"," ",$relation_origins[$i])!=$relation_origins[$i]){
                    unset($relation_origins[$i]);
                    unset($relation_types[$i]);
                }
            }

            if ( ($relation->prop("to_class") == "collection" || $relation->prop("to_related_info_type") == "collection")
                && in_array($relation_type, $relation_types)) {
                if($relation->prop("to_title") != ""){
                    $related[] = array("@type"=>"CreativeWork","name"=>$relation->prop("to_title"),"url"=>self::base_url() ."view?key=".$relation->prop("to_key"));
                }else{
                    $related[] = array("@type"=>"CreativeWork","name"=>$relation->prop("relation_to_title"));
                }
            }
        }
        return $related;

    }

    public static function getProvider(
        $record,
        $data = null
    ){
        $related = [];
        $relationships = $data['relationships'];
        $provider_relationships = array("isOwnedBy", "isManagedBy");

        foreach ($relationships as $relation) {
            foreach ($provider_relationships as $provider_relationship) {
                $relation_types = [];
                if (is_array($relation->prop('relation_type'))) {
                    $relation_types = $relation->prop('relation_type');
                } else {
                    $relation_types[] = $relation->prop('relation_type');
                }
                if (($relation->prop("to_class") == "party" || $relation->prop("to_related_info_type") == "party")
                    && in_array($provider_relationship, $relation_types)
                ) {
                    if($relation->prop("to_type")=='group'|| $relation->prop("to_related_info_type")=='group'){
                        $type = "Organization";
                    } else{
                        $type = "Person";
                    }
                    if ($relation->prop("to_title") != "") {
                        $related[] = array("@type" => $type, "name" => $relation->prop("to_title"), "url" => self::base_url() . "view?key=" . $relation->prop("to_key"));
                    } else {
                        $related[] = array("@type" => $type, "name" => $relation->prop("relation_to_title"));
                    }
                }
            }
        }

        if(count($related) > 0) return $related;

        $related[] =array("@type" => "Organization", "name" => $record->group);

        return $related;

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

        return $dateCreated;

    }

    public static function getDistribution(
        RegistryObject $record,
        $data = null
    )
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
                $distArray["fileFormat"] = (string)$distribute->mediaType;
                if($notes) $distArray["description"] = $notes;
                $distribution[] = $distArray;
            }
        };
        return $distribution;
    }

    public static function getCodeRepository(
        RegistryObject $record,
        $data = null
    )
    {
        $codeRepository = [];
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic') AS $repository) {

            $codeRepository[] = (string)$repository->value;
             };
        return $codeRepository;
    }

    public static function getFileFormat(
        RegistryObject $record,
        $data = null
    )
    {
        $fileFormat = [];
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:location/ro:address/ro:electronic') AS $distribute) {
            if((string)$distribute['target']=='directDownload') {
                $fileFormat[] = (string)$distribute->mediaType;
            }
        };
        return $fileFormat;
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
        $creatorArray = array("isPrincipalInvestigatorOf","author","coInvestigator", "hasCollector");

        if (!$data) {
            $data = MetadataProvider::getSelective($record, ['recordData']);
        }
        $creator = [];

        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:citationInfo/ro:citationMetadata/ro:contributor') AS $contributor) {
            $names = (array)$contributor;
            if(is_array($names['namePart'])){
                $name = implode(" ",$names['namePart']);
            }else{
                $name = $names['namePart'];
            }
            $creator[]= array("@type"=>"Person","name"=>$name);
        };

        if(count($creator)>0) return $creator;

        $relationships = $data['relationships'];
        foreach ($relationships as $relation) {
            if (is_array($relation->prop('relation_type'))) {
                $relation_types = $relation->prop('relation_type');
            } else {
                $relation_types[] = $relation->prop('relation_type');
            }
            foreach ($creatorArray as $creatorType)
            if (($relation->prop("to_class") == "party" || $relation->prop("to_related_info_type") == "party")
                && in_array($creatorType, $relation_types)
            ) {
                if($relation->prop("to_type")=='group'|| $relation->prop("to_related_info_type")=='group'){
                    $type = "Organization";
                } else{
                    $type = "Person";
                }

                if($relation->prop("to_title") != ""){
                    $creator[] = array("@type"=>$type,"name"=>$relation->prop("to_title"),"url"=>self::base_url() ."view?key=".$relation->prop("to_key"));
                }else{
                    $creator[] = array("@type"=>$type,"name"=>$relation->prop("relation_to_title"));
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
            $names = (array)$contributor;
            if(is_array($names['namePart'])){
                $name = implode(" ",$names['namePart']);
            }else{
                $name = $names['namePart'];
            }
            $author[]= array("@type"=>"Person","name"=>$name);
        };

        if(count($author)>0) return $author;

        $relationships = $data['relationships'];

        foreach ($relationships as $relation) {
            if (is_array($relation->prop('relation_type'))) {
                $relation_types = $relation->prop('relation_type');
            } else {
                $relation_types[] = $relation->prop('relation_type');
            }
            foreach($authorArray as $auth_type) {
                if (($relation->prop("to_class") == "party" || $relation->prop("to_related_info_type") == "party")
                    && (in_array($auth_type, $relation_types))
                ) {
                    if ($relation->prop("to_type") == 'group' || $relation->prop("to_related_info_type") == 'group') {
                        $type = "Organization";
                    } else {
                        $type = "Person";
                    }

                    if ($relation->prop("to_title") != "") {
                        $author[] = array("@type" => $type, "name" => $relation->prop("to_title"), "url" => self::base_url() . "view?key=" . $relation->prop("to_key"));
                    } else {
                        $author[] = array("@type" => $type, "name" => $relation->prop("relation_to_title"));
                    }
                }
            }
        }

        if(count($author)>0) return $author;

        foreach ($relationships as $relation) {
            if (is_array($relation->prop('relation_type'))) {
                $relation_types = $relation->prop('relation_type');
            } else {
                $relation_types[] = $relation->prop('relation_type');
            }
            foreach($authorArray2 as $authorType)
            if (($relation->prop("to_class") == "party" || $relation->prop("to_related_info_type") == "party")
                && in_array($authorType,$relation_types)
            ) {
                if($relation->prop("to_type")=='group'||$relation->prop("to_related_info_type")=='group'){
                    $type = "Organization";
                } else{
                    $type = "Person";
                }

                if($relation->prop("to_title") != ""){
                    $author[] = array("@type"=>$type,"name"=>$relation->prop("to_title"),"url"=>self::base_url() ."view?key=".$relation->prop("to_key"));
                }else{
                    $author[] = array("@type"=>$type,"name"=>$relation->prop("relation_to_title"));
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

        $relationships = $data['relationships'];
        $usedRelTypes = ["isOwnedBy", "isOwnerOf", "isManagedBy", "isManagerOf"];
        $accountablePerson = [];

        foreach ($relationships as $relation) {
            $relation_types = [];
            if (is_array($relation->prop('relation_type'))) {
                $relation_types = $relation->prop('relation_type');
            } else {
                $relation_types[] = $relation->prop('relation_type');
            }
            if (($relation->prop("to_class") == "party" || $relation->prop("to_related_info_type") == "party")
                && (sizeof(array_intersect($usedRelTypes, $relation_types)) > 0)
            ) {

                if ($relation->prop("to_type") == 'group' || $relation->prop("to_related_info_type") == 'group') {
                    $type = "Organization";
                } else {
                    $type = "Person";
                }
                if ($relation->prop("to_title") != "") {
                    $accountablePerson[] = array("@type" => $type, "name" => $relation->prop("to_title"), "url" => self::base_url() . "view?key=" . $relation->prop("to_key"));
                } else {
                    $accountablePerson[] = array("@type" => $type, "name" => $relation->prop("relation_to_title"));
                }
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