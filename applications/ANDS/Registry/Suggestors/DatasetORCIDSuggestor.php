<?php
namespace ANDS\Registry\Suggestors;

use ANDS\Registry\Providers\ORCID\ORCIDRecord;
use ANDS\Util\Config;
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

        $partyIDs = [];

        // find all parties sharing the same surname or have the same orcid as identifier
        $query = "+class:party +(title_search:({$surname}) identifier_value_search:({$orcid->orcid_id}))";
        $result = $this->solr->setCore('portal')->search([
            'q' => $query,
            'fl' => 'id,title'
        ]);

        $sameName = collect($result->getDocs())->map(function($item) {
            return $item->id;
        })->toArray();
        $partyIDs = array_merge($partyIDs, $sameName);

        // all collection that relates to these partyIDs
        if (count($partyIDs) > 0) {
            $query = "+to_class:collection +from_id:(".implode(" OR ", $partyIDs).")";
            $result = $this->solr->setCore('relationships')->search([
                'q' => $query
            ]);
            if($result->getNumFound() > 0) {
                foreach ($result->getDocs() as $doc) {
                    $doc = $doc->toArray();
                    $suggested[] = [
                        'registry_object_id' => $doc['to_identifier'],
                        'title' => $doc['to_title'],
                        'url' => $doc['to_url']
                    ];
                }
            }
        }

        // all collection that has relatedInfo/citationInfo like the orcid_id
        $query = "+from_class:collection +relation_identifier_identifier:({$orcid->orcid_id})";
        $result = $this->solr->setCore('relations')->search([
            'q' => $query
        ]);
        if($result->getNumFound() > 0){
            foreach ($result->getDocs() as $doc) {
                $doc = $doc->toArray();
                $suggested[] = [
                    'registry_object_id' => $doc['from_id'],
                    'title' => $doc['from_title'],
                    'url' => $doc['from_url']
                ];
            }
        }


        // unique the values
        $suggested = collect($suggested)
            ->unique()->values()->toArray();

        return $suggested;
    }


}