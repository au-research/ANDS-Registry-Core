<?php
/**
 * Summary class, use for Analytics of a single period of time
 * @todo Load analytics modules
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Summary extends CI_Model
{

    public function get($filters) {
        $this->load->library('ElasticSearch');
        $filters['period']['startDate'] = date('Y-m-d', strtotime($filters['period']['startDate']));
        $filters['period']['endDate'] = date('Y-m-d', strtotime($filters['period']['endDate']));
        $this->elasticsearch->init()->setPath('/logs/production/_search');
        $this->elasticsearch
            ->setOpt('from', 0)->setOpt('size', 50000)
            ->andf('term', 'is_bot', 'false')
            ->andf('term', $filters['group']['type'], $filters['group']['value'])
            ->andf('range', 'date',
                array (
                    'from' => $filters['period']['startDate'],
                    'to' => $filters['period']['endDate']
                )
            );

        //dimensions
        $this->elasticsearch
            ->setAggs('date',
                array('date_histogram' =>
                    array(
                        'field'=>'date',
                        'format' => 'yyyy-MM-dd',
                        'interval' => 'day',
                        // 'aggs'=>array(
                        //     'events' => array('value_count'=>array('field'=>'event'))
                        // )
                    )
                )
            )
            ->setAggs('event',
                array('value_count'=>array('field'=>'event'))
            )
            ;
        // $this->elasticsearch
        //     ->setFacet('group', array('terms'=>array('field'=>'group')))
        //     ->setFacet('event', array('terms'=>array('field'=>'event')));

        $search_result = $this->elasticsearch->search();
        // dd($filters);
        // dd($search_result);


        $ranges = date_range(
            $filters['period']['startDate'],
            $filters['period']['endDate'],
            '+1day', 'Y-m-d'
        );

        $result = array();

        foreach ($ranges as $date) {
            $result[$date] = array('total' => 0);
            foreach ($filters['dimensions'] as $dimension) {
                $result[$date][$dimension] = 0;
            }
        }

        foreach ($search_result['hits']['hits'] as $hit) {
            $content = $hit['_source'];
            $date = date('Y-m-d', strtotime($content['date']));
            $result[$date]['total']++;
            foreach ($filters['dimensions'] as $dimension) {
                if ($content['event']==$dimension) {
                    $result[$date][$dimension]++;
                }
            }
        }
        return $result;
    }

    public function getStat($path, $filters) {
        $this->load->library('elasticsearch');
        $this->elasticsearch->init()->setPath($path.'_search');

        if (isset($filters['class'])) {
            $this->elasticsearch->andf('term', 'class', $filters['class']);
        }

        if (isset($filters['group'])) {
            $this->elasticsearch->andf('term', $filters['group']['type'], $filters['group']['value']);
        }

        if (isset($filters['ctype'])) {
            if ($filters['ctype']=='doi') {
                $this->elasticsearch->setAggs(
                    'missing_doi', array('missing'=>array('field'=>'doi'))
                );
            } else if($filters['ctype']=='tr') {
                $this->elasticsearch->setAggs('portal_cited',
                    ['terms'=>['field'=>'portal_cited']]
                );
            } else if($filters['ctype']=='accessed') {
                $this->elasticsearch->setAggs('accessed',
                    ['terms'=>['field'=>'portal_accessed']]
                );
            }
        }
        $this->elasticsearch->setOpt('size', 0);
        $search_result = $this->elasticsearch->search();

        return $search_result;

        $result = array(
            'total' => $search_result['hits']['total'],
            'missing_doi' => $search_result['aggregations']['missing_doi']['doc_count']
        );
        $result['has_doi'] = $result['total'] - $result['missing_doi'];
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
            $childs = $this->list_childs($row['role_id']);

            //get DOI APP_ID
            foreach ($childs as $child) {
                if ($child->role_type_id=='ROLE_DOI_APPID') {
                    $role['doi_app_id'][] = $child->role_id;
                }
            }

            //get Data sources
            if ($datasources = $this->get_datasources($row['role_id'])) {
                $role['groups'] = [];
                $role['data_sources'][] = $datasources;
                //get groups by this data source
                foreach ($datasources as $ds) {
                    $role['groups'] = array_merge($role['groups'], $this->getDataSourceGroups($ds['data_source_id']));
                    $role['groups'] = array_values(array_unique($role['groups'], SORT_STRING));
                }
            }

            $result[] = $role;
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
            ->select('value')
            ->from('registry_object_attributes')
            ->join('registry_objects', 'registry_objects.registry_object_id = registry_object_attributes.registry_object_id')
            ->where(
                array(
                    'registry_objects.data_source_id' => $dsid,
                    'registry_object_attributes.attribute' => 'group'
                )
            )
            ->get();

        if ($query->num_rows() == 0) {
            return $groups;
        } else {
            foreach ($query->result_array() AS $group) {
                $groups[] =  $group['value'];
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
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
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
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
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
