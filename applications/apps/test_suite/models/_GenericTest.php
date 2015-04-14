<?php
class _GenericTest extends CI_Model {

	public $result;
	public $report;
	public $elapsed;
	public $memory_usage;
	public $status;

	function run_test() {}

	function test() {
		$this->benchmark->mark('code_start');

		$this->run_test();

		$this->benchmark->mark('code_end');

		$this->result = $this->unit->result();
		$this->report = $this->unit->report();
		$this->elapsed = $this->benchmark->elapsed_time('code_start', 'code_end');
		$this->memory_usage =  memory_get_peak_usage();
		$this->status = $this->verifyStatus();
	}

	function verifyStatus() {
		foreach($this->result as $r){
			if($r['Result']!='Passed') {
				return 'Failed';
			}
		}
		return 'Passed';
	}

	function __construct() {
		set_exception_handler('json_exception_handler');
		$this->load->library('unit_test');
		$this->unit->use_strict(TRUE);
		$this->status = 'UnTested';
	}
}