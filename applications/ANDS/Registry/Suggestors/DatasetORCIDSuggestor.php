<?php
namespace ANDS\Registry\Suggestors;


use ANDS\Registry\Connections;
use ANDS\Registry\IdentifierRelationshipView;
use ANDS\Registry\Providers\ORCID\ORCIDRecord;
use ANDS\RegistryObject;
use ANDS\RegistryObject\Identifier;
use ANDS\Util\Config;
use Illuminate\Support\Collection;
use MinhD\SolrClient\SolrClient;

class DatasetORCIDSuggestor
{
    private $solr;

    /**
     * SubjectSuggestor constructor.
     */
    public function __construct()
    {
        // make solr into a Facade?
        $url = Config::get('app.solr_url');
        $this->solr = new SolrClient($url);

        // core needs to be in conf?
        $this->solr->setCore('portal');
    }

    /**
     * Return a list of suggested dataset for a particular ORCIDRecord
     * TODO: performance?
     *
     * @param ORCIDRecord $orcid
     * @return array
     */
    public function suggest(ORCIDRecord $orcid)
    {
        $suggested = [];

        // TODO: this should be cached

        // find all party sharing the same surname
        $bio = $orcid->bio;
        $surname = $bio['person']['name']['family-name']['value'];

        // $partyIDs = [86702]; // test party
        $partyIDs = [];
        // TODO: like query is slow, consider using SOLR?
        $sameName = RegistryObject::where('status', 'PUBLISHED')
            ->where('title', 'like', "%$surname%")
            ->pluck('registry_object_id')->toArray();
        $partyIDs = array_merge($partyIDs, $sameName);

        $sameOrcidID = Identifier::where('identifier', $orcid->orcid_id)
            ->pluck('registry_object_id')->toArray();
        $partyIDs = array_merge($partyIDs, $sameOrcidID);

        // all collection that relates to these partyIDs
        $provider = Connections::getStandardProvider();

        $relations = $provider->init()
            ->setFilter('to_class','collection')
            ->setFilter('from_id', $partyIDs)
            ->get();

        foreach ($relations as $relation) {
            $suggested[] = [
                'registry_object_id' => $relation->prop('to_id'),
                'title' => $relation->prop('to_title'),
                'key' => $relation->prop('to_key'),
                'slug' => $relation->prop('to_slug')
            ];
        }

        /**
         * looking for identifierRelationship where identifier is like something
         * is not optimised correctly, do a check first
         * This should've been cached anyway
         */
        $identifierRelationCount = RegistryObject\IdentifierRelationship::where('related_object_identifier', 'like', "%{$orcid->orcid_id}%")
            ->count();
        if ($identifierRelationCount == 0) {
            return $suggested;
        }

        // all collection that has relatedInfo/citationInfo like the orcid_id
        $collections = IdentifierRelationshipView::where('to_identifier', 'like', "%{$orcid->orcid_id}%")
            ->where('from_class', 'collection')
            ->limit(20) // TODO: magic number here
            ->get();
        foreach ($collections as $identifierView) {
            $suggested[] = [
                'registry_object_id' => $identifierView->from_id,
                'title' => $identifierView->from_title,
                'key' => $identifierView->from_key,
                'slug' => $identifierView->from_slug
            ];
        }

        return $suggested;
    }
}