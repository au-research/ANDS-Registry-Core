<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/citations.php');
/**
 * DCI Citation INDEX  handler
 * @author Liz Woods <liz.woods@ands.org.au>
 * @param  string type
 * @return array
 */


class DCI extends ROHandler {

    private $DCIRoot = null;
    private $DCIDom = null;
    private $citation_handler = null;

	function handle() {
        $this->DCIRoot = new SimpleXMLElement("<DataRecord></DataRecord>");
        $this->DCIDom = dom_import_simplexml($this->DCIRoot);
        $this->citation_handler = new citations($this->get_resource());

        $CI =& get_instance();
        $CI->load->model('data_source/data_sources','ds');
        $ds = $CI->ds->getByID($this->ro->data_source_id);
        $exportable = false;
        if($this->ro->hasTag('excludeDCI'))
            return "";
        if($this->overrideExportable || $ds->export_dci == DB_TRUE || $ds->export_dci == 1 || $ds->export_dci == 't')
            $exportable = true;
        $sourceUrl = $this->citation_handler->getSourceUrl();
        if($sourceUrl == null || !($exportable))
            return "";
        $this->getHeader();
        $this->getBibliographicData($sourceUrl);
        $this->getAbstract();
        $this->getRightsAndLicencing();
        $this->getParentDataRef();
        $this->getDescriptorsData();
        $this->getFundingInfo();
        $this->getCitationList();

        return $this->DCIDom->ownerDocument->saveXML($this->DCIDom->ownerDocument->documentElement, LIBXML_NOXMLDECL);
	}

    private function getHeader()
    {
        $header = $this->DCIRoot->addChild('Header');
        $header->addChild('DateProvided', date('Y-m-d', time()));
        $header->addChild('RepositoryName', str_replace('&', '&amp;', $this->ro->group));
        $header->addChild('Owner', str_replace('&', '&amp;', $this->ro->group));
        $header->addChild('RecordIdentifier', str_replace('&', '&amp;', $this->ro->key));
    }

    private function getAbstract()
    {
        if($this->index && isset($this->index['list_description'])) {
            //var_dump($this->index['list_description']);
            $this->DCIRoot->addChild('Abstract', str_replace('&', '&amp;', $this->index['list_description']));
        }

    }

    private function getBibliographicData($sourceUrl)
    {
        $bibliographicData = $this->DCIRoot->addChild('BibliographicData');
        $authorList = $bibliographicData->addChild('AuthorList');
        $this->getAuthors($authorList);
        $titleList = $bibliographicData->addChild('TitleList');
        $title = $titleList->addChild("ItemTitle", str_replace('&', '&amp;', $this->ro->title));
        $title['TitleType'] = "English title";
        $source = $bibliographicData->addChild('Source');
        $source->addChild("SourceURL", str_replace('&', '&amp;', $sourceUrl));
        $source->addChild("SourceRepository" , str_replace('&', '&amp;',$this->citation_handler->getPublisher()));
        $publicationDate = $this->citation_handler->getPublicationDate();
        if($publicationDate)
        {
            $source->addChild("CreatedDate" , $publicationDate);
        }
        $version  = $this->citation_handler->getVersion();
        if($version)
        {
            $source->addChild("Version" , $version);
        }
        $languageList = $bibliographicData->addChild('LanguageList');
        $languageList->addChild("Language", "English");
    }

    private function  getDescriptorsData(){
        $keyWords = $this->citation_handler->getKeywords();

        $spatialData = $this->citation_handler->getSpatial();
        $latestYear = false;
        $earliestYear = false;
        if(isset($this->xml->{$this->ro->class}->coverage->temporal->date)){

            foreach($this->xml->{$this->ro->class}->coverage->temporal->date as $date){
                $type = (string)$date['type'];
                if($type=='dateFrom'){
                    if(strlen(trim($date)) == 4)
                        $date = "Jan 01, ".trim($date);
                    $end = strtotime($date);
                    if(!$earliestYear || $earliestYear > date("Y",$end))
                        $earliestYear = date("Y",$end);
                }
                if($type=='dateTo')
                {
                    if(strlen(trim($date)) == 4)
                        $date = "Dec 12, ".trim($date);
                    $end = strtotime($date);
                    if(!$latestYear || $latestYear < date("Y",$end))
                        $latestYear = date("Y",$end);
                }
            }
        }

        $descriptorsData = $this->DCIRoot->addChild('DescriptorsData');
        if($keyWords){
            $keywordsList = $descriptorsData->addChild("KeywordsList");
            foreach($keyWords as $keyWord)
            {
                $keywordsList->addChild("Keyword" , str_replace('&', '&amp;', htmlentities($keyWord, ENT_DISALLOWED)));
            }
        }
        $descriptorsData->addChild("DataType", $this->xml->{$this->ro->class}["type"]);
        if($spatialData){
            $geographicalData = $descriptorsData->addChild("GeographicalData");
            foreach($spatialData as $sData)
            {
                $geographicalData->addChild("GeographicalLocation" , $sData);
            }
        }
        if($earliestYear || $latestYear)
        {
            $timeperiodList = $descriptorsData->addChild("TimeperiodList");
            if($earliestYear){
                $timePeriod = $timeperiodList->addChild("TimePeriod", $earliestYear);
                $timePeriod['TimeSpan'] = 'Start';
            }

            if($latestYear){
                $timePeriod = $timeperiodList->addChild("TimePeriod",$latestYear);
                $timePeriod['TimeSpan'] = 'End';
            }
        }

    }


    private function  getFundingInfo(){
        $forDCI = true;
        $relationshipTypeArray = array('isOutputOf');
        $classArray = array('activity');
        $grants = $this->ro->getRelatedObjectsByClassAndRelationshipType($classArray ,$relationshipTypeArray, $forDCI);
        if(is_array($grants) && sizeof($grants) > 0){
            foreach($grants as $grant){
                if($grant['type'] == 'grant'){
                    $identifierStr = '';
                    if(isset($grant['identifiers']))
                    {
                        foreach($grant['identifiers'] as $identifier){
                            $identifierStr .= $this->normaliseIdentifier($identifier[0], $identifier[1]).", ";
                        }
                        $grantIndex = $this->findGrantbyKey($grant['key']);
                        if($grantIndex && isset($grantIndex['funders'])){
                            $fundingInfo = $this->DCIRoot->addChild('FundingInfo');
                            $fundingInfoList = $fundingInfo->addChild('FundingInfoList');
                            $parsedFunding = $fundingInfoList->addChild('ParsedFunding');
                            $parsedFunding->addChild('GrantNumber', substr($identifierStr, 0, strlen($identifierStr) - 2));
                            $parsedFunding->addChild("FundingOrganization", str_replace('&', '&amp;', $grantIndex['funders'][0]));
                        }
                    }
                }
            }
        }
    }


    private function  getCitationList(){
        $publications = $this->gXPath->query("//ro:relatedInfo[@type='publication']");
        if($publications->length > 0)
        {
            $citationList = $this->DCIRoot->addChild('CitationList');
            foreach($this->xml->{$this->ro->class}->relatedInfo as $relatedInfo) {
                if($relatedInfo['type'] == 'publication'){
                    $citation = $citationList->addChild('Citation');
                    $citation['CitationType'] = "Citing Ref";
                    $citationText = $citation->addChild('CitationText');
                    $text = (string)$relatedInfo->title;
                    if($relatedInfo->identifier['type'] == 'uri')
                    {
                        $text .= ' &lt;'.$relatedInfo->identifier.'&gt;';
                    }
                    else
                    {
                        $text .= ' &lt;'.$relatedInfo->identifier['type'].':'.$relatedInfo->identifier.'&gt;';
                    }
                    if(isset($relatedInfo->notes))
                    {
                        $text .= '('.$relatedInfo->notes.')';
                    }
                    $citationText->addChild('CitationString', str_replace('&', '&amp;', $text));
                }

            }
        }
    }

    private function getAuthors($authorList){
        $seq = 0;
        $tempered = false;
        if(count($this->xml->xpath('//citationMetadata/contributor')) > 0){
            foreach($this->xml->xpath('//citationMetadata/contributor') as $contributor){
                $nameParts = Array();
                foreach($contributor->namePart as $namePart){
                    $nameParts[] = array(
                        'namePart_type' => (string)$namePart['type'],
                        'name' => (string)$namePart
                    );
                }
                $eAuthor = $authorList->addChild("Author");
                $cSeq = intval((string)$contributor['seq']);
                if($cSeq == 0  || $cSeq == '') // if empty or 0 we have to increment the sequence in-house
                {
                    $cSeq = ++$seq;
                    $tempered = true; // was dirty so we're fixin it
                }
                elseif($cSeq <= $seq && $tempered == true) // if this number is less than seq AND we are fixin in
                {
                    $cSeq = ++$seq;
                }
                if($cSeq > $seq){
                   $seq = $cSeq; //store the largest current sequence number
                }


                $eAuthor['seq'] = $cSeq;

                $eAuthor['AuthorRole'] = "Contributor";
                $eAuthor->addChild('AuthorName', str_replace('&', '&amp;',formatName($nameParts)));
            }
        }
        else{
            $forDCI = true;
            $relationshipTypeArray = array('IsPrincipalInvestigatorOf','hasPrincipalInvestigator','principalInvestigator','author','coInvestigator','isOwnedBy','hasCollector');
            $classArray = array('party');
            $authors = $this->ro->getRelatedObjectsByClassAndRelationshipType($classArray ,$relationshipTypeArray, $forDCI);
            if($authors)
            {
                $seq = 1;
                foreach($authors as $author)
                {
                    $eAuthor = $authorList->addChild("Author");
                    $eAuthor['seq'] = $seq++;
                    //return format_relationship($class, $relationshipText, $altered,$to_class);
                    $eAuthor['AuthorRole'] = format_relationship("collection",(string)$author["relation_type"], (string)$author['origin'], 'party');
                    $eAuthor->addChild('AuthorName', $author['title']);
                    if(isset($author['addresses']))
                    {
                        $authorAddress = $eAuthor->addChild('AuthorAddress');
                        foreach($author['addresses'] as $addr){
                            $authorAddress->addChild('AddressString', str_replace('&', '&amp;',(string) $addr));
                            break;
                        }
                    }
                    if(isset($author['electronic_addresses']))
                    {
                        foreach($author['electronic_addresses'] as $addr){
                            $eAuthor->addChild('AuthorEmail', str_replace('&', '&amp;', (string) $addr));
                            break;
                        }
                    }
                    if(isset($author['identifiers']) && sizeof($author['identifiers'] > 0))
                    {
                        foreach($author['identifiers'] as $id){
                            $authorId = $eAuthor->addChild('AuthorID', trim(str_replace('&', '&amp;', $id[0])));
                            $authorId['type'] = $id[1];
                        }
                    }
                }
            }
            else{
                $eAuthor = $authorList->addChild("Author");
                $eAuthor['seq'] = '1';
                $eAuthor->addChild('AuthorName', str_replace('&', '&amp;', $this->ro->group));
            }
        }
    }

    private function getParentDataRef()
    {
        $relationshipTypeArray = array('isPartOf');
        $classArray = array('collection');
        $parentCollections = $this->ro->getRelatedObjectsByClassAndRelationshipType($classArray ,$relationshipTypeArray);
        if($parentCollections)
        {
            foreach($parentCollections as $parentCollection)
            {
                if(!($parentCollection['key'] == '' || $parentCollection['origin'] == "REVERSE_INT"
                    ||  $parentCollection['origin'] == "REVERSE_EXT")){
                    $this->DCIRoot->addChild("ParentDataRef", str_replace('&', '&amp;', $parentCollection['key']));
                    break;
                }
            }
        }
    }

    private function getRightsAndLicencing()
    {
     $licencing = $this->ro->processLicence();
        if(is_array($licencing) and count($licencing) > 0){
            $rights = '';
            $licence = '';
            $rights_Licensing = $this->DCIRoot->addChild('Rights_Licensing');
            foreach($licencing as $rl){
                if($rl['type'] == 'rightsStatement' || $rl['type'] == 'accessRights'){
                    if($rl['value'] != '')
                        $rights .= $rl['value'].' ';
                    if($rl['value'] == '' && isset($rl['rightsUri']))
                        $rights .= $rl['rightsUri'].' ';
                }
                if($rl['type'] == 'licence')
                    $licence .= $rl['value'];
            }
            $rights_Licensing->addChild('RightsStatement', str_replace('&', '&amp;', $rights));
            $rights_Licensing->addChild('LicenseStatement', str_replace('&', '&amp;', $licence));
        }
    }

    function normaliseIdentifier($value, $type)
    {
        $_orcidPrefix = "http://orcid.org/";
        $_nlaPrefix = "http://nla.gov.au/";

        if ($value == '')
        {
            return "";
        }
        else{
            if(strtolower($type) == "orcid")
            {
                if (strpos($value, $_orcidPrefix) === FALSE)
                {
                    return $_orcidPrefix . $value;
                }
                else
                {
                    return $value;
                }
            }
            else if ($type == "AU-ANL:PEAU")
            {
                if (strpos($value, $_nlaPrefix) === FALSE)
                {
                    return $_nlaPrefix . $value;
                }
                else
                {
                    return $value;
                }
            }
            else if (in_array(strtolower($type), array("uri","purl")))
            {
                return $value;
            }
            else
            {
                return (strtolower($type) . ": " . $value);
            }
        }
    }

    private function findGrantbyKey($key){
        $CI =& get_instance();
        $CI->load->library('solr');
        $CI->solr->init();
        $CI->solr->setOpt('q','key:"'.$key.'"');
        $CI->solr->setOpt('rows', 1);
        $result = $CI->solr->executeSearch(true);
        if ($result['response']['numFound'] > 0) {
            $record = $result['response']['docs'][0];
            return $record;
        } else {
            return false;
        }
    }

}



