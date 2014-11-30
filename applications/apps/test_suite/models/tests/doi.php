<?php
require_once(APP_PATH. 'test_suite/models/_GenericTest.php');

/**
 * Class Doi
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Doi extends _GenericTest {


	/**
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 */
	function run_test() {
		/* Set up variables to pass to the test functions to stimulate good and faulty DOI API calls */
		$app_id = $this->config->item('gDOIS_TEST_APP_ID');
		$incorrect_app_id = 'mvivnnvnjvn4tjvmve2432nvbthth';

		$shared_secret = $this->config->item('gDOIS_TEST_SHARED_SECRET');
		$incorrect_shared_secret = 'gmpryonty';

		$url = 'http://devl.ands.org.au/example1.php';
		$incorrect_url = 'http;//no.domain.exists/example.php';

		$doiversion_service_points = array('v1.0'=>'https://services.ands.org.au/home/dois/doi_' , 'v1.1'=>'https://services.ands.org.au/doi/1.1/', 'test' => apps_url().'/mydois/');

		$validxml = 'xml='.file_get_contents(asset_url('test_cases/doi/validxml.xml'),'test_suite');
		$invalidxml = 'xml='.file_get_contents(asset_url('test_cases/doi/invalidxml.xml'),'test_suite');

		$requestURI = $doiversion_service_points['v1.1'];
		$requestURI = $doiversion_service_points['test'];
		$v1_service_url = $doiversion_service_points['v1.0'];

		/* first we mint a DOI using the latest version of the CMD API*/

		$testDOI = $this->test_mint($app_id,$shared_secret,$url,$requestURI,'mint','json',$validxml);
		$this->unit->run(is_string($testDOI), true, 'Test Mint returns DOI: '. $testDOI);

		if($testDOI){
			$validxml = (str_replace('<identifier identifierType="DOI"></identifier>','<identifier identifierType="DOI">'.$testDOI.'</identifier>',$validxml));

			$this->test_doi_api_functions($app_id,$shared_secret, $url, $testDOI, $validxml, $requestURI);
			$this->test_doi_authentication($app_id,$incorrect_app_id,$shared_secret, $incorrect_shared_secret,$url,$testDOI,$requestURI);
			$this->test_doi_xml($app_id,$shared_secret,$url,$testDOI,$validxml,$invalidxml,$requestURI);
			$this->test_doi_service_point($app_id,$shared_secret,$url,$testDOI,$validxml,$v1_service_url,$requestURI);
			$this->test_response_type($app_id,$shared_secret,$url,$testDOI,$validxml,$requestURI);

		} else {
			return;
		}

	}

	/**
	 * @param $app_id
	 * @param $shared_secret
	 * @param $url
	 * @param string $testDOI
	 * @param $validxml
	 * @param $requestURI
	 * @author Liz Woods
	 */
	function test_doi_api_functions($app_id,$shared_secret, $url,$testDOI='',$validxml, $requestURI) {
		$test_cases = array(
			array('Update a doi', $app_id, $shared_secret, $url,$testDOI,'update', 'string',$requestURI, '[MT002]'),
			array('Deactivate a doi', $app_id, $shared_secret, $url,$testDOI,'deactivate','string',$requestURI, '[MT003]'),
			array('Activate a doi', $app_id, $shared_secret, $url,$testDOI,'activate','string',$requestURI, '[MT004]'),
		);

		foreach($test_cases as $case){
			$test = $this->test_doi_api($case[1], $case[2], $case[3],$case[4],$case[5],$case[6],$case[7]);
			$this->unit->run($test, $case[8], $case[0].':  Expected message '. $case[8]);
		}
	}

	/**
	 * @param $app_id
	 * @param $incorrect_app_id
	 * @param $shared_secret
	 * @param $incorrect_shared_secret
	 * @param $url
	 * @param $testDOI
	 * @param $requestURI
	 * @author Liz Woods
	 */
	function test_doi_authentication($app_id,$incorrect_app_id,$shared_secret, $incorrect_shared_secret,$url,$testDOI,$requestURI)
	{
		$test_cases = array(
			array('Update a doi - correct app_id and shared_secret', $app_id, $shared_secret, $url,$testDOI,'update', 'string',$requestURI, '[MT002]'),
			array('Update a doi - incorrect app_id', $incorrect_app_id, $shared_secret, $url,$testDOI,'update', 'string',$requestURI, '[MT009]'),
			array('Update a doi - incorrect shared_secret', $app_id, $incorrect_shared_secret, $url,$testDOI,'update','string',$requestURI, '[MT009]'),
		);

		foreach($test_cases as $case){
			$test = $this->test_doi_api($case[1], $case[2], $case[3],$case[4],$case[5],$case[6],$case[7]);
			$this->unit->run($test, $case[8], $case[0].':  Expected message '. $case[8]. ' got '. $test);
		}
	}

	/**
	 * @param $app_id
	 * @param $shared_secret
	 * @param $url
	 * @param $testDOI
	 * @param $validxml
	 * @param $invalidxml
	 * @param $requestURI
	 * @author Liz Woods
	 */
	function test_doi_xml($app_id,$shared_secret,$url,$testDOI,$validxml,$invalidxml,$requestURI)
	{

		$test_xml_cases = array(
			array('Update a doi - valid xml', $app_id, $shared_secret, $url,$testDOI,'update', 'string',$requestURI,$validxml, '[MT002]'),
			array('Mint a doi - Invalid xml', $app_id, $shared_secret, $url,$testDOI,'mint', 'string',$requestURI ,$invalidxml,'[MT006]'),
			array('Update a doi - Invalid xml', $app_id, $shared_secret, $url,$testDOI,'update', 'string',$requestURI, $invalidxml, '[MT007]'),

		);

		foreach($test_xml_cases as $case){
			$test = $this->test_doi_api($case[1], $case[2], $case[3],$case[4],$case[5],$case[6],$case[7],$case[8]);
			$this->unit->run($test, $case[9], $case[0].':  Expected message '. $case[9]. ' got '. $test);
		}
	}

	/**
	 * @param $app_id
	 * @param $shared_secret
	 * @param $url
	 * @param $testDOI
	 * @param $validxml
	 * @param $v1_service_url
	 * @param $requestURI
	 * @author Liz Woods
	 */
	function test_doi_service_point($app_id,$shared_secret,$url,$testDOI,$validxml,$v1_service_url,$requestURI)
	{

		$test_xml_cases = array(
			array('Update a doi - v1.0 service url', $app_id, $shared_secret, $url,$testDOI,'update', 'string',$v1_service_url,$validxml, '[MT002]'),
			array('Update a doi - v1.1 service url', $app_id, $shared_secret, $url,$testDOI,'update', 'string',$requestURI ,$validxml,'[MT002]'),

		);

		foreach($test_xml_cases as $case){
			$test = $this->test_doi_api($case[1], $case[2], $case[3],$case[4],$case[5],$case[6],$case[7],$case[8]);
			$this->unit->run($test, $case[9], $case[0].':  '.$case[7].' Expected message '. $case[9]. ' got '. $test);
		}
	}

	/**
	 * @param $app_id
	 * @param $shared_secret
	 * @param $url
	 * @param $testDOI
	 * @param $validxml
	 * @param $requestURI
	 * @author Liz Woods
	 */
	function test_response_type($app_id,$shared_secret,$url,$testDOI,$validxml,$requestURI)
	{

		$test_xml_cases = array(
			array('String response type', $app_id, $shared_secret, $url,$testDOI,'update', 'string',$requestURI ,$validxml, '[MT002]'),
			array('json response type', $app_id, $shared_secret, $url,$testDOI,'update', 'json',$requestURI ,$validxml,'MT002'),
			array('XML response type', $app_id, $shared_secret, $url,$testDOI,'update', 'xml',$requestURI ,$validxml,'MT002'),

		);

		foreach($test_xml_cases as $case){
			$test = $this->test_doi_api($case[1], $case[2], $case[3],$case[4],$case[5],$case[6],$case[7],$case[8]);
			$this->unit->run($test, $case[9], $case[0].':  '.$case[7].' Expected message '. $case[9]. ' got '. $test);
		}
	}

	/**
	 * @param $app_id
	 * @param $shared_secret
	 * @param $url
	 * @param $testDOI
	 * @param $action
	 * @param $response_type
	 * @param $requestURI
	 * @param string $postdata
	 * @return SimpleXMLElement[]|string
	 * @author Liz Woods
	 */
	function test_doi_api($app_id,$shared_secret,$url,$testDOI,$action,$response_type,$requestURI,$postdata='')
	{

		$context  = array('Content-Type: application/xml;charset=UTF-8','Authorization: Basic '.base64_encode($app_id.":".$shared_secret));

		if(str_replace("doi_","",$requestURI)!=$requestURI)
		{
			$action= $action.".php";
		}else{
			$action = $action.".".$response_type;
		}
		$requestURI = $requestURI.$action.'/?url='.$url.'&app_id='.$app_id.'&doi='.$testDOI;
		$newch = curl_init();
		curl_setopt($newch, CURLOPT_URL, $requestURI);
		curl_setopt($newch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($newch, CURLOPT_POST, 1);
		curl_setopt($newch, CURLOPT_POSTFIELDS,$postdata);
		curl_setopt($newch, CURLOPT_HTTPHEADER,$context);
		curl_setopt($newch, CURLOPT_FOLLOWLOCATION, TRUE);

		$result = curl_exec($newch);

		curl_close($newch);

		if($response_type=='string') {
			$message_code = substr($result,0,7);
		} elseif($response_type=='json') {
			$obj = json_decode( $result, true );
			$message_code = $obj['response']['responsecode'];
		} elseif($response_type=='xml') {
			$obj = simplexml_load_string($result);
			$message_code = $obj->{'responsecode'};
		} else {
			echo $result;
		}

		return $message_code;

	}

	/**
	 * @param $app_id
	 * @param $shared_secret
	 * @param $url
	 * @param $requestURI
	 * @param $action
	 * @param $response_type
	 * @param $postdata
	 * @return mixed
	 * @author Liz Woods
	 */
	function test_mint($app_id,$shared_secret,$url,$requestURI,$action,$response_type,$postdata) {
		$context  = array('Content-Type: application/xml;charset=UTF-8','Authorization: Basic '.base64_encode($app_id.":".$shared_secret));
		$requestURI = $requestURI.$action.'.'.$response_type.'/?url='.$url.'&app_id='.$app_id;
		$newch = curl_init();
		curl_setopt($newch, CURLOPT_URL, $requestURI);
		curl_setopt($newch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($newch, CURLOPT_POST, 1);
		curl_setopt($newch, CURLOPT_POSTFIELDS,$postdata);
		curl_setopt($newch, CURLOPT_HTTPHEADER,$context);
		$result = curl_exec($newch);
		curl_close($newch);
		$obj = json_decode( $result, true );
		$doi = $obj['response']['doi'];
		return $doi;
	}


}