<?php


namespace ANDS\Registry\Providers\DCI;


use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RegistryContentProvider;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\RIFCS\IdentifierProvider;
use ANDS\Registry\Providers\ScholixProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\StrUtil;
use ANDS\Util\XMLUtil;
use SimpleXMLElement;

class DataCitationIndexProvider implements RegistryContentProvider
{

    protected $namespace = "https://clarivate.com/products/web-of-science/web-science-form/data-citation-index/";

    public $record;
    public $dom;

    /** @var SimpleXMLElement */
    public $DCIRoot;

    /** @var SimpleXMLElement */
    public $xml;

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

        $xml = $record->getCurrentData()->data;

        // gxpath
        $rifDom = new \DOMDocument();
        $rifDom->loadXML($xml);
        $provider->gXPath = new \DOMXpath($rifDom); // TODO
        $provider->gXPath->registerNamespace('ro', 'http://ands.org.au/standards/rif-cs/registryObjects');

        $provider->sxml = XMLUtil::getSimpleXMLFromString($xml);

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
        $this->getBibliographicData();
        $this->getAbstract();
        $this->getRightsAndLicencing();
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
        $header->addChild('RepositoryName', StrUtil::xmlSafe($this->record->group));
        $header->addChild('Owner', StrUtil::xmlSafe($this->record->group));
        $header->addChild('RecordIdentifier', StrUtil::xmlSafe($this->record->key));
    }

    /**
     * @throws \Exception
     */
    private function getBibliographicData()
    {
        $bibliographicData = $this->DCIRoot->addChild('BibliographicData');

        // BibliographicData/TitleList/ItemTitle
        // BibliographicData/TitleList/ItemTitle@TitleType
        $titleList = $bibliographicData->addChild('TitleList');
        $title = $titleList->addChild("ItemTitle", StrUtil::xmlSafe($this->record->title));
        $title['TitleType'] = "English title";

        // BibliographicData/AuthorList/Author
        $authors = MetadataProvider::getAuthors($this->record, $this->sxml);
        $authorList = $bibliographicData->addChild('AuthorList');
        $seq = 1;
        foreach ($authors as $author) {
            $authorElement = $authorList->addChild("Author");
            $authorElement['seq'] = $seq++;
            $authorElement['AuthorRole'] = format_relationship($this->record->class, $author['relation'], "EXPLICIT", 'party');
            $authorElement->addChild('AuthorName', $author['name']);

            $party = RegistryObjectsRepository::getRecordByID($author['id']);
            if (!$party) {
                continue;
            }

            foreach ($addresses = MetadataProvider::getAddress($party) as $address) {
                // BibliographicData/AuthorList/Author/AuthorEmail
                if ($address['type'] === 'email') {
                    $authorElement->addChild('AuthorEmail', $address['value']);
                }

                // TODO BibliographicData/AuthorList/Author/AuthorAddress
            }

            // BibliographicData/AuthorList/Author/AuthorID
            foreach ($identifiers = IdentifierProvider::get($party) as $identifier) {
                $authorID = $authorElement->addChild('AuthorID', $identifier['value']);
                $authorID['type'] = $identifier['type'];
            }
        }

        // BibliographicData/Source
        $source = $bibliographicData->addChild('Source');

        // BibliographicData/Source/SourceURL
        $sourceURL = MetadataProvider::getSourceURL($this->record, $this->sxml);
        // TODO format url based on the type (probably)
        $source->addChild("SourceURL", StrUtil::xmlSafe($sourceURL['value']));

        // BibliographicData/Source/Publisher
        $publisher = MetadataProvider::getPublisher($this->record, $this->sxml);
        $source->addChild("SourceRepository" , StrUtil::xmlSafe($publisher));

        // BibliographicData/Source/CreatedDate
        if($publicationDate = DatesProvider::getPublicationDate($this->record)) {
            $source->addChild("CreatedDate" , $publicationDate);
        }

        // BibliographicData/Source/Version
        if ($version = MetadataProvider::getVersion($this->record, $this->sxml)) {
            $source->addChild("Version" , $version);
        }

        // BibliographicData/LanguageList/Language
        $languageList = $bibliographicData->addChild('LanguageList');
        $languageList->addChild("Language", "English");
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

        $abstract = null ? 'Not available' : $abstract['value'];
        $this->DCIRoot->addChild('Abstract', StrUtil::xmlSafe($abstract));
    }

    private function getRightsAndLicencing()
    {
        foreach ($rights = MetadataProvider::getRights($this->record) as $right) {
            $licensing = $this->DCIRoot->addChild('Rights_Licensing');
            $licensing->addChild('RightsStatement', $right['rightsStatement']['value']);
            $licensing->addChild('LicenseStatement', $right['licence']['value']);
        }
    }

}