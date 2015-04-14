<?php
require_once(APP_PATH. 'test_suite/models/_GenericTest.php');
class Round_trip extends _GenericTest {


	function run_test() {

		//import
		$this->load->model('registry/data_source/data_sources', 'ds');

		//create example datasource
		$sample_key = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);

		$data_source = $this->ds->create($sample_key, url_title($sample_key));
		$data_source->setAttribute('title', $sample_key);
		$data_source->save();

		$test = $this->ds_exist_db($sample_key);
		$this->unit->run($test, true, 'Datasource Exists in db after insert. Key='.$sample_key);

		$this->load->library('importer');

		//delete example datasource
		if($data_source) $data_source->eraseFromDB();

		$test = $this->ds_exist_db($sample_key);
		$this->unit->run($test, false, 'Datasource disappeared in db');
	}

	function ds_exist_code($key){
		$this->load->model('registry/data_source/data_sources', 'ds');
		$ds = $this->ds->getByKey($key);
		if ($ds) {
			return true;
		}else return false;
	}

	function ds_exist_db($key) {
		$result = $this->db->get_where('data_sources', array('key'=>$key));
		if($result->num_rows() > 0){
			return true;
		}else return false;
	}

}