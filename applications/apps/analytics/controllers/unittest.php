<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Unit Test Controller for Analytics Module
 * @todo make official Test Class usecase
 * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 */
class Unittest extends MX_Controller
{

    /**
     * Test Data
     * @var array
     */
    private $testdata = array(
        array(
            'log' => 'portal',
            'period' => ['startDate' => '2015-06-01', 'endDate' => '2015-06-06'],
            'group' => [
                'type' => 'group',
                'value' => 'Australian Antarctic Data Centre',
            ],
            'dimensions' => ['portal_view', 'portal_search'],
        ),
        array(
            'log' => 'portal',
            'period' => ['startDate' => '2015-06-01', 'endDate' => '2015-06-06'],
            'group' => [
                'type' => 'group',
                'value' => 'Griffith University',
            ],
            'dimensions' => ['portal_view', 'portal_search'],
        ),
        array(
            'log' => 'portal',
            'period' => ['startDate' => '2015-06-01', 'endDate' => '2015-06-06'],
            'group' => [
                'type' => 'group',
                'value' => 'N2O Network',
            ],
            'dimensions' => ['portal_view', 'portal_search'],
        ),
        array(
            'log' => 'portal',
            'period' => ['startDate' => '2015-06-01', 'endDate' => '2015-06-06'],
            'group' => [
                'type' => 'group',
                'value' => 'CSIRO',
            ],
            'dimensions' => ['portal_view', 'portal_search'],
        ),
        array(
            'log' => 'portal',
            'period' => ['startDate' => '2015-06-01', 'endDate' => '2015-06-06'],
            'group' => [
                'type' => 'group',
                'value' => 'Curtin University',
            ],
            'dimensions' => ['portal_view', 'portal_search'],
        ),
    );

    /**
     * Index entry
     * If CLI then just return pass or fail
     * If Browser then return the entire test results
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @return mixed results
     */
    public function index()
    {

        $this->run_tests();

        if ($this->input->is_cli_request()) {
            $this->output->set_header('Content-type: application/json');
            set_exception_handler('json_exception_handler');
            $result = $this->unit->result();
            $pass = true;
            foreach ($result as $res) {
                if ($res['Result'] != 'Passed') {
                    $pass = false;
                }
            }
            echo $pass ? 'Unit Test Passed' . "\n" : "Unit Test Failed" . "\n";
        } else {
            echo $this->unit->report();
        }

    }

    /**
     * Actually run the tests
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @return void
     */
    public function run_tests()
    {
        $this->testSummaryAPI($this->testdata[0]);
        $this->testSummaryAPI($this->testdata[1]);
        $this->testGetStatAPI('doi', $this->testdata[0]);
        $this->testGetStatAPI('tr', $this->testdata[0]);
        $this->testGetStatAPI('doi', $this->testdata[1]);
        $this->testGetStatAPI('tr', $this->testdata[1]);
        $this->testGetStatAPI('doi_minted', $this->testdata[2]);
        // $this->testGetStatAPI('doi_client', $this->testdata[3]);
        // $this->testGetStatAPI('doi_client', $this->testdata[4]);
    }

    /**
     * Test Summary API
     * @param  mixed $testdata
     * @return void
     */
    public function testSummaryAPI($testdata)
    {
        $result = $this->callAPI('summary', $testdata);
        //check valid and is JSON
        $this->unit->run($this->isJson($result), true, 'Valid Request');
        $result = json_decode($result, true);
        $this->unit->run($result, 'is_array', 'Array return');

        $this->unit->run($result['dates'], 'is_array', 'Has Result');
        foreach ($result['dates'] as $date => $res) {
            $this->unit->run($res, 'is_array', 'Has result for ' . $date);
            foreach ($res as $key => $data) {
                $this->unit->run($data, 'is_int', 'Has data for ' . $key . ' ' . $data);
            }
        }
    }

    /**
     * Test GetStat API
     * @param  string $path
     * @param  mixed $testdata
     * @return void
     */
    public function testGetStatAPI($path, $testdata)
    {

        $result = $this->callAPI('getStat/' . $path, $testdata);
//         dd($result);
        //check valid and is JSON
        $this->unit->run($this->isJson($result), true, 'Valid Request');
        $result = json_decode($result, true);
        $this->unit->run($result, 'is_array', 'Array return');

        if ($path == 'doi') {
            $this->unit->run($result['total'], 'is_int', 'Has Total ' . $result['total']);
            $this->unit->run($result['missing_doi'], 'is_int', 'Has Missing ' . $result['missing_doi']);
            $this->unit->run($result['has_doi'], 'is_int', 'Has DOI ' . $result['has_doi']);
        } elseif ($path == 'tr') {
            //has doc count on all result
            foreach ($result as $res) {
                $this->unit->run($res['doc_count'], 'is_int', 'Has ' . $res['key'] . ' => ' . $res['doc_count']);
            }
        } elseif ($path=='doi_minted') {
            //has minted
            foreach ($result as $res) {
                $this->unit->run($res['count'], 'is_int', 'Has ' . $res['activity'] . ' => ' . $res['count']);
            }
        } elseif ($path=='doi_client') {
            $this->unit->run($result['client_id'], 'is_string', 'Has Client ID: '. $result['client_id']);
            $this->unit->run($result['url_num'], 'is_string', 'Has URLs : '. $result['client_id']);
            $this->unit->run($result['url_broken_num'], 'is_string', 'Has Broken URLs: '. $result['client_id']);
        }
    }

    /**
     * Helper function to determine if a string is a json
     * @param  string  $string
     * @return boolean         is a json or not
     */
    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Helper function to POST data to the API
     * @param  string $path
     * @param  mixed $filters
     * @return mixed
     */
    public function callAPI($path, $filters)
    {
        $filters['filters'] = $filters;
        $result = curl_post(apps_url('analytics/' . $path), json_encode($filters));
        return $result;
    }

    //boring constructor
    public function __construct()
    {
        $this->load->model('summary');
        $this->load->model('dois');
        $this->load->library('unit_test');
    }

}
