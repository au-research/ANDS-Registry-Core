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
}



