<?php


namespace ANDS\Registry\Providers\RIFCS;
use ANDS\Log\Log;
use ANDS\Registry\Providers\RelatedTitlesProvider;
use ANDS\RegistryObject;
use ANDS\Util\Config;
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
    public static function get(RegistryObject $record)
    {
        if (!self::isIndexable($record)) {
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
            ->merge(SpatialProvider::getIndexableArray($record))
            ->merge(RelatedInfoProvider::getIndexableArray($record))
            ->merge(CitationProvider::getIndexableArray($record))
            ->toArray();

        /* activity records should have grants metadata indexed */
        if ($record->class === "activity") {
            $index = collect($index)->merge(GrantsMetadataProvider::getIndexableArray($record));
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
        if ($record->isDraftStatus()) {
            return false;
        }

        // CC-1286. Remove Activity Records from Contributor 'Public Records of Victoria' from RDA
        if ($record->class === "activity" && $record->group === "Public Record Office Victoria") {
            return false;
        }

        return true;
    }

    public static function indexRecord(RegistryObject $record, $index = [], SolrClient $solrClient = null)
    {
        Log::debug(__FUNCTION__ . " Indexing RegistryObject", ['id' => $record->id]);
        $solrClient = $solrClient !== null ? $solrClient : SolrIndex::getClient("portal");
        $index = is_array_empty($index) ? self::get($record) : $index;

        $result = $solrClient->add(new SolrDocument($index));

        Log::debug("Indexing Result", $result);
    }

    public static function removeIndexRecord(RegistryObject $record, SolrClient $solrClient = null)
    {
        Log::debug(__FUNCTION__ . " Removing SOLR Index for RegistryObject", ['id' => $record->id]);
        $solrClient = $solrClient !== null ? $solrClient : SolrIndex::getClient("portal");

        $result = $solrClient->remove([$record->id]);
        Log::debug("Indexing Result", $result);
    }

    public static function updateField(RegistryObject $record, $field, SolrClient $solrClient = null)
    {
        Log::debug(__FUNCTION__. " Updating SOLR indexed field for RegistryObject", [
            'id' => $record->id,
            'field' => $field
        ]);

        $index = [];

        switch ($field) {
            case "tags":
                $index = collect($index)->merge(TagProvider::getIndexableArray($record));
                break;
            default:
                Log::warning(__FUNCTION__ ." Unknown field", ["field" => $field]);
                return;
        }

        if (collect($index)->isEmpty()) {
            Log::warning(__FUNCTION__ ." Index is empty, nothing to update");
            return;
        }

        // add id to identify the record
        $index = collect($index)->merge([
            'id' => $record
        ])->toArray();

        $solrClient = $solrClient !== null ? $solrClient : self::getSOLRClient();
        // todo atomic update
    }
}