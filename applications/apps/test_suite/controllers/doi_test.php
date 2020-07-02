<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * DOI API function Test
 * Perform a series of test cases to determine the functionality of the DOI API
 * used mainly in the DOI_APP
 *
 * @author Liz Woods <liz.woods@ands.org.au>
 */
class Doi_test extends MX_Controller {

	function index(){

		/* Set up variables to pass to the test functions to simulate good and faulty DOI API calls */
		$app_id = '83b4bceb3072dbz7f836f06f1ec5b85a493f836f';
		$incorrect_app_id = 'mvivnnvnjvn4tjvmve2432nvbthth';

		$shared_secret = '15c62bb5bb';
		$incorrect_shared_secret = 'gmpryonty';

		$url = 'http://devl.ands.org.au/example1.php';
		$incorrect_url = 'http://no.domain.exists/example.php';

        $doiversion_service_points = array(
            'v1.0'=>'https://services.ands.org.au/home/dois/doi_' ,
            'v1.1'=>'https://services.ands.org.au/doi/1.1/',
            'ands' => 'https://researchdata.ands.org.au/api/doi/',
            'ardc' => 'https://researchdata.edu.au/api/doi/',
            'identifiers' => 'https://identifiers.ardc.edu.au/doi/'
        );

		$validxml = 'xml='.urlencode('<?xml version="1.0" encoding="UTF-8"?>
<resource xmlns="http://datacite.org/schema/kernel-3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://datacite.org/schema/kernel-3 http://schema.datacite.org/meta/kernel-3/metadata.xsd">
    <identifier identifierType="DOI">10.5072/00/mydoitest14</identifier>
    <creators>
        <creator>
            <creatorName>Elizabeth Woods</creatorName>
            <affiliation>FOA</affiliation>
        </creator>
    </creators>
    <titles>
        <title>Acacia abrupta</title>
        <title titleType="Subtitle">Version 42 &#x3C; 43</title>
    </titles>
    <publisher>FOA</publisher>
    <publicationYear>2015</publicationYear>
    <subjects>
        <subject>Acacia abrupta</subject>
    </subjects>
    <contributors>
        <contributor contributorType="Editor">
            <contributorName>Mark Chambers</contributorName>
            <affiliation>FOA</affiliation>
        </contributor>
    </contributors>
    <dates>
        <date dateType="Created">2015-07-14</date>
    </dates>
    <language>en</language>
    <resourceType resourceTypeGeneral="Text">Species information</resourceType>
    <descriptions>
        <description descriptionType="Other">Taxonomic treatment for Acacia abrupta</description>
    </descriptions>
</resource>');
// it's invalid for v.2.1 because the affiliation element was introduced in v.3
$invalidxml = 'xml='.urlencode('<?xml version="1.0" encoding="UTF-8"?>
<resource xmlns="http://datacite.org/schema/kernel-2.1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://datacite.org/schema/kernel-2.1 http://schema.datacite.org/meta/kernel-2.1/metadata.xsd">
    <identifier identifierType="DOI">10.5072/example2</identifier>
    <creators>
        <creator>
            <creatorName>B.R.Maslin</creatorName>
            <affiliation>FOA</affiliation>
        </creator>
    </creators>
    <titles>
        <title>Acacia abrupta</title>
        <title titleType="Subtitle">Version 42</title>
    </titles>
    <publisher>FOA</publisher>
    <publicationYear>2015</publicationYear>
    <subjects>
        <subject>Acacia abrupta</subject>
    </subjects>
    <contributors>
        <contributor contributorType="Editor">
            <contributorName>Mark Chambers</contributorName>
            <affiliation>FOA</affiliation>
        </contributor>
    </contributors>
    <dates>
        <date dateType="Created">2015-07-14</date>
    </dates>
    <language>en</language>
    <resourceType resourceTypeGeneral="Text">Species information</resourceType>
    <descriptions>
        <description descriptionType="Other">Taxonomic treatment for Acacia abrupta</description>
    </descriptions>
</resource>');


		$requestURI = $doiversion_service_points['identifiers'];

        /* first we mint a DOI using the latest version of the CMD API*/
		$testDOI = $this->test_mint($app_id,$shared_secret,$url,$requestURI,'mint','json',$validxml);

		if($testDOI)
		{
			$validxml = (str_replace(urlencode('<identifier identifierType="DOI"></identifier>'),urlencode('<identifier identifierType="DOI">'.$testDOI.'</identifier>'),$validxml));
			
			$data['test_mint']="   <span style='color: #0C0;'>Passed </span> ".$testDOI." minted successfully.";

			/* Now use the just minted test DOI to test autentication function using the update DOI API call */

			$this->load->library('unit_test');
			/**
		 	* A series of test case
		 	* @var in the form of array(test_name, 1st argument, 2nd argument, 3rd argument, 4th argument, 5th argument, 6th argument, 7th argument, expected_result)
		 	*/

			$data['test_functions'] = $this->test_doi_api_functions($app_id,$shared_secret, $url,$testDOI,$validxml, $requestURI);
			unset($this->unit->results);
			$data['authentication'] = $this->test_doi_authentication($app_id,$incorrect_app_id,$shared_secret, $incorrect_shared_secret,$url,$testDOI,$requestURI);
			unset($this->unit->results);
			$data['valid_xml'] = $this->test_doi_xml($app_id,$shared_secret,$url,$testDOI,$validxml,$invalidxml,$requestURI);
			unset($this->unit->results);
			$data['service_point'] = $this->test_doi_service_point($app_id,$shared_secret,$url,$testDOI,$validxml,$doiversion_service_points,$requestURI);
			unset($this->unit->results);
			$data['response_type'] = $this->test_response_type($app_id,$shared_secret,$url,$testDOI,$validxml,$requestURI);
		}else{
			$data['test_mint'] = "<span style='color: #C00;'>Failed </span> -  System could not perform initial mint - testing cannot continue.<br />";
            $data['test_functions'] = '';
            $data['authentication'] = '';
			$data['valid_xml'] = '';
			$data['service_point'] = ''; 
			$data['response_type'] =  '';
		}

		$this->load->view('doi_test',$data);
	}

	function test_doi_api_functions($app_id,$shared_secret, $url,$testDOI='',$validxml, $requestURI)
	{
		$test_cases = array(
			array('Update a doi', $app_id, $shared_secret, $url,$testDOI,'update', 'string',$requestURI, '[MT002]'),			
			array('Deactivate a doi', $app_id, $shared_secret, $url,$testDOI,'deactivate','string',$requestURI, '[MT003]'),
			array('Activate a doi', $app_id, $shared_secret, $url,$testDOI,'activate','string',$requestURI, '[MT004]'),			
		);

		foreach($test_cases as $case){
			$test = $this->test_doi_api($case[1], $case[2], $case[3],$case[4],$case[5],$case[6],$case[7]);
			$this->unit->run($test, $case[8], $case[0].':  Expected message '. $case[8]);
		} 
		return $this->unit->report(); 

	}

	function test_doi_authentication($app_id,$incorrect_app_id,$shared_secret, $incorrect_shared_secret,$url,$testDOI,$requestURI)
	{
		$test_cases = array(
			array('Update a doi - correct app_id and shared_secret', $app_id, $shared_secret, $url,$testDOI,'update', 'string',$requestURI, '[MT002]'),	
			array('Update a doi - incorrect app_id', $incorrect_app_id, $shared_secret, $url,$testDOI,'update', 'string',$requestURI, '[MT009]'),			
			array('Update a doi - incorrect shared_secret', $app_id, $incorrect_shared_secret, $url,$testDOI,'update','string',$requestURI, '[MT009]'),		
		);

		foreach($test_cases as $case){
			$test = $this->test_doi_api($case[1], $case[2], $case[3],$case[4],$case[5],$case[6],$case[7]);
			$this->unit->run($test, $case[8], $case[0].':  Expected message '. $case[8]);
		} 
		return $this->unit->report(); 

	}


	function test_doi_xml($app_id,$shared_secret,$url,$testDOI,$validxml,$invalidxml,$requestURI)
	{

		$test_xml_cases = array(
			array('Update a doi - valid xml', $app_id, $shared_secret, $url,$testDOI,'update', 'string',$requestURI,$validxml, '[MT002]'),	
			array('Mint a doi - Invalid xml', $app_id, $shared_secret, $url,$testDOI,'mint', 'string',$requestURI ,$invalidxml,'[MT006]'),
			array('Update a doi - Invalid xml', $app_id, $shared_secret, $url,$testDOI,'update', 'string',$requestURI, $invalidxml, '[MT007]'),			
	
		);

		foreach($test_xml_cases as $case){
			$test = $this->test_doi_api($case[1], $case[2], $case[3],$case[4],$case[5],$case[6],$case[7],$case[8]);
			$this->unit->run($test, $case[9], $case[0].':  Expected message '. $case[9]);
		} 

		return $this->unit->report(); 
	}

	function test_doi_service_point($app_id,$shared_secret,$url,$testDOI,$validxml,$doiversion_service_points,$requestURI)
	{

		$test_xml_cases = array(
			array('Update a doi - v1.0 service url', $app_id, $shared_secret, $url,$testDOI,'update', 'string',$doiversion_service_points['v1.0'],$validxml, '[MT002]'),
			array('Update a doi - v1.1 service url', $app_id, $shared_secret, $url,$testDOI,'update', 'string',$doiversion_service_points['v1.1'] ,$validxml,'[MT002]'),
            array('Update a doi - ANDS service url', $app_id, $shared_secret, $url,$testDOI,'update', 'string',$doiversion_service_points['ands'],$validxml, '[MT002]'),
            array('Update a doi - ARDC service url', $app_id, $shared_secret, $url,$testDOI,'update', 'string',$doiversion_service_points['ardc'] ,$validxml,'[MT002]'),
            array('Update a doi - Identifier service url', $app_id, $shared_secret, $url,$testDOI,'update', 'string',$doiversion_service_points['identifiers'], $validxml,'[MT002]'),

        );

		foreach($test_xml_cases as $case){
			$test = $this->test_doi_api($case[1], $case[2], $case[3],$case[4],$case[5],$case[6],$case[7],$case[8]);
			$this->unit->run($test, $case[9], $case[0].':  '.$case[7].' Expected message '. $case[9]);
		} 

		return $this->unit->report(); 
	}

	function test_response_type($app_id,$shared_secret,$url,$testDOI,$validxml,$requestURI)
	{

		$test_xml_cases = array(
			array('String response type', $app_id, $shared_secret, $url,$testDOI,'update', 'string',$requestURI ,$validxml, '[MT002]'),	
			array('json response type', $app_id, $shared_secret, $url,$testDOI,'update', 'json',$requestURI ,$validxml,'MT002'),	
			array('XML response type', $app_id, $shared_secret, $url,$testDOI,'update', 'xml',$requestURI ,$validxml,'MT002'),		
	
		);

		foreach($test_xml_cases as $case){
			$test = $this->test_doi_api($case[1], $case[2], $case[3],$case[4],$case[5],$case[6],$case[7],$case[8]);
			$this->unit->run($test, $case[9], $case[0].':  '.$case[7].' Expected message '. $case[9]);
		} 

		return $this->unit->report(); 
	}

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

		$curlinfo = curl_getinfo($newch);
		curl_close($newch);

		if($response_type=='string')
		{
			$message_code = substr($result,0,7);

		}elseif($response_type=='json')
		{
			$obj = json_decode( $result, true );
			$message_code = $obj['response']['responsecode'];

		}elseif($response_type=='xml')
		{
		    $obj = simplexml_load_string($result);
			$message_code = $obj->{'responsecode'};

		}else
		{
			echo $result;
		}

		return $message_code;

	}

	function test_mint($app_id,$shared_secret,$url,$requestURI,$action,$response_type,$postdata)
	{

		$context  = array('Content-Type: application/xml;charset=UTF-8','Authorization: Basic '.base64_encode($app_id.":".$shared_secret));

		$requestURI = $requestURI.$action.'.'.$response_type.'/?url='.$url.'&app_id='.$app_id;

		$newch = curl_init();
		curl_setopt($newch, CURLOPT_URL, $requestURI);
		curl_setopt($newch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($newch, CURLOPT_POST, 1);
		curl_setopt($newch, CURLOPT_POSTFIELDS,$postdata);				
		curl_setopt($newch, CURLOPT_HTTPHEADER,$context);

		$result = curl_exec($newch);
		$curlinfo = curl_getinfo($newch);
		$obj = json_decode( $result, true );
		$doi = $obj['response']['doi'];
		return $doi;
	}

    function test_datacite_client_metadata($app_id,$shared_secret,$postdata)
    {

        $context  = array('Content-Type: application/xml;charset=UTF-8','Authorization: Basic '.base64_encode($app_id.":".$shared_secret));

        $requestURI = api_url().'doi/datacite/metadata';

        $newch = curl_init();
        curl_setopt($newch, CURLOPT_URL, $requestURI);
        curl_setopt($newch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($newch, CURLOPT_POST, 1);
        curl_setopt($newch, CURLOPT_POSTFIELDS,$postdata);
        curl_setopt($newch, CURLOPT_HTTPHEADER,$context);

        $result = curl_exec($newch);

        curl_close($newch);

        return $result;

    }

    function test_datacite_client_doi($app_id,$shared_secret,$postdata)
    {

        $context  = array('Content-Type: application/xml;charset=UTF-8','Authorization: Basic '.base64_encode($app_id.":".$shared_secret));

        $requestURI = api_url().'doi/datacite/doi';

        $newch = curl_init();
        curl_setopt($newch, CURLOPT_URL, $requestURI);
        curl_setopt($newch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($newch, CURLOPT_POST, 1);
        curl_setopt($newch, CURLOPT_POSTFIELDS,$postdata);
        curl_setopt($newch, CURLOPT_HTTPHEADER,$context);

        $result = curl_exec($newch);

        curl_close($newch);

        return $result;

    }
    function test_datacite_client_delete($doi,$app_id,$shared_secret)
    {

        $context  = array('Content-Type: application/xml;charset=UTF-8','Authorization: Basic '.base64_encode($app_id.":".$shared_secret));
        $requestURI = api_url().'doi/datacite/metadata/'.$doi;
        $newch = curl_init();
        curl_setopt($newch, CURLOPT_URL, $requestURI);
        curl_setopt($newch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($newch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($newch, CURLOPT_HTTPHEADER,$context);

        $result = curl_exec($newch);

        curl_close($newch);

        return $result;

    }

    function test_datacite_client_get($doi,$app_id,$shared_secret){
        $context  = array('Content-Type: application/xml;charset=UTF-8','Authorization: Basic '.base64_encode($app_id.":".$shared_secret));
        $requestURI = api_url().'doi/datacite/doi/'.$doi;
        $newch = curl_init();
        curl_setopt($newch, CURLOPT_URL, $requestURI);
        curl_setopt($newch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($newch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($newch, CURLOPT_HTTPHEADER,$context);

        $result = curl_exec($newch);

        curl_close($newch);

        return $result;

    }

    function test_datacite_client_getMetadata($doi,$app_id,$shared_secret){
        $context  = array('Content-Type: application/xml;charset=UTF-8','Authorization: Basic '.base64_encode($app_id.":".$shared_secret));
        $requestURI = api_url().'doi/datacite/metadata/'.$doi;
        $newch = curl_init();
        curl_setopt($newch, CURLOPT_URL, $requestURI);
        curl_setopt($newch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($newch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($newch, CURLOPT_HTTPHEADER,$context);

        $result = curl_exec($newch);

        curl_close($newch);

        return $result;

    }
}
