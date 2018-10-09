<?php


namespace ANDS\Registry\Providers\DCI;


use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RegistryContentProvider;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\RegistryObject;
use ANDS\Util\StrUtil;
use SimpleXMLElement;

class DataCitationIndexProvider implements RegistryContentProvider
{

    protected $namespace = "https://clarivate.com/products/web-of-science/web-science-form/data-citation-index/";

    public $record;
    public $dom;
    public $DCIRoot;
    public $gXPath;

    /**
     * Process the object and (optionally) store processed data
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function process(RegistryObject $record)
    {
        // TODO: Implement process() method.
    }

    /**
     * Return the processed content for given object
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function get(RegistryObject $record)
    {
        $provider = new static;
        $provider->record = $record;
        $provider->DCIRoot = new SimpleXMLElement("<DataRecord></DataRecord>");
        $provider->dom = dom_import_simplexml($provider->DCIRoot);

        // gxpath
        $rifDom = new \DOMDocument();
        $rifDom->loadXML($record->getCurrentData()->data);
        $provider->gXPath = new \DOMXpath($rifDom); // TODO
        $provider->gXPath->registerNamespace('ro', 'http://ands.org.au/standards/rif-cs/registryObjects');


        $dci = $provider->getDCI();

//        $xml = $record->getCurrentData()->data;
//
//        // DOM
//        $root = new SimpleXMLElement("<DataRecord></DataRecord>");
//        $dom = dom_import_simplexml($root);
//
//        // TODO: hasTag excludeDCI
//
//        // TODO: data source attribute check
//
//        $header = $root->addChild('Header');
//        static::getHeader($header, $record);
//
//        $bibliographicData = $root->addChild('BibliographicData');
//        static::getBibliographicData($bibliographicData);

        // getAbstract
        // getRightsAndLicencing
        // getParentDataRef
        // getDescriptorsData
        // getFundingInfo
        // getCitationList

        // TODO: clean up
        return $provider->dom->ownerDocument->saveXML($provider->dom->ownerDocument->documentElement, LIBXML_NOXMLDECL);
    }

    private function getDCI() {
        $this->getHeader();
        $this->getBibliographicData($this->getSourceUrl());
        $this->getAbstract();
//        $this->getRightsAndLicencing();
//        $this->getParentDataRef();
//        $this->getDescriptorsData();
//        $this->getFundingInfo();
//        $this->getCitationList();
    }

    /**
     *
     */
    private function getHeader()
    {
        $header = $this->DCIRoot->addChild('Header');
        $header->addChild('DateProvided', date('Y-m-d', time()));
        $header->addChild('RepositoryName', str_replace('&', '&amp;', $this->record->group));
        $header->addChild('Owner', str_replace('&', '&amp;', $this->record->group));
        $header->addChild('RecordIdentifier', str_replace('&', '&amp;', $this->record->key));
    }

    private function getBibliographicData($sourceUrl)
    {
        $bibliographicData = $this->DCIRoot->addChild('BibliographicData');
//        $authorList = $bibliographicData->addChild('AuthorList');
//        $this->getAuthors($authorList); // TODO
        $titleList = $bibliographicData->addChild('TitleList');
        $title = $titleList->addChild("ItemTitle", str_replace('&', '&amp;', $this->record->title));
        $title['TitleType'] = "English title";
        $source = $bibliographicData->addChild('Source');

        $source->addChild("SourceURL", str_replace('&', '&amp;', $sourceUrl));
//        $source->addChild("SourceRepository" , str_replace('&', '&amp;',$this->citation_handler->getPublisher())); // TODO

        if($publicationDate = DatesProvider::getPublicationDate($this->record)) {
            $source->addChild("CreatedDate" , $publicationDate);
        }
        // TODO
//        $version  = $this->citation_handler->getVersion();
//        if($version) {
//            $source->addChild("Version" , $version);
//        }

        $languageList = $bibliographicData->addChild('LanguageList');
        $languageList->addChild("Language", "English");
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


    private function getSourceUrl($output = null)
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
            }
        }

        return  $sourceUrl;
    }

    /**
     * registryObject:collection:description:type="full"
    AND/OR
    registryObject:collection:description:type="brief"
    Where present, the types below will also be transformed
    registryObject:collection:description:type="SignificanceStatement"
    registryObject:collection:description:type="Notes"
    registryObject:collection:description:type="Lineage"
     */
    private function getAbstract()
    {
        $descriptions = MetadataProvider::getDescriptions($this->record);
        $validTypes = ['full', 'brief', 'SignificanceStatement', 'Notes', "Lineage"];
        $abstract = collect($descriptions)
            ->filter(function ($description) use ($validTypes) {
                // get only the descriptions that has those types
                return in_array($description['type'], $validTypes);
            })->sortBy(function ($description) use ($validTypes) {
                // sort the descriptions by the order of the valid Types
                return array_search($description['type'], $validTypes);
            })->first();

        $abstract = $abstract['value'] ?: 'Not available';
        $this->DCIRoot->addChild('Abstract', StrUtil::xmlSafe($abstract));
    }

}