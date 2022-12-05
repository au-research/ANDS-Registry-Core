<?php

namespace ANDS\API\Registry\Handler;

class SolrQueryHandler extends Handler
{

    /**
     * Handles registry/solrquery
     * very lean (as always when started)
     * This handler will be used by various new portal-as-a-service projects
     * doesn't include the numerous business logic but expecting them to grow
     */
    function handle()
    {
        $q = $this->ci->input->get('q');
        $type = $this->ci->input->get('type');
        $rows = $this->ci->input->get('rows');
        $start = $this->ci->input->get('start');

        $this->ci->load->library('solr');

        $this->ci->solr->setFilters([
                'q' => $q,
                'type' => $type,
                'rows' => $rows,
                'start' => $start
            ]);

        $result = $this->ci->solr->executeSearch(true);

        $data = array(
            'numFound' => $result['response']['numFound'],
            'result' => $result['response'],
            'solr_header' => $result['responseHeader'],
            'status' => 0
        );

        return $data;
    }
}