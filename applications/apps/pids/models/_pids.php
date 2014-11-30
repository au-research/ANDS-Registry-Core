<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class _pids extends CI_Model
{

	private $_CI; 
	private $pid_db = null;
	private $PIDS_SERVICE_BASE_URI = null;
	private $PIDS_APP_ID = null;

	function __construct(){
		parent::__construct();
		$this->_CI =& get_instance();
		$this->pid_db = $this->load->database('pids', TRUE);
		$this->PIDS_APP_ID = $this->_CI->config->item('pids_server_app_id');
		$this->PIDS_SERVICE_BASE_URI = $this->_CI->config->item('pids_server_base_url');
	}

	function getTrustedClients(){
		$result = $this->pid_db->get('public.trusted_client');
		return $result->result_array();
	}

	function addTrustedClient($ip, $desc, $appId){
			$requestURI = $this->PIDS_SERVICE_BASE_URI.'addClient';
			$requestURI .= "?ip=".$ip."&desc=".$desc;
			$requestURI .= (strlen($appId)==40 ? "&appId=" . $appId : '');
			$response = file_get_contents($requestURI);
			$result_array = array();
			if (!$response) {
				$result_array['errorMessages'] = "Error whilst attempting to fetch from URI: " . $this->PIDS_SERVICE_BASE_URI;
			} else {
			
				$responseDOMDoc = new DOMDocument();
				$result = $responseDOMDoc->loadXML($response);
				if( $result )
				{
					$messageType = strtoupper($responseDOMDoc->getElementsByTagName("response")->item(0)->getAttribute("type"));
					if( $messageType == 'SUCCESS' )
					{
						
						$xPath = new DOMXPath($responseDOMDoc);
						$nodeList = $xPath->query("//property[@name='appId']");
						$appId = $nodeList->item(0)->getAttribute("value");
						
						if( strlen($appId) == 40 )
						{
							$result_array['app_id'] = $appId;
						} 
						else 
						{
							$result_array['errorMessages'] = "Could not extract appId. Status of request unknown.<br/>";
						}
						
					} elseif ( $messageType == 'FAILURE' ) {
						
						foreach ($responseDOMDoc->getElementsByTagName("response")->item(0)->getElementsByTagName("message") AS $message) {
						    $result_array['errorMessages'] = $message->nodeValue . "<br/>";
						}
					}
					
				} else {
					
					$result_array['errorMessages'] = "Error whilst attempting to load XML response. Response could not be parsed.";
				}
			}		
			return $result_array;
	}

	function getAllAppID(){
		$result = array();
		$query = $this->pid_db->select('app_id')->distinct()->from('public.trusted_client')->get();
		if($query->num_rows()==0) return array();
		foreach($query->result_array() as $r){
			$result[] = $r['app_id'];
		}
		return $result;
	}

    function processUploadedFile($upload_path, $fileName){
        $status = 'SUCCESS';
        $log = '';
        $updateCount = 0;
        $errorCount = 0;
        $mintCount = 0;
        $csv = $this->getAssocArrayFromFile($upload_path.$fileName.'.csv');
        $userIdentifier = $this->_CI->session->userdata(PIDS_USER_IDENTIFIER);
        $userDomain = $this->_CI->session->userdata(PIDS_USER_DOMAIN);
        $ownerHandle = $this->getOwnerHandle($userIdentifier, $userDomain);
        $userHandles = $this->getHandles($ownerHandle);
        $file = fopen($upload_path.$fileName.'_result.csv','x+');
        fputcsv($file,  array("NUMBER",'HANDLE','DESC','URL'), ',', '"');
        $i = 0;
        foreach($csv as $handleInfo)
        {
            $i++;
            $handleValue = $handleInfo['HANDLE'];
            if($handleValue != '')
            {
                if(in_array($handleValue, $userHandles)){
                    $log .= $this->updateHandle($handleInfo);
                    fputcsv($file, array($i, $handleInfo['HANDLE'], $handleInfo['DESC'], $handleInfo['URL']), ',',  '"');
                    $updateCount++;
                }
                else{
                    $log .= 'Error updating handle: '.$handleValue." doesn't exist in your domain!";
                    $errorCount++;
                }
            }
            else{
                $result = $this->mintHandle($handleInfo);
                $log .= $result['log'];
                fputcsv($file, array($i, $result['handle'], $handleInfo['DESC'], $handleInfo['URL']), ',',  '"');
                $mintCount++;
            }
        }
        fclose($file);
        $message = 'Handles Received:'.count($csv).NL.'Updated:'.$updateCount.NL.'Minted: '.$mintCount.NL.'Error:'.$errorCount;
        return array('status' => $status,'message' => $message, 'log' => $log);
    }

    function updateHandle($handleInfo){
        $handleValue = $handleInfo['HANDLE'];
        $description = $handleInfo['DESC'];
        $url = $handleInfo['URL'];
        $log = 'UPDATE LOG:'.NL;
        $hadDescription = false;
        $hadUrl = false;
        $handlesDetails = $this->getHandlesDetails(array($handleValue));

        foreach($handlesDetails as $h){
            $index = $h['idx'];
            if($h['type']=='DESC'){
                $hadDescription = true;
                if($description == '') {
                    $updateResponse= $this->delete_value_by_index($handleValue, $index);
                    $log .= $handleValue." ".$this->pidsGetUserMessage($updateResponse).NL.' (DESC)';
                }
                elseif($description != $h['data'])
                {
                    $updateResponse = $this->modify_value_by_index($handleValue, $description, $index);
                    $log .= $handleValue." ".$this->pidsGetUserMessage($updateResponse).' DESC: '.$description.NL;
                }
                else{
                    $log .= $handleValue ." DESC ".$h['data']." wasn't changed".NL;
                }
            }
            if($h['type']=='URL'){
                $hadUrl = true;
                if($url == ''){
                    $updateResponse = $this->delete_value_by_index($handleValue, $index);
                    $log .= $handleValue." ".$this->pidsGetUserMessage($updateResponse).NL.' (URL)';
                }
                elseif($url != $h['data'])
                {
                    $updateResponse = $this->modify_value_by_index($handleValue, $url, $index);
                    $log .= $handleValue." ".$this->pidsGetUserMessage($updateResponse).' URL: '.$url.NL;
                }
                else{
                    $log .= $handleValue ." URL ".$h['data']." wasn't changed".NL;
                }
            }
        }
        if(!$hadDescription && $description != ''){
            $updateResponse = $this->pidsRequest('addValue', 'type=DESC&value='.urlencode($description).'&handle='.urlencode($handleValue));
            $log .= pidsGetUserMessage($updateResponse).NL.' DESC: '.$description.NL;
        }
        if(!$hadUrl && $url != ''){
            $updateResponse = $this->pidsRequest('addValue', 'type=URL&value='.urlencode($url).'&handle='.urlencode($handleValue));
            $log .= pidsGetUserMessage($updateResponse).NL.' URL: '.$url.NL;
        }

        return $log;
    }

    function mintHandle($handleInfo){
        $handleValue = '';
        $description = urlencode($handleInfo['DESC']);
        $url = urlencode($handleInfo['URL']);
        $log = 'MINT LOG:'.NL;
        if($url && $description){
            $response = $this->pidsRequest('mint', 'type=DESC&value='.$description);
            if($this->pidsGetResponseType($response) == 'SUCCESS'){
                $handleValue = $this->pidsGetHandleValue($response);
                $updateResponse = $this->pidsRequest('addValue', 'type=URL&value='.$url.'&handle='.urlencode($handleValue));
                if($this->pidsGetResponseType($updateResponse) != 'SUCCESS'){
                    $log .= "Couldn't add URL: ".$url." to Handle:".$handleValue;
                }
            }
        }else if($url){
            $response = $this->pidsRequest('mint', 'type=URL&value='.$url);
        }else if($description){
            $response = $this->pidsRequest('mint', 'type=DESC&value='.$description);
        }
        if($this->pidsGetResponseType($response) == 'SUCCESS'){
            $handleValue = $this->pidsGetHandleValue($response);
            $log .= "Successfully created Handle:".$handleValue." with DESC:".$handleInfo['DESC']." URL:".$handleInfo['URL'];
        }
        return array('handle'=>$handleValue, 'log'=>$log);
    }

    function getAssocArrayFromFile($file)
    {
        ini_set('auto_detect_line_endings',true);
        $rows = array();
        $headers = array();
        if (file_exists($file) && is_readable($file)) {
            $handle = fopen($file, 'r');
            while (($row = fgetcsv($handle, 2048, ',', '"')) !== false)
            {
                if (empty($headers))
                    $headers = $row;
                else if (is_array($row)) {
                    array_splice($row, count($headers));
                    $rows[] = array_combine($headers, $row);
                }
            }
            fclose($handle);
        }
        return $rows;
    }

	function removeTrustedClient($ip, $appId){
		$this->pid_db->delete('public.trusted_client', array('ip_address'=>$ip, 'app_id'=>$appId));
	}

    function getFilePrefixForCurrentIdentifier()
    {
        $result = strtolower($this->_CI->session->userdata(PIDS_USER_IDENTIFIER));
        $result = preg_replace("/[^a-z0-9\s-]/", "", $result);
        return trim(preg_replace("/[\s-]+/", " ", $result));
    }



    function getBatchPidsCSVforIdentifier()
    {
        $upload_path = './assets/uploads/pids/';
        $userFilePrefix = $this->getFilePrefixForCurrentIdentifier();
        $fileArray = array();
        if(!$userFilePrefix) return $fileArray;
        if (is_dir($upload_path)){
            $fileNames = scandir($upload_path ,SCANDIR_SORT_DESCENDING);
                foreach($fileNames as $file){
                    if(strpos($file,$userFilePrefix) === 0){
                        array_push($fileArray, $file);
                    }
                }
        }
        return $fileArray;
    }

	function getPidOwners()
	{
		$result = array();
		$searchQuery = $this->pid_db
					->select('*')
					->from('public.search_view')
					->like('data', '####')
					->get();
		if($searchQuery->num_rows()==0) return array();
		foreach($searchQuery->result_array() as $r){
			$arr = explode("####", $r['data'], 2);
			$identifier = $arr[0];
			$result[] = array('handle'=>$r['handle'], 'identifier'=>$identifier);
		}
		return $result;
	}

	function setOwnerHandle($pid_handler , $owner_handler)
	{
		$this->pid_db->where('type', 'AGENTID');
		$this->pid_db->where('handle', $pid_handler);
		$query = $this->pid_db->update('public.handles', array('data'=>$owner_handler)); 
		return $query;
	}


	function getOwnerHandle($userIdentifier, $userDomain)
	{
		$this->_CI->session->set_userdata(PIDS_USER_IDENTIFIER, $userIdentifier);
		$this->_CI->session->set_userdata(PIDS_USER_DOMAIN, $userDomain);
		$identifierStr = $userIdentifier.'####'.$userDomain;
		$query = $this->pid_db->get_where("public.handles", array("type"=>'DESC', 'data'=>$identifierStr));
		if($query->num_rows()>0){
			$array = $query->result_array();
			return $array[0]['handle'];
		}
	}

	function getHandles($ownerHandle, $searchText = null)
	{
		$aHandles = array();
		$query = $this->pid_db
			->select('handle')->distinct()
			->from('public.handles')
			->where('handle !=',$ownerHandle)
			->where("type",'AGENTID')
			->where('data',$ownerHandle)
			->order_by('handle', 'asc')
			->get();

		if($query->num_rows() == 0){
			$result = array();
			return $result;
		}

		if($searchText){
			$handles = $query->result_array();
			foreach($query->result_array() as $r)
			{
				$aHandles[] = $r['handle'];
			}
			$searchQuery = $this->pid_db
					->select('handle')->distinct()
					->from('public.search_view')
					->where('handle !=', $ownerHandle)
					->like('data', $searchText)
					->or_like('handle', $searchText)
					->where_in('handle', $aHandles)
					->order_by('handle', 'asc')
					->get();
			if($searchQuery->num_rows()==0) return array();
			foreach($searchQuery->result_array() as $r){
				$result[] = $r['handle'];
			}
			return $result;
		}else{
			if($query->num_rows()>0){
				foreach($query->result_array() as $r)
				{
					$aHandles[] = $r['handle'];
				}
			}
			return $aHandles;
		}
		
	}


	function getHandlesDetails($handles)
	{
		$query = $this->pid_db->select('*')->from("public.handles")->where_in("handle",$handles)->get();
		if($query->num_rows()>0){
			return $query->result_array();
		}
	}


	function pidsGetHandleURI($handle)
	{
		return 'http://hdl.handle.net/'.$handle;
	}

	function pidsGetResponseType($response)
	{
		$responseType = 'FAILURE';
		$responseDOMDoc = new DOMDocument();
		$result = $responseDOMDoc->loadXML($response);
		if( $result )
		{
			$responseType = strtoupper($responseDOMDoc->getElementsByTagName("response")->item(0)->getAttribute("type"));
		}
		return $responseType;
	}

	function pidsGetUserMessage($response)
	{
		$userMessage = '';
		$responseDOMDoc = new DOMDocument();
		$result = $responseDOMDoc->loadXML($response);
		if( $result )
		{
			$messageType = strtoupper($responseDOMDoc->getElementsByTagName("message")->item(0)->getAttribute("type"));
			if( $messageType == 'USER' )
			{
				$userMessage = $responseDOMDoc->getElementsByTagName("message")->item(0)->nodeValue;
			}
		}
		return $userMessage;
	}

	function pidsGetHandleValue($response)
	{
		$handleValue = '';
		$responseDOMDoc = new DOMDocument();
		$result = $responseDOMDoc->loadXML($response);
		if( $result )
		{
			$handleValue = $responseDOMDoc->getElementsByTagName("identifier")->item(0)->getAttribute("handle");
		}
		return $handleValue;
	}

	function modify_value_by_index($handle, $value, $index)
	{			
		$index = urlencode($index);
		$propertyValue = urlencode($value);
		$handle = urlencode($handle);

		$serviceName = "modifyValueByIndex";
		$parameters  = "handle=".$handle;
		$parameters .= "&index=".$index;
		$parameters .= "&value=".$propertyValue;
		$response = $this->pidsRequest($serviceName, $parameters);
		return $response;
		// return json_encode($response);
	}

	function delete_value_by_index($handle, $index)
	{			
		$index = urlencode($index);
		$handle = urlencode($handle);
		$serviceName = "deleteValueByIndex";
		$parameters  = "handle=".$handle;
		$parameters .= "&index=".$index;
		$response = $this->pidsRequest($serviceName, $parameters);
		return $response;
		// return json_encode($response);
	}

	function pidsRequest($serviceName, $parameters)
	{
		
		$userIdentifier = $this->_CI->session->userdata(PIDS_USER_IDENTIFIER);
		$userDomain = $this->_CI->session->userdata(PIDS_USER_DOMAIN);

		$resultXML = '';		
		$requestURI = $this->PIDS_SERVICE_BASE_URI.$serviceName."?".$parameters;
		$requestBody  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$requestBody .= '<request name="'.$serviceName.'">'."\n";
		$requestBody .= '  <properties>'."\n";
		$requestBody .= '    <property name="appId" value="'.$this->PIDS_APP_ID.'" />'."\n";
		$requestBody .= '    <property name="identifier" value="'.$userIdentifier.'" />'."\n";
		$requestBody .= '    <property name="authDomain" value="'.$userDomain.'" />'."\n";
		$requestBody .= '  </properties>'."\n";
		$requestBody .= '</request>';
		$result = curl_post($requestURI, $requestBody, array("Content-Type: text/plain"));
		if( $result )
		{
			$resultXML = $result;
		}
		return $resultXML;
		
	}

	function pidsGetHandleListDescription($handle)
	{
		$listDescription = '';
				      	
	    // Get the handle to display the first property
		$serviceName = "getHandle";
		$parameters = "handle=".urlencode($handle);
		$response = $this->pidsRequest($serviceName, $parameters);
		
		if( $response )
		{
			$responseDOMDoc = new DOMDocument();
			$result = $responseDOMDoc->loadXML($response);
			
			if( $result )
			{
				// Get the value of the first property.
				if( $responseDOMDoc->getElementsByTagName("property")->item(0) )
				{
					$firstPropertyValue = $responseDOMDoc->getElementsByTagName("property")->item(0)->getAttribute("value");
					if( $firstPropertyValue )
					{
						$listDescription = $firstPropertyValue;
					}
				}

			}
		}
		
		return $listDescription;
	}
}
