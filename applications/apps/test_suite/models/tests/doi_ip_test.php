<?php
require_once(APP_PATH. 'test_suite/models/_GenericTest.php');

/**
 * Class Doi_ip_test
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Doi_ip_test extends _GenericTest {


	/**
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 */
	function run_test() {

		require(APP_PATH.'mydois/helpers/doi_db_helper.php');

		/**
		 * A series of test case
		 * @var in the form of array(test_name, 1st argument, 2nd argument, expected_result)
		 */
		$test_cases = array(
			array('Test IP in CSV Range w/ more than 2 values (list mode)', '192.168.10.14', '192.168.10.1,192.168.10.15,192.168.10.14', true),
			array('Test IP in CSV Range w/ 2 values (range mode)', '192.168.10.14', '168.168.10.1-192.168.10.15', true),
			array('Host Name against IP', '130.56.60.128', 'ands3.anu.edu.au', true),
			array('Sample Test', '130.56.111.71', '130.56.60.97-130.56.111.71', true),
			array('Host Name against IP', '130.56.62.109', 'ands3.anu.edu.au', false),
			array('Host Name against IP', 'hello world!#$%$#%', '130.56.62.129',  false),
			array('Test Matching Exact', '127.0.0.1', '127.0.0.1', true),
			array('Test Not Matching Exact', '1.x.x.x', 'test+str', false),
			array('Test Matching Exact', '1.2.3.5', '1.2.3.5', true),
			array('Test Not Matching Exact', '127.0.0.1', '127.0.0.2', false),
			array('Test Matching Exact In Range', '127.0.0.1', '127.0.0.1-194.123.123.123', true),
			array('Test IP CIDR', '192.168.1.23', '192.168.1.0/24', true),
			array('Test IP CIDR', '192.168.1.5', '192.168.1.4/32',  false),
			array('Test IP CIDR', '192.168.1.4', '192.168.1.4/32', true),
			array('Test IP CIDR', '192.168.1.23', '192.168.1.0/24',  true),
			array('Test IP CIDR Not Matching', '92.168.4.23',  '192.162.1.0/24',false),
			array('Test IP CIDR Not Matching', '192.168.1.23','192.162.1.0/24',  false),
		);

		//Run the test case
		foreach($test_cases as $case){
			$test = test_ip($case[1], $case[2]);
			$this->unit->run($test, $case[3], $case[0].':  <b>'.$case[1]. '</b> and <b>'.$case[2].'</b> expected '. (($case[3]) ? 'true' : 'false'));
		}

	}

}