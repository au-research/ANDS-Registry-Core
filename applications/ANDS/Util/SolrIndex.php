<?php
namespace ANDS\Util;


use MinhD\SolrClient\SolrClient;

class SolrIndex
{

    /**
     * Return an instance of the SolrClient for use
     *
     * @param string $core
     * @return SolrClient
     */
    public static function getClient($core = "portal")
    {
        $solrClient = new SolrClient(Config::get('app.solr_url'));
        $solrClient->setCore($core);
        return $solrClient;
    }

    public static function getFacets($field)
    {
        $client = static::getClient();
        $result = $client
            ->setFacet($field)
            ->setSearchParams('facet.limit', "-1")
            ->query('*');
        return $result->getFacetField($field);
    }

}