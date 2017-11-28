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

        // collect descriptions
        $descriptions = DescriptionProvider::get($record);
        if ($descriptions['primary_description']) {
            $doc->set('short-description', $descriptions['primary_description']);
        }

        $doc->set('url', $record->portalUrl);

        // collect citations
        $citations = CitationProvider::get($record);
        $citationValue = $citations['full'];
        $citationType = $citations['full_style'];
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
        if ($identifiers && count($identifiers) > 0) {

            $identifiers = collect($identifiers)->map(function($item) {
                if (!in_array($item['type'], self::$validExternalIDsIdentifierType)) {
                    $item['type'] = 'other-id';
                }
                return $item;
            })->toArray();
            $doc->set('external-ids', $identifiers);
        }

        // contributors
        if ($contributors = self::getContributors($record, $data)) {
            $doc->set('contributors', $contributors);
        }

        return $doc->toXML();
    }

    private static function getContributors($record, $data)
    {
        $contributors = [];
        // registryObject/collection/citationInfo/citationMetadata/contributor
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class.'/ro:citationInfo/ro:citationMetadata/ro:contributor') AS $object) {
            // TODO: get contributor from citationMetadata

            $contributors[] = [
                'credit-name' => (string) $object->namePart,
                'contributor-orcid' => null,
                'contributor-attributes' => [
                    'contributor-sequence' => (string) $object->attributes()['seq'],
                    'contributor-role' => 'author'
                ]
            ];
        }

        $validParty = collect($data['relationships'])->filter(function($item) {
            return $item->to() && $item->hasRelationTypes(self::$validContributorRelationTypes);
        });

        foreach ($validParty as $party) {
            $contributors[] = [
                'credit-name' => $party->prop('to_title'),
                'contributor-orcid' => null, // TODO get contributor-orcid
                'contributor-attributes' => [
                    'contributor-sequence' => null,
                    'contributor-role' => self::getContributorRole($party)
                ]
            ];
        }

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

}