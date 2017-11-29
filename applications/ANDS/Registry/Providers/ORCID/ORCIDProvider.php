<?php
namespace ANDS\Registry\Providers\ORCID;
use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RegistryContentProvider;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Registry\Providers\RIFCS\CitationProvider;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\RIFCS\DescriptionProvider;
use ANDS\Registry\Providers\RIFCS\IdentifierProvider;
use ANDS\Registry\Relation;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;
use DOMDocument;

/**
 * Class ORCIDProvider
 * @package ANDS\Registry\Providers\ORCID
 */
class ORCIDProvider implements RegistryContentProvider
{
    protected static $validExternalIDsIdentifierType = [
        'agr',
        'arxiv',
        'asin',
        'asin',
        'authenticusid',
        'bibcode',
        'cba',
        'cienciaiul',
        'cit',
        'ctx',
        'doi',
        'eid',
        'ethos',
        'grant_number',
        'handle',
        'hir',
        'isbn',
        'issn',
        'jfm',
        'jstor',
        'kuid',
        'lccn',
        'lensid',
        'mr',
        'oclc',
        'ol',
        'osti',
        'other',
        'pat',
        'pdb',
        'pmc',
        'pmid',
        'rfc',
        'rrid',
        'source-work-id',
        'ssrn',
        'uri',
        'urn',
        'wosuid',
        'zbl'
    ];

    protected static $validContributorRelationTypes = [
        'hasPrincipalInvestigator',
        'author',
        'coInvestigator',
        'isOwnedBy',
        'hasCollector'
    ];

    /**
     * Process the object and (optionally) store processed data
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function process(RegistryObject $record)
    {
        return;
    }

    /**
     * Return the processed content for given object
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function get(RegistryObject $record)
    {
        return [
            'xml' => self::getORCIDXML($record, new ORCIDRecord())
        ];
    }

    /**
     * Getting the ORCID in XML format
     *
     * @param RegistryObject $record
     * @param ORCIDRecord $orcid
     * @return string
     */
    public static function getORCIDXML(RegistryObject $record, ORCIDRecord $orcid)
    {
        $doc = self::getORCID($record, $orcid);
        return $doc->toXML();
    }

    private static function getContributors($record, $data)
    {
        $contributors = [];
        // registryObject/collection/citationInfo/citationMetadata/contributor
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class.'/ro:citationInfo/ro:citationMetadata/ro:contributor') AS $object) {

            // TODO: Refactor this to a common provider, used in a lot of places
            $name = [];
            $order = ['given', 'family'];
            foreach ($order as $o) {
                foreach ($object->namePart as $namePart) {
                    if ((string) $namePart['type'] == $o) {
                        $name[] = (string) $namePart;
                    }
                }
            }
            $name = implode(" ", $name);
            $contributors[] = [
                'credit-name' => $name,
                'contributor-orcid' => null,
                'contributor-attributes' => [
                    'contributor-sequence' => (string) $object->attributes()['seq'],
                    'contributor-role' => 'author'
                ]
            ];
        }

        // does not find more contributors if there's enough from citationMetadata
        if (count($contributors) > 0) {
            $contributors = self::remapContributorsSequences($contributors);
            return $contributors;
        }

        $validParty = collect($data['relationships'])->filter(function($item) {
            return $item->hasRelationTypes(self::$validContributorRelationTypes, true);
        });

        foreach ($validParty as $party) {
            $title = $party->prop('to_title');
            if (!$title) {
                $title = $party->prop('relation_to_title');
            }

            // check for contributor-orcid in record identifier
            $orcids = [];
            if ($party->to()) {
                $identifiers = IdentifierProvider::get($party->to());
                $orcids = array_merge($orcids, collect($identifiers)->filter(function($item) {
                    return $item['type'] == 'orcid';
                })->toArray());
            }

            // check for contributor-orcid in relatedInfo
            if ($party->prop('to_identifier_type') == "orcid") {
                $orcids[] = [
                    'value' => $party->prop('to_identifier'),
                    'type' => 'orcid'
                ];
            }
            if (count($orcids) > 0) {
                $orcids = self::formatContributorOrcid($orcids);
            }

            $contributors[] = [
                'credit-name' => $title ,
                'contributor-orcid' => count($orcids) ? $orcids : null,
                'contributor-attributes' => [
                    'contributor-sequence' => null,
                    'contributor-role' => self::getContributorRole($party)
                ]
            ];
        }

        // first, additional mapping for sequence
        $contributors = self::remapContributorsSequences($contributors);

        return $contributors;
    }



    public static function getContributorRole($party)
    {
        $mapping = [
            "hasPrincipalInvestigator" => "principal-investigator",
            "author" => "author",
            "coinvestigator" => "co-investigator"
        ];

        foreach ($mapping as $key => $value) {
            if ($party->hasRelationType($key)) {
                return $value;
            }
        }
        return "author";
    }

    public static function getORCID(RegistryObject $record, ORCIDRecord $orcid)
    {
        $data = MetadataProvider::get($record);

        $doc = new ORCIDDocument();

        // check if this is an update
        $existing = $orcid->exports->filter(function ($item) use ($record) {
            return $item->registry_object_id === $record->registry_object_id && $item->in_orcid;
        })->first();
        if ($existing) {
            $doc->set('put_code', $existing->put_code);
        }

        $doc->set('title', $record->title);

        // type
        switch ($record->type) {
            case 'collection':
            case 'dataset' :
                $type = 'data-set';
                break;
            case 'publication':
                $type = 'journal-article';
                break;
            default:
                $type = 'other';
                break;
        }
        $doc->set('work-type', $type);

        // collect descriptions
        $descriptions = DescriptionProvider::get($record);
        if ($descriptions['primary_description']) {
            $doc->set('short-description', $descriptions['primary_description']);
        }

        $doc->set('url', $record->portalUrl);

        // collect citations
        $citations = CitationProvider::get($record);
        $citationValue = $citations['full'];

        $citationType = self::getFullCitationStyle($citations['full_style']);
        if (!$citationValue) {
            $citationValue = $citations['bibtex'];
            $citationType = 'bibtex';
        }

        $doc->set('citation', [
            'type' => $citationType,
            'value' => $citationValue
        ]);

        // dates
        $publicationDate = DatesProvider::getPublicationDate($record, $data);
        if ($publicationDate) {
            $doc->set('publication-date', [
                'year' => DatesProvider::formatDate($publicationDate, 'Y'),
                'month' => DatesProvider::formatDate($publicationDate, 'm'),
                'day' => DatesProvider::formatDate($publicationDate, 'd')
            ]);
        }

        // external-ids
        $identifiers = IdentifierProvider::get($record);
        $identifiers[] = [
            'type' => 'other-id',
            'value' => $record->portalUrlWithKey
        ];

        $identifiers = collect($identifiers)->map(function($item) {
            if (!in_array($item['type'], self::$validExternalIDsIdentifierType)) {
                $item['type'] = 'other-id';
            }
            return $item;
        })->toArray();

        $doc->set('external-ids', $identifiers);


        // contributors
        if ($contributors = self::getContributors($record, $data)) {
            $doc->set('contributors', $contributors);
        }

        return $doc;
    }

    private static function getFullCitationStyle($full_style)
    {
        $mapping = [
            'Harvard' => 'formatted-harvard',
            'APA' => 'formatted-apa',
            'MLA' => 'formatted-mla',
            'Vancouver' => 'formatted-vancouver',
            'IEEE' => 'formatted-ieee',
            'Chicago' => 'formatted-chicago'
        ];
        if (array_key_exists($full_style, $mapping)) {
            return $mapping[$full_style];
        }
        return 'formatted-unspecified';
    }

    /**
     * @param $contributors
     * @return array
     */
    private static function remapContributorsSequences($contributors)
    {
        $contributors = collect($contributors)->map(function ($contributor) {
            if ($seq = $contributor['contributor-attributes']['contributor-sequence']) {
                $contributor['contributor-attributes']['contributor-sequence'] = $seq == 1 ? "first" : "additional";
            }
            return $contributor;
        })->toArray();
        return $contributors;
    }

    private static function formatContributorOrcid($orcids)
    {
        return collect($orcids)->unique()->map(function ($item) {
            $value = $item['value'];
            $prefix = "https://orcid.org/";
            $uri = strpos($value, 'http', 0) ? $value : $prefix.$value;
            $path = !strpos($value, 'http', 0) ? $value : end(explode('/', $value));
            return [
               'uri' => $uri,
               'path' => $path,
               'host' => 'orcid.org'
           ];
        })->first();
    }

}