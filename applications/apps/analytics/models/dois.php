<?php
/**
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Dois extends CI_Model
{

    private $doi_db;

    public function getStat($filters) {
        $this->load->library('elasticsearch');
        $this->elasticsearch->init()->setPath('/rda/production/_search')
            ->andf('term', 'class', 'collection')
            ->andf('term', $filters['group']['type'], $filters['group']['value'])
            ->setOpt('size', 0)
            ->setAggs(
                'missing_doi', array('missing'=>array('field'=>'doi'))
            )
            ->setAggs(
                'doi', array('terms'=>array('field'=>'doi'))
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

    public function getActivity($offset = 0, $limit = 20) {
        $result = $this->doi_db->get('activity_log', $limit, $offset);
        return $result->result_array();
    }

    public function getGroupForDOI($doi_id) {
        $result = $this->doi_db->get_where('doi_objects', array('doi_id'=>$doi_id));
        if ($result->num_rows() > 0) {
            $result = $result->first_row();
            return $result->publisher;
        } else {
            return 'No Publisher Found';
        }
    }

    public function getTotalActivityLogs() {
        return $this->doi_db->count_all('activity_log');
    }

    //boring class construction
    public function __construct()
    {
        $this->doi_db = $this->load->database('dois', true);
        parent::__construct();
    }
}