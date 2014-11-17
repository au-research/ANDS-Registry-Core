<?php
    function getxml($doi_id){
    	$CI =& get_instance();
    	$db = $CI->load->database('dois', TRUE);
    	$result = $db->get_where('doi_objects', array('doi_id'=>$doi_id));
    	return $result;
    }
    
    function getDoiList()
    {
     	$CI =& get_instance();
     	$db = $CI->load->database('dois', TRUE);
    	$result = $db->get_where('doi_objects', array('status'=>"ACTIVE"));
    	return $result;   	
    }
    
    function getDoiListxml()
    {
     	$CI =& get_instance();
     	$db = $CI->load->database('dois', TRUE);
     	$result = $db->query("select * from doi_objects WHERE status = 'ACTIVE'");
    	return $result;   	
    }  
    function getDoisClientDetails($client_id)
	{
     	$CI =& get_instance();
     	$db = $CI->load->database('dois', TRUE);
    	$result = $db->get_where('doi_client', array('client_id'=>$client_id));
    	return $result; 
	} 
	   
	function importDoiObject($doiObjects, $url, $client_id, $created_who='SYSTEM', $status='REQUESTED',$xml)
	{
	
		$runErrors = '';
		$errors = null;	
				
		// Doi Object
		// =========================================================================
		$doiObjectList = $doiObjects->getElementsByTagName("resource");
		
		// Doi Object
		// =====================================================================
		$doiObject = $doiObjectList->item(0);
	
		// Doi Identifier
		// =====================================================================
		
		$doiIdentifier = $doiObject->getElementsByTagName("identifier")->item(0)->nodeValue;
				
		if( $doiIdentifier)
		{			
			//Doi publisher
			// =====================================================================		
			$publisher = $doiObject->getElementsByTagName("publisher")->item(0)->nodeValue;
			
			//Doi publish year
			// =====================================================================
			$publish_year = $doiObject->getElementsByTagName("publicationYear")->item(0)->nodeValue;			
			
			// Doi language
			// =====================================================================
			$languageValue = $doiObject->getElementsByTagName("language")->item(0)->nodeValue;						
			
			// Doi version
			// =====================================================================		
			$versionValue = $doiObject->getElementsByTagName("version")->item(0)->nodeValue;	
					
			// Doi rights
			// =====================================================================		
			$rightsValue = $doiObject->getElementsByTagName("rights")->item(0)->nodeValue;	
												
			$runErrors .= insertDoiObject($doiIdentifier,$publisher,$publish_year,$client_id,$created_who,"REQUESTED",$languageValue,$versionValue,"DOI",$rightsValue,$url,$xml);

		}
		else
		{
				$runErrors .= "Couldn't create DOI without identifier.</br>";
		}
			
		echo $runErrors;
		return $runErrors;
	}	
	   
	function updateDoiObject($doi_id, $doiObjects, $url,$xml)
	{	
		$runErrors = '';
		$errors = null;	
	
		if($url){
			$runErrors .= updateDoiUrl($doi_id,$url);
		}
		
		if($doiObjects)
		{
			// Doi Object
			// =========================================================================
			$doiObjectList = $doiObjects->getElementsByTagName("resource");
			// Doi Object
			// =====================================================================
			$doiObject = $doiObjectList->item(0);
	
			// Doi Identifier
			// =====================================================================	
			$doiIdentifier = $doiObject->getElementsByTagName("identifier")->item(0)->nodeValue;
			
			if( $doiIdentifier )
			{			
				//Doi publisher
				// =====================================================================		
				$publisher = $doiObject->getElementsByTagName("publisher")->item(0)->nodeValue;
			
				//Doi publish year
				// =====================================================================
				$publish_year = $doiObject->getElementsByTagName("publicationYear")->item(0)->nodeValue;			
			
				// Doi language
				// =====================================================================
				$languageValue = $doiObject->getElementsByTagName("language")->item(0)->nodeValue;						
			
				// Doi version
				// =====================================================================		
				$versionValue = $doiObject->getElementsByTagName("version")->item(0)->nodeValue;	
					
				// Doi rights
				// =====================================================================		
				$rightsValue = $doiObject->getElementsByTagName("rights")->item(0)->nodeValue;	
				
				$runErrors .= deleteDoiObjectXml($doiIdentifier);
			
				$runErrors .= updateDoiObjectAttributes($doiIdentifier,$publisher,$publish_year,$languageValue,$versionValue,$rightsValue,$xml);

			}
			else
			{
				$runErrors .= "Couldn't update DOI without identifier.</br>";
			}
		}	
		return $runErrors;
	}

	
	function insertDoiObject($doi_id,$publisher,$publicationYear,$client_id,$created_who,$status,$language,$version,$identifier_type,$rights,$url,$xml)
	{
		$updateTime = date("Y-m-d H:i:s");
		$data = array(
		'doi_id'=> $doi_id, 
		'publisher' => $publisher,
		'publication_year' => $publicationYear,
        'updated_when' => $updateTime,
		'client_id'=> $client_id,
		'created_who'=>$created_who,
		'status'=>$status,
		'language' => $language,
		'version' => $version,
		'identifier_type' => $identifier_type,		
		'rights' =>$rights, 
		'url' => $url,
		'datacite_xml'=>$xml);

		$CI =& get_instance();
		$db = $CI->load->database('dois', TRUE);
    	$query_str = $db->insert_string('doi_objects', $data); 

		$result = $db->query($query_str);

    	if($result!=1)
    	{
    		return 'Error inserting object xml';
    	}
	}
	
	function deleteDoiObjectXml($doi_id)
	{
		$updateTime = date("Y-m-d H:i:s");
		$data = array('publisher' => '','publication_year' => '','language' => '','version' => '','rights' =>'', 'updated_when' => $updateTime, 'datacite_xml'=>'');
		$where = "doi_id = '".$doi_id."'";
		
		$CI =& get_instance();
		$db = $CI->load->database('dois', TRUE);

    	$query_str = $db->update_string('doi_objects', $data, $where); 
    	$result = $db->query($query_str);

    	$db->delete('doi_alternate_identifiers', array('doi_id' => $doi_id)); 
    	$db->delete('doi_contributors', array('doi_id' => $doi_id)); 
    	$db->delete('doi_creators', array('doi_id' => $doi_id)); 
    	$db->delete('doi_dates', array('doi_id' => $doi_id)); 
    	$db->delete('doi_descriptions', array('doi_id' => $doi_id)); 
    	$db->delete('doi_formats', array('doi_id' => $doi_id)); 
    	$db->delete('doi_related_identifiers', array('doi_id' => $doi_id)); 

    	$db->delete('doi_resource_types', array('doi_id' => $doi_id)); 
		$db->delete('doi_sizes', array('doi_id' => $doi_id)); 
		$db->delete('doi_subjects', array('doi_id' => $doi_id));
		$db->delete('doi_titles', array('doi_id' => $doi_id));

    	if($result!=1)
    	{
    		return 'Error deleting object xml';
    	}
	}

	function updateDoiObjectAttributes($doi_id,$publisher,$publish_year,$languageValue,$versionValue,$rightsValue,$xml)
	{
        $updateTime = date("Y-m-d H:i:s");
        $data = array('publisher' => $publisher,'publication_year' => $publish_year,'updated_when'=> $updateTime,'language' => $languageValue,'version' => $versionValue,'rights' => $rightsValue,'datacite_xml'=>$xml);
		$where = "doi_id = '".$doi_id."'";
		$CI =& get_instance();
		$db = $CI->load->database('dois', TRUE);
    	$query_str = $db->update_string('doi_objects', $data, $where); 
    	$result = $db->query($query_str);
    	if($result!=1)
    	{
    		return 'Error updating object attributes ';
    	}	
	}
	
	function updateDoiUrl($doi_id,$url)
	{
		$updateTime = date("Y-m-d H:i:s");		
		$data = array('url' => $url, 'updated_when' => $updateTime,);
		$where = "doi_id = '".$doi_id."'";
		$CI =& get_instance();
		$db = $CI->load->database('dois', TRUE);
    	$query_str = $db->update_string('doi_objects', $data, $where); 
    	$result = $db->query($query_str);
    	if($result!=1)
    	{
    		return 'Error updating url for doi '.$doi_id;
    	}	
	}	
	
	function checkDoisValidClient($ip_address,$app_id)
	{
     	$CI =& get_instance();
     	$db = $CI->load->database('dois', TRUE);
    	$results = $db->get_where('doi_client', array('app_id'=>$app_id)); 	

		$data = file_get_contents("php://input");
		parse_str($data, $output);

		$shared_secret = false;               	
		if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
        {
        	$shared_secret = $_SERVER['PHP_AUTH_PW'];
        }
   		elseif(isset($output['shared_secret']))
   		{
   			$shared_secret = $output['shared_secret'];
   		} 
 
		if( $results->num_rows()>0 )
		{		
			foreach($results->result() as $row)
			{	
				if($shared_secret && $shared_secret == $row->shared_secret) return $row->client_id;
				if($ip_address && test_ip($ip_address, $row->ip_address)) return $row->client_id;	
			}
			return false;
	
		}else{
			return false;
		}
	}

	/**
	 * Test a string free form IP against a range
	 * @param  string $ip       the ip address to test on
	 * @param  string $ip_range the ip address to match on / or a comma separated list of ips to match on
	 * @return bool           returns true if the ip is found in some manner that match the ip range
	 */
	function test_ip($ip, $ip_list){
		// first lets get the lists of ip address or ranges separated by commas
		$ip_ranges = explode(',',$ip_list);
		foreach($ip_ranges as $ip_range)
		{
			$ip_range = explode('-',$ip_range);
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

			}else{
				if(ip_match($ip,$ip_range[0])){return true;}
			}
		}
		return false;
	}
	/**
	 * a helper function for test_ip
	 * @param  string $ip    the ip address of most any form to test
	 * @param  string $match the ip to perform a matching truth test
	 * @return bool        return true/false depends on if the first ip match the second ip
	 */
	function ip_match($ip, $match){
		if(ip2long( $match)){//is an actual IP
				if($ip==$match){
				return true;
			}
		}else{//is something weird
			if(strpos($match, '/')){//if it's a cidr notation
				if(cidr_match($ip, $match))
				{
					return true;
				}
			}else{//is a random string (let's say a host name)
				$match = gethostbyname($match);
				if($ip==$match){
					return true;
				}else{
					return false;
				}
			}
		}
		return false;
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
	
	function checkDoisClientDoi($doi_id,$client_id)
	{
     	$CI =& get_instance();
     	$doi_db = $CI->load->database('dois', TRUE);		
    	$results = $doi_db->get_where('doi_objects', array('doi_id'=>$doi_id,'client_id'=>$client_id));
   					
		if( $results->num_rows()>0 )
		{
			foreach($results->result() as $row)
			{
				$return = $row->client_id;		
			}
			return $return;		
		}else{
			return false;
		}
	}

    function doisDomainAvailible($domain)
	{	
	    //initialize curl
	    $curlInit = curl_init($domain);
	    curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
	    curl_setopt($curlInit,CURLOPT_HEADER,true);
	    curl_setopt($curlInit,CURLOPT_NOBODY,true);
	    curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);
	
	    //get answer
	    $response = curl_exec($curlInit);  
	    $curlInfo = curl_getinfo($curlInit);
	    curl_close($curlInit);
	    //check http status code
	    if ($curlInfo["http_code"]<400) return true;
	
	    return false;
	}
	
	function getDoisClient($app_id)
	{	
	   	$CI =& get_instance();
	   	$db = $CI->load->database('dois', TRUE);
	    $results = $db->get_where('doi_client', array('app_id'=>$app_id));
		
		return $results;
	}
	
	function insertDoiActivity($activity,$doiValue='NULL',$result,$client_id='0',$message)
	{
		$data = array('activity' => $activity,'doi_id' => $doiValue,'result'=>$result, 'client_id'=> $client_id, 'message'=>$message);
		$CI =& get_instance();
		$db = $CI->load->database('dois', TRUE);
    	$result = $db->insert('activity_log', $data); 
		////$result = $db->query($query_str);			
	    if($result!=1)
	    {
	    	$insertError = 'Error inserting into activity';
	    }  		
	}
	
	function setDoiStatus($doi_id, $status)
	{
		$data = array('status' => $status);
		$where = "doi_id = '".$doi_id."'";
		$CI =& get_instance();
		$db = $CI->load->database('dois', TRUE);
    	$query_str = $db->update_string('doi_objects', $data, $where); 
    	$result = $db->query($query_str);
    	if($result!=1)
    	{
    		return 'Error updating object status ';
    	}				
	}
	
	function getDoiStatus($doi_id)
	{

	    $CI =& get_instance();
	    $db = $CI->load->database('dois', TRUE);
    	$results = $db->get_where('doi_objects', array('doi_id'=>$doi_id));
   					
		if( $results->num_rows()>0 )
		{
			foreach($results->result() as $row)
			{
				$return = $row->status;		
			}
			return $return;		
		}else{
			return false;
		}		
		
	}

    function getClientDomains($client_id)
    {
        $CI =& get_instance();
        $doi_db = $CI->load->database('dois', TRUE);
        $results = $doi_db->get_where('doi_client_domains', array('client_id'=>$client_id));

        if( $results->num_rows()>0 )
        {
            return $results->result();
        }else{
            return false;
        }

    }
 ?>