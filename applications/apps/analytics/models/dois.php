<?php

/**
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Dois extends CI_Model
{

    private $doi_db;

    /**
     * Get General Statistic based on a filter
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
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
        // dd($search_result);
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
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @param $filters
     * @return array
     */
    public function getMinted($filters)
    {
        $result = array();
        $group = $filters['group']['value'];

        //get client id
        $query = $this->doi_db->get_where('doi_client', array('client_name' => $group))->first_row(true);
        $client_id = isset($query['client_id']) ? $query['client_id'] : false;


        //get activity by client_id
        if ($client_id) {
            $query = $this->doi_db
                ->select('activity, count(*) as count')
                ->from('activity_log')
                ->where('client_id', $client_id)
                ->group_by('activity')->get();

            if ($query->num_rows() > 0) {
                $result = $query->result_array();
            }

            foreach ($result as &$res) {
                if (isset($res['count'])) $res['count'] = (int)$res['count'];
            }
        }

        return $result;
    }

    /**
     * Get Client Statistic
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @param $filters
     * @return array
     */
    public function getClientStat($filters)
    {
        $this->load->library('elasticsearch');

        $result = [];
        //get client id
        $query = $this->doi_db->get_where('doi_client', array('client_name' => $filters['group']['value']))->first_row(true);
        $client_id = isset($query['client_id']) ? $query['client_id'] : false;

        if ($client_id) {
            $search_result = $this->elasticsearch->init()->setPath('/report/doi/' . $client_id)->get(false);
            if (isset($search_result['found']) && $search_result['found']) {
                return $search_result['_source'];
            }
        } else {
            return $result;
        }
        return $result;
    }


    /**
     * Return activity log
     * Paginatable
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
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
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
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
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
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
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
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