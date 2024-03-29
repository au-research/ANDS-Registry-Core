<?php


namespace ANDS\Registry\Providers\RIFCS;
use ANDS\Log\Log;
use ANDS\Registry\ContentProvider\ANZCTR\ContentProvider;
use ANDS\Registry\Providers\RelatedTitlesProvider;
use ANDS\RegistryObject;
use ANDS\Util\SolrIndex;
use MinhD\SolrClient\SolrClient;
use MinhD\SolrClient\SolrDocument;

class RIFCSIndexProvider
{

    public static function process(RegistryObject $record)
    {
        // TODO: Implement process() method.
    }

    /**
     * Provides an indexable array for the RIFCS record
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function get(RegistryObject $record, $includeSpatial = true)
    {
        if (!self::isIndexable($record)) {
            Log::debug("Not Indexable ". $record->id);
            return [];
        }

        $index = collect([])
            ->merge(CoreMetadataProvider::getIndexableArray($record))
            ->merge(TitleProvider::getIndexableArray($record))
            ->merge(DescriptionProvider::getIndexableArray($record))
            ->merge(IdentifierProvider::getIndexableArray($record))
            ->merge(SubjectProvider::getIndexableArray($record))
            ->merge(MatchingIdentifierProvider::getIndexableArray($record))
            ->merge(TagProvider::getIndexableArray($record))
            ->merge(RelatedTitlesProvider::getIndexableArray($record))
            ->merge(ThemePageProvider::getIndexableArray($record))
            ->merge(AccessRightsProvider::getIndexableArray($record))
            ->merge(LicenceProvider::getIndexableArray($record))
            ->merge(TemporalProvider::getIndexableArray($record))
            ->merge(RelatedInfoProvider::getIndexableArray($record))
            ->merge(CitationProvider::getIndexableArray($record))
            ->toArray();

        /* activity records should have grants metadata indexed */
        if ($record->class === "activity") {
            $index = collect($index)->merge(GrantsMetadataProvider::getIndexableArray($record))->toArray();
        }


        if ($record->type === "health.dataset") {
      /*
      <relatedInfo type="activity">
        <identifier type="uri">https://www.anzctr.org.au/Trial/Registration/TrialReview.aspx?ACTRN=12622000922774</identifier>
        <relation type="isOutputOf"/>
      </relatedInfo>
      */
            $index = collect($index)->merge(ContentProvider::getIndexableArray($record))->toArray();
            $index['anzctr_publisher'] = $record->group;
        }
        // try indexing without the spatial data if record has an invalid WKT
        // $includeSpatial is true by default but
        // the index handlers will need to catch "org.locationtech.jts.geom.TopologyException" from solr and
        // request a solr doc without spatial data if WKT is invalid otherwise the record will not be indexed at all
        if($includeSpatial){
            $index = collect($index)->merge(SpatialProvider::getIndexableArray($record))->toArray();
        }

        return $index;
    }

    /**
     * Determine whether the record should be indexed
     *
     * @param RegistryObject $record
     * @return bool
     */
    public static function isIndexable(RegistryObject $record)
    {
        // only index PUBLISHED records
        if (!$record->isPublishedStatus()) {
            return false;
        }

        // CC-1286. Remove Activity Records from Contributor 'Public Records of Victoria' from RDA
        if ($record->class === "activity" && $record->group === "Public Record Office Victoria") {
            return false;
        }

        return true;
    }

    /**
     * Index a RegistryObject
     *
     * @param \ANDS\RegistryObject $record
     * @param $index
     * @param \MinhD\SolrClient\SolrClient|null $solrClient
     * @return void
     */
    public static function indexRecord(RegistryObject $record, $index = [], SolrClient $solrClient = null)
    {
        Log::debug(__FUNCTION__ . " Indexing RegistryObject", ['id' => $record->id]);
        $solrClient = $solrClient !== null ? $solrClient : SolrIndex::getClient("portal");
        $index = is_array_empty($index) ? self::get($record) : $index;

        $result = $solrClient->add(new SolrDocument($index));

        Log::debug("Indexing Result", $result);
    }

    /**
     * Remove a RegistryObject from the Index
     *
     * @param \ANDS\RegistryObject $record
     * @param \MinhD\SolrClient\SolrClient|null $solrClient
     * @return void
     */
    public static function removeIndexRecord(RegistryObject $record, SolrClient $solrClient = null)
    {
        Log::debug(__FUNCTION__ . " Removing SOLR Index for RegistryObject", ['id' => $record->id]);
        $solrClient = $solrClient !== null ? $solrClient : SolrIndex::getClient("portal");

        $result = $solrClient->remove([$record->id]);
        Log::debug("Indexing Result", $result);
    }

    /**
     * Update a single field
     *
     * @param \ANDS\RegistryObject $record
     * @param $field
     * @param \MinhD\SolrClient\SolrClient|null $solrClient
     * @return void
     */
    public static function regenerateField(RegistryObject $record, $field, SolrClient $solrClient = null)
    {
        Log::debug(__FUNCTION__. " Updating SOLR indexed field for RegistryObject", [
            'id' => $record->id,
            'field' => $field
        ]);

        $update = [
            'id' => $record->id
        ];

        switch ($field) {
            case "tags":
                $index = TagProvider::getIndexableArray($record);
                $update['tag'] = [
                    'set' => $index['tag']
                ];
                break;
            default:
                Log::warning(__FUNCTION__ ." Unknown field", ["field" => $field]);
                return;
        }

        // todo check if the set value is not empty
//        if (collect($update)->isEmpty()) {
//            Log::warning(__FUNCTION__ ." Index is empty, nothing to update");
//            return;
//        }

        $solrClient = $solrClient !== null ? $solrClient : SolrIndex::getClient();
        $solrClient->request('POST', 'portal/update/json', [], json_encode([$update]), 'body');
        // todo check the response
    }
}