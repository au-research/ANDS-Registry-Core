<?php
namespace ANDS\API\Registry\Handler;
use \Exception as Exception;

/**
 * Handles registry/search
 * Search the registry
 * Used mainly for registry widget
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class SearchHandler extends Handler{

    /**
     * Handles registry/search
     */
    function handle()
    {
        $query = $this->ci->input->get('q');
        $custom_query = $this->ci->input->get('custom_q');
        $query = urldecode($query);

        $this->ci->load->library('solr');
        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');

        //custom query handling
        if ($custom_query) {
            $this->ci->solr->setCustomQuery($custom_query);
        } else {
            $this->ci->solr->setFilters([
                'q'=>$query,
                'qf' => 'key^2 title_search^1 identifier_value^0.05 tag_search^0.05 fulltext^0.01 _text_^0.01'
            ]);
        }

        $result = $this->ci->solr->executeSearch(true);

        //add entire rif document if custom_query is defined
        if ($custom_query) {
            foreach ($result['response']['docs'] as &$doc) {
                $this->ci->db->select('data')
                    ->from('record_data')
                    ->where('registry_object_id',$doc['id'])
                    ->where('scheme','rif')
                    ->where('current',true)
                    ->limit(1);
                $query = $this->ci->db->get();
                foreach ($query->result_array() as $row) {
                    $doc['rif'] = simplexml_load_string($row['data']);
                }
            }
        }

        $data = array(
            'numFound' => $result['response']['numFound'],
            'result' => $result['response'],
            'solr_header' => $result['responseHeader'],
            'status' => 0
        );

        return $data;
    }
}