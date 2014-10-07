<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Core Data Sources model
 * 
 * This model allows the reference and initialisation 
 * of Data Sources. All instances of the _data_source 
 * PHP class should be invoked through this model. 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */
include_once("applications/registry/data_source/models/_transforms.php");
class Import extends CI_Model {


	function importPayloadToDataSource($data_source_id, $xml, $harvestID = '', $debug=false, $mode = 'HARVEST')
	{

		ob_start();
		$this->output->enable_profiler(FALSE);
		$this->load->model('registry_object/registry_objects', 'ro');
		$this->load->model('registry_object/registry_objects', 'oldRo');
		$this->load->model('registry_object/rifcs', 'rifcs');
		$this->load->model('data_source/data_sources', 'ds');


		$imput = $xml;
		bench(0);
		$timewaiting = 0;
		$record_count = 0;
		$reg_obj_count = 0;
		$duplicate_record_count = 0;

		// An array of record ids created in this harvest (used in two-phase harvesting)
		$harvested_record_ids = array();

		gc_enable();

		// XXX: COMMENTME
		if ($harvestID == '') { $harvestID = "MANUAL-".time(); }


		/* PHASE ONE => HARVEST THE RECORD IN ISOLATION */
		$data_source = $this->ds->getByID($data_source_id);
		try
		{
			// Validate
			$this->validateRIFCSXML($xml);
			$sxml = $this->getSimpleXMLFromString($xml);
			$status = $this->getDefaultRecordStatusForDataSource($data_source);

			foreach($sxml->xpath('//registryObject') AS $registryObject)
			{
				// Determine the registry object class
				$reg_obj_count++;
				$ro_class = NULL;
				if($mode == 'HARVEST')
				{
					foreach ($this->ro->valid_classes AS $class)
					{
						if (property_exists($registryObject, $class))
						{
							$ro_class = $class;
						}
	
						foreach($registryObject->{$class} AS $ro_xml)
						{
	
							// Flag records that are duplicates within this harvest and choose not to harvest them again (repeated keys in single harvest are dumb!)
							$reharvest = true;
							if($oldRo = $this->oldRo->getByKey((string)$registryObject->key))
							{
								$oldharvestID = $oldRo->getAttribute("harvest_id");
								if($oldharvestID == $harvestID)
								$reharvest = false;
	
								// XXX: Record ownership, reject if record already exists within the registry
							}
	
							if($reharvest)
							{
								// XXX: Record owner should only be system if this is a harvest?
								$record_owner = "SYSTEM";
	
								// Create a frame instance of the registryObject
								$ro = $this->ro->create($data_source, (string)$registryObject->key, $ro_class, "", $status, "defaultSlug", $record_owner, $harvestID);
								$ro->created_who = $record_owner;
								$ro->data_source_key = $data_source->key;
								$ro->group = (string) $registryObject['group'];
								$ro->setAttribute("harvest_id", $harvestID);
	
								// Order is important here!
								$changed = $ro->updateXML($registryObject->asXML());
	
								// Generate the list and display titles first, then the SLUG
								$ro->updateTitles();
								$ro->generateSlug();
								$ro->processIdentifiers();	
								// Save all our attributes to the object
								$ro->save($changed);

								// Add this record to our counts, etc.
								$harvested_record_ids[] = $ro->id;
								$record_count++;
	
								// Memory management...
								unset($ro);
							}
							else
							{
								// XXX: Verbose message?
								$duplicate_record_count++;
							}
						}
	
	
					}
				}

			}

			// Clean up our memory objects...
			unset($sxml);
			unset($xml);
			gc_collect_cycles();
		}
		catch (Exception $e)
		{
			$data_source->append_log("UNABLE TO HARVEST FROM THIS DATA SOURCE" . NL . $e->getMessage() . NL ,"importer", "HARVEST_ERROR");
			throw new Exception ("UNABLE TO HARVEST FROM THIS DATA SOURCE" . NL . $e->getMessage() . NL);
		}



		/* PHASE TWO - ENRICH THE HARVESTED RECORD IN LIGHT OF ITS CONTEXT */
		try
		{
			// Only enrich records received in this harvest
			foreach ($harvested_record_ids AS $ro_id)
			{

				$ro = $this->ro->getByID($ro_id);

				$ro->addRelationships();
				// XXX: re-enrich records which are related to this one

				$ro->update_quality_metadata();

				// spatial resooultion, center, coords in enrich?
				$ro->determineSpatialExtents();


				// vocab indexing resolution

				// Generate extrif
				$ro->enrich();

				unset($ro);
				clean_cycles();
			}

			gc_collect_cycles();

		}
		catch (Exception $e)
		{
			$data_source->append_log("UNABLE TO HARVEST FROM THIS DATA SOURCE" . NL . $e->getMessage() . NL ,"importer", "HARVEST_ERROR");
			throw new Exception ("UNABLE TO HARVEST FROM THIS DATA SOURCE" . NL . $e->getMessage() . NL);
		}

		// Index the datasource we just harvested?? XXX: Should this just index the records enriched?
		//$this->indexDS($data_source_id);

		echo ((float) bench(0) - (float) $timewaiting) . " seconds to harvest " . NL;
		echo $reg_obj_count. " received " .NL.$record_count . " records inserted " . NL;
		if($duplicate_record_count > 0)
		{
		echo $duplicate_record_count." records ignored" . NL;
		}		
		if($reg_obj_count == 0)
		{
		//echo "INPUT " .$imput;
		echo "DONE WITH ERRORS" . NL;
		}
		else{
		echo "DONE" . NL;
		}


		return ob_get_clean();
	}	


	private function getRIFCSFromURI($uri)
	{
		$xml = file_get_contents($uri);

		if (!$xml)
		{
			throw new Exception ("Unable to retreive valid feed data from: $uri");
		}
		return $xml;
	}

	private function validateRIFCSXML($xml)
	{
		$doc = @DOMDocument::loadXML($xml);
		if(!$doc)
		{
			$data_source->append_log("Unable to parse XML. Perhaps your XML file is not well-formed?".$xml,"importer", "HARVEST_ERROR");
			throw new Exception("Unable to parse XML. Perhaps your XML file is not well-formed?".$xml);
		}
		libxml_use_internal_errors(true);
		$validation_status = @$doc->schemaValidate("applications/registry/registry_object/schema/registryObjects.xsd");

		if ($validation_status === TRUE) 
		{
			return TRUE;
		}
		else
		{
			$errors = libxml_get_errors();
			$error_string = '';
			foreach ($errors as $error) {
			    $error_string .= TAB . "Line " .$error->line . ": " . $error->message;
			}
			libxml_clear_errors();
			$data_source->append_log("Unable to validate XML document against schema: " . NL . $error_string ,"importer", "HARVEST_ERROR");		
			throw new Exception("Unable to validate XML document against schema: " . NL . $error_string);
		}
	}

	private function getSimpleXMLFromString($xml)
	{
		// Simplexml doesn't play nicely with namespaces :-(
		$xml = str_replace('xmlns="http://ands.org.au/standards/rif-cs/registryObjects"', '', $xml);
		$xml = simplexml_load_string($xml, "SimpleXMLElement", 0);
		//$xml->registerXPathNamespace("ro", "http://ands.org.au/standards/rif-cs/registryObjects");

		if ($xml === false)
		{
			$exception_message = "Could not parse Registry Object XML" . NL;
			foreach(libxml_get_errors() as $error) {
        		$exception_message .= "\t" . $error->message;
			}
			throw new Exception($exception_message);	
		}
		return $xml;
	}

	private function getDefaultRecordStatusForDataSource(_data_source $data_source)
	{

		/*
		 * Harvest to the correct record mode
		 * QA = SUBMIT FOR ASSESSMENT
		 * !QA, AP = PUBLISHED
		 * !QA, !AP = APPROVED
		 */
		if ($data_source->qa_flag === DB_TRUE)
		{
			$status = $this->ro->valid_status['SUBMITTED_FOR_ASSESSMENT'];
		}
		else
		{
			if ($data_source->manual_publish === DB_TRUE)
			{
				$status = $this->ro->valid_status['APPROVED'];
			}
			else
			{
				$status = $this->ro->valid_status['PUBLISHED'];
			}
		}

		return $status;
	}

	function indexDS($data_source_id){
		$solrUrl = 'http://ands3.anu.edu.au:8983/solr/';
		$solrUpdateUrl = $solrUrl.'update/?wt=json';
		$this->load->model('registry_objects', 'ro');
		$this->load->model('data_source/data_sources', 'ds');

		$ids = $this->ro->getIDsByDataSourceID($data_source_id);
		echo 'total='.sizeof($ids).BR;
		$i = 1;
		foreach($ids as $ro_id){
			try{
				$ro = $this->ro->getByID($ro_id);
				//echo $ro->getExtRif();
				$solrXML =  str_replace("&lt;field","\n&lt;field", htmlentities($ro->transformForSOLR()));
				$solrXML = $ro->transformForSOLR();
				echo $solrXML;
				$result = curl_post($solrUpdateUrl, $solrXML);
				$result = json_decode($result);
				if($result->{'responseHeader'}->{'status'}==0){
					echo $i. ' - id:'.$ro_id.' indexed'.BR;
					$i++;
				}
			}catch (Exception $e){
				echo "UNABLE TO Index this registry object id = ".$ro_id . BR;	
				echo "<pre>" . nl2br($e->getMessage()) . "</pre>" . BR;
			}
		}
		return curl_post($solrUpdateUrl.'?commit=true', '<commit waitSearcher="false"/>');
	}

	function getRifcsFromHarvest($xmlData)
	{
		// Simplexml doesn't play nicely with namespaces :-(
		$result = ''; 		
		try{
			$xslt_processor = HarvestTransforms::get_feed_to_rif_transformer();
			$dom = new DOMDocument();
			if(substr($xmlData, 0, 1) == '<')
			{
				$dom->loadXML($xmlData);
			}
			else
			{
				$dom->loadXML(utf8_decode($xmlData));
			}
			$result = $xslt_processor->transformToXML($dom);
		}catch (Exception $e)
		{
			$result = "ERROR UNABLE TO EXTRACT RIF" . nl2br($e->getMessage());
		}
		return $result;
	}

}