<?php 
class Doi extends CI_Controller {
	
	public function index(){
		echo 'eh';
	}
	
	public function putxml(){
		echo "in here<br />";
		exit;
		$doiList = getDoiListxml();
		
		foreach($doiList->result() as $doi)
		{
			$xml = file_get_contents("http://".eHOST.eROOT_DIR."/xml?doi=".$doi->doi_id);
			$data = array('datacite_xml' => $xml);
			$where = "doi_id = '".$doi->doi_id."'";
    		$query_str = $this->db->update_string('doi_objects', $data, $where);
    		$result = $this->db->query($query_str);			

		}
	}
	
	public function xml(){		
		$xml = '';	
		$doi_id = $this->input->get('doi');		
		$doidata = getxml($doi_id);		
		if($doidata->num_rows() > 0){			
			foreach($doidata->result() as $row)
			{
				if($row->status=='ACTIVE')
				{
					//we need to update this to echo the datacite_xml field - but it isn't done yet;
					/*$xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<resource xmlns="http://datacite.org/schema/kernel-2.1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://datacite.org/schema/kernel-2.1 http://schema.datacite.org/meta/kernel-2.1/metadata.xsd">';
					$xml .='
	';
					$xml .= '<identifier identifierType="DOI">'.$doi_id.'</identifier>';
					$xml .= $this->exportDoiCreators($doi_id);
					$xml .= $this->exportDoiTitles($doi_id);
					$xml .= $this->exportDoiPublisher($doi_id);
					$xml .= $this->exportDoiPublicationYear($doi_id);
					$xml .= $this->exportDoiSubjects($doi_id);
					$xml .= $this->exportDoiContributors($doi_id);
					$xml .= $this->exportDoiDates($doi_id);
					$xml .= $this->exportDoiLanguage($doi_id);
					$xml .= $this->exportDoiResourceType($doi_id);
					$xml .= $this->exportDoiAlternateIdentifiers($doi_id);
					$xml .= $this->exportDoiRelatedIdentifiers($doi_id);
					$xml .= $this->exportDoiSizes($doi_id);
					$xml .= $this->exportDoiFormats($doi_id);
					$xml .= $this->exportDoiVersion($doi_id);
					$xml .= $this->exportDoiRights($doi_id);
					$xml .= $this->exportDoiDescriptions($doi_id);
					$xml .='</resource>'; */
					$xml = $row->datacite_xml;
					header('Content-type: text/xml');											
				}else{
					
					$xml = $this->doisGetUserMessage("MT012", $doi_id);
				}
			}

		}else{			
			$xml = $this->doisGetUserMessage("MT011", $doi_id);		
		}	
		echo $xml;
	}
	
	public function update(){
		$xml ='';	
		$errorMessages = '';	
		$notifyMessage = '';
		$logMessage = '';
		$outstr = '';
		$doiObjects = null;
		$response1 = "OK";
		$response2 = "OK";				
		$app_id = $this->input->get('app_id');		//passed as a parameter
		$urlValue = $this->input->get('url');		//passed as a parameter
		$doiValue = $this->input->get('doi');		//passed as a parameter
		$doi = getxml($doiValue);		 			// check if doi is a valid doi and get information about it
		$ip_address = trim($_SERVER['REMOTE_ADDR']);
	
		if($doi->num_rows() > 0){
			//we need to get the xml if that is to be updated as well
			if($_POST)
			{
				if(str_replace("<?xml version=","",implode($_POST))==implode($_POST))
				{
					$xml = "<?xml version=".implode($_POST); 	// passed as posted content
				}
			else 
				{
					$xml = implode($_POST);				// passed as posted content
				}
			}
			
			//first up, lets check that this client is permitted to update this doi.
			$client_id = checkDoisValidClient($ip_address,$app_id);

			if(!$client_id)
			{
				$errorMessages .= $this->doisGetUserMessage("MT009", $doi_id=NULL);
				header("HTTP/1.0 415 Authentication Error");
			}else{				
				if(!checkDoisClientDoi($doiValue,$client_id))
				{
					$errorMessages .= $this->doisGetUserMessage("MT008", $doiValue);
					header("HTTP/1.0 415 Authentication Error");
				} 				
			}				
			if($xml) // if the client has posted xml to be updated
			{
				$doiObjects = new DOMDocument();
						
				$result = $doiObjects->loadXML($xml);
		
				$errors = error_get_last();
			
				if( $errors )
				{
					$errorMessages .= "Document Load Error: ".$errors['message']."\n";
					header("HTTP/1.0 500 Internal Server Error");
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
		  
					//$result = $doiObjects->schemaValidate(gCMD_SCHEMA_URI);

					$xml = $doiObjects->saveXML();

					$errors = error_get_last();
					if( $errors )
					{
						$errorMessages .= $this->doisGetUserMessage("MT007", $doi_id=NULL);
						$errorMessages .= "Document Validation Error: ".$errors['message']."\n";
						header("HTTP/1.0 500 Internal Server Error");
					}				
				}	
			}
			
			if( $errorMessages == '' )
			{
				// Update doi information
				$updateError = updateDoiObject($doiValue,$doiObjects,$urlValue,$xml);	
				if(!$updateError){	
				// Update the DOI.
					if($urlValue)
					{
						$response1 = $this->doisRequest("mint",$doiValue, $urlValue, $xml,$client_id);				
					}
					
					if($doiObjects)
					{
						$response2 = $this->doisRequest("update",$doiValue, $urlValue, $xml,$client_id);
					}
					
					if( $response1 && $response2 )
					{
						if( $response1 == gDOIS_RESPONSE_SUCCESS && $response2 == gDOIS_RESPONSE_SUCCESS)
						{
							// We have successfully updated the doi through datacite.
							$notifyMessage = $this->doisGetUserMessage("MT002", $doiValue);
							header("HTTP/1.0 200 OK");
						}
						else
						{
							$errorMessages .= $this->doisGetUserMessage("MT010", $doi=NULL);
							$logMessage = "MT010 ".$response;
							header("HTTP/1.0 500 Internal Server Error");
						}
					}
					else
					{	
						$errorMessages .= $this->doisGetUserMessage("MT005", $doi=NULL);
						header("HTTP/1.0 500 Internal Server Error");
					}
				}else{
						
					$errorMessages .= '<br />'.$updateError;
					header("HTTP/1.0 500 Internal Server Error");		
				}
			}
		}else{
			$errorMessages .= $this->doisGetUserMessage("MT011", $doi_id=$doiValue);
		}
				
		echo $errorMessages;
		echo $notifyMessage;		
	}
	
	public function mint(){

		$ip_address = trim($_SERVER['REMOTE_ADDR']);
		$xml ='';	
		$errorMessages = '';	
		$notifyMessage = '';
		$logMessage = '';
		$outstr = '';
		$doiObjects = null;
		$response1 = "OK";
		$response2 = "OK";				
		$app_id = $this->input->get('app_id');		//passed as a parameter
		$urlValue = $this->input->get('url');		//passed as a parameter

		$client_id = checkDoisValidClient($ip_address,$app_id);
			
		$clientDetails = getDoisClient($app_id );
		
		foreach($clientDetails->result() as $clientDetail)
		{
			if($clientDetail->client_id<'10')
			{
				$client_id2 = "0".$clientDetail->client_id;
			}else{
				$client_id2 = $clientDetail->client_id;
			}
		}
		$datacite_prefix = $clientDetail->datacite_prefix;
		
		
		$doiValue = strtoupper($datacite_prefix.$client_id2.'/'.uniqid());	//generate a unique suffix for this doi for this client 
			
		if($_POST){
		if(str_replace("<?xml version=","",implode($_Post))==implode($_Post))
		{
			$xml = "<?xml version=".implode($_Post); 	// passed as posted content
		}
		else 
		{
			$xml = implode($_POST);				// passed as posted content
		}
		}
		if($xml=='')
		{
			$xml ='';
		}
		
		$doiObjects = new DOMDocument();
						
		$result = $doiObjects->loadXML($xml);
		
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
		
		//$xml = $doiObjects->saveXML();

		if( $errors )
		{
			$errorMessages .= "Document Load Error: ".$errors['message']."\n";
			header("HTTP/1.0 500 Internal Server Error");
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
		  
			$result = $doiObjects->schemaValidate(gCMD_SCHEMA_URI);
			$xml = $doiObjects->saveXML();
			
			$errors = error_get_last();
			if( $errors )
			{
				$errorMessages .= doisGetUserMessage("MT006", $doi_id=NULL);
				$errorMessages .= "Document Validation Error: ".$errors['message']."\n";
				header("HTTP/1.0 500 Internal Server Error");
			}			
				
		}					
			
		if(!$client_id)
		{
			$errorMessages .= doisGetUserMessage("MT009", $doi_id=NULL);
			header("HTTP/1.0 415 Authentication Error");
		}		
						
		if($urlValue=='')
		{
			$errorMessages .= "URL is a mandatory value to mint a doi.<br />";
			header("HTTP/1.0 500 Internal Server Error");		
		}			
			
		if( $errorMessages == '' )
		{
			// Insert doi information into the database

			$insertResult = importDoiObject($doiObjects,$urlValue, $client_id, $created_who='SYSTEM', $status='REQUESTED',$xml);

			if(!$insertResult){	
				// Mint the DOI.					
				$response = $this->doisRequest("mint",$doiValue, $urlValue, $xml,$client_id);
	
				if( $response )
				{
					if( $response == gDOIS_RESPONSE_SUCCESS )
					{
						// We have successfully minted the doi through datacite.										
						$response = $this->doisRequest("update",$doiValue, $urlValue, $xml,$client_id);							
						if( $response == gDOIS_RESPONSE_SUCCESS )			
						{
							$notifyMessage = $this->doisGetUserMessage("MT001", $doiValue);
							$status = "ACTIVE";
							$activateResult = setDoiStatus($doiValue,$status);
							header("HTTP/1.0 200 OK");
						}else{
							$errorMessages .=  $this->doisGetUserMessage("MT010", $doi=NULL);
							$logMessage = "MT010 ".$response;
							header("HTTP/1.0 500 Internal Server Error");
						}												
					}else{
						$errorMessages .=  $this->doisGetUserMessage("MT010", $doi=NULL);
						$logMessage = "MT010 ".$response;
						header("HTTP/1.0 500 Internal Server Error");
					}
				}else{
	
					$errorMessages .=  $this->doisGetUserMessage("MT005", $doi=NULL);
					header("HTTP/1.0 500 Internal Server Error");
				}
			}else{
				$errorMessages .= '..<br />'.$insertResult;
				header("HTTP/1.0 500 Internal Server Error");					
			}				
		}
		
		if($errorMessages)
		{		
			$outstr =  $errorMessages;	
			//We need to log this activity as errorred
			if($logMessage)
			{
				$errorMessages .= $logMessage;
			}
			insertDoiActivity("MINT",$doiValue,"FAILURE",$client_id,$errorMessages);		
		}
			
		if($notifyMessage)
		{
			//We need to log this activity
			insertDoiActivity("MINT",$doiValue,"SUCCESS",$client_id,$notifyMessage);		
			$outstr = $notifyMessage;
		}
			
		//we now need to return the result back to the calling program.
		header('Content-type: text/html');
		echo $outstr;		
		
	}
	
	public function activate(){
		$errorMessages = '';
		$notifyMessage = '';
		$logMessage = '';
		$outstr = '';
		
		$ip_address = trim($_SERVER['REMOTE_ADDR']);
		
		$app_id = $this->input->get('app_id');		//passed as a parameter
		$doiValue = $this->input->get('doi');		//passed as a parameter	
		
		//first up, lets check that this client is permitted to update this doi.
	
		$client_id = checkDoisValidClient($ip_address,$app_id);
		
		if(!$client_id)
		{
			$errorMessages .= $this->doisGetUserMessage("MT009", $doi_id=NULL);
			header("HTTP/1.0 415 Authentication Error");
		}else{				
			if(!checkDoisClientDoi($doiValue,$client_id))
			{
				$errorMessages .= $this->doisGetUserMessage("MT008", $doiValue);
				header("HTTP/1.0 415 Authentication Error");
			} 				
		}	

		if(getDoiStatus($doiValue)!="INACTIVE")
		{
			$errorMessages .= "DOI ".$doiValue." is not set to inactive so cannot activate it.<br />";	
			header("HTTP/1.0 500 Internal Server Error");	
		}
	
		if( $errorMessages == '' )
		{
			// Update doi information
			$status = "ACTIVE";
			$activateResult = setDoiStatus($doiValue,$status);
			$xml = file_get_contents("http://".eHOST.eROOT_DIR."/xml?doi=".$doiValue);
			if(!$activateResult){	
			// Activate the DOI.
	
				$response = $this->doisRequest("update",$doiValue,$urlValue = NULL ,$xml, $client_id );
	
				if($response)
				{
					if( $response == gDOIS_RESPONSE_SUCCESS )
					{
						// We have successfully activated the doi through datacite.
						$notifyMessage .= $this->doisGetUserMessage("MT004", $doiValue);
						header("HTTP/1.0 200 OK");					
	
					}
					else
					{
						$activateResult = setDoiStatus($doiValue,'INACTIVE');
						$errorMessages .= $this->doisGetUserMessage("MT010", $doi=NULL);
						$logMessage = "MT010 ".$response;
						header("HTTP/1.0 500 Internal Server Error");										
					}
				}
				else
				{	
					$errorMessages .= $this->doisGetUserMessage("MT005", $doi=NULL);
					header("HTTP/1.0 500 Internal Server Error");					
				}
			}else{
					
				$errorMessages .= '<br />'.$activateResult;	
				header("HTTP/1.0 500 Internal Server Error");		
			}
		}
	
		if($errorMessages)
		{	
			
			$outstr =  $errorMessages;	
			//We need to log this activity as errorred	
			if($logMessage)
			{
				$errorMessages .= $logMessage;
			}
			insertDoiActivity("ACTIVATE",$doiValue,"FAILURE",$client_id,$errorMessages);		
	
		}
		
		if($notifyMessage)
		{
			//We need to log this activity as successful
			insertDoiActivity("ACTIVATE",$doiValue,"SUCCESS",$client_id,$notifyMessage);		
			$outstr = $notifyMessage;
		}
		
		//we now need to return the result back to the calling program.
		header('Content-type: text/html');
		echo $outstr;		
	}
	
	public function deactivate(){
		$errorMessages = '';
		$notifyMessage = '';
		$logMessage = '';
		$outstr = '';
		
		$ip_address = trim($_SERVER['REMOTE_ADDR']);
		
		$app_id = $this->input->get('app_id');		//passed as a parameter
		$doiValue = $this->input->get('doi');		//passed as a parameter	
		
		//first up, lets check that this client is permitted to update this doi.
	
		$client_id = checkDoisValidClient($ip_address,$app_id);
		
		if(!$client_id)
		{
			$errorMessages .= $this->doisGetUserMessage("MT009", $doi_id=NULL);
			header("HTTP/1.0 415 Authentication Error");
		}else{				
			if(!checkDoisClientDoi($doiValue,$client_id))
			{
				$errorMessages .= $this->doisGetUserMessage("MT008", $doiValue);
				header("HTTP/1.0 415 Authentication Error");
			} 				
		}	
	
		if(getDoiStatus($doiValue)!="ACTIVE")
		{
				$errorMessages .= "DOI ".$doiValue." is not set to active so cannot deactivate it.<br />";
				header("HTTP/1.0 500 Internal Server Error");		
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
						$notifyMessage .= $this->doisGetUserMessage("MT003", $doiValue);
						header("HTTP/1.0 200 OK");					
					}
					else
					{
						$errorMessages .= $this->doisGetUserMessage("MT010", $doi=NULL);
						$logMessage = "MT010 ".$response;
						header("HTTP/1.0 500 Internal Server Error");
					}
				}
				else
				{	
					$errorMessages .= $this->doisGetUserMessage("MT005", $doi=NULL);
					header("HTTP/1.0 500 Internal Server Error");			
				}
			}else{
					
				$errorMessages .= '<br />'.$inactivateResult;
				header("HTTP/1.0 500 Internal Server Error");		
			}
		}
	
		if($errorMessages)
		{	
			
			$outstr =  $errorMessages;
			//We need to log this activity as errorred
			if($logMessage)
			{
				$errorMessages .= $logMessage;
			}
			insertDoiActivity("INACTIVATE",$doiValue,"FAILURE",$client_id,$errorMessages);
	
		}
		
		if($notifyMessage)
		{
			//We need to log this activity
			insertDoiActivity("INACTIVATE",$doiValue,"SUCCESS",$client_id,$notifyMessage);
		
			$outstr = $notifyMessage;
		}		
		//we now need to return the result back to the calling program.
		header('Content-type: text/html');
		echo $outstr;		
		
	}
	
	public function checkurl(){	
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

	
/*	function exportDoiCreators($doi_id)
	{
	$xml = '';
	$creators = getcreators($doi_id);
	if($creators->num_rows()>0)
	{
		$xml = '
	<creators>
	';			
		foreach($creators->result() as $creator)
		{
			$xml .= '	<creator><creatorName>'.$creator->creator_name.'</creatorName>';
			if($creator->name_identifier_scheme)
			{
				$xml .='<nameIdentifier nameIdentifierScheme="'.$creator->name_identifier_scheme.'">'.$creator->name_identifier.'</nameIdentifier>';
			}
			$xml .= '</creator>
	';
		}
		$xml .='</creators>
';
	}	
	return $xml;		
	}
	
	function exportDoiTitles($doi_id)
	{
		$xml = '';
		$titles = gettitles($doi_id);	
		if($titles->num_rows>0)
		{
			$xml = '	<titles>
';			
			foreach($titles->result() as $title)
			{
				$xml .= '		<title';
				if($title->title_type)
				{
					$xml .= ' titleType="'.$title->title_type.'"';
				}
				$xml .='>'.$title->title.'</title>
';					
			}
			$xml .='	</titles>
';
		}	
		return $xml;
	}
	
	function exportDoiDates($doi_id)
	{
		$dates = getdates($doi_id);	
		$xml = '';
		if($dates->num_rows>0)
		{
		$xml = '	<dates>
';			
		foreach($dates->result() as $date)
		{
			$xml .= '		<date';
			if($date->date_type)
			{
				$xml .= ' dateType="'.$date->date_type.'"';
			}
			$xml .='>'.$date->date.'</date>
';					
		}
		$xml .='	</dates>
';
		}	
		return $xml;
	}	
	function exportDoiSubjects($doi_id)
	{
		$subjects = getsubjects($doi_id);	
		$xml = '';
		if($subjects->num_rows>0)
		{
			$xml = '	<subjects>
	';
			foreach($subjects->result() as $subject)
			{
				$xml .= '	<subject';
				if($subject->subject_scheme)
				{
					$xml .= ' subjectScheme="'.$subject->subject_scheme.'"';
				}
				$xml .='>'.$subject->subject.'</subject>
';					
			}
		$xml .='	</subjects>
';
		}	
		return $xml;
	}
		
	function exportDoiPublisher($doi_id)
	{
		$publishers = getxml($doi_id);	
		$xml;
		if($publishers->num_rows>0)
		{
			foreach($publishers->result() as $publisher)
			{
			$xml = '	<publisher>'.$publisher->publisher.'</publisher>
';
			}
		}
		return $xml;
	}	
		
	function exportDoiLanguage($doi_id)
	{
		$languages = getxml($doi_id);	
		$xml;
		if($languages->num_rows>0)
		{
			foreach($languages->result() as $language)
			{
			$xml = '	<language>'.$language->language.'</language>
';
			}
		}
		return $xml;
	}	
		
	function exportDoiPublicationYear($doi_id)
	{
		$publicationYears = getxml($doi_id);	
		$xml;
		if($publicationYears->num_rows>0)
		{
			foreach($publicationYears->result() as $publicationYear)
			{
			$xml = '	<publicationYear>'.$publicationYear->publication_year.'</publicationYear>
';
			}
		}
		return $xml;
	}	
	
	function exportDoiContributors($doi_id)
	{
	$xml = '';
	$contributors = getcontributors($doi_id);
	if($contributors->num_rows()>0)
	{
		$xml = '	<contributors>
';			
		foreach($contributors->result() as $contributor)
		{
			$xml .= '		<contributor';
			if($contributor->contributor_type)
			{
				$xml .= ' contributorType="'.$contributor->contributor_type.'"';
			}
			$xml .='><contributorName>'.$contributor->contributor_name.'</contributorName>';
			if($contributor->name_identifier_scheme)
			{
				$xml .='<nameIdentifier nameIdentifierScheme="'.$contributor->name_identifier_scheme.'">'.$contributor->name_identifier.'</nameIdentifier>';
			}
			$xml .= '</contributor>
';
		}
		$xml .='	</contributors>
';
	}	
	return $xml;		
	}	
		
	function exportDoiResourceType($doi_id)
	{
	$xml = '';
	$resourceTypes = getresourcetypes($doi_id);
	if($resourceTypes->num_rows()>0)
	{
		foreach($resourceTypes->result() as $resourceType)
		{		
			$xml = '	<resourceType';
			if($resourceType->resource_type_general)
			{
				$xml .= ' resourceTypeGeneral="'.$resourceType->resource_type_general.'"';
			}
				
			if($resourceType->resource_description)
			{
				$xml .= ' resourceDescription="'.$resourceType->resource_description.'"';
			}
			$xml .='>'.$resourceType->resource.'</resourceType>
';	
		}		
	}	
	return $xml;		
	}
	
	function exportDoiAlternateIdentifiers($doi_id)
	{
	$xml = '';
	$altIdentifiers = getaltidentifiers($doi_id);
	if($altIdentifiers->num_rows()>0)
	{
		$xml = '	<alternateIdentifiers>
';			
		foreach($altIdentifiers->result() as $altIdentifier)
		{		
			$xml .= '		<alternateIdentifier';
			if($altIdentifier->alternate_identifier_type)
			{
				$xml .= ' alternateIdentifierType="'.$altIdentifier->alternate_identifier_type.'"';
			}
			$xml .='>'.$altIdentifier->alternate_identifier.'</alternateIdentifier>
';					
		}
		$xml .='	</alternateIdentifiers>
';
	}	
	return $xml;		
	}	
	
	function exportDoiRelatedIdentifiers($doi_id)
	{
	$xml = '';
	$relIdentifiers = getrelidentifiers($doi_id);
	if($relIdentifiers->num_rows()>0)
	{
		$xml = '	<relatedIdentifiers>
';			
		foreach($relIdentifiers->result() as $relIdentifier)
		{
			$xml .= '		<relatedIdentifier';
			if($relIdentifier->related_identifier_type)
			{
				$xml .= ' relatedIdentifierType="'.$relIdentifier->related_identifier_type.'"';
			}
			if($relIdentifier->relation_type)
			{
				$xml .= ' relationType="'.$relIdentifier->relation_type.'"';
			}			
			$xml .='>'.$relIdentifier->related_identifier.'</relatedIdentifier>
';					
		}
		$xml .='	</relatedIdentifiers>
';
	}	
	return $xml;		
	}	
	
	function exportDoiSizes($doi_id)
	{
	$xml = '';
	$sizes = getsizes($doi_id);
	if($sizes->num_rows()>0)
	{
		$xml = '	<sizes>
';			
		foreach($sizes->result() as $size)
		{
			$xml .= '		<size>'.$size->size.'</size>
';					
		}
		$xml .='	</sizes>
';
	}	
	return $xml;		
	}	
	
	function exportDoiFormats($doi_id)
	{
	$xml = '';
	$formats = getformats($doi_id);
	if($formats->num_rows()>0)
	{
		$xml = '	<formats>
';			
		foreach($formats->result() as $format)
		{
			$xml .= '		<format>'.$format->format.'</format>
';					
		}
		$xml .='	</formats>
';
	}	
	return $xml;		
	}
	
	function exportDoiVersion($doi_id)
	{
		$versions = getxml($doi_id);	
		$xml;
		if($versions->num_rows>0)
		{
			foreach($versions->result() as $version)
			{
			$xml = '	<version>'.$version->version.'</version>
';
			}
		}
		return $xml;
	}
	
	function exportDoiRights($doi_id)
	{
		$rights = getxml($doi_id);	
		$xml;
		if($rights->num_rows>0)
		{
			foreach($rights->result() as $right)
			{
			$xml = '	<rights>'.$right->rights.'</rights>
';
			}
		}
		return $xml;
	}	
	
	function exportDoiDescriptions($doi_id)
	{
		$xml = '';
		$descriptions = getdescriptions($doi_id);	
		$xml;
		if($descriptions->num_rows>0)
		{
			$xml = '	<descriptions>
';			
			foreach($descriptions->result() as $description)
			{
				$xml .= '		<description';
				if($description->description_type)
				{
					$xml .= ' descriptionType="'.$description->description_type.'"';
				}
				$xml .='>'.$description->description.'</description>
';					
			}
			$xml .='	</descriptions>
';
		}
		return $xml;
	}	
*/							
	function doisGetUserMessage($messageId, $doi_id)
	{
		$userMessage = '';
	
		switch($messageId)
		{
			case "MT001":
				$userMessage = "[".$messageId."] DOI ".$doi_id." was successfully minted.";
				break;
			case "MT002":
				$userMessage = "[".$messageId."] DOI ".$doi_id." was successfully updated.";
				break;
			case "MT003":
				$userMessage = "[".$messageId."] DOI ".$doi_id." was successfully inactivated.";
				break;
			case "MT004":
				$userMessage = "[".$messageId."] DOI ".$doi_id." was successfully activated.";		
				break;
			case "MT005":
				$userMessage = "[".$messageId."] The ANDS Cite My Data service is currently unavailable. Please try again at a later time. If you continue to experience problems please contact services@ands.org.au.";		
				break;
			case "MT006":
				$userMessage = "[".$messageId."] The metadata you have provided to mint a new DOI has failed the schema validation. 
				Metadata is validated against the latest version of the DataCite Metadata Schema. 
				For information about the schema and the latest version supported, 
				please visit the ANDS website http://ands.org.au. 
				Detailed information about the validation errors can be found below.<br />";		
				break;
			case "MT007":
				$userMessage = "[".$messageId."] The metadata you have provided to update DOI ".$doi_id." has failed the schema validation. 
				Metadata is validated against the DataCite Metadata Schema.
				For information about the schema and the latest version supported, 
				please visit the ANDS website http://ands.org.au. 
				Detailed information about the validation errors can be found below.<br />";		
				break;
			case "MT008":
				$userMessage = "[".$messageId."] You do not appear to be the owner of DOI ".$doi_id.". If you believe this to be incorrect please contact services@ands.org.au.";		
				break;								
			case "MT009":
				$userMessage = "[".$messageId."] You are not authorised to use this service. For more information or to request access to the service please contact services@ands.org.au.";
				break;
			case "MT010":
				$userMessage = "[".$messageId."] There has been an unexpected error processing your doi request. For more information please contact services@ands.org.au.";					
				break;
			case "MT011":
				$userMessage = "[".$messageId."] DOI ".$doi_id." does not exist in the ANDS Cite My Data service.";					
				break;	
			case "MT012":
				$userMessage = "[".$messageId."] No metadata exists in the Cite My Data service for DOI ".$doi_id;					
				break;						
			default:
				$userMessage = "There has been an unidentified error processing your doi request. For more information please contact services@ands.org.au.";
				break;									
		}
		return $userMessage;
	}
		
	function doisRequest($service, $doi, $url, $metadata,$client_id)
	{
	
		$resultXML = '';
		//$mode ="?testMode=true";
		$mode='';
		$authstr = gDOIS_DATACENTRE_NAME_PREFIX.".".gDOIS_DATACENTRE_NAME_MIDDLE."-".$client_id.":".gDOIS_DATACITE_PASSWORD;
		$requestURI = gDOIS_SERVICE_BASE_URI;
			
		$ch = curl_init();
				
		if($service=="mint")
		{
			$context  = array('Content-Type:text/plain;charset=UTF-8','Authorization: Basic '.base64_encode($authstr));
			$metadata="doi=".$doi."\nurl=".$url;
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
	
}

?>