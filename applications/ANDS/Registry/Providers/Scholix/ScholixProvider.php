<?php


namespace ANDS\Registry\Providers;


use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\Scholix\ScholixDocument;
use ANDS\Registry\Relation;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\XMLUtil;

class ScholixProvider implements RegistryContentProvider
{
    protected static $scholixableAttr = "scholixable";

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
            return $item->prop('to_related_info_type') ?: $item->prop('to_type');
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
        $record->deleteRegistryObjectAttribute(self::$scholixableAttr);

        $relationships = RelationshipProvider::getMergedRelationships($record);
        if (self::isScholixable($record, $relationships)) {
            $record->setRegistryObjectAttribute(self::$scholixableAttr, true);
        }

        return;
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

        $relationships = self::getRelationships($record, $data);
        if (count($relationships) > 0) {
            $commonLinkMetadata['relationship'] = $relationships;
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
                $toIdentifiers = IdentifierProvider::get($to);

                if (count($toIdentifiers) == 0) {
                    $targets[] = self::getTargetMetadataObject($publication, []);
                }

                foreach ($toIdentifiers as $identifier) {
                    $targets[] = self::getTargetMetadataObject($publication, $identifier);
                }
            } else {
                $targets[] = self::getTargetMetadataRelatedInfo($publication);
            }
        }


        // collection/identifier
        // collection/citationInfo/citationMetadata/identifier
        $identifiers = array_merge(
            IdentifierProvider::get($record, $data['recordData']),
            IdentifierProvider::getCitationMetadataIdentifiers($record, $data['recordData'])
        );

        //unique
        $identifiers = collect($identifiers)->unique()->toArray();

        foreach ($identifiers as $identifier) {
            $identifierlink = $commonLinkMetadata;
            $identifierlink['source'] = self::getIdentifierSource($record, $identifier, $data);
            foreach ($targets as $target) {
                $identifierlink['target'] = $target;
                $doc->addLink($identifierlink);
            }
        }

        // return the ScholixDocument if there's enough links
        if (count($doc->toArray()) > 0) {
            return $doc;
        }

        // last resort, use key as a source
        $keyLink = $commonLinkMetadata;
        $keyLink['source'] = self::getKeySource($record, $data);
        foreach ($targets as $target) {
            $identifierlink['target'] = $target;
            $doc->addLink($identifierlink);
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
                ['identifier' => baseUrl('view?key=') . $record->key, 'schema' => 'Research Data Australia']
            ],
            'objectType' => $record->type,
            'title' => $record->title,
            'creator' => []
        ];

        $creators = self::getSourceCreators($record, $data);
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
        })->toArray();
        return $relationships;
    }

    /**
     * Returns the relationships for the given link
     *
     * @param RegistryObject $record
     * @param null $data
     * @return array
     */
    public static function getRelationships(RegistryObject $record, $data = null)
    {
        if (!$data) {
            $data = MetadataProvider::get($record);
        }

        $relationships = collect($data['relationships'])->map(function($item) {
            return [
                'name' => $item->prop('relation_type'),
                'schema' => 'RIF-CS',
                'inverse' => getReverseRelationshipString($item->prop('relation_type'))
            ];
        })->filter(function($item) {
            return $item;
        })->values()->toArray();

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
            $validRelations = ['hasPrincipalInvestigator', 'hasAuthor', 'coInvestigator', 'isOwnedBy', 'hasCollector'];
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

            $name = "$given, $family";

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

        // TODO Creator
        // relation[@type=author]
        $creators = collect(RelationshipProvider::getMergedRelationships($record))->filter(function($item){
            return $item->prop('relation_type') == 'author';
        })->toArray();

        // TODO citationMetadata/contributor

        return $target;
    }
}