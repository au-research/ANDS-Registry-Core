<?php

namespace ANDS\Registry\Suggestors;

use ANDS\Log\Log;
use ANDS\Mycelium\RelationshipSearchService;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Suggestor that would return the related datasets based on a list of priority relationTypes
 *
 * @see \Suggest
 * @see \Related_object_suggestor
 */
class RelatedDatasetSuggestor implements RegistryObjectSuggestor
{

    /**
     * Suggest Records based on related objects.
     */
    private $relationMappings = [
        'collection' =>  [
            'describes',
            'isLocationFor',
            'isDescribedBy',
            'isLocatedIn',
            'hasAssociationWith'
        ],
        'party' =>  [
            'isPrincipalInvestigatorOf',
            'hasPrincipalInvestigator',
            'principalInvestigator',
            'author',
            'coInvestigator',
            'hasCoInvestigator',
            'isOwnedBy',
            'hasCollector',
            'isManagedBy',
            'enriches',
            'hasAssociationWith',
        ],
        'activity' => [
            'isPrincipalInvestigatorOf',
            'isPartOf',
            'isOutputOf',
            'hasAssociationWith',
            'isManagerOf',
            'isManagedBy',
            'isOwnedBy',
            'hasAssociationWith',
            'isOwnerOf',
        ],
        'service' => []// any relationship type
    ];

    public function suggest(RegistryObject $record)
    {
        $parameter = [
            'from_identifier' => $record->id,
            'to_identifier_type' => '"ro:id"',
            'to_class' => 'collection'
        ];

        /**
         * filter by the relationTypes
         * and also boost by those relationTypes in decreasing value
         * @see RelationshipSearchService::getSolrParameters
         */
        $relationTypes = $this->relationMappings[$record->class];
        if (count($relationTypes)) {
            $parameter['relation_type'] = join(',', $relationTypes);
            $parameter['boost_relation_type'] = join(',', $relationTypes);
        }

        try {
            $result = RelationshipSearchService::search($parameter, ['rows' => 100])->toArray();
            return collect($result['contents'])->map(function($rel) {

                // find the related object slug dynamically because it's not indexed
                // could potentially extract from the to_url
                // unsure slug is really needed
                $slug = '';
                $relatedRecord = RegistryObjectsRepository::getRecordByID($rel['to_identifier']);
                if ($relatedRecord) {
                    $slug = $relatedRecord->slug;
                }

                // relation_type is multiples now, so it's a comma separated value
                return [
                    'id' => $rel['to_identifier'],
                    'key' => array_key_exists('to_key', $rel) ? $rel['to_key'] : '',
                    'slug' => $slug,
                    'title' => $rel['to_title'],
                    'class' => $rel['to_class'],
                    'RDAUrl' => $rel['to_url'],
                    'relation_type' => join(',', collect($rel['relations'])->pluck('relation_type')->unique()->toArray()),
                    'score' => array_key_exists('score', $rel) ? $rel['score'] : 1
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error(__METHOD__ . " Error occur while suggesting dataset for RegistryObject[id={$record->id}]. Message: {$e->getMessage()}");
            return [];
        }
    }
}