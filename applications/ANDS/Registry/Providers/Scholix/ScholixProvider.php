<?php


namespace ANDS\Registry\Providers;


use ANDS\API\Task\ImportSubTask\ProcessDelete;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\RIFCS\IdentifierProvider;
use ANDS\Registry\Providers\RIFCS\LocationProvider;
use ANDS\Registry\Providers\Scholix\Scholix;
use ANDS\Registry\Providers\Scholix\ScholixDocument;
use ANDS\Registry\Relation;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\XMLUtil;

class ScholixProvider implements RegistryContentProvider
{
    protected static $scholixableAttr = "scholixable";
    public static $validSourceIdentifierTypes = ["ark","doi","handle","purl","uri ","url"];
    public static $validTargetIdentifierTypes = ['ark','doi','eissn','handle','isbn','issn','pubMedId','purl','uri','url'];

    /**
     * if the record is a collection
     * and is related to a type of publication
     *
     * @param RegistryObject $record
     * @param null $relationships
     * @return bool
     */
    public static function isScholixable(RegistryObject $record, $relationships = null)
    {
        // early return if it's not a collection
        if ($record->class != "collection") {
            return false;
        }

        // record type needs to be a dataset or a collection
        if (!in_array($record->type, ['dataset', 'collection'])) {
            return false;
        }

        // search through combined relationships to see if there's a related publication
        if (!$relationships) {
            $relationships = RelationshipProvider::getMergedRelationships($record);
        }

        $types = collect($relationships)->map(function($item) {
            return $item->prop('to_type') ?: $item->prop('to_related_info_type');
        })->toArray();

        if (!in_array('publication', $types)) {
            return false;
        }

        return true;
    }

    /**
     * Process the object and (optionally) store processed data
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function process(RegistryObject $record)
    {
        // determine Scholixable
        if (!self::isScholixable($record)) {
            return [
                'total' => 0,
                'updated' => [],
                'created' => [],
                'unchanged' => [],
            ];
        }

        // scholixable, proceed to create links
        $scholixDocuments = self::get($record);
        $links = $scholixDocuments->getLinks();

        $report = [
            'total' => count($links),
            'updated' => [],
            'created' => [],
            'unchanged' => []
        ];

        $new = [];
        foreach ($links as $link) {
            $id = $scholixDocuments->getLinkIdentifier($link);
            $new[] = $id;
            $xml = $scholixDocuments->json2xml($link['link']);
            $exist = Scholix::where('scholix_identifier', $id)->first();

            if ($exist) {
                // report
                if ($exist->hash != md5($xml)) {
                    $report['updated'][] = $id;
                } else {
                    $report['unchanged'][] = $id;
                }

                // update
                $exist->data = $xml;
                $exist->hash = md5($xml);
                $exist->save();

                continue;
            }

            $report['created'][] = $id;

            // create
            $scholix = new Scholix;
            $scholix->setRawAttributes([
                'scholix_identifier' => $id,
                'data' => $xml,
                'registry_object_id' => $record->id,
                'registry_object_data_source_id' => $record->data_source_id,
                'registry_object_group' => $record->group,
                'hash' => md5($xml),
                'registry_object_class' => $record->class
            ]);
            $scholix->save();
        }

        // delete existing links that are not generated (removed relationships)
        $exist = Scholix::where('registry_object_id', $record->id)
            ->pluck('scholix_identifier')->toArray();
        $shouldBeDeleted = array_diff($exist, $new);
        Scholix::whereIn('scholix_identifier', $shouldBeDeleted)->delete();

        return $report;
    }

    /**
     * Return the processed content for given object
     *
     * @param RegistryObject $record
     * @return ScholixDocument
     */
    public static function get(RegistryObject $record)
    {
        $data = MetadataProvider::get($record);

        $doc = new ScholixDocument;
        $doc->record = $record;

        $commonLinkMetadata = [
            'publicationDate' => DatesProvider::getCreatedDate($record),
            'publisher' => [
                'name' => $record->group
            ],
            'linkProvider' => [
                'name' => 'Australian National Data Service',
                'identifier' => [
                    ['identifier' =>  'http://nla.gov.au/nla.party-1508909',
                    'schema' => 'AU-ANL:PEAU']
                ],
                'objectType' => $record->type,
                'title' => $record->title
            ]
        ];

        // identifiers
        $identifiers = self::getIdentifiers($record, $data['relationships']);
        if (count($identifiers) > 0) {
            $commonLinkMetadata['publisher']['identifier'] = $identifiers;
        }

        /**
         * Business Rule:
         * for each collection/identifier OR citationInfo/citationMetadata/identifier OR key
         * Produces a link to each of the related publication
         */

        $relatedPublications = self::getRelatedPublications($record, $data);

        // construct targets
        $targets = [];
        foreach ($relatedPublications as $publication) {
            if ($to = $publication->to()) {

                // toIdentifiers
                $toIdentifiers = array_merge(
                    IdentifierProvider::get($to),
                    IdentifierProvider::getCitationMetadataIdentifiers($to)
                );

                // only go for valid target identifiers type
                $toIdentifiers = collect($toIdentifiers)->filter(function($item) {
                    return in_array($item['type'], self::$validTargetIdentifierTypes);
                })->toArray();

                // should be unique
                $toIdentifiers = collect($toIdentifiers)->unique()->toArray();

                if (count($toIdentifiers) == 0) {
                    $targets[] = [
                        'relationship' => $publication,
                        'target' => self::getTargetMetadataObject($publication, [])
                    ];
                }

                foreach ($toIdentifiers as $identifier) {
                    $targets[] = [
                        'relationship' => $publication,
                        'target' => self::getTargetMetadataObject($publication, $identifier)
                    ];
                }
            } else {
                $targets[] = [
                    'relationship' => $publication,
                    'target' =>  self::getTargetMetadataRelatedInfo($publication)
                ];
            }
        }

        // collection/identifier
        // collection/citationInfo/citationMetadata/identifier
        $identifiers = array_merge(
            IdentifierProvider::get($record, $data['recordData']),
            IdentifierProvider::getCitationMetadataIdentifiers($record, $data['recordData'])
        );

        //unique
        $identifiers = collect($identifiers)->filter(function($item){
            return in_array($item['type'], self::$validSourceIdentifierTypes);
        })->unique()->toArray();

        foreach ($identifiers as $identifier) {
            $identifierlink = $commonLinkMetadata;
            $identifierlink['source'] = self::getIdentifierSource($record, $identifier, $data);
            foreach ($targets as $target) {

                unset($identifierlink['relationship']);

                $identifierlink['relationship'] = self::getRelationships($target['relationship']);

                $identifierlink['target'] = $target['target'];
                $doc->addLink($identifierlink);
            }
        }

        // return the ScholixDocument if there's enough links
        if (count($doc->toArray()) > 0) {
            return $doc;
        }

        // second option, use collection/location/address/electronic[@type='url'] if no identifiers found
        $urls = LocationProvider::getElectronicUrl($record, $data['recordData']);
        if (count($urls) > 0) {
            foreach ($urls as $url) {
                $electronicUrlLink = $commonLinkMetadata;
                $electronicUrlLink['source'] = self::getElectronicUrlSource($record, $url);
                foreach ($targets as $target) {
                    $electronicUrlLink['relationship'] = self::getRelationships($target['relationship']);
                    $electronicUrlLink['target'] = $target['target'];
                    $doc->addLink($electronicUrlLink);
                }
            }
        }

        // return if there's enough links
        if (count($doc->toArray()) > 0) {
            return $doc;
        }

        // last resort, use key as a source
        $keyLink = $commonLinkMetadata;
        $keyLink['source'] = self::getKeySource($record, $data);
        foreach ($targets as $target) {
            $keyLink['relationship'] = self::getRelationships($target['relationship']);
            $keyLink['target'] = $target['target'];
            $doc->addLink($keyLink);
        }

        return $doc;
    }

    public static function getKeySource(RegistryObject $record, $data = null)
    {
        if (!$data) {
            $data = MetadataProvider::get($record);
        }

        $source = [
            'identifier' => [
                [
                    'identifier' => baseUrl('view?key=') . $record->key,
                    'schema' => 'url'
                ]
            ],
            'title' => $record->title,
            'objectType' => $record->type,
            'publicationDate' => DatesProvider::getPublicationDate($record),
            'publisher' => [
                'name' => $record->group
            ]
        ];

        $creators = self::getSourceCreators($record, $data);
        if (count($creators) > 0) {
            $source['creator'] = $creators;
        }

        return $source;
    }

    public static function getElectronicUrlSource(RegistryObject $record, $url)
    {
        $source = [
            'identifier' => [
                [
                    'identifier' => $url,
                    'schema' => 'url'
                ]
            ],
            'title' => $record->title,
            'objectType' => $record->type,
            'publicationDate' => DatesProvider::getPublicationDate($record),
            'publisher' => [
                'name' => $record->group
            ]
        ];

        $creators = self::getSourceCreators($record);
        if (count($creators) > 0) {
            $source['creator'] = $creators;
        }

        return $source;
    }

    /**
     * Returns possible related publications
     *
     * @param RegistryObject $record
     * @param null $data
     * @return Relation[]
     */
    public static function getRelatedPublications(RegistryObject $record, $data = null)
    {
        if (!$data) {
            $data = MetadataProvider::get($record);
        }

        $relationships = collect($data['relationships'])->filter(function($item) {
            $type = $item->prop('to_related_info_type');
            if (!$type) {
                $type = $item->prop('to_type');
            }

            if ($type == 'publication') {
                return true;
            }

            // check if the actual object exists, and then check the type
            if ($to = $item->to()) {
                if ($to->type == "publication") {
                    return true;
                }
            }

            return false;
        })->filter(function($item){
            // remove item with non valid identifier types
            $identiferType = $item->prop('to_identifier_type');
            if ($identiferType && !in_array($identiferType, self::$validTargetIdentifierTypes)) {
                return false;
            }
            return true;
        })->toArray();

        return $relationships;
    }

    /**
     * Returns the relationships for the given link
     *
     * @param Relation $publication
     * @return array
     */
    public static function getRelationships(Relation $publication)
    {
        $relationType = $publication->prop('relation_type');
        $relationOrigin = $publication->prop('relation_origin');
        if (is_array($relationOrigin)) {
            $relationOrigin = implode(" ", $relationOrigin);
        }

        $relationships = [];

        if (is_string($relationType)) {
            $relationType = explode(', ', $relationType);
        }

        if (is_array($relationType)) {
            foreach ($relationType as $type) {

                if (strpos($relationOrigin, "REVERSE") > -1) {
                    $type = getReverseRelationshipString($type);
                }

                $relationships[] = [
                    'name' => $type,
                    'schema' => 'RIF-CS',
                    'inverse' => getReverseRelationshipString($type)
                ];
            }
        }

        return $relationships;
    }

    /**
     * Find the identifiers of a relatedObject/party
     * with the title = record[group]
     * @param RegistryObject $record
     * @param null $relationships
     * @return array
     */
    public static function getIdentifiers(RegistryObject $record, $relationships = null)
    {
        if (!$relationships) {
            $data = MetadataProvider::getSelective($record, ['relationships']);
            $relationships = $data['relationships'];
        }

        $party = collect($relationships)->filter(
            function($relation) use ($record){
                return $relation->prop('to_class') == 'party'
                    && $relation->prop('to_title') == $record->group;
            }
        );

        if ($party->count() == 0) {
            return [];
        }

        // get 1
        $party = $party->pop();
        $party = RegistryObjectsRepository::getRecordByID($party->prop('to_id'));
        $identifiers = collect(IdentifierProvider::get($party))
            ->map(function($item){
                return [
                    'identifier' => $item['value'],
                    'schema' => $item['type']
                ];
            }
        )->toArray();


        return $identifiers;
    }

    /**
     * Returns the Scholix Source Element for a given identifier
     *
     * @param RegistryObject $record
     * @param $identifier
     * @param null $data
     * @return array
     */
    private static function getIdentifierSource(RegistryObject $record, $identifier, $data = null)
    {
        if (!$data) {
            $data = MetadataProvider::get($record);
        }

        $source = [
            'identifier' => [
                [
                    'identifier' => $identifier['value'],
                    'schema' => $identifier['type']
                ]
            ],
            'title' => $record->title,
            'objectType' => $record->type,
            'publicationDate' => DatesProvider::getPublicationDate($record),
            'publisher' => [
                'name' => $record->group
            ]
        ];

        $creators = self::getSourceCreators($record, $data);
        if (count($creators) > 0) {
            $source['creator'] = $creators;
        }

        return $source;
    }

    public static function getSourceCreators(RegistryObject $record, $data = null)
    {
        if (!$data) {
            $data = MetadataProvider::get($record);
        }

        /**
         * source[creator]
         * relatedObject/party
         * relatedInfo/relation/party
         */
        $creators = collect($data['relationships'])->filter(function($item) {

            $validRelations = ['hasPrincipalInvestigator', 'hasAuthor', 'coInvestigator', 'isOwnedBy', 'hasCollector', "author"];

            if ($item->isReverse()) {
                return in_array(getReverseRelationshipString($item->prop('relation_type')), $validRelations) && ($item->prop('to_class') == "party");
            }

            return in_array($item->prop('relation_type'), $validRelations) && ($item->prop('to_class') == "party");

        });

        // map and get identfiers
        $creators = $creators->map(function($item) {
            $to = $item->to();
            $creator = [
                'name' => $to->title
            ];
            $identifiers = collect(IdentifierProvider::get($to))->map(function($item) {
                return [
                    'identifier' => $item['value'],
                    'schema' => $item['type']
                ];
            })->toArray();
            if (count($identifiers) > 0) {
                $creator['identifier'] = $identifiers;
            }
            return $creator;
        })->values()->toArray();

        /**
         * source[creator]
         * citationMetadata/contributor
         * TODO
         */
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:citationInfo/ro:citationMetadata/ro:contributor') AS $contributor) {
            $nameParts = [];
            foreach ($contributor->namePart as $part) {
                $nameParts[] = [
                    'value' => (string) $part,
                    'type' => (string) $part['type']
                ];
            }

            $given = collect($nameParts)->filter(function($item){
                return $item['type'] == 'given';
            })->pluck('value')->first();

            $family = collect($nameParts)->filter(function($item){
                return $item['type'] == 'family';
            })->pluck('value')->first();

            $name = "";

            if ($given || $family) {
                $name = "$given, $family";
            }

            // first name part if no given nor family
            if ((!$given || !$family) && (count($nameParts) > 0)){
                $name = $nameParts[0]['value'];
            }

            $creators[] = [ 'name' => $name ];
        }

        return $creators;
    }

    public static function getTargetMetadataRelatedInfo($publication)
    {
        $target = [
            'identifier' => [
                ['identifier' => $publication->prop('to_identifier'),
                'schema' => $publication->prop('to_identifier_type')]
            ],
            'objectType' => 'literature'
        ];

        // no publication date

        if ($publication->prop('to_title')) {
            $target['title'] = $publication->prop('to_title');
        }

        if ($publication->prop('relation_to_title')) {
            $target['title'] = $publication->prop('relation_to_title');
        }

        // No creator

        return $target;
    }

    public static function getTargetMetadataObject($publication, $identifier = [])
    {
        $record = $publication->to();

        $identifiers = [];

        if ($identifier) {
            $identifiers = [
                [
                    'identifier' => $identifier['value'],
                    'schema' => $identifier['type']
                ]
            ];
        }

        // use key as last resort
        if (count($identifiers) === 0) {
            $identifiers = [
                [
                    'identifier' => baseUrl('view?key=') . $record->key,
                    'schema' => 'Research Data Australia'
                ]
            ];
        }

        $target = [
            'identifier' => $identifiers,
            'objectType' => $record->type,
            'title' => $record->title,
            'publicationDate' => DatesProvider::getPublicationDate($record),
            'publisher' => [
                'name' => $record->group
            ]
        ];

        $data = MetadataProvider::getSelective($record, ['relationships', 'recordData']);

        // relation[@type=author]
        $creators = collect($data['relationships'])->filter(function($item) {
            $validRelations = ['author'];
            return in_array($item->prop('relation_type'), $validRelations) && ($item->prop('to_class') == "party");
        })->map(function($item) {
            $to = $item->to();
            $creator = [
                'name' => $to->title
            ];
            $identifiers = collect(IdentifierProvider::get($to))->map(function($item) {
                return [
                    'identifier' => $item['value'],
                    'schema' => $item['type']
                ];
            })->toArray();
            if (count($identifiers) > 0) {
                $creator['identifier'] = $identifiers;
            }
            return $creator;
        })->values()->toArray();

        // citationMetadata/contributor
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:citationInfo/ro:citationMetadata/ro:contributor') AS $contributor) {
            $nameParts = [];
            foreach ($contributor->namePart as $part) {
                $nameParts[] = [
                    'value' => (string) $part,
                    'type' => (string) $part['type']
                ];
            }

            $given = collect($nameParts)->filter(function($item){
                return $item['type'] == 'given';
            })->pluck('value')->first();

            $family = collect($nameParts)->filter(function($item){
                return $item['type'] == 'family';
            })->pluck('value')->first();

            $name = "";

            if ($given || $family) {
                $name = "$given, $family";
            }

            // first name part if no given nor family
            if ((!$given || !$family) && (count($nameParts) > 0)){
                $name = $nameParts[0]['value'];
            }

            $creators[] = [ 'name' => $name ];
        }

        if (count($creators) > 0) {
            $target['creator'] = $creators;
        }

        return $target;
    }
}