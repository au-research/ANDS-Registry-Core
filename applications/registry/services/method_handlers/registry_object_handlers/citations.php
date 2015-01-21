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
        $doi = '';
        foreach($this->xml->{$this->ro->class}->citationInfo->citationMetadata->identifier as $identifiers) {
            if($identifiers['type']=='doi'){
                $doi = $identifiers;
            }
        }
        if($doi=='') {
            foreach($this->xml->{$this->ro->class}->identifier as $identifiers) {
                if($identifiers['type']=='doi'){
                    $doi = $identifiers;
                }
            }
        }
        if($doi!=''){
            if(strpos($doi,"doi.org/")) {
                $doi = substr($doi,strpos($doi,"doi.org/")+8);
            }
            $endNote .= "DO  - ".$doi."
";
        }

        $endNote .= $this->getPublicationDate();
        return $endNote;
    }

    private function getPublicationDate()
    {
        $publicationDate = '';
        if($theDates = $this->xml->xpath("//citationInfo/citationMetadata/date[@type='publicationDate']")) {



        }
        elseif($theDates = $this->xml->xpath("//citationInfo/citationMetadata/date[@type='issued']")) {
                $publicationDate = substr($theDate,1,4);
            foreach($theDates as $theDate ) {
                $publicationDate = substr($theDate,1,4);
            }
        }

        if($publicationDate!='')
        {
            $publicationDate = "PY  - ".$publicationDate."
";
        }

        return $publicationDate;

    }
}

/*

<xsl:template name="getPublishedDate">
        <xsl:choose>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='publicationDate']">
                <xsl:value-of select="substring(ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='publicationDate'],1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='issued']">
                <xsl:value-of select="substring(ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='issued'],1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='created']">
                <xsl:value-of select="substring(ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='created'],1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:dates[@type='dc.issued']">
                <xsl:value-of select="substring(ro:collection/ro:dates[@type='dc.issued']/ro:date,1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:dates[@type='dc.available']">
                <xsl:value-of select="substring(ro:collection/ro:dates[@type='dc.available']/ro:date,1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:dates[@type='dc.created']">
                <xsl:value-of select="substring(ro:collection/ro:dates[@type='dc.created']/ro:date,1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/@dateModified">
                <xsl:value-of select="substring(ro:collection/@dateModified,1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/@dateAccessioned">
                <xsl:value-of select="substring(ro:collection/@dateAccessioned,1,4)"/>
            </xsl:when>
            <xsl:when test="$dateHarvested">
                <xsl:value-of select="substring($dateHarvested,1,4)" />
            </xsl:when>
        </xsl:choose>
</xsl:template> 8/