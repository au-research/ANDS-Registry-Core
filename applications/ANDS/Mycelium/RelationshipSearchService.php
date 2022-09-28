<?php

namespace ANDS\Mycelium;

use ANDS\Log\Log;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
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

    protected static $defaultFieldLists = "*, score";

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
        Log::debug(__METHOD__ . "Search started", array_merge($criterias, $pagination));

        // construct the solrClient based on the solr_url provided by the application's configuration
        $solrClient = new SolrClient(Config::get('app.solr_url'));
        $solrClient->setCore(static::$collection);

        // convert criterias and pagination to Solr parameters and perform the search
        $parameters = static::getSolrParameters($criterias, $pagination);

        Log::debug(__METHOD__ . "SOLR parameters", $parameters);

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
                case "sort":
                    $params[$key] = $value;
                    break;
                case "offset":
                    // solr calls it 'start'
                    $params['start'] = $value;
                    $params[$key] = $value;
                    break;
                case "boost_relation_type":
                    // RDA-627 support weighted boost relation_type
                    // using an array and boost decreasing by order in the array
                    if(is_array($value)){
                        $boost = sizeof($value);
                        $queryStr = '{!parent which=$parentFilter score=total}';
                        foreach($value as $val){
                           $queryStr  .= 'relation_type:"' . $val . '"^'.$boost;
                           $boost -= 1;
                        }
                    $params['bq'] = $queryStr;
                    }
                    else {
                        $params['bq'] = '{!parent which=$parentFilter score=total}relation_type:"' . $value . '"';
                    }
                    break;
                case "boost_to_group":
                    $params['bq'] = "to_group:${value}";
                    break;
            }
        }

        // set the SOLR fl fields, this field is modifiable from fl, relations.fl and relations.limit
        $relationsFieldLists = isset($pagination['relations_fl']) ? $pagination['relations_fl'] : '*';
        $fieldLists = isset($pagination['fl']) ? $pagination['fl'] : static::$defaultFieldLists;
        $relationsLimit = isset($pagination['relations_limit']) ? $pagination['relations_limit'] : '100';
        $fl = "$fieldLists,[child parentFilter=\$parentFilter childFilter=\$childFilter fl=$relationsFieldLists limit=$relationsLimit]";
        $params['fl'] = $fl;

        // construct filter queries based on provided criterias
        $fqs = [ '+type:relationship' ];
        // todo filter the criteras to remove empty value

        foreach ($criterias as $key => $value) {
            switch ($key) {
                case "from_id":
                case "from_key":
                case "to_class":
                case "from_class":
                case "to_identifier":
                case "to_identifier_type":
                case "to_title":
                    $fqs[] = "+$key:" . escapeSolrValue($value);
                    break;
                case "to_type":
                    // supports comma separated value, and PHP array (when using PHP API)
                    // party -> +(to_type:party)
                    // party,person -> +(to_type:party OR to_type:person)
                    $value = is_array($value) ? $value : explode(',', $value);
                    $value = collect($value)->map(function ($val) {
                        return 'to_type:' . $val;
                    })->toArray();
                    $value = implode(' OR ', $value);
                    $fqs[] = "+($value)";
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
                case "relation_url_search":
                    // supports comma separated value, and PHP array (when using PHP API)
                    // thredds -> +({!parent which=$parentFilter}relation_url_search:thredds)
                    // thredds,catalog.html -> +({!parent which=$parentFilter}relation_url_search:thredds
                    // AND {!parent which=$parentFilter}relation_url_search:catalog.html)
                    $value = is_array($value) ? $value : explode(',', $value);
                    $value = collect($value)->map(function ($val) {
                        return '{!parent which=$parentFilter}relation_url_search:' . $val;
                    })->toArray();
                    $value = implode(' AND ', $value);
                    $fqs[] = "+($value)";
                    break;
                case "not_to_type":
                    $fqs[] = "-to_type:$value";
                    break;
                case "relation_origin":
                    // supports comma separated value, and PHP array (when using PHP API)
                    // RelatedObject -> +({!parent which=$parentFilter}relation_origin:RelatedObject)
                    // RelatedObject,RelatedInfo -> +({!parent which=$parentFilter}relation_origin:RelatedObjectOR {!parent which=$parentFilter}relation_origin:RelatedObject)
                    $value = is_array($value) ? $value : explode(',', $value);
                    $value = collect($value)->map(function ($val) {
                        return '{!parent which=$parentFilter}relation_origin:' . $val;
                    })->toArray();
                    $value = implode(' OR ', $value);
                    $fqs[] = "+($value)";
                    break;

            }

        }

        //we will check if the datasource of 'from_id' has allowed reverse internal and external links
        if(array_key_exists('from_id', $criterias)) {
            // if the reverse links are not to be allowed,then we need to exclude the reverse relationships in the search
            // by allowing only relationships that are DIRECT
            $dont_allow_reverse = static::getDatasourceSettings($criterias['from_id']);
            // if no reverse links of any kind is allowed
            if(array_key_exists('allow_reverse_internal_links', $dont_allow_reverse) && array_key_exists('allow_reverse_external_links', $dont_allow_reverse) )
            {
                // allow only if there is at least one edges that is => direct AND from RegistryObject or RelatedInfo origin
                $value = '{!parent which=$parentFilter}relation_reverse:false 
                AND ({!parent which=$parentFilter}relation_origin:RelatedObject OR {!parent which=$parentFilter}relation_origin:RelatedInfo)';
                $fqs[] = "+($value)";
            }
            // if ONLY direct internal reverse links are allowed
            elseif(array_key_exists('allow_reverse_internal_links', $dont_allow_reverse)){
                // allow only if there is at least one edges that is => (direct OR external) AND from RegistryObject or RelatedInfo origin
                $value = '({!parent which=$parentFilter}relation_reverse:false OR {!parent which=$parentFilter}relation_internal:false) 
                AND ({!parent which=$parentFilter}relation_origin:RelatedObject OR {!parent which=$parentFilter}relation_origin:RelatedInfo)';
                $fqs[] = "+($value)";
            }
            // if only direct
            elseif(array_key_exists('allow_reverse_external_links', $dont_allow_reverse)){
                // allow only if there is at least one edge is (direct OR internal) AND from RegistryObject or RelatedInfo origin
               $value = '({!parent which=$parentFilter}relation_reverse:false OR {!parent which=$parentFilter}relation_internal:true) 
               AND ({!parent which=$parentFilter}relation_origin:RelatedObject OR {!parent which=$parentFilter}relation_origin:RelatedInfo)';
               $fqs[] = "+($value)";
            }
        }

        // joins all fqs into a single long (space separated) fq parameter to POST to SOLR
        $fq = implode(" ", $fqs);
        $params['fq'] = $fq;
        return $params;
    }


    /**
     * Returns if a record datasource has not been set to allow reverse links
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function getDatasourceSettings($ro_id)
    {
        $record = RegistryObjectsRepository::getRecordByID($ro_id);
        if($record == null){
            return [];
        }
        $dataSource = $record->datasource;
        $reverse_permissions = [];
        //obtain the value of the datasource reverse links attributes
        if(!$dataSource->attr('allow_reverse_internal_links')) {
            $reverse_permissions['allow_reverse_internal_links'] = false ;
        }
        if(!$dataSource->attr('allow_reverse_external_links')) {
            $reverse_permissions['allow_reverse_external_links'] = false ;
        }
        return $reverse_permissions;
    }

}