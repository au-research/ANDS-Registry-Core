<?php
/**
 * Class:  DatasourceHandler
 * @author: Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 */

namespace ANDS\API\Registry\Handler;
use \Exception as Exception;

class DatasourceHandler extends Handler
{
    /**
     * DatasourceHandler constructor.
     */
    public function __construct($params)
    {
        parent::__construct($params);
        $this->db = $this->ci->load->database('registry', true);
        $this->ci->load->model('registry/data_source/data_sources', 'ds');
    }

    /**
     * handles registry/datasource/:identifier
     * @return array|string
     * @throws Exception
     */
    public function handle()
    {
        switch (strtolower($this->params['identifier'])) {
            default:
                return $this->report($this->params['identifier']);
        }
    }


    /**
     * Returns the report of a particular data source or multiples
     * @param $dataSourceID
     * @return array|string
     * @throws Exception
     */
    private function report($dataSourceID)
    {
        $dataSources = array();
        if ($dataSourceID) {
            return $this->formatDataSource($this->getDataSource($dataSourceID));
        } else {
            $query = $this->db->get('data_sources');
            if (!$query) throw new Exception ("Error getting Data Sources");
            if ($query->num_rows() == 0) return "No data sources found";
            foreach ($query->result_array() as $ds) {
                $dataSources[] = $this->formatDataSource($this->getDataSource($ds['data_source_id']));
            }
        }
        return $dataSources;
    }

    /**
     * Returns a data source object based on ID
     * @param $dataSourceID
     * @return mixed
     * @throws Exception
     */
    public function getDataSource($dataSourceID)
    {
        if (!$dataSourceID) throw new Exception("Data Source ".$dataSourceID. " not found");
        return $this->ci->ds->getByID($dataSourceID);
    }

    /**
     * Formats a data source object
     * Takes `includes` into account
     * @param $dataSource
     * @return array
     */
    private function formatDataSource($dataSource)
    {
        $result = [
            'id' => $dataSource->id,
            'key' => $dataSource->key,
            'title' => $dataSource->title,
            'slug' => $dataSource->slug,
            'record_owner' => $dataSource->record_owner,
            'created' => $dataSource->created,
            'updated' => $dataSource->updated
        ];

        $includes = $this->ci->input->get('includes') ? explode(',', $this->ci->input->get('includes')) : [];
        foreach ($includes as $inc) {
            switch($inc) {
                case "count":
                    $result['count_PUBLISHED'] = $dataSource->count_PUBLISHED;
                    $result['count_INDEXED'] = $dataSource->getIndexedCount();
                    break;
            }
        }

        return $result;
    }


}