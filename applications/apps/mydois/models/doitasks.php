<?php

class Doitasks extends CI_Model {
	private $_CI;
	const DATACITE_WAIT_TIMEOUT_SECONDS = 5;

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
 		$this->_CI =& get_instance();
 		$this->doi_db = $this->load->database('dois', TRUE);
    }

	function putxml(){
		global $host, $doi_root;
		$base_url	= 'http://'.$host.'/home/dois/';
		$CI =& get_instance();
		$doiList = getDoiListxml();
		foreach($doiList->result() as $doi)
		{
			$xml = file_get_contents($base_url."/doi_xml.php?doi=".$doi->doi_id);
			$data = array('datacite_xml' => $xml);
			$where = "doi_id = '".$doi->doi_id."'";
    		$query_str = $this->db->update_string('doi_objects', $data, $where);
    		$result = $CI->db->query($query_str);
    		if($result!=1)
    		{
    			echo 'Error updating object xml '.$doi->doi_id.'<br/>';
    		}else{
      			echo 'Updated object xml '.$doi->doi_id.'<br/>';
    		}
		}
		exit;
	}

	function xml(){
		global $api_version;
		$xml = '';
        $log_response =array();

		$debug = $this->input->get('debug');

		if($debug && $debug == 'true')
		{
			$this->debugOn();
		}

		$doi_id = $this->input->get('doi');
		$doi_id = rawurldecode($doi_id);
		$response_type = $this->input->get('response_type');
		if(!$response_type)	$response_type = 'string';
		$api_version = $this->input->get('api_version');
		if(!$api_version)	$api_version = '1.0';

        $log_type = "array";
        $log_response['type'] = "log";
        $log_response['responsecode'] = "MT000";
        $log_response['message'] = "Request for xml";
        $log_response['doi'] = $doi_id;
        $log_response['url'] = "";
        $log_response['app_id'] = "public";
        $log_response['verbosemessage'] = "XML has been provided";

		if(!$doi_id)
		{
			$xml = doisGetUserMessage("MT010", $doi_id ,$response_type,$app_id=NULL, "You must provide the doi value to obtain it's xml",$urlValue=NULL);
            $log_response = doisGetUserMessage("MT010", $doi_id ,$log_type,$app_id=NULL, "You must provide the doi value to obtain it's xml",$urlValue=NULL);
		}
		if($xml=='')
		{
			$doidata = getxml($doi_id);
			if($doidata->num_rows() > 0){
				foreach($doidata->result() as $row)
				{
					if($row->status=='ACTIVE')
					{
						$xml = $row->datacite_xml;
						header('Content-type: text/xml');
					}else{
						$xml = doisGetUserMessage("MT012", $doi_id, $response_type,$app_id=NULL, "",$urlValue=NULL);
                        $log_response = doisGetUserMessage("MT012", $doi_id, $log_type,$app_id=NULL, "",$urlValue=NULL);
					}
				}

			}else{
				$xml = doisGetUserMessage("MT011", $doi_id, $response_type,$app_id=NULL, "",$urlValue=NULL);
                $log_response = doisGetUserMessage("MT011", $doi_id, $log_type,$app_id=NULL, "",$urlValue=NULL);
			}
		}
        $this->doilog($log_response,"doi_XML");
		echo $xml;

	}

	function update(){

		$xml ='';
		$errorMessages = '';
		$notifyMessage = '';
		$logMessage = '';
		$verbosemessage = '';
		$outstr = '';
		$doiObjects = null;
		$response1 = "OK";
		$response2 = "OK";
		$testing = 'no';
        $log_type = "array";
        $dataciteSchema = $this->config->item('gCMD_SCHEMA_URIS');
		$debug = $this->input->get('debug');

		if($debug && $debug == 'true')
		{
			$this->debugOn();
		}
        $manual_update = $this->input->get('manual_update');
        if($manual_update)
        {
            $client_id = rawurldecode($this->input->get_post('client_id'));
        }
		$app_id = $this->getAppId();
		if(substr($app_id,0,4)=='TEST')
		{
			$app_id = substr($app_id,4,strlen($app_id));
			$testing = 'yes';
		}
		$urlValue = $this->input->get('url');		//passed as a parameter
		$urlValue = rawurldecode($urlValue);
		$doiValue = $this->input->get('doi');		//passed as a parameter
		$doiValue = rawurldecode($doiValue);
		$response_type = $this->input->get('response_type');	//passed as a parameter
		if(!$response_type) $response_type = 'string';
		$api_version = $this->input->get('api_version');
		if(!$api_version)	$api_version = '1.0';
		if(!$app_id)
		{
			$errorMessages = doisGetUserMessage("MT010", $doiValue ,$response_type,$app_id, "You must provide an app id to update a doi",$urlValue);
            $log_response = doisGetUserMessage("MT010", $doiValue ,$log_type,$app_id, "You must provide an app id to update a doi",$urlValue);
		}
		if(!$doiValue)
		{
			$errorMessages = doisGetUserMessage("MT010", $doiValue ,$response_type,$app_id, "You must provide the doi value to update a doi",$urlValue);
            $log_response = doisGetUserMessage("MT010", $doiValue ,$log_type,$app_id, "You must provide the doi value to update a doi",$urlValue);
		}
		if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    {
			$ip=$_SERVER["HTTP_X_FORWARDED_FOR"];
		} else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )    {
			$ip=$_SERVER["HTTP_CLIENT_IP"];
		} else if ( isset($_SERVER["REMOTE_ADDR"]) )    {
			$ip=$_SERVER["REMOTE_ADDR"];
		} else {
			// Run by command line??
			$ip="127.0.0.1";
		}
		if($errorMessages == '')
		{
			if(!$manual_update) $client_id = checkDoisValidClient($ip,$app_id);
            $client_domains = getClientDomains($client_id);

			if($client_id===false)
			{
				$errorMessages = doisGetUserMessage("MT009", $doiValue,$response_type,$app_id, $verbosemessage,$urlValue);
                $log_response = doisGetUserMessage("MT009", $doiValue,$log_type,$app_id, $verbosemessage,$urlValue);
			}else{
				if(checkDoisClientDoi($doiValue,$client_id)===false)
				{
					$errorMessages = doisGetUserMessage("MT008", $doiValue,$response_type,$app_id, $verbosemessage,$urlValue);
                    $log_response = doisGetUserMessage("MT008", $doiValue,$log_type,$app_id, $verbosemessage,$urlValue);
				}
			}
		}
		if($errorMessages == '')
		{
		$doidata = getxml($doiValue);		 			// check if doi is a valid doi and get information about it


		if($doidata->num_rows() > 0){

			$xml = $this->getXmlInput();

			//first up, lets check that this client is permitted to update this doi.
            $xml_row = $doidata->row();
            $old_xml = $xml_row->datacite_xml;

            if(trim($xml)===trim($old_xml)) $xml='';

			if(isset($xml) && $xml) // if the client has posted xml to be updated
			{
				$doiObjects = new DOMDocument();

				$result = $doiObjects->loadXML($xml);

				if( !$result)
				{
                    $errors = error_get_last();
                    $errorMessages = "Document Load Error: ".$errors['message']."\n";

				}
				else
				{
					// Validate it against the datacite schema.
					error_reporting(0);
					// Create temporary file and save manually created DOMDocument.
					$tempFile = "/tmp/" . time() . '-' . rand() . '-document.tmp';
					$doiObjects->save($tempFile);
					// Create temporary DOMDocument and re-load content from file.
					$doiObjects = new DOMDocument();
					$doiObjects->load($tempFile);
					 //Delete temporary file.
					if (is_file($tempFile))
					{
						unlink($tempFile);
					}
                    $resources = $doiObjects->getElementsByTagName('resource');
                    $theSchema = 'unknown';
                    if($resources->length>0)
                    {
                        if(isset($resources->item(0)->attributes->item(0)->name))
                        {
                            $theSchema = $this->getXmlSchema($resources->item(0)->attributes->item(0)->nodeValue);
                        }
                    }

                    libxml_use_internal_errors(true);
                    $error_string = '';

                    $result = $doiObjects->schemaValidate(asset_url('schema').$dataciteSchema[$theSchema]);

                    if ($result !== TRUE)
                    {
                        $errors = libxml_get_errors();
                        foreach ($errors as $error) {
                            $error_string .= TAB . "Line " .$error->line . ": " . $error->message;
                        }
                        libxml_clear_errors();
                    }

					$xml = $doiObjects->saveXML();

					$errors = error_get_last();
					if( $errors || !$result)
					{
						$verbosemessage = "Document Validation Error: ".$error_string."\n";
						$errorMessages = doisGetUserMessage("MT007", $doiValue,$response_type,$app_id, $verbosemessage,$urlValue);
                        $log_response = doisGetUserMessage("MT007", $doiValue,$log_type,$app_id, $verbosemessage,$urlValue);
					}
				}
			}

            if(!$this->validDomain($urlValue,$client_domains)){
                $verbosemessage = 'URL not permitted.';
                $errorMessages = doisGetUserMessage("MT014", $doiValue, $response_type,$app_id, $verbosemessage,$urlValue);
                $log_response = doisGetUserMessage("MT014", $doiValue, $log_type,$app_id, $verbosemessage,$urlValue);
            }

			if( $errorMessages == '' )
			{
				// Update doi information
				$updateError = updateDoiObject($doiValue,$doiObjects,$urlValue,$xml);

				if(!$updateError){

					/* Fix: 09/01/2013, DataCite requires metadata FIRST, then DOI call */
					// Send DataCite the metadata first
					// Update the DOI
					if($doiObjects && $xml)
					{
						$response2 = $this->doisRequest("update", $doiValue, $urlValue, $xml, $client_id);
					}

					if($urlValue)
					{
						$response1 = $this->doisRequest("mint", $doiValue, $urlValue, $xml, $client_id);
					}

					if( $response1 && $response2 )
					{

						if( doisGetResponseType($response1) == gDOIS_RESPONSE_SUCCESS && doisGetResponseType($response2) == gDOIS_RESPONSE_SUCCESS)
						{
							// We have successfully updated the doi through datacite.
							$verbosemessage = $response1." / ".$response2;
							$notifyMessage = doisGetUserMessage("MT002", $doiValue,$response_type,$app_id, $verbosemessage,$urlValue);
                            $log_response = doisGetUserMessage("MT002", $doiValue,$log_type,$app_id, $verbosemessage,$urlValue);
						}
						else
						{
							$verbosemessage = $response1." / ".$response2;
							$errorMessages = doisGetUserMessage("MT010", $doiValue,$response_type,$app_id, $verbosemessage,$urlValue);
                            $log_response = doisGetUserMessage("MT010", $doiValue,$log_type,$app_id, $verbosemessage,$urlValue);
						}
					}
					else
					{
						$verbosemessage = '';
						if($response1!=gDOIS_RESPONSE_SUCCESS) $verbosemessage .= $response1;
						if($response2!=gDOIS_RESPONSE_SUCCESS) $verbosemessage .= $response2;
						$errorMessages = doisGetUserMessage("MT005",  $doiValue,$response_type,$app_id, $verbosemessage,$urlValue);
                        $log_response = doisGetUserMessage("MT005",  $doiValue,$log_type,$app_id, $verbosemessage,$urlValue);
					}
				}else{
					$verbosemessage = $updateError;
					$errorMessages = doisGetUserMessage("MT010",  $doiValue,$response_type,$app_id, $verbosemessage,$urlValue);
                    $log_response = doisGetUserMessage("MT010",  $doiValue,$log_type,$app_id, $verbosemessage,$urlValue);
				}
			}
		}else{
			$errorMessages = doisGetUserMessage("MT011", $doi_id=$doiValue,$response_type,$app_id, $verbosemessage,$urlValue);
            $log_response = doisGetUserMessage("MT011", $doi_id=$doiValue,$log_type,$app_id, $verbosemessage,$urlValue);
		}
		}
        if($manual_update){
             $update = "M_UPDATE";
        }else{
             $update = "UPDATE";
        }

		if($errorMessages)
		{
			//We need to log this activity as errorred
			insertDoiActivity($update,$doiValue,"FAILURE",$client_id,$errorMessages);
            $this->doilog($log_response,"doi_".$update,$client_id);
		}

		if($notifyMessage)
		{
			//We need to log this activity
			insertDoiActivity($update,$doiValue,"SUCCESS",$client_id,$notifyMessage);
            $this->doilog($log_response,"doi_".$update,$client_id);
		}

		echo $errorMessages;
		echo $notifyMessage;
	}

	function mint(){


		$dataciteSchema = $this->config->item('gCMD_SCHEMA_URIS');

		if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    {
			$ip=$_SERVER["HTTP_X_FORWARDED_FOR"];
		} else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )    {
			$ip=$_SERVER["HTTP_CLIENT_IP"];
		} else if ( isset($_SERVER["REMOTE_ADDR"]) )    {
			$ip=$_SERVER["REMOTE_ADDR"];
		} else {
			// Run by command line??
			$ip="127.0.0.1";
		}

		$xml ='';
		$client_id = 0;
		$errorMessages = '';
		$notifyMessage = '';
		$log_type = 'array';
		$outstr = '';
		$doiValue = '';
		$verbosemessage = '';
		$errors = '';
		$testing = 'no';
		$doiObjects = null;
		$response1 = "OK";
		$response2 = "OK";
		$debug = $this->input->get('debug');

        $manual_mint = $this->input->get('manual_mint');
        if($manual_mint)
        {
            $doiValue = rawurldecode($this->input->get_post('doi_id'));
            $client_id = rawurldecode($this->input->get_post('client_id'));
        }


        if($debug && $debug == 'true')
		{
			$this->debugOn();
		}

		$app_id = $this->getAppId();
		if(substr($app_id,0,4)=='TEST')
		{
			$app_id = substr($app_id,4,strlen($app_id));
			$testing = 'yes';
		}
		$urlValue = $this->input->get('url');		//passed as a parameter
		$urlValue = rawurldecode($urlValue);
		$response_type = $this->input->get('response_type');
		if(!$response_type) $response_type = 'string';
		$api_version = $this->input->get('api_version');
		if(!$api_version)	$api_version = '1.0';
					//first up, lets check that this client is permitted to update this doi.
		if(!$app_id)
		{
			$errorMessages = doisGetUserMessage("MT010", $doiValue ,$response_type,$app_id, "You must provide an app id to mint a doi",$urlValue);
            $log_response = doisGetUserMessage("MT010", $doiValue ,$log_type,$app_id, "You must provide an app id to mint a doi",$urlValue);

		}
		if($urlValue=='' && $errorMessages == '')
		{
			$verbosemessage = 'You must provide a url when minting a doi.';
			$errorMessages = doisGetUserMessage("MT010", $doiValue, $response_type,$app_id, $verbosemessage,$urlValue);//"URL is a mandatory value to mint a doi.<br />";
            $log_response = doisGetUserMessage("MT010", $doiValue, $log_type,$app_id, $verbosemessage,$urlValue);
        }
		if($errorMessages == '')
		{
			if(!$manual_mint) $client_id = checkDoisValidClient($ip,trim($app_id));
			if($client_id===false)
			{
				$verbosemessage = 'Client with app_id '.$app_id.' from ip address '.$ip. ' is not a registered doi client.';
				$errorMessages = doisGetUserMessage("MT009",  $doiValue, $response_type,$app_id, $verbosemessage,$urlValue);
                $log_response = doisGetUserMessage("MT009",  $doiValue, $log_type,$app_id, $verbosemessage,$urlValue);
			}

			$xml = $this->getXmlInput();

			if(!$xml){
				$xml = '';
				$verbosemessage = 'You must post xml when minting a doi.';
				$errorMessages = doisGetUserMessage("MT010",  $doiValue, $response_type,$app_id, $verbosemessage,$urlValue);
                $log_response = doisGetUserMessage("MT010",  $doiValue, $log_type,$app_id, $verbosemessage,$urlValue);
			}
            $doiObjects = new DOMDocument();

            $result = $doiObjects->loadXML($xml);

            if(!$result)
            {
                $errors = error_get_last();
                $errorMessages = "Document Load Error: ".$errors['message']."\n";

            }
		}
		if(!$errorMessages)
		{
			$clientDetails = getDoisClient($app_id);

			foreach($clientDetails->result() as $clientDetail)
			{

                if($clientDetail->client_id<'10')
				{
					$client_id2 = "0".$clientDetail->client_id;
				}else{
					$client_id2 = $clientDetail->client_id;
				}

                $client_domains = getClientDomains($clientDetail->client_id);

			}
			if($testing=='yes')
			{
				$datacite_prefix = "10.5072/";
			}else{
				$datacite_prefix = $clientDetail->datacite_prefix;
			}

            if(!$manual_mint) $doiValue = strtoupper($datacite_prefix.$client_id2.'/'.uniqid());	//generate a unique suffix for this doi for this client
			$resources = $doiObjects->getElementsByTagName('resource');
			$theSchema = 'unknown';
			if($resources->length>0)
			{
				if(isset($resources->item(0)->attributes->item(0)->name))
				{
					$theSchema = $this->getXmlSchema($resources->item(0)->attributes->item(0)->nodeValue);
				}
			}
			if($theSchema=="unknown")
			{
				$errors['message'] = "You have not provided a known schema location in your xml";
			}

			if($errors)
			{

				$verbosemessage = "Document Load Error: ".$errors['message'];
				$errorMessages .= doisGetUserMessage("MT010", $doiValue, $response_type, $app_id, $verbosemessage,$urlValue);
                $log_response  = doisGetUserMessage("MT010", $doiValue, $log_type, $app_id, $verbosemessage,$urlValue);
			}
			else{
				$errors = error_get_last();

				// we need to insert the determined doi value into the xml string to be sent to datacite
				// so we create a new 'identifier' element, set the identifierType attribute to DOI and
				// replace the current identifier element then  write out to the xml string that is passed
				$currentIdentifier=$doiObjects->getElementsByTagName('identifier');
				for($i=0;$i<$currentIdentifier->length;$i++){
					$doiObjects->getElementsByTagName('resource')->item(0)->removeChild($currentIdentifier->item($i));
				}
				$newdoi = $doiObjects->createElement('identifier',$doiValue);
				$newdoi->setAttribute('identifierType',"DOI");
				$doiObjects->getElementsByTagName('resource')->item(0)->insertBefore($newdoi,$doiObjects->getElementsByTagName('resource')->item(0)->firstChild);

			}

			if( $errors )
			{

				$verbosemessage = "Document Load Error: ".$errors['message'];
				$errorMessages  = doisGetUserMessage("MT010", $doiValue, $response_type, $app_id, $verbosemessage,$urlValue);
                $log_response  = doisGetUserMessage("MT010", $doiValue, $log_type, $app_id, $verbosemessage,$urlValue);
			}else{
				// Validate it against the datacite schema.
				error_reporting(0);

				// Create temporary file and save manually created DOMDocument.
				$tempFile = "/tmp/" . time() . '-' . rand() . '-document.tmp';

				$doiObjects->save($tempFile);

				// Create temporary DOMDocument and re-load content from file.
				$doiObjects = new DOMDocument();
				$doiObjects->load($tempFile);

				//Delete temporary file.
				if (is_file($tempFile))
				{
					unlink($tempFile);
				}

                libxml_use_internal_errors(true);
                $error_string = '';

                $result = $doiObjects->schemaValidate(asset_url('schema').$dataciteSchema[$theSchema]);

                if ($result !== TRUE)
                {
                    $errors = libxml_get_errors();
                    foreach ($errors as $error) {
                        $error_string .= TAB . "Line " .$error->line . ": " . $error->message;
                    }
                    libxml_clear_errors();
                }

				$xml = $doiObjects->saveXML();

				$errors = error_get_last();

				if( $errors || !$result)
				{
					$verbosemessage = "Document Validation Error: ".$error_string;
					$errorMessages = doisGetUserMessage("MT006",  $doiValue, $response_type, $app_id, $verbosemessage,$urlValue);
                    $log_response = doisGetUserMessage("MT006",  $doiValue, $log_type, $app_id, $verbosemessage,$urlValue);
				}

			}
            if(!$errors){
                //ensure provided url is valid with registered top level domain

                if(!$this->validDomain($urlValue,$client_domains)){
                    $verbosemessage = 'URL not permitted.';
                    $errorMessages = doisGetUserMessage("MT014", $doiValue, $response_type,$app_id, $verbosemessage,$urlValue);
                    $log_response = doisGetUserMessage("MT014", $doiValue, $log_type,$app_id, $verbosemessage,$urlValue);
                }

            }


		}

		if( $errorMessages == '' )
		{
			// Insert doi information into the database
			$insertResult = importDoiObject($doiObjects,$urlValue, $client_id, $created_who='SYSTEM', $status='REQUESTED',$xml);

			if(!$insertResult){
				/* Fix: 09/01/2013, DataCite requires metadata FIRST, then DOI call */
				// Send DataCite the metadata first
				$response = $this->doisRequest("update",$doiValue, $urlValue, $xml, $client_id);

				if( $response )
				{
					if( doisGetResponseType($response) == gDOIS_RESPONSE_SUCCESS )
					{
						// Now ask to mint the DOI
						$response = $this->doisRequest("mint",$doiValue, $urlValue, $xml, $client_id);

						if(doisGetResponseType($response) == gDOIS_RESPONSE_SUCCESS )
						{
							$notifyMessage = doisGetUserMessage("MT001", $doiValue, $response_type, $app_id,$response,$urlValue);
                            $log_response = doisGetUserMessage("MT001", $doiValue, $log_type, $app_id,$response,$urlValue);
							$status = "ACTIVE";
							$activateResult = setDoiStatus($doiValue,$status);
						}else{
							$errorMessages .=  doisGetUserMessage("MT010", $doiValue, $response_type, $app_id,$response,$urlValue);
                            $log_response =  doisGetUserMessage("MT010", $doiValue, $log_type, $app_id,$response,$urlValue);
						}
					}else{
						$errorMessages .=  doisGetUserMessage("MT010", $doiValue, $response_type, $app_id,$response,$urlValue);
                        $log_response =  doisGetUserMessage("MT010", $doiValue, $log_type, $app_id,$response,$urlValue);
					}
				}else{
					$errorMessages .=  doisGetUserMessage("MT005", $doiValue, $response_type, $app_id,$response,$urlValue);
                    $log_response =  doisGetUserMessage("MT005", $doiValue, $log_type, $app_id,$response,$urlValue);
				}
			}else{
				$errorMessages .= '..<br />'.$insertResult;

			}
		}
        if($manual_mint){
            $mint = "M_MINT";
        }else{
            $mint = "MINT";
        }

		if($errorMessages)
		{
			$outstr =  $errorMessages;

			//We need to log this activity as errorred

			insertDoiActivity($mint,$doiValue,"FAILURE",$client_id,$errorMessages);
            $this->doilog($log_response,"doi_".$mint,$client_id);
		}

		if($notifyMessage)
		{
			//We need to log this activity
			insertDoiActivity($mint,$doiValue,"SUCCESS",$client_id,$notifyMessage);
            $this->doilog($log_response,"doi_".$mint,$client_id);
			$outstr = $notifyMessage;
		}

		//we now need to return the result back to the calling program.
		echo $outstr;

	}

	function activate(){

		$errorMessages = '';
		$notifyMessage = '';
		$outstr = '';
		$urlValue = '';
		$verbosemessage = '';
		$client_id ='';
        $response = '';
        $log_type='array';
		if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    {
			$ip=$_SERVER["HTTP_X_FORWARDED_FOR"];
		} else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )    {
			$ip=$_SERVER["HTTP_CLIENT_IP"];
		} else if ( isset($_SERVER["REMOTE_ADDR"]) )    {
			$ip=$_SERVER["REMOTE_ADDR"];
		} else {
			// Run by command line??
			$ip="127.0.0.1";
		}
		$debug = $this->input->get('debug');

		if($debug && $debug == 'true')
		{
			$this->debugOn();
		}
        $manual_activate = $this->input->get('manual_activate');
        if($manual_activate)
        {
            $client_id = rawurldecode($this->input->get_post('client_id'));
        }
		//$app_id = $this->input->get('app_id');		//passed as a parameter
		$app_id = $this->getAppId();
		if(substr($app_id,0,4)=='TEST')
		{
			$app_id = substr($app_id,4,strlen($app_id));
			$testing = 'yes';
		}
		$doiValue = $this->input->get('doi');		//passed as a parameter
		$doiValue = rawurldecode($doiValue);
		$response_type = $this->input->get('response_type');		//passed as a parameter
		if(!$response_type) $response_type = 'string';
		$api_version = $this->input->get('api_version');
		if(!$api_version)	$api_version = '1.0';
		if(!$app_id)
		{
			$errorMessages = doisGetUserMessage("MT010", $doiValue ,$response_type,$app_id, "You must provide an app id to update a doi",$urlValue);
            $log_response = doisGetUserMessage("MT010", $doiValue ,$log_type,$app_id, "You must provide an app id to update a doi",$urlValue);
		}
		if(!$doiValue)
		{
			$errorMessages = doisGetUserMessage("MT010", $doiValue ,$response_type,$app_id, "You must provide the doi value to update a doi",$urlValue);
            $log_response = doisGetUserMessage("MT010", $doiValue ,$log_type,$app_id, "You must provide the doi value to update a doi",$urlValue);
		}
		//first up, lets check that this client is permitted to update this doi.
		if($errorMessages =='')
		{
        if(!$manual_activate) $client_id = checkDoisValidClient($ip,$app_id);
		//$client_id = checkDoisValidClient($ip,$app_id);
		if($client_id===false)
		{
			$verbosemessage = '';
			$errorMessages = doisGetUserMessage("MT009", $doiValue, $response_type,$app_id, $verbosemessage,$urlValue);
            $log_response = doisGetUserMessage("MT009", $doiValue, $log_type,$app_id, $verbosemessage,$urlValue);

		}else{
			if(checkDoisClientDoi($doiValue,$client_id)===false)
			{
				$verbosemessage = '';
				$errorMessages = doisGetUserMessage("MT008", $doiValue, $response_type,$app_id, $verbosemessage,$urlValue);
                $log_response = doisGetUserMessage("MT008", $doiValue, $log_type,$app_id, $verbosemessage,$urlValue);
			}
		}
		}
		if($errorMessages == '')
		{



		if(getDoiStatus($doiValue)!="INACTIVE")
		{
			$verbosemessage = "DOI ".$doiValue." is not set to inactive so cannot activate it.<br />";
			$errorMessages = doisGetUserMessage("MT010", $doiValue ,$response_type,$app_id, $verbosemessage,$urlValue);
            $log_response = doisGetUserMessage("MT010", $doiValue ,$log_type,$app_id, $verbosemessage,$urlValue);
		}

		if( $errorMessages == '' )
		{
			// Update doi information
			$status = "ACTIVE";
			$activateResult = setDoiStatus($doiValue,$status);

			$doidata = getxml($doiValue);
			if($doidata->num_rows() > 0){
				foreach($doidata->result() as $row)
				{
						$xml = $row->datacite_xml;
				}
			}
			if(!$activateResult){
			// Activate the DOI.

				$response = $this->doisRequest("update",$doiValue,$urlValue = NULL ,$xml, $client_id );

				if($response)
				{
					if( doisGetResponseType($response) == gDOIS_RESPONSE_SUCCESS || $response == gDOIS_RESPONSE_SUCCESS)
					{
						// We have successfully activated the doi through datacite.
						$notifyMessage = doisGetUserMessage("MT004", $doiValue,$response_type,$app_id, $response,$urlValue);
                        $log_response = doisGetUserMessage("MT004", $doiValue,$log_type,$app_id, $response,$urlValue);


					}
					else
					{
						$activateResult = setDoiStatus($doiValue,'INACTIVE');
						$errorMessages = doisGetUserMessage("MT010", $doiValue,$response_type,$app_id, $response,$urlValue);
                        $log_response = doisGetUserMessage("MT010", $doiValue,$log_type,$app_id, $response,$urlValue);

					}
				}
				else
				{
					$errorMessages = doisGetUserMessage("MT005",$doiValue,$response_type,$app_id, $verbosemessage,$urlValue);
                    $log_response = doisGetUserMessage("MT005",$doiValue,$log_type,$app_id, $verbosemessage,$urlValue);

				}
			}else{

				$verbosemessage = $response;
				$errorMessages = doisGetUserMessage("MT010",$doiValue,$response_type,$app_id, $verbosemessage,$urlValue);
                $log_response = doisGetUserMessage("MT010",$doiValue,$log_type,$app_id, $verbosemessage,$urlValue);
			}
		}
		}

        if($manual_activate){
            $activate = "M_ACTIVATE";
        }else{
            $activate = "ACTIVATE";
        }
		if($errorMessages)
		{

			$outstr =  $errorMessages;
			//We need to log this activity as errorred

			insertDoiActivity($activate,$doiValue,"FAILURE",$client_id,$errorMessages);

            $this->doilog($log_response,"doi_".$activate,$client_id);

		}

		if($notifyMessage)
		{
			//We need to log this activity as successful
			insertDoiActivity($activate,$doiValue,"SUCCESS",$client_id,$notifyMessage);

            $this->doilog($log_response,"doi_".$activate,$client_id);

			$outstr = $notifyMessage;
		}

		//we now need to return the result back to the calling program.
		echo $outstr;
	}

	function deactivate(){
	//	global $api_version;
		$errorMessages = '';
		$notifyMessage = '';
		$outstr = '';
		$urlValue = '';
		$verbosemessage = '';
		$client_id ='';
        $log_type = 'array';

		if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    {
			$ip=$_SERVER["HTTP_X_FORWARDED_FOR"];
		} else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )    {
			$ip=$_SERVER["HTTP_CLIENT_IP"];
		} else if ( isset($_SERVER["REMOTE_ADDR"]) )    {
			$ip=$_SERVER["REMOTE_ADDR"];
		} else {
			// Run by command line??
			$ip="127.0.0.1";
		}
		$debug = $this->input->get('debug');

		if($debug && $debug == 'true')
		{
			$this->debugOn();
		}
		//$app_id = $this->input->get('app_id');		//passed as a parameter
        $manual_deactivate = $this->input->get('manual_deactivate');
        if($manual_deactivate)
        {
            $client_id = rawurldecode($this->input->get_post('client_id'));
        }
		$app_id = $this->getAppId();
		if(substr($app_id,0,4)=='TEST')
		{
			$app_id = substr($app_id,4,strlen($app_id));
			$testing = 'yes';
		}
		$doiValue = $this->input->get('doi');		//passed as a parameter
		$doiValue = rawurldecode($doiValue);
		$response_type = $this->input->get('response_type');		//passed as a parameter
		if(!$response_type) $response_type = 'string';
		$api_version = $this->input->get('api_version');
		if(!$api_version)	$api_version = '1.0';
		if(!$app_id)
		{
			$errorMessages = doisGetUserMessage("MT010", $doiValue ,$response_type,$app_id, "You must provide an app id to update a doi",$urlValue);
            $log_response = doisGetUserMessage("MT010", $doiValue ,$log_type,$app_id, "You must provide an app id to update a doi",$urlValue);
		}
		if(!$doiValue)
		{
			$errorMessages = doisGetUserMessage("MT010", $doiValue ,$response_type,$app_id, "You must provide the doi value to update a doi",$urlValue);
            $log_response = doisGetUserMessage("MT010", $doiValue ,$log_type,$app_id, "You must provide the doi value to update a doi",$urlValue);
		}
		//first up, lets check that this client is permitted to update this doi.
		if($errorMessages =='')
		{
		if(!$manual_deactivate) $client_id = checkDoisValidClient($ip,$app_id);
		if($client_id===false)
		{
			$verbosemessage = '';
			$errorMessages = doisGetUserMessage("MT009", $doiValue, $response_type,$app_id, $verbosemessage,$urlValue);
            $log_response = doisGetUserMessage("MT009", $doiValue, $log_type,$app_id, $verbosemessage,$urlValue);

		}else{
			if(checkDoisClientDoi($doiValue,$client_id)===false)
			{
				$verbosemessage = '';
				$errorMessages = doisGetUserMessage("MT008", $doiValue, $response_type,$app_id, $verbosemessage,$urlValue);
                $log_response = doisGetUserMessage("MT008", $doiValue, $log_type,$app_id, $verbosemessage,$urlValue);
			}
		}
		}
		if($errorMessages == '')
		{


		if(getDoiStatus($doiValue)!="ACTIVE")
		{
				$verbosemessage = "DOI ".$doiValue." is not set to active so cannot deactivate it.";
				$errorMessages = doisGetUserMessage("MT010", $doiValue, $response_type,$app_id, $verbosemessage,$urlValue);
                $log_response = doisGetUserMessage("MT010", $doiValue, $log_type,$app_id, $verbosemessage,$urlValue);
		}

		if( $errorMessages == '' )
		{
			// Update doi information
			$status = "INACTIVE";
			$inactivateResult = setDoiStatus($doiValue,$status);
			if(!$inactivateResult){
				// deactivate the DOI.
				$response = $this->doisRequest("delete",$doiValue,$urlValue = NULL ,$xml = NULL,$client_id );

				if($response)
				{
					if( $response == gDOIS_RESPONSE_SUCCESS )
					{
						// We have successfully deactivated the doi through datacite.
						$notifyMessage .= doisGetUserMessage("MT003", $doiValue, $response_type,$app_id, $response,$urlValue);
                        $log_response = doisGetUserMessage("MT003", $doiValue, $log_type,$app_id, $response,$urlValue);
					}
					else
					{
						$errorMessages .= doisGetUserMessage("MT010", $doiValue, $response_type,$app_id, $response,$urlValue);
                        $log_response = doisGetUserMessage("MT010", $doiValue, $log_type,$app_id, $response,$urlValue);;
					}
				}
				else
				{
					$errorMessages .= doisGetUserMessage("MT005", $doiValue, $response_type,$app_id, $verbosemessage,$urlValue);
                    $log_response = doisGetUserMessage("MT005", $doiValue, $log_type,$app_id, $verbosemessage,$urlValue);
				}
			}else{

				$verbosemessage = $inactivateResult;
				$errorMessages = doisGetUserMessage("MT010", $doiValue, $response_type,$app_id, $verbosemessage,$urlValue);
                $log_response = doisGetUserMessage("MT010", $doiValue, $log_type,$app_id, $verbosemessage,$urlValue);
			}
		}
		}

        if($manual_deactivate){
            $deactivate = "M_INACTIVATE";
        }else{
            $deactivate = "INACTIVATE";
        }

		if($errorMessages)
		{

			$outstr =  $errorMessages;
			//We need to log this activity as errorred

			insertDoiActivity($deactivate,$doiValue,"FAILURE",$client_id,$errorMessages);

            $this->doilog($log_response,"doi_".$deactivate,$client_id);

		}

		if($notifyMessage)
		{
			//We need to log this activity
			insertDoiActivity($deactivate,$doiValue,"SUCCESS",$client_id,$notifyMessage);

            $this->doilog($log_response,"doi_".$deactivate, $client_id);

			$outstr = $notifyMessage;
		}
		//we now need to return the result back to the calling program.
		echo $outstr;

	}


	/**
	 * Check the availability of the DOI service and upstream DataCite service
	 * Note: this is an indication of service availability, not a guarantee
	 *       that subsequent mints will be processed successfully.
	 *
	 * @author Ben Greenwood <ben.greenwood@anu.edu.au>
	 */
	function status()
	{
		// XXX: These parameter getters should be in a constructor somewhere
		$response_type = $this->input->get('response_type') ?: "string";
		$api_version = $this->input->get('api_version') ?: "1.0";

		$response_status = true;
		$timer_start = microtime(true);

        $status = "CHECK_STATUS";

		// Ping the local DOI database
		if (!$this->doi_db->simple_query("SELECT 1;"))
		{
			$response_status = false;
		}

		// Ping DataCite DOI HTTPS service
		if (!$this->_isDataCiteAlive(self::DATACITE_WAIT_TIMEOUT_SECONDS))
		{
			$response_status = false;
		}

		// Send back our response
		if ($response_status)
		{
			$response_time = round(microtime(true) - $timer_start, 3) * 1000;
			echo doisGetUserMessage("MT090", NULL, $response_type, NULL, "(took " . $response_time . "ms)");
            $log_response = doisGetUserMessage("MT090", NULL, 'array', NULL, "(took " . $response_time . "ms)");
            $this->doilog($log_response,"doi_".$status);
			return;
		}
		else
		{
			echo doisGetUserMessage("MT091", NULL, $response_type);
            $log_response = doisGetUserMessage("MT091", NULL, 'array');
            $this->doilog($log_response,"doi_".$status);
			return;
		}
	}

	/* Ping the DataCite HTTP API, timeout after ~5 seconds (or other if specified) */
	private function _isDataCiteAlive($timeout = 5)
	{
		$curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, gDOIS_SERVICE_BASE_URI);
        curl_setopt($curl, CURLOPT_FILETIME, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
		curl_exec($curl);

		return !(curl_errno($curl) || curl_getinfo($curl, CURLINFO_HTTP_CODE) != "200");
	}




	function checkurl(){
		$unavailableCount = 0;
		$message = '';
		$subject = "Cite My Data DOI url availability check";
		$recipient = "services@ands.org.au";
		$recipient = "lizwoods.ands@gmail.com";
		$notifyMessage = '';
		$lastupdate = '';
		$doiList = getDoiList();

		if($doiList)
		{
			foreach($doiList->result() as $doi)
			{
				//we want to check if the url is available
				if(!doisDomainAvailible($doi->url))
				{
					$lastupdate = $doi->updated_when;
					if(!$lastupdate) $lastupdate = $doi->created_when;
					$clientDetails = getDoisClientDetails($doi->client_id);
					$clientName = $clientDetails->result();
					$notifyMessage .= $doi->doi_id." ".$doi->url." ".$clientName[0]->client_name." ".$lastupdate."\n";
					$unavailableCount++;
				}
			}
		}


		$message .= "There are ".$unavailableCount." doi urls unavailable on ".date("d/m/Y h:m:s")."\n";
		$message .= $notifyMessage;
		mail($recipient,$subject,$message);
	}




	function doisRequest($service, $doi, $url, $metadata,$client_id)
	{

		$resultXML = '';

		$mode='';

		if($client_id<10) $client_id = '-'.$client_id;

		$authstr = gDOIS_DATACENTRE_NAME_PREFIX.".".gDOIS_DATACENTRE_NAME_MIDDLE.$client_id.":".gDOIS_DATACITE_PASSWORD;
		$requestURI = gDOIS_SERVICE_BASE_URI;

		$ch = curl_init();

		if($service=="mint")
		{
			$context  = array('Content-Type:text/plain;charset=UTF-8','Authorization: Basic '.base64_encode($authstr));
			$metadata="url=".$url."\ndoi=".$doi;
			$requestURI = gDOIS_SERVICE_BASE_URI."doi".$mode;
			curl_setopt($ch, CURLOPT_POST,1);
		}
		elseif($service=="update")
		{
			$context  = array('Content-Type:application/xml;charset=UTF-8','Authorization: Basic '.base64_encode($authstr));
			$requestURI = gDOIS_SERVICE_BASE_URI."metadata".$mode;
			curl_setopt($ch, CURLOPT_POST,1);
		}
		elseif($service=="delete")
		{
			$context  = array('Content-Type:text/plain;charset=UTF-8','Authorization: Basic '.base64_encode($authstr));
			$requestURI = gDOIS_SERVICE_BASE_URI."metadata/".$doi;
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		}

		curl_setopt($ch, CURLOPT_URL, $requestURI);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER,$context);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$metadata);
		$result = curl_exec($ch);

		$curlinfo = curl_getinfo($ch);

		curl_close($ch);

		if($result)
		{
			$resultXML = $result;
		}
		return $resultXML;
	}

    function getXmlSchema($theSchemaLocation)
    {
    	if(str_replace("kernel-2.0","",$theSchemaLocation)!=$theSchemaLocation)
    	{
    		return "2.0";
    	}
    	elseif(str_replace("kernel-2.1","",$theSchemaLocation)!=$theSchemaLocation)
    	{
    		return "2.1";
    	}
    	elseif(str_replace("kernel-2.2","",$theSchemaLocation)!=$theSchemaLocation)
    	{
    		return "2.2";
    	}
    	elseif(str_replace("kernel-3","",$theSchemaLocation)!=$theSchemaLocation)
    	{
    		return "3";
    	}
    	else
    	{
    		return "unknown";
    	}
    }

	function debugOn()
	{

		ini_set('display_errors',1);
 		error_reporting(E_ALL);
		$theGets = $this->input->get();
		if($theGets)
		{
			echo "Get parameters passed:<br />";

			foreach($theGets as $name=>$value)
			{
				print $name. "=".$value."<br />";
			}
		}
		$thePosts = $this->input->post();
		if($thePosts)
		{
			echo "Post parameters passed:<br />";

			foreach($thePosts as $name=>$value)
			{
				print $name. "=".$value."<br />";
			}
		}
	}

	function getAppId(){
		$app_id= '';

		$app_id = $this->input->get('app_id');		//passed as a parameter
		//or app_id might be passed as part of the authstr

		if(!$app_id && isset($_SERVER['PHP_AUTH_USER']))
		{
   			$app_id = $_SERVER['PHP_AUTH_USER'];
		}
		return $app_id;
	}

	function getXmlInput(){

        $output= '';
        $data = file_get_contents("php://input");

        parse_str(htmlentities($data), $output);
        if(isset($output['xml']) )
        {
          return trim($output['xml']);

        }elseif(count($output)>1)
        {
          //hotfix to return XML that is not empty,
          //implode($output) returns empty for no reason
          //todo verify and check
          return trim($data);
          //return trim(implode($output));

        }else{
            return trim($data);
        }

    }

    function validDomain($urlValue,$client_domains){
        //check that the host component of a given url belings to one of the clients registered domains
        $theDomain = parse_url($urlValue);
        foreach($client_domains as $domain){
            $check =  strpos($theDomain['host'], $domain->client_domain);
            if($check||$check===0){
                return true;
            }
        }
        return false;
    }

    function doilog($log_response,$event="doi_xml",$client_id=NULL){

        $message = array();
        $message["event"] = strtolower($event);
        $message["response"]= $log_response;
        $message["doi"]["id"] = $log_response["doi"];
        $message["client"]["id"] = $client_id;
        if(strtolower(substr($event,0,6))=='doi_m_'){
            $message['request']['manual']= true;
            $message["event"] = str_replace("_m_","_", $message["event"]);
        }else{
            $message['request']['manual']= false;
        }

      //  $doidata = getxml($log_response["doi"]);
      //  if($doidata->num_rows() > 0){
      //      foreach($doidata->result() as $row)
       //     {
       //         $message["doi"]["id"]= $row->doi_id;
       //         $message["doi"]["client"] = $row->client_id;
        //    }
       // }
        $test_check = strpos($log_response["doi"],'10.5072');
        if($test_check||$test_check===0) {
            $message["doi"]["production"] = false;
        }else{
            $message["doi"]["production"] = true;
        }

        $message["api_key"] = $log_response["app_id"];

        monolog($message,"doi_api", "info", true) ;

    }


}
