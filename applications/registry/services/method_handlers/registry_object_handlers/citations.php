<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');

/**
 * Citations handler
 * @author Liz Woods <liz.woods@ands.org.au>
 * @param  string type
 * @return array
 */
class Citations extends ROHandler {
	function handle() {
        $result = array();
        if ($this->xml) {
            $endNote = $this->getEndnoteText();
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
                        'date' => date("Y",strtotime((string)$citationMetadata->date)),
                        'contributors' => $displayNames,
                        'endNote' => $endNote
                    );

                }
                foreach($citation->fullCitation as $fullCitation){
                    $result[] = array(
                        'type'=> 'fullCitation',
                        'value' => (string)$fullCitation,
                        'citation_type' => (string)$fullCitation['style'],
                        'endNote' => $endNote
                    );

                }
            }
            if(!$this->xml->{$this->ro->class}->citationInfo){
                $result[] = array(
                    'endNote' => $endNote
                );
            }
        }
        return $result;
	}

    private function getEndnoteText()
    {
        $endNote = 'Provider: Australian National Data Service
Database: Research Data Australia
Content:text/plain; charset="utf-8"


TY  - DATA
Y2  - '.date("Y-m-d")."
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
                $endNote .= "AU  - ".$contributor['name']."
";
            }
        }
        else{
            $endNote .= "AU  - Anonymous
";
        }
        $funders = $this->getFunders();
        if($funders!=''){
            foreach($funders as $funder){
                $endNote .= "A4  - ".$funder."
";
            }
        }
        $endNote .= "TI  - ".$this->ro->title."
";
        $sourceUrl = $this->getSourceUrl();
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
            if($right['value']!='') $endNote .="C1  - ".$right['value']."
";
        }

        return $endNote;
    }

    private function getDoi(){

        $doi = '';
        $query = '';
        if($this->gXPath->evaluate("count(//ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi'])")>0) {
            $query = "//ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi']";
        }
        elseif($this->gXPath->evaluate("count(//ro:identifier/[@type='doi'])")>0) {
            $query = "//ro:identifier/[@type='doi']";
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

    private function getPublicationDate()
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
        elseif($this->gXPath->evaluate("count(//ro:collection/@dateModified)")>0) {
            $query = "//ro:collection/@dateModified";
        }
        elseif($this->gXPath->evaluate("count(//ro:collection/@dateAccessioned)")>0) {
            $query = "ro:collection/@dateAccessioned";
        }

        if($query!=''){
            $dates = $this->gXPath->query($query);
            foreach($dates as $date) {
                $publicationDate = date("Y",strtotime($date->nodeValue));
            }
        }else{
            $publicationDate = $this->ro->created;
        }

        return  $publicationDate;
    }

    private function getSourceUrl()
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
        elseif($this->gXPath->evaluate("count(//ro:collection/ro:identifier[@type='purl']")>0) {
            $query = "//ro:collection/ro:identifier[@type='purl']";
            $type = 'purl';
        }
        elseif($this->gXPath->evaluate("count(//ro:collection/ro:citationInfo/ro:citationMetadata/ro:url")>0) {
            $query = "//ro:collection/ro:citationInfo/ro:citationMetadata/ro:url";
            $type = 'url';
        }
        elseif($this->gXPath->evaluate("count(//ro:collection/ro:location/ro:address/ro:electronic[@type='url']")>0) {
            $query = "//ro:collection/ro:location/ro:address/ro:electronic[@type='url']";
            $type = 'url';
        }
        if($query!=''){
            $urls = $this->gXPath->query($query);
            foreach($urls as $url) {
                $sourceUrl = $url->nodeValue;
                $resolved = identifierResolution($sourceUrl,$type);
                $sourceUrl = $resolved['href'];
            }
        } else {

            $sourceUrl = portal_url();
        }

        return  $sourceUrl;
    }

    private function getPublisher()
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
    
    private function getCreatedDate()
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

    private function getVersion()
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

    private function getContributors()
    {
       $contributors = Array();
       foreach($this->xml->{$this->ro->class}->citationInfo->citationMetadata->contributor as $contributor){
             $nameParts = Array();
             foreach($contributor->namePart as $namePart){
                    $nameParts[] = array(
                            'namePart_type' => (string)$namePart['type'],
                            'name' => (string)$namePart
                        );
             }
             $contributors[] =array(
                   'name' => formatName($nameParts),
                    'seq' => (string)$contributor['seq'],
                );
        }

       if(!$contributors){
            $relationshipTypeArray = ['hasPrincipalInvestigator','principalInvestigator','author','coInvestigator','isOwnedBy','hasCollector'];
            $classArray = ['party'];
            $authors = $this->ro->getRelatedObjectsByClassAndRelationshipType($classArray ,$relationshipTypeArray);
            if(count($authors)>0)
            {
                foreach($authors as $author)
                {
                    if($author['status']==PUBLISHED)
                    {
                        $contributors[] =array(
                            'name' => $author['title'],
                            'seq' => ''
                        );
                    }
                }
            }
       }

        usort($contributors,"seq");
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
                foreach ($grant_objects as $grant_object)
                {
                    $grant_sxml = $grant_object->getSimpleXML(NULL, true);

                    // Handle the researcher IDs (using the normalisation_helper.php)
                    $grant_id = $grant_sxml->xpath("//ro:identifier[@type='arc'] | //ro:identifier[@type='nhmrc'] | //ro:identifier[@type='purl']");

                    $related_party = $grant_object->getRelatedObjectsByClassAndRelationshipType(['party'] ,['isFunderOf','isFundedBy']);
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

        return $funders;

    }


}



