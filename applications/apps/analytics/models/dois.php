<?php

/**
 * @author  Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 */
class Dois extends CI_Model
{

    private $doi_db;

    /**
     * Get General Statistic based on a filter
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @param $filters
     * @return array
     */
    public function getStat($filters)
    {
        $this->load->library('elasticsearch');
        $this->elasticsearch->init()->setPath('/rda/production/_search')
            ->andf('term', 'class', 'collection')
            ->andf('term', $filters['group']['type'], $filters['group']['value'])
            ->setOpt('size', 0)
            ->setAggs(
                'missing_doi', array('missing' => array('field' => 'doi'))
            )
            ->setAggs(
                'doi', array('terms' => array('field' => 'doi'))
            );
        $search_result = $this->elasticsearch->search();
        $result = array(
            'total' => $search_result['hits']['total'],
            'missing_doi' => $search_result['aggregations']['missing_doi']['doc_count']
        );
        $result['has_doi'] = $result['total'] - $result['missing_doi'];
        return $result;
    }

    /**
     * Get the activity log statistics
     * Mainly for minted statistics
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @param $filters
     * @return array
     */
    public function getDOIActivityStat($filters)
    {
        $result = array();

        if (isset($filters['doi_app_id'])) {
           // echo "we here";
            if($this->user->hasFunction('DOI_USER')){
                $result['display'] = true;
            }else{
                $result['display'] = false;
            }
            foreach ($filters['doi_app_id'] as $app_id) {
               // echo "is the app_id";
                $query = $this->doi_db->get_where('doi_client', ['app_id'=>$app_id])->first_row(true);
                $client_id = isset($query['client_id']) ? $query['client_id'] : false;
                if ($client_id) {
                    $query = $this->doi_db
                        ->select('activity, count(*) as count')
                        ->from('activity_log')
                        ->where('client_id', $client_id)
                        ->like('activity','MINT')
                        ->like('result','SUCCESS')
                        ->like('doi_id', '10.4', 'after')
                        ->group_by('activity')->get();

                    if ($query->num_rows() > 0) {
                        $result[$app_id] = $query->result_array();
                    }else{
                        $result[$app_id] =[];
                    }

                    foreach ($result[$app_id] as $res) {
                        if (isset($res['count'])) $res['count'] = (int)$res['count'];
                    }
                }
            }
        } else {
            if(isset($filters['Masterview'])){
                $result['display']=true;
            }else{
                $result['display']=false;
            }
            //get all

            $query = $this->doi_db
                ->select('activity, count(*) as count')
                ->from('activity_log')
                ->like('activity','MINT')
                ->like('result','SUCCESS')
                ->like('doi_id', '10.4', 'after')
                ->group_by('activity')->get();

            if ($query->num_rows() > 0) {
                $result['all'] = $query->result_array();
            }

            foreach ($result['all'] as &$res) {
                if (isset($res['count'])) $res['count'] = (int)$res['count'];
            }

        }


        return $result;
    }

    /**
     * Get Client Statistic
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @param $filters
     * @return array
     */
    public function getClientStat($filters)
    {
        $this->load->library('elasticsearch');

        $result = [];

        if (isset($filters['doi_app_id'])) {
            foreach ($filters['doi_app_id'] as $app_id) {
                $query = $this->doi_db->get_where('doi_client', ['app_id'=>$app_id])->first_row(true);
                $client_id = isset($query['client_id']) ? $query['client_id'] : false;
                if ($client_id) {
                    $search_result = $this->elasticsearch->init()->setPath('/report/doi/' . $client_id)->get(false);
                    if (isset($search_result['found']) && $search_result['found']) {
                        $result[$app_id] = $search_result['_source'];
                    }
                }
            }
            return $result;
        } else {
            return $result;
        }
    }


    /**
     * Return activity log
     * Paginatable
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @param int $offset
     * @param int $limit
     * @return mixed
     */
    public function getActivity($offset = 0, $limit = 20)
    {
        $result = $this->doi_db->get('activity_log', $limit, $offset);
        return $result->result_array();
    }

    /**
     * Return a list of clients
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @return array
     */
    public function getClients()
    {
        $result = $this->doi_db->get('doi_client');
        if ($result->num_rows() > 0) {
            return $result->result_array();
        } else return array();
    }

    /**
     * Get a group given a DOI ID
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @param $doi_id
     * @return string
     */
    public function getGroupForDOI($doi_id)
    {
        $result = $this->doi_db->get_where('doi_objects', array('doi_id' => $doi_id));
        if ($result->num_rows() > 0) {
            $result = $result->first_row();
            return $result->publisher;
        } else {
            return 'No Publisher Found';
        }
    }

    /**
     * Get the total number of activity logs existed
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @return mixed
     */
    public function getTotalActivityLogs()
    {
        return $this->doi_db->count_all('activity_log');
    }

    //boring class construction
    public function __construct()
    {
        $this->doi_db = $this->load->database('dois', true);
        parent::__construct();
    }
}