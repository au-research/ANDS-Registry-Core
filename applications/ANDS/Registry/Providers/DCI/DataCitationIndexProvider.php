<?php


namespace ANDS\Registry\Providers\DCI;


use ANDS\File\Storage;
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
use Carbon\Carbon;
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

        // TODO: should create a helper function to test for True and False
        if ($allow == null || $allow == DB_FALSE || $allow == "" || $allow == "0") {
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
            $existing->updated_at = $record->modified_at;
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
            'updated_at' => $record->modified_at
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

    public static function validate($dciXML)
    {
        $util = new XMLUtil();
        $schemaPath = Storage::disk('schema')->getPath('dci/DCI schema_providers_V4.4_120116.xsd');
        $result = $util->validateFileSchema($schemaPath, $dciXML);
        return !$result ? $util->getValidationMessage() : $result;
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

        // BibliographicData/AuthorList/Author
        $authors = $this->getAuthors($this->record, $this->sxml);
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

        // BibliographicData/TitleList/ItemTitle
        // BibliographicData/TitleList/ItemTitle@TitleType
        $titleList = $bibliographicData->addChild('TitleList');
        $title = $titleList->addChild("ItemTitle", StrUtil::xmlSafe($this->record->title));
        $title['TitleType'] = "English title";

        // BibliographicData/Source
        $source = $bibliographicData->addChild('Source');

        // BibliographicData/Source/SourceURL
        $sourceURL = MetadataProvider::getSourceURL($this->record, $this->sxml);

        $url = $sourceURL['type'] === "doi" ?
            collect(explode('doi.org/', $sourceURL['value']))->last() :
            $sourceURL['value'];
        $source->addChild("SourceURL", StrUtil::xmlSafe($url));

        // BibliographicData/Source/Publisher
        $publisher = MetadataProvider::getPublisher($this->record, $this->sxml);
        $source->addChild("SourceRepository" , StrUtil::xmlSafe($publisher));

        // BibliographicData/Source/CreatedDate
        if($publicationDate = DatesProvider::getPublicationDate($this->record)) {
            $source->addChild("CreatedDate" , Carbon::parse($publicationDate)->format('Y-m-d'));
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
     * @param RegistryObject $record
     * @param null $simpleXML
     * @return array
     * @throws \Exception
     */
    public function getAuthors(RegistryObject $record, $simpleXML = null)
    {
        $simpleXML = $simpleXML ? $simpleXML : MetadataProvider::getSimpleXML($record);

        /**
         * registryObject:collection:citationInfo:citationMetadata:contributor
        OR relatedObject:Party:name:relationType=IsPrincipalInvestigatorOf
        OR relatedObject:Party:name:relationType=author
        OR relatedObject:Party:name:relationType=coInvestigator
        OR relatedObject:Party:name:relationType=isOwnedBy
        OR relatedObject:Party:name:relationType=hasCollector
        OR registryObject@Group
         */
        $authors = [];

        $author = $simpleXML->xpath('//ro:citationInfo/ro:citationMetadata/ro:contributor');
        if (count($author) > 0 && $elem = array_pop($author)) {
            // TODO format name by namepart type
            $authors[] = [
                'relation' => 'Contributor',
                'name' => (string) $elem->namePart,
                'id' => null
            ];
        }

        if (count($authors)) {
            return $authors;
        }

        $validRelationTypes = ['IsPrincipalInvestigatorOf', 'author', 'coInvestigator', 'isOwnedBy', 'hasCollector'];
        foreach ($validRelationTypes as $relationType) {
            $authors = array_merge($authors, RelationshipProvider::getRelationByType($record, [$relationType]));
            if (count($authors)) {
                return $authors;
            }
        }

        // TODO registryObject@Group

        return $authors;
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
        $validTypes = ['full', 'brief', 'significancestatement', 'notes', 'note', "lineage"];
        $abstracts = collect($descriptions)
            ->filter(function ($description) use ($validTypes) {
                // get only the descriptions that has those types
                return in_array(strtolower($description['type']), $validTypes);
            })->sortBy(function ($description) use ($validTypes) {
                // sort the descriptions by the order of the valid Types
                return array_search(strtolower($description['type']), $validTypes);
            })->pluck('value');

        $abstract = !$abstracts->count() ? 'Not available' : implode(NL, $abstracts->toArray());
        $this->DCIRoot->addChild('Abstract', StrUtil::xmlSafe($abstract));
    }

    private function getRightsAndLicencing()
    {
        $rights = MetadataProvider::getRights($this->record);

        $rightsStatement = collect($rights)
            ->pluck('rightsStatement')->map(function($item){
                return $item['value'] ." ". $item['uri'];
            })->filter(function($item){
                return trim($item) != "";
            });
        $accessRights = collect($rights)
            ->pluck('accessRights')->map(function($item){
                return $item['value'] ." ". $item['uri'];
            })->filter(function($item){
                return trim($item) != "";
            });
        $licenseStatement = collect($rights)
            ->pluck('licence')->map(function($item){
                return $item['value'] ." ". $item['uri'];
            })->filter(function($item){
                return trim($item) != "";
            });

        $rightsStatement = collect($rightsStatement)->merge($accessRights)->implode(' ');
        $licenseStatement = collect($licenseStatement)->implode(' ');

        if ($rightsStatement || $licenseStatement) {
            $licensing = $this->DCIRoot->addChild('Rights_Licensing');
            $licensing->addChild('RightsStatement', $rightsStatement);
            $licensing->addChild('LicenseStatement', $licenseStatement);
        }
    }

    private function getDescriptorsData()
    {
        $descriptor = $this->DCIRoot->addChild('DescriptorsData');

        // Keywords
        $subjects = SubjectProvider::processSubjects($this->record);
        if (count($subjects) > 0) {
            $keywordsList = $descriptor->addChild('KeywordsList');
            foreach ($subjects as $subject) {
                $subjectText = array_key_exists('resolved', $subject) ? $subject['resolved'] : $subject['value'];
                $keywordsList->addChild('Keyword', StrUtil::xmlSafe($subjectText));
            }
        }

        // DataType
        $descriptor->addChild('DataType', $this->record->type);

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