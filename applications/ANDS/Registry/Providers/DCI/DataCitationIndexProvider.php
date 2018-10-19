<?php


namespace ANDS\Registry\Providers\DCI;


use ANDS\Registry\Providers\GrantsConnectionsProvider;
use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RegistryContentProvider;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\RIFCS\IdentifierProvider;
use ANDS\Registry\Providers\RIFCS\SubjectProvider;
use ANDS\Registry\Providers\ScholixProvider;
use ANDS\Registry\RelationshipView;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\StrUtil;
use ANDS\Util\XMLUtil;
use SimpleXMLElement;

class DataCitationIndexProvider implements RegistryContentProvider
{
    public static $namespace = "https://clarivate.com/products/web-of-science/web-science-form/data-citation-index/";

    public $record;
    public $dom;

    /** @var SimpleXMLElement */
    public $DCIRoot;

    /** @var SimpleXMLElement */
    public $sxml;

    public $gXPath;

    /**
     * Process the object and (optionally) store processed data
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function process(RegistryObject $record)
    {
        // only provide dci if the data source is allowed
        $allow = $record->datasource->getDataSourceAttributeValue('export_dci');
        if (!$allow) {
            return false;
        }

        if (!static::isValid($record)) {
            // a record might not be valid anymore (type change), remove
            DCI::where('registry_object_id', $record->id)->delete();
            return false;
        }

        // get dci and then save it
        $dci = static::get($record);

        // if there's an existing, update it
        if ($existing = DCI::where('registry_object_id', $record->id)->first()) {
            $existing->data = $dci;
            $existing->save();
            return true;
        }

        // if not, create it
        $model = new DCI;
        $model->setRawAttributes([
            'data' => $dci,
            'hash' => md5($dci),
            'registry_object_id' => $record->id,
            'registry_object_group' => $record->group,
            'registry_object_data_source_id' => $record->data_source_id,
        ]);
        $model->save();

        return true;
    }

    /**
     * Return the processed content for given object
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function get(RegistryObject $record)
    {
        if (!static::isValid($record)) {
            return null;
        }

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

        $provider->build();

        // TODO: clean up
        return $provider->dom->ownerDocument->saveXML($provider->dom->ownerDocument->documentElement, LIBXML_NOXMLDECL);
    }

    /**
     * Checks if a record is dci-able
     *
     * @param RegistryObject $record
     * @return bool
     */
    public static function isValid(RegistryObject $record)
    {
        if ($record->class != "collection") {
            return false;
        }

        $acceptedTypes = ['collection', 'repository', 'dataset'];
        if (!in_array(strtolower($record->type), $acceptedTypes)) {
            return false;
        }

        return true;
    }

    private function build() {
        $this->getHeader();
        $this->getBibliographicData();
        $this->getAbstract();
        $this->getRightsAndLicencing();
        $this->getParentDataRef();
        $this->getDescriptorsData();
        $this->getFundingInfo();
        $this->getCitationList();
        $this->getMethodologyList();
        $this->getNamedPersonList();
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
        $authors = MetadataProvider::getAuthors($this->record, $this->sxml, true);
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

            $partyXML = $party->getCurrentData()->data;

            // BibliographicData/AuthorList/Author/AuthorEmail
            foreach ($addresses = MetadataProvider::getElectronicAddress($party, $partyXML) as $address) {
                if ($address['type'] === 'email') {
                    $authorElement->addChild('AuthorEmail', $address['value']);
                }
            }

            // BibliographicData/AuthorList/Author/AuthorAddress
            $addresses = MetadataProvider::getPhysicalAddress($party, $partyXML);
            if (count($addresses) > 0) {
                $authorAddress = $authorElement->addChild('AuthorAddress');
                foreach ($addresses as $address) {
                    $authorAddress->addChild('AddressString', StrUtil::xmlSafe($address['value']));
                }
            }

            // BibliographicData/AuthorList/Author/AuthorID
            foreach ($identifiers = IdentifierProvider::get($party, $partyXML) as $identifier) {
                $authorID = $authorElement->addChild('AuthorID', $identifier['value']);
                $authorID['type'] = $identifier['type'];
            }
        }

        // BibliographicData/Source
        $source = $bibliographicData->addChild('Source');

        // BibliographicData/Source/SourceURL
        $sourceURL = MetadataProvider::getSourceURL($this->record, $this->sxml);
        $url = $sourceURL['type'] === "doi" ?
            end(explode('doi.org/', $sourceURL['value'])) :
            $sourceURL['value'];
        $source->addChild("SourceURL", StrUtil::xmlSafe($url));

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
            $rightsText = $right['rightsStatement']['value']
                . ' '. $right['rightsStatement']['uri'] . ' ' . $right['accessRights']['value'];
            $licensing->addChild('RightsStatement', $rightsText);
            $licensing->addChild('LicenseStatement', $right['licence']['value']);
        }
    }

    private function getDescriptorsData()
    {
        $descriptor = $this->DCIRoot->addChild('DescriptorsData');

        // DataType
        $descriptor->addChild('DataType', $this->record->type);

        // Keywords
        $subjects = SubjectProvider::getSubjects($this->record);
        if (count($subjects) > 0) {
            $keywordsList = $descriptor->addChild('KeywordsList');
            foreach ($subjects as $subject) {
                $keywordsList->addChild('Keyword', StrUtil::xmlSafe($subject['value']));
            }
        }

        $coverages = MetadataProvider::getCoverages($this->record);

        // Spatial
        if (count($coverages['spatial']) > 0) {
            $geographicalData = $descriptor->addChild('GeographicalData');
            foreach ($coverages['spatial'] as $spatial) {
                $geographicalData->addChild('GeographicalLocation', StrUtil::xmlSafe($spatial['value']));
            }
        }

        // Temporal
        if (count($coverages['temporal']) > 0) {

            $froms = collect([]);
            $tos = collect([]);

            foreach ($coverages['temporal'] as $temporal) {
                $froms = $froms->merge(collect($temporal)->filter(function($date){
                    return $date['type'] === 'dateFrom';
                })->pluck('value'));
                $tos = $froms->merge(collect($temporal)->filter(function($date){
                    return $date['type'] === 'dateTo';
                })->pluck('value'));
            }

            $froms = $froms->map(function($date){
                return DatesProvider::formatDate($date, 'Y-m-d H:i:s');
            })->sort();
            $tos = $tos->map(function($date){
                return DatesProvider::formatDate($date, 'Y-m-d H:i:s');
            })->sort();

            $TimeperiodList = $descriptor->addChild('TimeperiodList');
            if ($froms->count()) {
                $earliest = $TimeperiodList->addChild('TimePeriod', $froms->first());
                $earliest['TimeSpan'] = 'Start';
            }
            if ($tos->count()) {
                $latest = $TimeperiodList->addChild('TimePeriod', $tos->last());
                $latest['TimeSpan'] = 'End';
            }
        }
    }

    private function getParentDataRef()
    {
        $direct = RelationshipView::where('from_id', $this->record->id)
            ->where('relation_type', 'isPartOf')
            ->where('to_class', 'collection')
            ->get();

        foreach ($direct as $relation) {
            $this->DCIRoot->addChild('ParentDataRef', StrUtil::xmlSafe($relation->to_key));
        }
    }

    private function getCitationList()
    {
        $relatedInfoPublications = XMLUtil::getElementsByXPathFromSXML($this->sxml, "//ro:relatedInfo[@type='publication']");
        if (count($relatedInfoPublications) === 0) {
            return;
        }

        $citationList = $this->DCIRoot->addChild("CitationList");

        foreach ($relatedInfoPublications as $pub) {
            $citation = $citationList->addChild('Citation');
            $citationText = $citation->addChild('CitationText');
            $citation['CitationType'] = "Citing Ref";
            $text = (string) $pub->title;
            $text .= $pub->identifier['type'] === 'uri'
                ? ' &lt;'. (string) $pub->identifier.'&gt;'
                : ' &lt;'.(string) $pub->identifier['type'].':'.(string) $pub->identifier.'&gt;';
            $text .= $pub->notes ? '('.(string) $pub->notes.')' : '';
            $citationText->addChild("CitationString", $text);
        }
    }

    private function getFundingInfo()
    {
        // FundingInfo/FundingInfoList/ParsedFunding
        $direct = RelationshipView::where('from_id', $this->record->id)
            ->where('to_class', 'activity')
            ->where('relation_type', 'isOutputOf')
            ->get();
        // reverse?

        if (count($direct) === 0) {
            return;
        }

        // building a list of fundingInfos before adding to the DCIRoot
        // because some activity might not have a funder and/or grant number
        $fundingInfos = [];
        foreach ($direct as $relation) {
            $activity = RegistryObjectsRepository::getRecordByID($relation->to_id);

            // registryObject:activity:identifier[@type='arc' or 'nhmrc']
            $identifiers = IdentifierProvider::get($activity);
            $grantID = collect($identifiers)->filter(function($identifier){
                return in_array($identifier['type'], ['arc', 'nhmrc']);
            })->first();
            if (!$grantID) {
                continue;
            }

            $fundingInfo = [
                'GrantNumber' => $grantID['value']
            ];

            if ($funder = RelationshipProvider::getFunder($activity)){
                $fundingInfo['FundingOrganization'] = $funder->title;
            }

            $fundingInfos[] = $fundingInfo;
        }

        if (count($fundingInfos) === 0) {
            return;
        }

        // FundingInfo/FundingInfoList/ParsedFunding/GrantNumber
        // FundingInfo/FundingInfoList/ParsedFunding/FundingOrganisation
        $FundingInfo = $this->DCIRoot->addChild('FundingInfo');
        $FundingInfoList = $FundingInfo->addChild('FundingInfoList');
        foreach ($fundingInfos as $fundingInfo) {
            $ParsedFunding = $FundingInfoList->addChild('ParsedFunding');
            $ParsedFunding->addChild('GrantNumber', $fundingInfo['GrantNumber']);
            if (isset($fundingInfo['FundingOrganization'])) {
                $ParsedFunding->addChild('FundingOrganisation', $fundingInfo['FundingOrganization']);
            }
        }

    }

    /**
     * MethodologyList/Methodology
     * registryObject:collection:relatedInfo:type="reuseInformation"
     */
    private function getMethodologyList()
    {
        $reuseInfo = $this->sxml->xpath("//ro:relatedInfo[@type='reuseInformation']");
        if (!count($reuseInfo)) {
            return;
        }

        $MethodologyList = $this->DCIRoot->addChild('MethodologyList');
        foreach ($reuseInfo as $info) {
            $methodology = implode(NL, [
                $info->title ? (string) $info->title : "",
                $info->identifier ? (string) $info->identifier : "",
                $info->notes ? (string) $info->notes : ""
            ]);
            $MethodologyList->addChild("Methodology", $methodology);
        }
    }

    /**
     *  registryObject:collection:subject:type="AU-ANL:PEAU"
        AND
        registryObject:collection:subject:type="orcid"
     */
    private function getNamedPersonList()
    {
        $subjects = SubjectProvider::getSubjects($this->record);
        if (!count($subjects)) {
            return;
        }

        $valid = collect($subjects)->filter(function($subject) {
            return in_array($subject['type'], ['AU-ANL:PEAU', 'orcid']);
        });

        if (!count($valid)) {
            return;
        }

        $NamedPersonList = $this->DCIRoot->addChild('NamedPersonList');
        foreach ($valid as $subject) {
            $NamedPersonList->addChild('NamedPerson', $subject['value']);
        }
    }

}