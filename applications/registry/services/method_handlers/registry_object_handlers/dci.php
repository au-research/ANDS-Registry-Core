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
        $sourceUrl = $this->citation_handler->getSourceUrl();
        if($sourceUrl == null)
            return "not Exportable";
        $this->getHeader();
        $this->getBibliographicData($sourceUrl);
        $this->getAbstract();
        $this->getRightsAndLicencing();
        $this->getDescriptorsData();

        return $this->DCIDom->ownerDocument->saveXML($this->DCIDom->ownerDocument->documentElement, LIBXML_NOXMLDECL);
	}

    private function getHeader()
    {
        $header = $this->DCIRoot->addChild('Header');
        $header->addChild('DateProvided', date('Y-m-d', time()));
        $header->addChild('RepositoryName', $this->ro->group);
        $header->addChild('Owner', $this->ro->group);
        $header->addChild('RecordIdentifier', $this->ro->key);
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
        $title = $titleList->addChild("ItemTitle", $this->ro->title);
        $title['TitleType'] = "English title";
        $source = $bibliographicData->addChild('Source');
        $source->addChild("sourceUrl", $sourceUrl);
        $source->addChild("SourceRepository" , $this->citation_handler->getPublisher());
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
        $earliestYear = (isset($this->index['earliest_year']) ? $this->index['earliest_year'] : null);
        $latestYear = (isset($this->index['latest_year']) ? $this->index['latest_year'] : null);
        //var_dump($keyWords);
        if($keyWords || $spatialData || $earliestYear || $latestYear)
        {
            $descriptorsData = $this->DCIRoot->addChild('DescriptorsData');
            if($keyWords){
                $keywordsList = $descriptorsData->addChild("KeywordsList");
                foreach($keyWords as $keyWord)
                {
                    $keywordsList->addChild("Keyword" , htmlentities($keyWord, ENT_DISALLOWED));
                }
            }
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
    }


    private function getAuthors($authorList){
        $forDCI = true;
        $relationshipTypeArray = array('hasAssociationWith','hasPrincipalInvestigator','principalInvestigator','author','coInvestigator','isOwnedBy','hasCollector');
        $classArray = array('party');
        $authors = $this->ro->getRelatedObjectsByClassAndRelationshipType($classArray ,$relationshipTypeArray, $forDCI);
        $seq = 1;
        foreach($authors as $author)
        {
            $eAuthor = $authorList->addChild("Author");
            $eAuthor['seq'] = $seq++;
            $eAuthor['AuthorRole'] = format_relationship("collection",(string)$author["relation_type"]);
            $eAuthor->addChild('AuthorName', $author['title']);
            if(isset($author['identifiers']) && sizeof($author['identifiers'] > 0))
            {
                foreach($author['identifiers'] as $id){
                    $authorId = $eAuthor->addChild('AuthorID', trim($id[0]));
                    $authorId['type'] = $id[1];
                }
            }
            if(isset($author['addresses']))
            {
                $authorAddress = $eAuthor->addChild('AuthorAddress');
                foreach($author['addresses'] as $addr){
                    $authorAddress->addChild('AddressString', (string) $addr);
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
                if($rl['type'] == 'rightsStatement' || $rl['type'] == 'accessRights')
                  $rights .= $rl['value'].' ';
                if($rl['type'] == 'licence')
                    $licence .= $rl['value'];
            }
            $rights_Licensing->addChild('RightsStatement', $rights);
            $rights_Licensing->addChild('LicenseStatement', $licence);
        }
    }



}



