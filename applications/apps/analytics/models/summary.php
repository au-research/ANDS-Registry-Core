<?php
/**
 * Summary class, use for Analytics of a single period of time
 * @todo Load analytics modules
 * @author  Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 */
class Summary extends CI_Model
{

    public function get($filters) {
        $this->load->library('ElasticSearch');

        //setup
        $this->elasticsearch->init()->setPath('/portal-*/_search');
        $this->elasticsearch->setOpt('from', 0)->setOpt('size', 1);

        $this->elasticsearch->setFilters($filters);

        //aggregation for stats
        $this->elasticsearch
            ->setAggs('date',
                array('date_histogram' =>
                    array(
                        'field'=>'doc.@timestamp',
                        'format' => 'yyyy-MM-dd',
                        'interval' => 'day',
                        "time_zone" => "+10:00"
                    ),
                    'aggs'=>array(
                        'events' => array('terms'=>array('field'=>'doc.@fields.event.raw'))
                    )
                )
            )
            ->setAggs('event',
                array(
                    'terms'=>array('field'=>'doc.@fields.event.raw'),
                    'aggs' => [
                        'events' => ['terms'=>['field'=>'doc.@fields.record.group.raw']]
                    ]
                )
            )
            ->setAggs('search_event',
                array(
                    'terms'=>array('field'=>'doc.@fields.event.raw'),
                    'aggs' => [
                        'events' => ['terms'=>['field'=>'doc.@fields.filters.group.raw']]
                    ]
                )
            )
            ->setAggs('class',
                array(
                    'terms'=>array('field'=>'doc.@fields.record.class.raw'),
                    'aggs' => [
                        'classes' => ['terms'=>['field'=>'doc.@fields.record.class.raw']]
                    ]
                )
            )
            ->setAggs('group',
                array(
                    'terms'=>array('field'=>'doc.@fields.record.group.raw'),
                    'aggs' => [
                        'event' => ['terms'=>['field'=>'doc.@fields.event.raw']]
                    ]
                )
            )
            ->setAggs('search_group',
                array(
                    'terms'=>array('field'=>'doc.@fields.result.result_group.raw'),
                    'aggs' => [
                        'event' => ['terms'=>['field'=>'doc.@fields.event.raw']]
                    ]
                )
            )
            ->setAggs(
                'rostat', array('terms' => array('field' => 'doc.@fields.record.id.raw'))
            )
            ->setAggs(
                'viewedstat',
                array(
                    'filter' => array('term' => array('doc.@fields.event.raw'=>'portal_view')),
                    'aggs'=>array("key"=>array("terms"=>array('field'=>'doc.@fields.record.id.raw')))
                )
            )
            ->setAggs(
                'qstat', array('terms' => array('field' => 'doc.@fields.filters.q.raw'))
            )
            ->setAggs(
                'accessedstat',
                array(
                    'filter' => array('term' => array('doc.@fields.event.raw'=>'portal_accessed')),
                    'aggs'=>array("key"=>array("terms"=>array('field'=>'doc.@fields.record.id.raw')))
                )
            )
        ;

        $search_result = $this->elasticsearch->search();
//         dd($this->elasticsearch->getOptions());
//         dd(json_encode($this->elasticsearch->getOptions()));
//         dd($search_result);
        // dd($filters);
        // dd($search_result['aggregations']['date']);
//         dd($search_result['aggregations']['search_event']);
//         dd($search_result['aggregations']['group']);

        //prepare result
        $result = [
            'dates' => [],
            'group_event' => [],
            'aggs' => $search_result['aggregations']
        ];

        //dates
        foreach ($search_result['aggregations']['date']['buckets'] as $date) {
            if (sizeof($date['events']['buckets']) > 0) {
                foreach ($date['events']['buckets'] as $event) {
                    $result['dates'][$date['key_as_string']][$event['key']] = $event['doc_count'];
                }
            }
        }

        //padding for dates
        foreach ($result['dates'] as &$date) {
            $date['total'] = 0;
            foreach ($filters['dimensions'] as $dimension) {
                if (!isset($date[$dimension])) $date[$dimension] = 0;
                $date['total'] += $date[$dimension];
            }
        }

        //group_event padding
        if (isset($filters['groups'])) {
            foreach ($filters['groups'] as $group) {
                $result['group_event'][$group] = array();
                foreach ($filters['dimensions'] as $dimension) {
                    $result['group_event'][$group][$dimension] = 0;
                }
            }
        }

        // dd($search_result['aggregations']['group']);

        //group_event
        foreach ($search_result['aggregations']['group']['buckets'] as $group) {
            foreach ($group['event']['buckets'] as $event) {
                $result['group_event'][$group['key']][$event['key']] = $event['doc_count'];
            }
        }

        foreach ($search_result['aggregations']['search_group']['buckets'] as $group) {
            foreach ($group['event']['buckets'] as $event) {
                $result['group_event'][$group['key']][$event['key']] = $event['doc_count'];
            }
        }

        //removing groups not in the filters out of the group event
        if (array_key_exists('groups', $filters)) {
            foreach ($result['group_event'] as $key=>$value) {
                if (!in_array($key, $filters['groups'])) {
                    unset($result['group_event'][$key]);
                }
            }
        }

        return $result;
    }

    public function getStat($path, $filters) {
        $this->load->library('elasticsearch');
        $this->elasticsearch->init()->setPath($path.'_search');

        $this->elasticsearch->setFilters($filters);
        // $this->elasticsearch->shouldf('term', 'group', $filters['groups'][0]);

        // echo json_encode($this->elasticsearch->getOptions()); die();

        //set all the aggs
        $this->elasticsearch
            ->setAggs(
                'missing_doi', array('missing'=>array('field'=>'identifier_doi'))
            )
            ->setAggs(
                'missing_ands', array('missing'=>array('field'=>'ands_doi'))
            )
            ->setAggs('portal_cited',
                ['terms'=>['field'=>'portal_cited']]
            )
            ->setAggs('quality_level',
                ['terms'=>['field'=>'quality_level']]
            )
			->setAggs('access_rights',
                ['terms'=>['field'=>'access_rights']]
            )
            ->setAggs('class',
                ['terms'=>['field'=>'class']]
            )
            ->setAggs('group',
                ['terms'=>['field'=>'group']]
            )
        ;

        $this->elasticsearch->setOpt('size', 0);

        // echo json_encode($this->elasticsearch->getOptions()); die();

        $search_result = $this->elasticsearch->search();

        // dd($search_result['hits']);

        return $search_result;
    }

    public function getSolrStat($content, $filters) {
        $result = array();
        $this->load->library('solr');
        $this->solr->setFilters($filters);
        $this->solr->setOpt('rows', 0)->setOpt('fl', "group");
        $this->solr->setFacetOpt('field', $content);
        $this->solr->setFacetOpt('mincount', 1);
        $solr_result = $this->solr->executeSearch(true);

        $facet_result = $solr_result['facet_counts']['facet_fields'][$content];
        for ($i = 0; $i < sizeof($facet_result) -1 ; $i+=2) {
            $result[] = array(
                'key' => $facet_result[$i],
                'doc_count' => $facet_result[$i+1]
            );
        }
        return $result;
    }

    public function getSolrDOIStat($filters) {
        $result = array();
        $this->load->library('solr');
        $this->solr
            ->setFilters($filters)->setOpt('rows', 0)->setOpt('fl', '')
            ->setFacetOpt('query', '{!ex=dt key=hasdoi} identifier_type:(doi)')
            ->setFacetOpt('query', '{!ex=dt key=hasandsdoi} identifier_value:(10.4225/*) OR identifier_value:(10.4226/*) identifier_value:(10.4227/*)')
            ;
        $solr_result = $this->solr->executeSearch(true);

        $result = array(
            // 'has_doi' => $solr_result['facet_counts']['facet_queries']['hasdoi'],
            'missing_doi' => $solr_result['response']['numFound'] - $solr_result['facet_counts']['facet_queries']['hasdoi'],
            'has_ands_doi' => $solr_result['facet_counts']['facet_queries']['hasandsdoi'],
            'has_non_ands_doi' => $solr_result['facet_counts']['facet_queries']['hasdoi'] - $solr_result['facet_counts']['facet_queries']['hasandsdoi']
        );
        return $result;
    }

    public function getOrgs() {
        $result = array();

        $this->load->model('authenticator', 'auth');

        //get all org role
        $roles_db = $this->load->database('roles', true);
        $query = $roles_db->get_where('roles', array('role_type_id'=>'ROLE_ORGANISATIONAL'));
        foreach ($query->result_array() as $row) {
            $role = $row;
         //   $childs = $this->list_childs($row['role_id']);

            //get DOI APP_ID
         /*   foreach ($childs as $child) {
               if ($child->role_type_id=='ROLE_DOI_APPID') {
                   $role['doi_app_id'][] = $child->role_id;
                }
            } */

            //get Data sources
            if ($datasources = $this->get_datasources($row['role_id'])) {
                $role['groups'] = [];
                $role['data_sources'] = $datasources;
                //get groups by this data source
                foreach ($datasources as $ds) {
                    $role['groups'] = array_merge($role['groups'], $this->getDataSourceGroups($ds['data_source_id']));
                    $role['groups'] = array_values(array_unique($role['groups'], SORT_STRING));
                }
                $result[] = $role;
            }
        }

        return $result;

    }


    /**
     * recursive function that goes through and collect all of the (parents) of a role
     * @param  string $role_id
     * @return array_object if an object has a child, object->childs will be a list of the child objects
     */
    function list_childs($role_id, $include_doi=false, $prev=array()){
        $res = array();
        // $role = $this->get_role($role_id);
        // return $res;

        $roles_db = $this->load->database('roles', true);

        $result = $roles_db
                ->select('role_relations.parent_role_id, roles.role_type_id, roles.name, roles.role_id')
                ->from('role_relations')
                ->join('roles', 'roles.role_id = role_relations.parent_role_id')
                ->where('role_relations.child_role_id', $role_id)
                ->where('enabled', DB_TRUE)
                ->where('role_relations.parent_role_id !=', $role_id)
                ->get();

        if($result->num_rows() > 0){
            foreach($result->result() as $r){
                if(trim($r->role_type_id)=='ROLE_DOI_APPID' && $include_doi){
                    $res[] = $r;
                }else if(!$include_doi){
                    $res[] = $r;
                }
                if(!in_array($r->role_id, $prev)) {
                    array_push($prev, $r->role_id);
                    $childs = $this->list_childs($r->parent_role_id, $include_doi, $prev);
                    if(sizeof($childs) > 0){
                        $r->childs = $childs;
                    }else{
                        $r->childs = false;
                    }
                }
            }
        }
        return $res;
    }

    public function get_datasources($role_id) {
        $registry_db = $this->load->database('registry', true);
        $query = $registry_db->get_where('data_sources', array('record_owner'=>$role_id));
        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return false;
        }
    }

    /**
     * Returns groups which this datasource has objects which are contributed by
     *
     * @param the data source ID
     * @return array of groups or NULL
     */
    function getDataSourceGroups($dsid)
    {
        $groups = array();
        $registry_db = $this->load->database('registry', true);
        $query = $registry_db
            ->distinct()
            ->select('group')
            ->from('registry_objects')
            ->where(
                array(
                    'data_source_id' => $dsid
                )
            )
            ->get();

        if ($query->num_rows() == 0) {
            return $groups;
        } else {
            foreach ($query->result_array() AS $group) {
                $groups[] =  $group['group'];
            }
        }
        return $groups;
    }

    /**
     * Get the summary of a given filter set
     * @param  array $filters
     * @return array
     */
    public function get_deprecate($filters)
    {
        $result = array();

        $ranges = date_range(
            $filters['period']['startDate'],
            $filters['period']['endDate'],
            '+1day', 'Y-m-d'
        );

        foreach ($ranges as $date) {

            //get the stat from various sources
            $lines = $this->getStatFromInternalLog($date, $filters);
            array_merge($lines, $this->getStatFromGoogle($date, $filters));

            //setting up values
            $result[$date] = array('total' => 0);
            foreach ($filters['dimensions'] as $dimension) {
                $result[$date][$dimension] = 0;
            }

            //process the lines
            foreach ($lines as $line) {
                $line = json_encode($line);
                $content = readString($line);

                $group = $filters['group'];
                $group_type = $group['type'];
                $group_value = $group['value'];

                if (isset($content[$group_type]) && $content[$group_type] == $group_value) {
                    if (!isbot($content['user_agent'])) {
                        $result[$date]['total']++;
                    }

                    foreach ($filters['dimensions'] as $d) {
                        if (isset($content['event']) && $content['event'] == $d) {
                            if (!isbot($content['user_agent'])) {
                                $result[$date][$d]++;
                            }

                        }
                    }
                }
            }
        }
        return $result;
    }

    public function getStatFromES($date, $filters = false) {
        $result = array();
        $filters = [
            'query'=> [
                'match' => [
                    'date' => $date
                ]
            ]
        ];
        $response = curl_post('http://localhost:9200/logs/production/_search', json_encode($filters));
        $response = json_decode($response, true);
        if (isset($response['hits'])) {
           foreach($response['hits']['hits'] as $doc) {
                $result[] = $doc['_source'];
           }
        }
        return $result;
    }

    /**
     * Return the statistic lines from the internal log collected via portal
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @param  string $date    Date for the event
     * @param  array $filters  filters passed down
     * @return array(lines)
     */
    public function getStatFromInternalLog($date, $filters)
    {
        $file_path = 'engine/logs/' . $filters['log'] . '/log-' . $filters['log'] . '-' . $date . '.php';
        $lines = readFileToLine($file_path);
        return $lines;
    }

    /**
     * Return the statistics lines from GoogleAnalytics
     * @todo
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @param  string $date    Date for the event
     * @param  string $filters filters passed down
     * @return array(lines)
     */
    private function getStatFromGoogle($date, $filters)
    {
        return array();
    }

    //boring class construction
    public function __construct()
    {
        parent::__construct();
    }
}
