<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * DOI IP Test
 * Perform a series of test cases to determine the viability for ip testing
 * used mainly in the DOI_APP
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Doi_ip_test extends MX_Controller {

	function index(){
		$this->load->library('unit_test');

		/**
		 * A series of test case
		 * @var in the form of array(test_name, 1st argument, 2nd argument, expected_result)
		 */
		$test_cases = array(
			array('Test IP in CSV Range w/ more than 2 values (list mode)', '192.168.10.14', '192.168.10.1,192.168.10.15,192.168.10.14', true),
			array('Test IP in CSV Range w/ 2 values (range mode)', '192.168.10.14', '168.168.10.1,192.168.10.15', true),
			array('Host Name against IP', 'ands3.anu.edu.au', '130.56.62.109', true),
			array('Sample Test', '130.56.111.71', '130.56.60.97,130.56.111.71', true),
			array('Host Name against IP', 'ands3.anu.edu.au', '130.56.62.129', false),
			array('Host Name against IP', 'hello world!#$%$#%', '130.56.62.129', false),
			array('Test Matching Exact', '127.0.0.1', '127.0.0.1', true),
			array('Test Not Matching Exact', '1.x.x.x', 'test+str', false),
			array('Test Matching Exact', '1.2.3.5', '1.2.3.5', true),
			array('Test Not Matching Exact', '127.0.0.1', '127.0.0.2', false),
			array('Test Matching Exact In Range', '127.0.0.1', '127.0.0.1,123.123.123', true),
			array('Test IP CIDR', '192.168.1.0/24', '192.168.1.23', true),
			array('Test IP CIDR', '192.168.1.0/24', '192.168.1.23,192.168.1.1', true),
			array('Test IP CIDR Not Matching', '192.162.1.0/24', '92.168.4.23', false),
			array('Test IP CIDR Not Matching', '192.162.1.0/24', '192.168.1.23', false),
		);

		//Run the test case
		foreach($test_cases as $case){
			$test = $this->test_ip($case[1], $case[2]);
			$this->unit->run($test, $case[3], $case[0].':  <b>'.$case[1]. '</b> and <b>'.$case[2].'</b> expected '. (($case[3]) ? 'true' : 'false'));
		}

		//Print out the test report
		echo $this->unit->report();
	}

	/**
	 * Test a string free form IP against a range
	 * @param  string $ip       the ip address to test on
	 * @param  string $ip_range the ip address to match on / or a comma separated list of ips to match on
	 * @return bool           returns true if the ip is found in some manner that match the ip range
	 */
	function test_ip($ip, $ip_range){
		$ip_range = explode(',',$ip_range);
		if(sizeof($ip_range)>1){

			$target_ip = ip2long($ip);
			// If exactly 2, then treat the values as the upper and lower bounds of a range for checking
			// AND the target_ip is valid
			if (count($ip_range) == 2 && $target_ip)
			{
				// convert dotted quad notation to long for numeric comparison
				$lower_bound = ip2long($ip_range[0]);
				$upper_bound = ip2long($ip_range[1]);

				// If the target_ip is valid
				if ($target_ip >= $lower_bound && $target_ip <= $upper_bound)
				{
					return true;
				}
			}
			else
			{
				// Else, fallback to treating them as a list 
				foreach($ip_range as $ip_to_match){
					if($this->ip_match($ip,$ip_to_match)){
						return true;
					}
				}

			}
			return false;
		}else{
			return $this->ip_match($ip,$ip_range[0]);
		}
	}


	/**
	 * a helper function for test_ip
	 * @param  string $ip    the ip address of most any form to test
	 * @param  string $match the ip to perform a matching truth test
	 * @return bool        return true/false depends on if the first ip match the second ip
	 */
	function ip_match($ip, $match){
		if(ip2long($ip)){//is an actual IP
			if($ip==$match){
				return true;
			}else return false;
		}else{//is something weird
			if(strpos($ip, '/')){//if it's a cidr notation
				return $this->cidr_match($match, $ip);
			}else{//is a random string (let's say a host name)
				$ip = gethostbyname($ip);
				if($ip==$match){
					return true;
				}else return false;
			}
		}
	}

	/**
	 * match an ip against a cidr
	 * @param  string $ip   the ip address to perform a truth test on
	 * @param  string $cidr the cidr in the form of xxx.xxx.xxx.xxx/xx to match on
	 * @return bool       return true/false depends on the truth test
	 */
	function cidr_match($ip, $range){
	    list ($subnet, $bits) = explode('/', $range);
	    $ip = ip2long($ip);
	    $subnet = ip2long($subnet);
	    $mask = -1 << (32 - $bits);
	    $subnet &= $mask; // nb: in case the supplied subnet wasn't correctly aligned
	    return ($ip & $mask) == $subnet;
	}
}
	