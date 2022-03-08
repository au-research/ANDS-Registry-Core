<?php

namespace ANDS\Registry\Providers\Scholix;

use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RegistryContentProvider;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\RIFCS\IdentifierProvider;
use ANDS\Registry\Providers\RIFCS\LocationProvider;
use ANDS\Registry\Relation;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\XMLUtil;
use function baseUrl;
use function collect;
use function getReverseRelationshipString;

class ScholixProvider implements RegistryContentProvider
{
    protected static $scholixableAttr = "scholixable";
    public static $validSourceIdentifierTypes = [
        "ark" => 'ark',
        "doi" => 'doi',
        "handle" => 'hdl',
        "purl" => 'purl',
        "uri" => 'url',
        "url" => 'url'
    ];
    public static $validTargetIdentifierTypes = [
        "ark" => 'ark',
        "doi" => 'doi',
        'eissn' => 'issn',
        "handle" => 'hdl',
        'isbn' => 'isbn',
        'issn' => 'issn',
        'pubMedId' => 'pubmed',
        "purl" => 'purl',
        "uri" => 'url',
        "url" => 'url'
    ];

    /**
     * if the record is a collection
     * and is related to a type of publication
     *
     * @param RegistryObject $record
     * @return bool
     */
    public static function isScholixable(RegistryObject $record)
    {
        // early return if it's not a collection
        if ($record->class != "collection") {
            return false;
        }

        // record type needs to be a dataset or a collection
        if (!in_array($record->type, ['dataset', 'collection'])) {
            return false;
        }

        // record needs to be related to a publication
        return (RelationshipProvider::hasRelatedClass($record,'publication'));
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
        $report['deleted'][] = $shouldBeDeleted;

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
                    [
                        'identifier' =>  'http://nla.gov.au/nla.party-1508909',
                        'schema' => 'url'
                    ]
                ],
                'objectType' => $record->type,
                'title' => $record->title
            ]
        ];

        // identifiers
        $identifiers = self::getIdentifiers($record);
        if (count($identifiers) > 0) {
            $commonLinkMetadata['publisher']['identifier'] = $identifiers;
        }

        /**
         * Business Rule:
         * for each collection/identifier OR citationInfo/citationMetadata/identifier OR key
         * Produces a link to each of the related publication
         */

        $relatedPublications = self::getRelatedPublications($record);

        // construct targets
        $targets = [];
        foreach ($relatedPublications as $publication) {
           // if ($to = $publication->to()) {
            if($publication['to_identifier_type'] == 'ro:id'){
                $to = RegistryObjectsRepository::getRecordByID($publication['to_identifier']);

                // toIdentifiers
                $toIdentifiers = array_merge(
                    IdentifierProvider::get($to),
                    IdentifierProvider::getCitationMetadataIdentifiers($to)
                );

                // only go for valid target identifiers type
                $toIdentifiers = collect($toIdentifiers)->filter(function($item) {
                    return in_array($item['type'], array_keys(self::$validTargetIdentifierTypes));
                })->toArray();

                // should be unique and format properly
                $toIdentifiers = collect($toIdentifiers)->unique()->map(function($item) {
                    $item['type'] = self::$validTargetIdentifierTypes[$item['type']];
                    return $item;
                })->toArray();

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
            IdentifierProvider::get($record, $record->getCurrentData()->data),
            IdentifierProvider::getCitationMetadataIdentifiers($record,  $record->getCurrentData()->data)
        );


        //unique and format
        $identifiers = collect($identifiers)->filter(function($item){
            return in_array($item['type'], array_keys(self::$validSourceIdentifierTypes));
        })->unique()->map(function($item) {
            $item['type'] = self::$validSourceIdentifierTypes[$item['type']];
            return $item;
        })->toArray();

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
        $keyLink['source'] = self::getKeySource($record);
        foreach ($targets as $target) {
            $keyLink['relationship'] = self::getRelationships($target['relationship']);
            $keyLink['target'] = $target['target'];
            $doc->addLink($keyLink);
        }

        return $doc;
    }

    public static function getKeySource(RegistryObject $record)
    {
        $source = [
            'identifier' => [
                [
                    'identifier' => baseUrl($record->slug) ."/". $record->id,
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
     * @return Relation[]
     */
    public static function getRelatedPublications(RegistryObject $record)
    {
        $publications = [];
        $relatedPublications = RelationshipProvider::getRelationByClassTypeRelationType($record,'','publication',[]);
        foreach( $relatedPublications as $publication){
            //we have a relatedInfo publication with a valid identifier type
            if ($publication['to_identifier_type'] && in_array($publication['to_identifier_type'], array_keys(self::$validTargetIdentifierTypes))) {
                $publications[] = $publication;
            }
            if($publication['to_identifier_type'] == 'ro:id'){
                //we have a relatedObject publication - need to check if at least one of its identifiers are a valid type
                 $relatedObjectPublication = RegistryObjectsRepository::getRecordByID($publication['to_identifier']);
                 $objectPublications = IdentifierProvider::get($relatedObjectPublication);
                 $validFound = false;
;                foreach($objectPublications as $objectPublication) {
                    if ( in_array($objectPublication['type'], array_keys(self::$validTargetIdentifierTypes))) {
                        $validFound = true;
                    }
                }
                if($validFound) $publications[] = $publication;
            }
        }
        return $publications;
    }

    /**
     * Returns the relationships for the given link
     *
     * @param array $publication (relationship)
     * @return array
     */
    public static function getRelationships(array $publication)
    {
        $relationships = [];

        foreach($publication['relations'] as $relation){
            $relationships[] = [
                'name' => $relation['relation_type'],
                'schema' => 'RIF-CS',
                'inverse' => getReverseRelationshipString($relation['relation_type'])
            ];
        }

        return $relationships;
    }

    /**
     * Find the identifiers of a relatedObject/party
     * with the title = record[group]
     * @param RegistryObject $record
     * @return array
     */
    public static function getIdentifiers(RegistryObject $record)
    {
        $partyRelationships = RelationshipProvider::getRelationByClassAndType($record,'party',[]);

        foreach($partyRelationships as $party){
            if($party['to_identifier_type'] == 'ro:id' && $party['to_title'] == $party['from_group']) {
                $party = RegistryObjectsRepository::getRecordByID($party['to_identifier']);
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
        }
        return [];
    }

    /**
     * Returns the Scholix Source Element for a given identifier
     *
     * @param RegistryObject $record
     * @param $identifier
     * @param null $data
     * @return array
     */
    private static function getIdentifierSource(RegistryObject $record, $identifier)
    {
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

        $creators = self::getSourceCreators($record);
        if (count($creators) > 0) {
            $source['creator'] = $creators;
        }

        return $source;
    }

    public static function getSourceCreators(RegistryObject $record)
    {
        /**
         * source[creator]
         * relatedObject/party
         * relatedInfo/relation/party
         */

        $validRelations = ['hasPrincipalInvestigator', 'hasAuthor', 'coInvestigator', 'isOwnedBy', 'hasCollector', "author"];
        $authors = RelationshipProvider::getRelationByClassAndType($record,'party',$validRelations);


        // map and get identfiers
        foreach($authors as $author){
            if($author['to_identifier_type']== 'ro:id'){
                $to = RegistryObjectsRepository::getRecordByID($author['to_identifier']);
                $identifiers = collect(IdentifierProvider::get($to))->map(function($item) {
                    return [
                        'identifier' => $item['value'],
                        'schema' => $item['type']
                    ];
                })->toArray();
                if (count($identifiers) > 0) {
                    $creator['identifier'] = $identifiers;
                }
            }
            $creator = [
                'name' => $author['to_title']
            ];

            $creators[] = $creator;
        }


        /**
         * source[creator]
         * citationMetadata/contributor
         * TODO
         */
        foreach (XMLUtil::getElementsByXPath($record->getCurrentData()->data,
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
        $identifierType = $publication['to_identifier_type'];
        $identifierType = self::$validTargetIdentifierTypes[$identifierType];
        $target = [
            'identifier' => [
                [
                    'identifier' => $publication['to_identifier'],
                    'schema' => $identifierType
                ]
            ],
            'objectType' => 'literature'
        ];

        // no publication date

        if ($publication['to_title']) {
            $target['title'] = $publication['to_title'];
        }

      //  if ($publication['relation_to_title']) {
       //     $target['title'] = $publication['relation_to_title'];
      //  }

        // No creator

        return $target;
    }

    public static function getTargetMetadataObject($publication, $identifier = [])
    {
        $record = RegistryObjectsRepository::getRecordByID($publication['to_identifier']);

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
                    'identifier' => $publication['to_url'],
                    'schema' => 'url'
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

        $authors = RelationshipProvider::getRelationByClassAndType($record,'party',['author']);
        foreach($authors as $author){
            if($author['to_identifier_type']=='ro:id'){
                $to = RegistryObjectsRepository::getRecordByID($author['to_identifier']);
                $identifiers = collect(IdentifierProvider::get($to))->map(function($item) {
                    return [
                        'identifier' => $item['value'],
                        'schema' => $item['type']
                    ];
                })->toArray();
                if (count($identifiers) > 0) {
                    $creator['identifier'] = $identifiers;
                }
            }
            $creator['name']  = $author['to_title'];
            $creators[] = $creator;

        }

        // citationMetadata/contributor
        foreach (XMLUtil::getElementsByXPath($record->getCurrentData()->data,
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