<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Unit Test Controller for Registry Object Module
 * @todo make official Test Class usecase
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Unittest extends MX_Controller {

    /**
     * Index entry
     * If CLI then just return pass or fail
     * If Browser then return the entire test results
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @return test results
     */
    public function index(){

        $this->run_tests();

        if ($this->input->is_cli_request()) {
            $this->output->set_header('Content-type: application/json');
            set_exception_handler('json_exception_handler');
            $result = $this->unit->result();
            $pass = true;
            foreach ($result as $res) {
                if ($res['Result']!='Passed') {
                    $pass = false;
                }
            }
            echo $pass ? 'Unit Test Passed'."\n" : "Unit Test Failed"."\n";
        } else {
            echo $this->unit->report();
        }

    }

    /**
     * Actually run the tests
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @return void
     */
    public function run_tests() {

        //test getAttribute() function
        $this->unit->use_strict(TRUE);
//        $this->unit->run($this->ro->getAttribute('1840', 'group'), 'The University of Sydney');
//        $this->unit->run($this->ro->getAttribute('1840', 'type'), 'search-http');
//        $this->unit->run($this->ro->getAttribute('2019', 'type'), 'person');

        //these records must not have any administering institution
        $not_have = array(461184, 461184, 461188, 461180, 461182, 461142);
        foreach ($not_have as $ro) {
            $this->unit->run($this->hasAdminiteringInst($ro), false);
        }

        //these records should have administering institution
        $have = array(455262, 72803);
        foreach ($have as $ro) {
            $this->unit->run($this->hasAdminiteringInst($ro), true);
        }
    }

    /**
     * Test Administering Inst
     * @param  int  $ro_id ro_id
     * @return boolean
     */
    private function hasAdminiteringInst($ro_id) {
        $ro = $this->ro->getByID($ro_id);
        $index = $ro->indexable_json();
        if (isset($index['administering_institution'])) {
            return true;
        } else return false;
    }

    //boring constructor
    public function __construct() {
        $this->load->model('registry/registry_object/registry_objects', 'ro');
        $this->load->library('unit_test');
    }

}