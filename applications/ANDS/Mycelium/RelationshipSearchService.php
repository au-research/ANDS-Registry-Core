<?php

namespace ANDS\Mycelium;

use ANDS\Util\Config;
use MinhD\SolrClient\SolrClient;

class RelationshipSearchService
{
    protected static $collection = 'relationships';

    protected static $defaultParameters = [
        'q' => '*:*',
        'defType' => 'edismax',
        'parentFilter' => 'type:relationship',
        'childFilter' => 'type:edge',
        'rows' => 15,
        'offset' => 0,
        'sort' => 'score desc, to_title asc'
    ];

    /**
     * Search for relationships
     *
     * @param array $criterias search criterias
     * @param array $pagination pagination, sort and boost
     * @return \ANDS\Mycelium\Paginator search result
     * @throws \Exception
     */
    public static function search($criterias, $pagination = [])
    {
        // construct the solrClient based on the solr_url provided by the application's configuration
        $solrClient = new SolrClient(Config::get('app.solr_url'));
        $solrClient->setCore(static::$collection);

        // convert criterias and pagination to Solr parameters and perform the search
        $parameters = static::getSolrParameters($criterias, $pagination);
        $result = $solrClient->search($parameters);

        return Paginator::fromSolrResult($result);
    }

    /**
     * Generate an associative array to be used with SolrClient::search
     *
     * @param array $criterias search criterias
     * @param array $pagination pagination, sort and boost
     * @return array the array to be used with SolrClient
     */
    public static function getSolrParameters($criterias, $pagination)
    {
        // starts with default parameters
        $params = static::$defaultParameters;

        // construct rows, offset, boosts based on pagination
        foreach ($pagination as $key => $value) {
            switch ($key) {
                case "rows":
                case "offset":
                case "sort":
                    $params[$key] = $value;
                    break;
                case "boost_relation_type":
                    $params['bq'] = '{!parent which=$parentFilter score=total}relation_type:' . $value;
                    break;
                case "boost_to_group":
                    $params['bq'] = "to_group:${value}";
                    break;
            }
        }

        // set the SOLR fl fields, this field is modifiable from fl, relations.fl and relations.limit
        $relationsFieldLists = isset($pagination['relations_fl']) ? $pagination['relations_fl'] : '*';
        $fieldLists = isset($pagination['fl']) ? $pagination['fl'] : '*';
        $relationsLimit = isset($pagination['relations_limit']) ? $pagination['relations_limit'] : '100';
        $fl = "$fieldLists,[child parentFilter=\$parentFilter childFilter=\$childFilter fl=$relationsFieldLists limit=$relationsLimit]";
        $params['fl'] = $fl;

        // construct filter queries based on provided criterias
        $fqs = [ '+type:relationship' ];
        // todo filter the criteras to remove empty value
        foreach ($criterias as $key => $value) {
            switch ($key) {
                case "from_id":
                case "to_class":
                case "to_identifier":
                case "to_identifier_type":
                case "to_title":
                case "to_type":
                    $fqs[] = "+$key:$value";
                    break;
                case "relation_type":
                    // supports comma separated value, and PHP array (when using PHP API)
                    // isPartOf -> +({!parent which=$parentFilter}relation_type:isPartOf)
                    // isPartOf,isOutputOf -> +({!parent which=$parentFilter}relation_type:isPartOf OR {!parent which=$parentFilter}relation_type:isOutputOf)
                    $value = is_array($value) ? $value : explode(',', $value);
                    $value = collect($value)->map(function ($val) {
                        return '{!parent which=$parentFilter}relation_type:' . $val;
                    })->toArray();
                    $value = implode(' OR ', $value);
                    $fqs[] = "+($value)";
                    break;
                case "not_to_type":
                    $fqs[] = "-to_type:$value";
                    break;
            }
        }

        // joins all fqs into a single long (space separated) fq parameter to POST to SOLR
        $fq = implode(" ", $fqs);
        $params['fq'] = $fq;

        return $params;
    }
}