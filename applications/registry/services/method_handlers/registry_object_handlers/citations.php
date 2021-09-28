<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');


/**
 * Citations handler
 * @author Liz Woods <liz.woods@ands.org.au>
 * @param  string type
 * @return array
 */
use ANDS\Registry\Providers\RIFCS\TitleProvider;
use ANDS\RegistryObject;
class Citations extends ROHandler {

    public $ro;

	function handle() {

        $result = array();


        if ($this->xml) {
            $coins = $this->getCoinsSpan();
            foreach($this->xml->{$this->ro->class}->citationInfo as $citation){
                foreach($citation->citationMetadata as $citationMetadata){
                    $contributors = Array();
                    foreach($citationMetadata->contributor as $contributor)
                    {
                        $nameParts = Array();
                        foreach($contributor->namePart as $namePart)
                        {
                            $nameParts[] = array(
                                'namePart_type' => (string)$namePart['type'],
                                'name' => (string)$namePart
                            );
                        }
                        $contributors[] =array(
                            'name' => $nameParts,
                            'seq' => (string)$contributor['seq'],
                        );
                    }
                    usort($contributors,"seq");
                    $displayNames ='';
                    $contributorCount = 0;
                    foreach($contributors as $contributor){
                        $contributorCount++;
                        $displayNames .= formatName($contributor['name']);
                        if($contributorCount < count($contributors)) $displayNames .= "; ";
                    }
                    $identifierResolved = identifierResolution((string)$citationMetadata->identifier, (string)$citationMetadata->identifier['type']);

                    $publicationDate = (string)$citationMetadata->date;
                    if(strlen($citationMetadata->date) > 4){
                        $publicationDate = date("Y",strtotime($citationMetadata->date));
                    }

                    $result[] = array(
                        'type'=> 'metadata',
                        'identifier' => (string)$citationMetadata->identifier,
                        'identifier_type' => strtoupper((string)$citationMetadata->identifier['type']),
                        'identifierResolved' => $identifierResolved,
                        'version' => (string)$citationMetadata->version,
                        'publisher' => (string)$citationMetadata->publisher,
                        'url' => (string)$citationMetadata->url,
                        'context' => (string)$citationMetadata->context,
                        'placePublished' => (string)$citationMetadata->placePublished,
                        'title' => (string)$citationMetadata->title,
                        'date_type' => (string)$citationMetadata->date['type'],
                        'date' =>  $publicationDate,
                        'contributors' => $displayNames,
                        'coins' =>$coins
                    );

                }
                foreach($citation->fullCitation as $fullCitation){
                    $result[] = array(
                        'type'=> 'fullCitation',
                        'value' => (string)$fullCitation,
                        'citation_type' => (string)$fullCitation['style'],
                        'coins' => $coins
                    );

                }
            }
            if(!$result){
                $result[] = array(
                    'type'=> 'fullCitation',
                    'value' => '',
                    'citation_type' => '',
                    'coins' => $coins
                );
            }
         }

        return $result;
	}

    public function getEndnoteText()
    {
        $endNote = 'Provider: Australian Research Data Commons
Database: Research Data Australia
Content:text/plain; charset="utf-8"


';

        $type = $this->getType();
        $endNote .= "TY  - ".$type."
";

        $endNote .= 'Y2  - '.date("Y-m-d")."
";

        $doi = $this->getDoi();
        if($doi!=''){
            $endNote .= "DO  - ".$doi."
";
        }

        $publicationDate = $this->getPublicationDate();
        if($publicationDate!='') {
            $endNote .= "PY  - ".$publicationDate."
";
        }

        $contributors = $this->getContributors();
        if($contributors!=''){
            foreach($contributors as $contributor){
                if(isset($contributor['to_id'])){
                    $record = RegistryObject::find($contributor['to_id']);
                    $names = TitleProvider::get($record);
                    $nameParts = '';
                    foreach($names["raw"][0]["value"] as $namePart=>$nameParts_raw){
                        $nameParts[] = array(
                            'namePart_type' => (string)$nameParts_raw['@attributes']['type'],
                            'name' => (string)$nameParts_raw['value']
                        );
                    }
                    $contributor["name"] = formatName($nameParts);
                }
                $endNote .= "AU  - ".$contributor['name']."
";
            }
        }
        else{
            $endNote .= "AU  - Anonymous
";
        }

        $funders = $this->getFunders();
        foreach($funders as $funder){
            $endNote .= "A4  - ".$funder."
";
        }

        $endNote .= "TI  - ".$this->getTitle()."
";

        $sourceUrl = $this->getSourceUrl($output='endNote');
        if($sourceUrl!=''){
            $endNote .= "UR  - ".$sourceUrl."
";
        }

        $publisher = $this->getPublisher();
        if($publisher!=''){
            $endNote .= "PB  - ".$publisher."
";
        }

        $createdDate = $this->getCreatedDate();
        if($createdDate!=''){
            $endNote .= "DA  - ".$createdDate."
";
        }

        $version = $this->getVersion();
        if($version!=''){
            $endNote .= "ET  - ".$version."
";
        }

        $endNote .="LA  - English
";
        $rights = $this->ro->processLicence();
        foreach($rights as $right) {
            if($right['value']!='') $endNote .="C5  - ".$right['value']."
";
        }

        $keywords = $this->getKeywords();
        foreach($keywords as $keyword) {
            $endNote .="KW  - ".$keyword."
";
        }

        $spatials = $this->getSpatial();
        foreach($spatials as $spatial) {
            $endNote .="RI  - ".$spatial."
";
        }

        $notes = $this->getNotes();
        foreach($notes as $note) {
            $endNote .="N1  - ".$note."
";
        }

        $dates = $this->getDates();
        foreach($dates as $date) {
            $endNote .="C1  - ".$date."
";
        }

        $descriptions = $this->getDescriptions();
        foreach($descriptions as $description) {
            $endNote .="AB  - ".$description."
";
        }

        $endNote .= "ER  -
";

        return html_entity_decode($endNote);
    }

    private function getCoinsSpan()
    {
        $coins = '';


        $rft_id =  $this->getSourceUrl($output='coins');
        $rft_identifier = $this->getIdentifier();
        $rft_publisher = $this->getPublisher();
        $descriptions = $this->getDescriptions();
        $rft_description = '';
        foreach($descriptions as $description){
            $rft_description .= $description;
        }
        $rft_creators = '';
        $creators= $this->getContributors();
        foreach($creators as $creator){
            $rft_creators .= '&rft.creator='.$creator['name'];
        }

        $rft_date = $this->getPublicationdate();

        $rights  = $this->ro->processLicence();

        $rft_rights = '';
        foreach($rights as $right){
            if(isset($right['type'])){
                if($right['type'] == 'rightsStatement' || $right['type'] == 'licence'){
                    if(isset($right['value'])) $rft_rights .= '&rft_rights='.$right['value'];
                    if(isset($right['rightsUri'])) $rft_rights .= " ".$right['rightsUri'];
                }
            }
        }
       // return $rft_rights;
        $subjects = $this->getKeywords();
        $rft_subjects = '';
        foreach($subjects as $subject){
            $rft_subjects .= '&rft_subject='.$subject;
        }
        $rft_edition = $this->getVersion();
        $coverages = $this->getSpatial();
        $rft_coverages = '';
        foreach($coverages as $coverage){
            $rft_coverages .= '&rft.coverage='.$coverage;
        }

        $relations = $this->getRelation();
        $rft_relations = '';
        foreach($relations as $relation){
            $rft_relations .= '&rft.relation='.$relation;
        }
        $rft_place = $this->getPlace();
        $coins .=  'ctx_ver=Z39.88-2004&rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Adc&rfr_id=info%3Asid%2FANDS';
        if($rft_id) $coins .= '&rft_id='.$rft_id;
        $coins .= '&rft.title='.$this->getTitle();
        if($rft_identifier) $coins .= '&rft.identifier='.$rft_identifier;
        if($rft_publisher) $coins .= '&rft.publisher='.$rft_publisher;
        $coins .= '&rft.description='.$rft_description.$rft_creators;
        if($rft_date) $coins .= '&rft.date='.$rft_date;
        if($rft_edition) $coins .= '&rft.edition='.$rft_edition;
        if($rft_relations) $coins .= $rft_relations;
        if($rft_coverages) $coins .=  $rft_coverages;
        if($rft_rights) $coins .= $rft_rights;
        if($rft_subjects) $coins .= $rft_subjects;
        if($rft_place) $coins .= '&rft_place='.$rft_place;
        if($this->getType()=='COMP'){
            $type = 'Computer Program';
        }else{
            $type = 'dataset';
        }
        $coins .= '&rft.type='.$type.'&rft.language=English';


        return $coins;
    }

    function getDoi(){

        $doi = '';
        $query = '';
        if($this->gXPath->evaluate("count(//ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi'])")>0) {
            $query = "//ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi']";
        }
        elseif($this->gXPath->evaluate("count(//ro:collection/ro:identifier[@type='doi'])")>0) {
            $query = "//ro:collection/ro:identifier[@type='doi']";
        }

        if($query!=''){
            $dois = $this->gXPath->query($query);
            foreach($dois as $doivalue) {
                $doi = $doivalue->nodeValue;
            }
            if(strpos($doi,"doi.org/")) {
                $doi = substr($doi,strpos($doi,"doi.org/")+8);
            }
        }
        return $doi;

    }
    function getIdentifier(){

        $identifier = '';
        $query = '';
        if($this->gXPath->evaluate("count(//ro:citationInfo/ro:citationMetadata/ro:identifier)")>0) {
            $query = "//ro:citationInfo/ro:citationMetadata/ro:identifier";
        }
        elseif($this->gXPath->evaluate("count(//ro:collection/ro:identifier)")>0) {
            $query = "//ro:collection/ro:identifier";
        }

        if($query!=''){
            $identifiers = $this->gXPath->query($query);
            foreach($identifiers as $identifiervalue) {
                $identifier = $identifiervalue->nodeValue;
            }
                    }
        return $identifier;

    }
    function getPublicationDate()
    {
        $publicationDate = '';
        $query = '';
        if($this->gXPath->evaluate("count(//ro:citationInfo/ro:citationMetadata/ro:date[@type='publicationDate'])")>0) {
            $query = "//ro:citationInfo/ro:citationMetadata/ro:date[@type='publicationDate']";
        }
        elseif($this->gXPath->evaluate("count(//ro:citationInfo/ro:citationMetadata/ro:date[@type='issued'])")>0) {
            $query = "//ro:citationInfo/ro:citationMetadata/ro:date[@type='issued']";
        }
        elseif($this->gXPath->evaluate("count(//ro:citationInfo/ro:citationMetadata/ro:date[@type='created'])")>0) {
            $query = "//ro:citationInfo/ro:citationMetadata/ro:date[@type='created']";
        }
        elseif($this->gXPath->evaluate("count(//ro:collection/ro:dates[@type='dc.issued'])")>0) {
            $query = "//ro:collection/ro:dates[@type='dc.issued']";
        }
        elseif($this->gXPath->evaluate("count(//ro:collection/ro:dates[@type='dc.available'])")>0) {
            $query = "//ro:collection/ro:dates[@type='dc.available']";
        }
        elseif($this->gXPath->evaluate("count(//ro:collection/ro:dates[@type='dc.created'])")>0) {
            $query = "//ro:collection/ro:dates[@type='dc.created']";
        }
        elseif($this->gXPath->evaluate("count(//ro:collection/@dateModified[. != ''])")>0) {
            $query = "//ro:collection/@dateModified";
        }
        elseif($this->gXPath->evaluate("count(//ro:collection/@dateAccessioned[. != ''])")>0) {
            $query = "//ro:collection/@dateAccessioned";
        }
        if($query!=''){

            $dates = $this->gXPath->query($query);
            foreach($dates as $date) {
                //assume only Year is given
                if(strlen($date->nodeValue) <= 4){
                    $publicationDate = $date->nodeValue;
                }
                else{
                    $publicationDate = date("Y",strtotime($date->nodeValue));
                }
            }
        }else{
            $publicationDate = date("Y",$this->ro->created);
        }

        return  $publicationDate;
    }

    function getSourceUrl($output=null)
    {
        $sourceUrl = '';
        $query = '';
        if($this->gXPath->evaluate("count(//ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi'])")>0) {
            $query = "//ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi']";
            $type = 'doi';
        }
        elseif($this->gXPath->evaluate("count(//ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='handle'])")>0) {
            $query = "//ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='handle']";
            $type = 'handle';
        }
        elseif($this->gXPath->evaluate("count(//ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='uri'])")>0) {
            $query = "//ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='uri']";
            $type = 'uri';
        }
        elseif($this->gXPath->evaluate("count(//ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='purl'])")>0) {
            $query = "//ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='purl']";
            $type = 'purl';
        }
        elseif($this->gXPath->evaluate("count(//ro:collection/ro:identifier[@type='doi'])")>0) {
            $query = "//ro:collection/ro:identifier[@type='doi']";
            $type = 'doi';
        }
        elseif($this->gXPath->evaluate("count(//ro:collection/ro:identifier[@type='handle'])")>0) {
            $query = "//ro:collection/ro:identifier[@type='handle']";
            $type = 'handle';
        }
        elseif($this->gXPath->evaluate("count(//ro:collection/ro:identifier[@type='uri'])")>0) {
            $query = "//ro:collection/ro:identifier[@type='uri']";
            $type = 'uri';
        }
        elseif($this->gXPath->evaluate("count(//ro:collection/ro:identifier[@type='purl'])")>0) {
            $query = "//ro:collection/ro:identifier[@type='purl']";
            $type = 'purl';
        }
        elseif($this->gXPath->evaluate("count(//ro:collection/ro:citationInfo/ro:citationMetadata/ro:url)")>0) {
            $query = "//ro:collection/ro:citationInfo/ro:citationMetadata/ro:url";
            $type = 'url';
        }
        elseif($this->gXPath->evaluate("count(//ro:collection/ro:location/ro:address/ro:electronic[@type='url'])")>0) {
            $query = "//ro:collection/ro:location/ro:address/ro:electronic[@type='url']";
            $type = 'url';
        }
        elseif($this->gXPath->evaluate("count(//ro:collection/ro:location/ro:address/ro:electronic[@type='uri'])")>0) {
            $query = "//ro:collection/ro:location/ro:address/ro:electronic[@type='uri']";
            $type = 'url';
        }
        if($query!=''){
            $urls = $this->gXPath->query($query);
            foreach($urls as $url) {
                $sourceUrl = trim($url->nodeValue);
                 if($output=='endNote' && $type!="url"){
                    $resolved = identifierResolution($sourceUrl,$type);
                    $sourceUrl = trim($resolved['href']);
                }elseif($output == 'coins'){
                    if(strpos($sourceUrl,"doi.org/")) $sourceUrl ="info:doi".substr($sourceUrl,strpos($sourceUrl,"doi.org/")+8);
                    elseif($type=='doi') $sourceUrl = "info:doi".$sourceUrl;
                }
            }
        }

        if($output=='endNote'){
            if($sourceUrl == ''){
                $sourceUrl = portal_url().$this->ro->slug;
            }else{
                $sourceUrl .= ", ".portal_url().$this->ro->slug;
            }
        }
        return  $sourceUrl;
    }

    function getPublisher()
    {

        $publisher = '';
        $query = '';
        if($this->gXPath->evaluate("count(//ro:collection/ro:citationInfo/ro:citationMetadata/ro:publisher)")>0) {
            $query = "//ro:collection/ro:citationInfo/ro:citationMetadata/ro:publisher";
        }
        elseif($this->gXPath->evaluate("count(//@group)")>0){
            $query = "//@group";
        }
        if($query!=''){
            $publishers = $this->gXPath->query($query);
            foreach($publishers as $apublisher) {
                $publisher = $apublisher->nodeValue;
            }
        }

        return $publisher;
    }

    function getCreatedDate()
    {

        $createdDate = '';
        $query = '';
        if($this->gXPath->evaluate("count(//ro:collection/ro:dates[@type='dc.created'])")>0) {
            $query = "//ro:collection/ro:dates[@type='dc.created']";
        }
        if($query!=''){
            $createdDates = $this->gXPath->query($query);
            foreach($createdDates as $created_Date) {
                $createdDate = date("Y",strtotime($created_Date->nodeValue));
            }
        }

        return $createdDate;
    }

    function getVersion()
    {

        $version = '';
        $query = '';
        if($this->gXPath->evaluate("count(//ro:collection/ro:citationInfo/ro:citationMetadata/ro:version)")>0) {
            $query = "//ro:collection/ro:citationInfo/ro:citationMetadata/ro:version";
        }
        if($query!=''){
            $versions = $this->gXPath->query($query);
            foreach($versions as $aversion) {
                $version = $aversion->nodeValue;
            }
        }

        return $version;
    }

    function getContributors()
    {
        $contributors = Array();
        $noseq = 9999;
        if (isset($this->xml->{$this->ro->class}->citationInfo->citationMetadata->contributor)) {
            foreach ($this->xml->{$this->ro->class}->citationInfo->citationMetadata->contributor as $contributor) {
                $nameParts = Array();
                foreach ($contributor->namePart as $namePart) {
                    $nameParts[] = array(
                        'namePart_type' => (string)$namePart['type'],
                        'name' => (string)$namePart
                    );
                }
                // ensure that if no sequence number is available to
                // set it to a high sequence number so that all contributors with a
                // provided sequence number provided are displayed first
                if(!isset($contributor['seq'])) {
                    $contributor['seq'] = $noseq;
                    $noseq++;
                }

                $contributors[] = array(
                    'name' => formatName($nameParts),
                    'seq' => (string)$contributor['seq'],
                    'to_id' => NULL
                );


            }
        }

        if (!$contributors) {
            /*
             * We do query the "relations" core for related parties.
             * We do index reverse relationships BUT without the mirrored (reversed) relationship type
             * adding the reversed types to the query ensures that the party is found in either ways in the index
             */
                $relationshipTypeArray = array(
                     'hasPrincipalInvestigator',
                     'isPrincipalInvestigatorOf',
                     'author',
                     'coInvestigator',
                     'isOwnedBy',
                     'isOwnerOf',
                     'hasCollector',
                     'isCollectorOf'
                 );
                 $classArray = array('party');

                 /* Ensure the search loops through the pre determined order of relationships
                    and return as soon as a particular type is found */

                 foreach ($relationshipTypeArray as $relationType) {
                 $authors = $this->ro->getRelatedObjectsIndex($classArray, [$relationType]);
                 if (sizeof($authors) > 0) {
                     foreach ($authors as $author) {
                         $contributors[] = array(
                             'name' => $author['to_title'],
                             'seq' => '',
                             'to_id' => $author['to_id']
                         );
                     }

                    /* sort the  array by name */
                     usort($contributors, "cmpTitle");
                     return $contributors;
                 }
             }
        }

        if (!$contributors) {
            $contributors[] = array(
                'name' => 'Anonymous',
                'seq' => '',
                'to_id' => ''
            );
        }
        usort($contributors, "seq");
        return $contributors;
    }

    private function getFunders()
    {
        $CI =& get_instance();
        $CI->load->model('registry_object/registry_objects', 'mro');
        $funders = Array();

        foreach($this->xml->{$this->ro->class}->relatedObject as $partyFunder){
            if($partyFunder->relation['type']=='isOutputOf'){
                $key = $partyFunder->key;

                $grant_objects = $CI->mro->getAllByKey($key);
                if($grant_objects){
                    foreach ($grant_objects as $grant_object)
                    {
                        // true means extrif !!
                        $grant_sxml = $grant_object->getSimpleXML(NULL);
                        if($grant_object->status == PUBLISHED){
                            $grant_id = $grant_sxml->xpath("//ro:identifier[@type='arc'] | //ro:identifier[@type='nhmrc'] | //ro:identifier[@type='purl']");

                            $related_party = array();
                            $relationships = $grant_object->getAllRelatedObjects();
                            foreach ($relationships as $rr) {
                                if (in_array($rr['class'], ['party']) && in_array($rr['relation_type'], ['isFunderOf','isFundedBy'])) {
                                    array_push($related_party, $rr);
                                }
                            }

                            if (is_array($grant_id))
                            {
                                if (is_array($related_party) && isset($related_party[0]))
                                {
                                    $funders[] = $related_party[0]['title'];
                                }
                            }
                        }
                    }
                }
            }
        }

        return $funders;
    }

    function getType()
    {

        if($this->xml->{$this->ro->class}['type'] == 'software'){
            return 'COMP';
        }

        return 'DATA';
    }

    function getKeywords(){

        $keywords = Array();
        if($this->index && isset($this->index['subject_value_resolved'])) {
            foreach($this->index['subject_value_resolved'] as $key=>$sub) {
                $keywords[] = htmlentities($this->index['subject_value_resolved'][$key]);
            }
        }

        return $keywords;
    }

    function getSpatial(){

        $spatials = Array();
        $ro_spatials = $this->gXPath->query("//ro:collection/ro:coverage/ro:spatial");

        foreach($ro_spatials as $a_spatial) {
            $spatials[] = $a_spatial->nodeValue;
        }
        return $spatials;
    }

    function getNotes(){
        $notes = Array();
        $ro_notes = $this->gXPath->query("//ro:collection/ro:description[@type='note']");
        foreach($ro_notes as $a_note) {
            $notes[] = strip_tags(html_entity_decode($a_note->nodeValue));
        }
        return $notes;
    }

    function getDates(){
        $dates = Array();
        if(isset($this->xml->{$this->ro->class}->coverage->temporal->date)){
            foreach($this->xml->{$this->ro->class}->coverage->temporal->date as $date){
                $type = '';
                $type = (string)$date['type'];
                if($type=='dateFrom') $type = 'From';
                if($type=='dateTo') $type = 'To';
                $dates[] = $type ." ".(string)($date);
            }
        }
        return $dates;
    }

    function getDescriptions(){
        $descriptions = Array();
        $ro_descriptions = $this->gXPath->query("//ro:collection/ro:description[@type='full']");
        foreach($ro_descriptions as $a_description) {
            $descriptions[] = strip_tags(html_entity_decode($a_description->nodeValue));
        }
        $ro_descriptions = $this->gXPath->query("//ro:collection/ro:description[@type='brief']");
        foreach($ro_descriptions as $a_description) {
            $descriptions[] = strip_tags(html_entity_decode($a_description->nodeValue));
        }

        $ro_descriptions = $this->gXPath->query("//ro:collection/ro:description[@type='significanceStatement']");
        foreach($ro_descriptions as $a_description) {
            $descriptions[] = strip_tags(html_entity_decode($a_description->nodeValue));
        }

     /*   $ro_descriptions = $this->gXPath->query("//ro:collection/ro:description[@type='note']");
        foreach($ro_descriptions as $a_description) {
            $descriptions[] = strip_tags(html_entity_decode($a_description->nodeValue));
        } */

        $ro_descriptions = $this->gXPath->query("//ro:collection/ro:description[@type='lineage']");
        foreach($ro_descriptions as $a_description) {
            $descriptions[] = strip_tags(html_entity_decode($a_description->nodeValue));
        }

        return $descriptions;
    }

    function getPlace(){
        $place = '';
        $ro_place = $this->gXPath->query("//ro:citationInfo/ro:citationMetadata/ro:placePublished");
        foreach($ro_place as $a_place) {
            $place = $a_place->nodeValue;
        }
        return $place;
    }

    function getRelation(){

        $relations = Array();
        foreach($this->xml->{$this->ro->class}->relatedInfo as $relation){

          if($relation['type']=='publication'){
              $relations[] =  (string) $relation->identifier;
          }

        }
        return $relations;
    }

    function getTitle(){

        $title = $this->ro->title;
        $ro_title = $this->gXPath->query("//ro:citationInfo/ro:citationMetadata/ro:title");
        foreach($ro_title as $a_title) {
            $title = $a_title->nodeValue;
            return $title;
        }
        return $title;
    }
}



