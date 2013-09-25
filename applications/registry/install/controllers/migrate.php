<?php

ini_set('memory_limit', '2048M');
class Migrate extends MX_Controller
{
	private $input; // pointer to the shell input
	private $start_time; // time script run (microtime float)
	private $exec_time; // time execution started
	private $_CI; 	// an internal reference to the CodeIgniter Engine 
	private $source;
	private $scpr = "dba."; //schema prefix
	private $recordLimitMin = 0;
	private $recordLimitMax = 4000;
	private $start_ds_id = null;
	private $noReindex = false;
	private $noEmails = true; // for debugging
	
	function index()
	{
		$scpr = $this->scpr; //schema prefix

		set_error_handler(array(&$this, 'cli_error_handler'));
		echo "Connected to migration target database..." . NL;

		$this->source->select('*');
		if ($this->start_ds_id)
		{
			$query = $this->source->order_by('data_source_key')->get($scpr . 'tbl_data_sources', 999, $this->start_ds_id);
		}
		else
		{
			$query = $this->source->order_by('data_source_key')->get($scpr . 'tbl_data_sources');
		}

		$num_data_sources = $query->num_rows();
		
		echo "Would you like to filter by a data source name/key (leave blank to continue for all data sources): ";
		$filter = $this->getInput(); 

		// Start the clock...
		$this->exec_time = microtime(true);

		$this->_CI->load->model("registry_object/registry_objects", "ro");
		$this->_CI->load->model('data_source/data_sources','ds');
		foreach ($query->result() AS $result)
		{
			if ($filter)
			{
				if (strpos(strtolower($result->data_source_key), $filter) === FALSE && strpos(strtolower($result->title), $filter) === FALSE)
				{
					continue;
				}
			}

			$data_source = $this->createOrUpdateDataSource($result);
			
			// Update logs (deletes any legacy logs and re-migrates them!)
			//$this->importDataSourceLogs($data_source->key, $data_source->id);
			//$data_source->append_log("Data Source was migrated to ANDS Online Services Release 10", "info", "legacy_log");

			// Now start importing registry objects
			//$this->deleteAllrecordsForDataSource($data_source);
			//$data_source->updateStats();
			//$this->migrateRegistryObjectsForDatasource($data_source);
			//$this->migrateDraftRegistryObjectsForDatasource($data_source);
			//$this->migrateDeletedRegistryObjectsForDatasource($data_source);
			//$this->reschedulePendingHarvests($data_source);

			echo NL . NL;
		}

		//$this->updateDanglingSlugs();
		//$this->updateContributorPages();


		echo NL . NL;

	}

	function reschedulePendingHarvests($data_source)
	{
		// Update the harvest date based on currently queued harvests
		$harvest_request_query = $this->source->get_where('dba.tbl_harvest_requests', array("data_source_key"=>$data_source->key));
		if ($harvest_request_query->num_rows())
		{
			echo "[HARVESTER] Rescheduling a previously scheduled harvest" . NL;
			$harvest = $harvest_request_query->result_array();
			$data_source->cancelAllharvests();
			if ($harvest[0]['harvest_date'] == "")
			{
				// Schedule the harvest
				$data_source->requestHarvest();
			}
			else
			{
				if ($harvest[0]['harvest_frequency'] == "daily")
				{
					// schedule for tomorrow at the same time
					$timestamp = strtotime($harvest[0]["harvest_date"]);
					$time_offset = (strtotime(gmdate("h:i:s A", $timestamp)) - strtotime("00:00:00"));
					$data_source->harvest_date = gmdate("Y-m-d\TH:i:s\Z", (strtotime(date("m/d/y") . " 00:00:00 GMT") + $time_offset + ONE_DAY));
				}
				// If we're on a weekly schedule, make sure we have the same time and day of the week (in the future) and future-date the harvest
				else if ($harvest[0]['harvest_frequency'] == "weekly" || $harvest[0]['harvest_frequency'] == "fortnightly")
				{	
					$timestamp = strtotime($harvest[0]["harvest_date"]);
					$upcoming_date = " next " . strtolower(gmdate("l", $timestamp));
					$time_offset = (strtotime(gmdate("h:i:s A", $timestamp)) - strtotime("00:00:00"));
					$upcoming_timestamp = strtotime($upcoming_date) + $time_offset;
					$data_source->harvest_date = gmdate("Y-m-d\TH:i:s\Z", $upcoming_timestamp);
				}
				$data_source->save();
				$data_source->requestHarvest();
			}
		}
	}

	function enrich($data_source_id)
    {

    echo "ENRICHING..." . NL;
    $this->exec_time = microtime(true);
           	$this->load->model('registry_object/registry_objects', 'ro');
            $this->load->model('data_source/data_sources', 'ds');

    $ids = $this->ro->getIDsByDataSourceID($data_source_id);
            if($ids)
            {

                   	/* TWO-STAGE ENRICH */

                    echo '----STAGE 1-----' . NL . NL;
                   	foreach($ids as $ro_id){
                   	echo '.';
                           	try{
                                   	$ro = $this->ro->getByID($ro_id);
                                   	if($ro->getRif()){
                                            $ro->addRelationships();
                                           	unset($ro);
                                    }
                           	}catch (Exception $e){
                                   	echo "<pre>error in: $e" . nl2br($e->getMessage()) . "</pre>" . BR;
                            }
                   	}
                   	echo '----STAGE 2----' . NL . NL;
                    foreach($ids as $ro_id){
                   	echo '*';
                           	try{
                                   	$ro = $this->ro->getByID($ro_id);
                                   	if($ro->getRif()){
                                           	$ro->update_quality_metadata();
                                           	echo "^";
                                           	$ro->enrich();
                                           	unset($ro);
                                           	gc_collect_cycles();
                                           	clean_cycles();
                                   	}
                            }catch (Exception $e){
                                   	echo "<pre>error in: $e" . nl2br($e->getMessage()) . "</pre>" . BR;
                            }
                    }
            }

    }

	function updateContributorPages()
	{
		$query = $this->source->get('dba.tbl_institution_pages');
		echo "[CONTRIBUTORS] Found " . $query->num_rows() . " contributor pages..." . NL;

		$this->db->where('group !=', '')->delete('institutional_pages');

		if ($query->num_rows() > 0)
		{
			foreach ($query->result() AS $contributor)
			{
				// Resolve RO keys to IDs
				$registry_object = $this->ro->getPublishedByKey($contributor->registry_object_key);
				if (!$registry_object) { 
					$registry_object = $this->ro->getDraftByKey($contributor->registry_object_key);
				}

				$ds = $this->ds->getByKey($contributor->authoritive_data_source_key);
			
				if ($registry_object && $ds)
				{

					$this->db->insert("institutional_pages", 
						array("group" => $contributor->object_group, 
							  "registry_object_id" => $registry_object->id, 
							  "authorative_data_source_id" => $ds->id
						)
					);
				}
				else
				{
					echo "Unable to match contributor page keys: " . $contributor->object_group . " (".$contributor->registry_object_key.")". NL;
				}
			}
		}
	}

	function updateDanglingSlugs()
	{
		$query = $this->source->get_where('dba.tbl_url_mappings', array("registry_object_key"=>""));
		echo "[SLUGS] Found " . $query->num_rows() . " dangling slugs...";
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() AS $slug)
			{
				$slug->url_fragment = substr($slug->url_fragment,0,255);

				$count_query = $this->db->get_where("url_mappings", 
					array("slug" => $slug->url_fragment));

				if ($count_query->num_rows() == 0)
				{
					$this->db->insert("url_mappings", 
						array("slug" => $slug->url_fragment, 
							  "created" => $slug->date_created, 
							  "updated" => max($slug->date_created, $slug->date_modified), 
							  "search_title" => $slug->search_title
						)
					);
				}
				
			}
		}
	}

	function createOrUpdateDataSource($result)
	{

			// Fetch this data source (or create it if it doesn't exist yet)
			$data_source = $this->ds->getByKey($result->data_source_key);
			if($data_source === NULL)
			{
				echo "Creating data source: " . $result->title . "..." . NL;
				$this->ds->create($result->data_source_key, url_title($result->title));
				$data_source = $this->ds->getByKey($result->data_source_key);
			}
			else
			{
				echo "Updating data source: " . $result->title . "..." . NL;
			}


			echo "----" . NL;
			// Start setting attributes
			$data_source->title = $result->title;
			$data_source->setAttribute("contact_name", $result->contact_name);
			$data_source->setAttribute("contact_email", $result->contact_email);

			$data_source->setAttribute("record_owner", $result->record_owner); // this should be an org role in cosi...unchanged from R9

			$data_source->created = strtotime($result->created_when); // convert our timestamps to UNIX
			$data_source->updated = strtotime($result->modified_when); // convert our timestamps to UNIX

			// Contact details unchanged
			$data_source->setAttribute("contact_name", $result->contact_name);

			if ($this->noEmails)
			{
				$data_source->setAttribute("contact_email", "ben.greenwood@ands.org.au");
			}
			else
			{
				$data_source->setAttribute("contact_email", $result->contact_email);
			}
			

			// Notes have to be trimmed down to 255 characters
			$data_source->setAttribute("notes", substr($result->notes, 0 ,255));

			// Don't track modified_who in R10
			//$data_source->setAttribute("modified_who", $result->modified_who);

			// Provider type values have changed...it now indicates the crosswalk provider type
			$data_source->setAttribute("uri", ($result->uri != "http://" ? $result->uri : ""));
			// Harvest method is the new provider_type...
			// $data_source->setAttribute("harvest_method", $result->harvest_method);
			if (trim($result->provider_type) == "OAI_RIF")
			{
				// RIF indicates OAI (why...)
				$data_source->setAttribute("harvest_method", "RIF");
			}
			else
			{
				// "GET" means normal HTTP request
				$data_source->setAttribute("harvest_method", "GET");
			}

			$data_source->provider_type = "rif"; // all datasources provide RIFCS
			$data_source->setAttribute("oai_set", $result->oai_set);
			$data_source->setAttribute("advanced_harvest_mode", $result->advanced_harvesting_mode);

			// Contact email field name is changed to be spelled correctly
			if ($this->noEmails)
			{
				$data_source->setAttribute("assessment_notify_email_addr", "ben.greenwood@ands.org.au");
			}
			else
			{
				$data_source->setAttribute("assessment_notify_email_addr", $result->assessement_notification_email_addr);
			}

			// Hold onto this legacy value in case this NL feature comes back one day
			$data_source->setAttribute("LEGACY-isil_value", $result->isil_value);
			$data_source->setAttribute("LEGACY-push_to_nla", $result->push_to_nla);

			// Translate the date to zulu timestamp (xsd:dateTime)
			if($result->harvest_date)
			{
				$data_source->setAttribute("harvest_date", gmdate("Y-m-d\TH:i:s\Z", strtotime($result->harvest_date)));
			}

			$data_source->setAttribute("harvest_frequency", $result->harvest_frequency);

			// Leo's retarded inverse flag logic

			if((string) $result->auto_publish == "f")
			{
				$data_source->setAttribute("manual_publish", "f");
			}
			else
			{
				$data_source->setAttribute("manual_publish", "t");
			}

			// Types remain "t" or "f"
			$data_source->setAttribute("allow_reverse_internal_links", $result->allow_reverse_internal_links);
			$data_source->setAttribute("allow_reverse_external_links", $result->allow_reverse_external_links);
			$data_source->setAttribute("qa_flag", $result->qa_flag);

			$data_source->setAttribute("create_primary_relationships", $result->create_primary_relationships);
			$data_source->setAttribute("primary_key_1", $result->primary_key_1);
			$data_source->setAttribute("class_1", $result->class_1);
			$data_source->setAttribute("collection_rel_1", $result->collection_rel_1);
			$data_source->setAttribute("party_rel_1", $result->party_rel_1);
			$data_source->setAttribute("activity_rel_1", $result->activity_rel_1);
			$data_source->setAttribute("service_rel_1", $result->service_rel_1);

			$data_source->setAttribute("primary_key_2", $result->primary_key_2);
			$data_source->setAttribute("class_2", $result->class_2);
			$data_source->setAttribute("collection_rel_2", $result->collection_rel_2);
			$data_source->setAttribute("party_rel_2", $result->party_rel_2);
			$data_source->setAttribute("activity_rel_2", $result->activity_rel_2);
			$data_source->setAttribute("service_rel_2", $result->service_rel_2);

			$data_source->setAttribute("institution_pages", $result->institution_pages);

			// No idea what this is doing..it looks exactly the same as the harvest date...
			$data_source->setAttribute("LEGACY-time_zone_value", $result->time_zone_value);


			if($result->data_source_key == 'PUBLISH_MY_DATA')
			{
				$data_source->setAttribute("qa_flag", "t");
			}

			$data_source->setSlug($data_source->title);
			$data_source->save();
			return $data_source;

	}


	function importDataSourceLogs($key, $id)
	{
		$this->source->select('*')->where('data_source_key', $key)->order_by('created_when', 'ASC');
		$query = $this->source->get($this->scpr . 'tbl_data_source_logs');

		if ($query->num_rows() > 0)
		{
			$this->db->delete('data_source_logs', array("data_source_id" => $id, "class" => "legacy_log"));

			foreach ($query->result_array() AS $result)
			{
				if ($result['event_description'])
				{
					$this->db->insert("data_source_logs", 
						array("data_source_id" => $id, 
							  "date_modified" => strtotime($result['created_when']), 
							  "type" => ($result['log_type'] == "INFO" ? "info" : "error"), 
							  "log" => $result['event_description'], 
							  "class" => "legacy_log")
					);
				}
			}
			echo "Included " . $query->num_rows() . " log entries." .NL;
			return $query->num_rows();
		}
		return 0;
	}



	function migrateRegistryObjectsForDatasource(_data_source $data_source)
	{
		$query = $this->source->get_where("dba.tbl_registry_objects", array("data_source_key"=>$data_source->key,"status"=>'PUBLISHED'));
		$num_records = $query->num_rows();
		if ($num_records < $this->recordLimitMin || $num_records > $this->recordLimitMax)
		{
			echo "[PUBLISHED RECORDS] Found ". $num_records . " records...skipping this datasource (".$this->recordLimitMin . " / " . $this->recordLimitMax.") " .NL;
			return;
		}

		echo "[PUBLISHED RECORDS] Found ". $num_records . " records..." .NL;

		$this->_CI->load->model("registry_object/registry_objects", "ro");
		$count = 0;
		$this->importer->_reset();

		$approved_records = array(); // approved records are now drafts, handle them seperately

		foreach ($query->result() AS $result)
		{
			$count++;			
			$this->importer->forcePublish();

			// Slug quarrels...
			if (strlen($result->url_slug) > 200)
			{
				$old_slug = $result->url_slug;
				$result->url_slug = substr($result->url_slug, 0 , 200); // must trim the slug for new system
			}
			else
			{
				$old_slug = null;
			}

			try
			{
				/* Get record RIFCS from raw_records table... */
				$this->source->where(array("registry_object_key"=>$result->registry_object_key, "data_source"=>$data_source->key))
							->order_by("created_when", "desc");

				$record_data_query = $this->source->get("dba.tbl_raw_records");
				$num_records = $query->num_rows();

				if ($num_records > 0)
				{
					$rifcs_count = 0;
					$this_ro_id = null;
					foreach ($record_data_query->result() AS $record_data_result)
					{
						$rifcs_count++;

						// Extract RIFCS XML
						$rifcs = $this->cleanRIFCSofEmptyTags($record_data_result->rifcs_fragment);
						$registryObjects = simplexml_load_string(wrapRegistryObjects(unWrapRegistryObjects($rifcs)));
						$registryObjects->registerXPathNamespace('rif', 'http://ands.org.au/standards/rif-cs/registryObjects');
						$registryObjectXML = $registryObjects->xpath('//rif:registryObject');
						if (!isset($registryObjectXML[0]))
						{
							throw new Exception("No RIFCS!");
						}
						$xml = wrapRegistryObjects(unWrapRegistryObjects($registryObjectXML[0]->asXML()));

						// First lot of record data...create the record
						if($rifcs_count == 1)
						{
								$this->importer->setXML($xml);

								if ($count == $num_records)
								{
									if ($this->noReindex)
									{
										$this->importer->setPartialCommitOnly(TRUE);
									}
									else
									{
										$this->importer->setPartialCommitOnly(FALSE);
									
											
									}
								}
								else
								{
									$this->importer->setPartialCommitOnly(TRUE);
								}

								$this->importer->setDatasource($data_source);
								$this->importer->commit();
								
								
								if ($this->importer->getMessages())
								{
									echo $this->importer->getMessages() . NL;
								}
								

								$registryObject = $this->ro->getPublishedByKey($result->registry_object_key);
								if ($registryObject)
								{
									$registryObject->record_owner = $result->created_who;
									if ($result->record_owner != "SYSTEM")
									{
										$registryObject->created_who = "Harvester"; // extract the harvest ID
										$registryObject->harvest_id = $result->record_owner;
									}
									else
									{
										$registryObject->created_who = $result->created_who;
										$registryObject->harvest_id = "MANUAL-R9-IMPORT";
									}
									
									$registryObject->manually_assessed = ($result->manually_assessed_flag == 0 ? "no" : "yes");
									
									if ($result->gold_status_flag == 1)
									{
										$registryObject->gold_status_flag = "t";
									}
									
									$registryObject->flag = $result->flag;
									$registryObject->created = strtotime($result->created_when);
									$registryObject->updated = max($result->registry_date_modified, strtotime($result->status_modified_when));

									// Update the raw record version too...
									$this->db->where('registry_object_id', $registryObject->id);
									$this->db->update('record_data', array("timestamp"=>$registryObject->updated)); 

									// If we have a slug conflict
									// (the newly generated slug doesn't match the original in the database)
									if ($registryObject->slug != $result->url_slug)
									{
										$this->db->where(array('slug'=>$result->url_slug));
										$slug_query = $this->db->get('url_mappings');
										if ($slug_query->num_rows() == 0)
										{
											$this->db->insert('url_mappings', array(
												"slug"=>$result->url_slug,
												"registry_object_id"=>$registryObject->id,
												"created"=>time(),
												"updated"=>time()
											));
										}
										else
										{
											$this->db->where(array("slug"=>$result->url_slug));
											$this->db->update('url_mappings', array(
												"registry_object_id"=>$registryObject->id,
												"updated"=>time()
											));
										}
									}


									// Save (But don't update the "updated" timestamp!)
									$registryObject->save(false);

									// Slug checking (on old slugs pointing to this registry object)
									$slug_query = $this->source->get_where('dba.tbl_url_mappings', 
												array('registry_object_key' => $registryObject->key, 'url_fragment !=' => ($old_slug ?: $result->url_slug)));

									if ($slug_query->num_rows() > 0)
									{
										foreach ($slug_query->result() AS $slug)
										{
											$slug->url_fragment =  trim(substr($slug->url_fragment, 0, 200));
											$this->db->where(array('slug'=>$slug->url_fragment));
											$slug_query2 = $this->db->get('url_mappings');
											if ($slug_query2->num_rows() == 0)
											{
												$this->db->insert('url_mappings', array(
													"slug"=>$slug->url_fragment,
													"registry_object_id"=>$registryObject->id,
													"created"=>time(),
													"updated"=>time()
												));
											}
											else
											{
												$this->db->where(array("slug"=>$slug->url_fragment));
												$this->db->update('url_mappings', array(
													"registry_object_id"=>$registryObject->id,
													"updated"=>time()
												));
											}
										}
									}



									echo "*";
									$this_ro_id = $registryObject->id;
									unset($registryObject);
								}
								else
								{
									throw new Exception("Appears that the record was not successfully created? Could not load after import!" . $xml);
								}
						}
						else
						{
							if (!is_null($this_ro_id) && $xml)
							{
								// Subsequent record data...just add an entry to the record_data table directly (no importer action required)
								$this->db->insert("record_data", 
									array("registry_object_id" => $this_ro_id, 
										  "current" => "", 
										  "data" => $xml, 
										  "timestamp" => strtotime($record_data_result->created_when), 
										  "scheme" => "rif",
										  "hash"=>md5($xml))
								);
							}
							else
							{
								echo "Failed to insert additional RO version data for this registry object..." . NL;
							}
						}
					}

				}	
				else
				{
					throw new Exception("No record data could be retrieved from raw_records table...");
				}

			}
			catch (Exception $e)
			{
				echo "Unable to import record: " . $result->registry_object_key . "(" . $e->getMessage() . ")" .NL;
			}
			
		}
		echo NL;

		/* Handle the approved records */
		// Very similar to above, except not published, no SLUG logic. 
		$query = $this->source->get_where("dba.tbl_registry_objects", array("data_source_key"=>$data_source->key,"status"=>'APPROVED'));
		$num_records = $query->num_rows();
		if ($num_records > $this->recordLimitMax) {
			echo "[APPROVED] Found ". $num_records . " records...skipping this datasource (>" . $this->recordLimitMax . ") " .NL;
			return;
		}

		echo "[APPROVED RECORDS] Found ". $num_records . " records..." .NL;

		$this->_CI->load->model("registry_object/registry_objects", "ro");
		$count = 0;
		$this->importer->_reset();
		$this->importer->forceDraft();

		foreach ($query->result() AS $result)
		{
			$count++;

			try
			{
				/* Get record RIFCS from raw_records table... */
				$this->source->where(array("registry_object_key"=>$result->registry_object_key, "data_source"=>$data_source->key))
							->order_by("created_when", "desc");

				$record_data_query = $this->source->get("dba.tbl_raw_records");
				$num_records = $query->num_rows();

				if ($num_records > 0)
				{
					$rifcs_count = 0;
					$this_ro_id = null;
					foreach ($record_data_query->result() AS $record_data_result)
					{
						$rifcs_count++;

						// First lot of record data...create the record
						if($rifcs_count == 1)
						{
							
							$rifcs = $this->cleanRIFCSofEmptyTags($record_data_result->rifcs_fragment);
							$registryObjects = simplexml_load_string(wrapRegistryObjects(unWrapRegistryObjects($rifcs)));
							$registryObjects->registerXPathNamespace('rif', 'http://ands.org.au/standards/rif-cs/registryObjects');
							$registryObjectXML = $registryObjects->xpath('//rif:registryObject');
							if (count($registryObjectXML) == 0)
							{
								throw new Exception("No RIFCS!");
							};
							$xml = wrapRegistryObjects($registryObjectXML[0]->asXML());

							// Record size check - massive records (such as QFAB) have all their relatedObjects trimmed off...
							if (strlen($xml) > 50000)
							{
								$xml = preg_replace('/<relatedObject.*?<\/relatedObject>|\s{2,}/ms', '', $xml);
								if (strlen($xml) > 50000)
								{
									echo "Skipping registry object - XML contents too large";
								}
							}
							
							$this->importer->setXML($xml);


							if ($count == $num_records)
							{
								$this->importer->setPartialCommitOnly(FALSE);
							}
							else
							{
								$this->importer->setPartialCommitOnly(TRUE);
							}
							$this->importer->setDatasource($data_source);
							$this->importer->commit();

							$registryObject = $this->ro->getDraftByKey($result->registry_object_key);
							if ($registryObject)
							{
								$registryObject->record_owner = $result->created_who;
								if ($result->record_owner != "SYSTEM")
								{
									$registryObject->created_who = "Harvester"; // extract the harvest ID
									$registryObject->harvest_id = $result->record_owner;
								}
								else
								{
									$registryObject->created_who = $result->created_who;
									$registryObject->harvest_id = "MANUAL-R9-IMPORT";
								}
								
								$registryObject->manually_assessed = ($result->manually_assessed_flag == 0 ? "no" : "yes");
								
								if ($result->gold_status_flag == 1)
								{
									$registryObject->gold_status_flag = "t";
								}

								$registryObject->original_status = $result->status;
								$registryObject->status = $result->status;

								$registryObject->flag = $result->flag;
								$registryObject->created = strtotime($result->created_when);
								$registryObject->updated = max($result->registry_date_modified, strtotime($result->status_modified_when));
								// Update the raw record version too...
								$this->db->where('registry_object_id', $registryObject->id);
								$this->db->update('record_data', array("timestamp"=>$registryObject->updated));

								// Save without updating the "updated" date...
								$registryObject->save(false);
								$this_ro_id = $registryObject->id;
								unset($registryObject);
							}
							else{
								echo "FAILED :-(";
							}

							if (!is_null($this_ro_id) && $xml)
							{
								// Subsequent record data...just add an entry to the record_data table directly (no importer action required)
								$this->db->insert("record_data", 
									array("registry_object_id" => $this_ro_id, 
										  "current" => "", 
										  "data" => $xml, 
										  "timestamp" => strtotime($record_data_result->created_when), 
										  "scheme" => "rif",
										  "hash"=>md5($xml))
								);
							}
							else{
								echo $this->importer->getErrors();
								echo $this->importer->getMessages();
							}
						}
					}
				}
			}
			catch (Exception $e)
			{
				echo "Unable to import draft: " . $result->draft_key . "(" . $e->getMessage() . ")" .NL;
			}			
		}
		$this->importer->_reset();
	}



	function migrateDeletedRegistryObjectsForDatasource(_data_source $data_source)
	{
		$query = $this->source->query("SELECT rr.registry_object_key AS deleted_key, rifcs_fragment, rr.created_when FROM dba.tbl_raw_records rr LEFT JOIN dba.tbl_registry_objects r on r.registry_object_key = rr.registry_object_key WHERE r.registry_object_key IS NULL AND rr.data_source = ?", array($data_source->key));
		$num_records = $query->num_rows();

		if ($num_records < $this->recordLimitMin || $num_records > $this->recordLimitMax)
		{
			echo "[DELETED RECORDS] Found ". $num_records . " records...skipping this datasource (".$this->recordLimitMin . " / " . $this->recordLimitMax.") " .NL;
			return;
		}
		
		echo "[DELETED RECORDS] Found ". $num_records . " deleted records to migrate..." .NL;
		if ($num_records == 0) return; 

		foreach ($query->result_array() AS $result)
		{
			$xml = wrapRegistryObjects(unWrapRegistryObjects($result['rifcs_fragment']));
			// Record size check - massive records (such as QFAB) have all their relatedObjects trimmed off...
			if (strlen($xml) > 50000)
			{
				$xml = trim(preg_replace('/<relatedObject.*?<\/relatedObject>|\s{2,}/ms', '', $xml));
				if (strlen($xml) > 50000)
				{
					echo "XML contents too large!!";
					continue;
				}
			}
			
			$this->db->insert("deleted_registry_objects", 
				array("data_source_id" => $data_source->id, 
					  "key" => $result['deleted_key'], 
					  "deleted" => strtotime($result['created_when']), 
					  "title" => $result['deleted_key'],
					  "record_data" => $xml)
			);
		}

	}

	function migrateDraftRegistryObjectsForDatasource(_data_source $data_source)
	{
		$this->load->library('importer');

		$query = $this->source->get_where("dba.tbl_draft_registry_objects", array("registry_object_data_source"=>$data_source->key));
		$num_records = $query->num_rows();

		if ($num_records < $this->recordLimitMin || $num_records > $this->recordLimitMax)
		{
			echo "[DRAFT RECORDS] Found ". $num_records . " records...skipping this datasource (".$this->recordLimitMin . " / " . $this->recordLimitMax.") " .NL;
			return;
		}

		echo "[DRAFT RECORDS] Found ". $num_records . " draft records to migrate..." .NL;

		$count = 0;
		foreach ($query->result() AS $result)
		{
			$count++;

			// echo "Importing Draft Record: " . $result->draft_key . "." .NL;

			try
			{
				$rifcs = $this->cleanRIFCSofEmptyTags($result->rifcs);
				$registryObjects = simplexml_load_string(wrapRegistryObjects(unWrapRegistryObjects($rifcs)));
				$registryObjects->registerXPathNamespace('rif', 'http://ands.org.au/standards/rif-cs/registryObjects');
				$registryObjectXML = $registryObjects->xpath('//rif:registryObject');
				if (!isset($registryObjectXML[0]))
				{
					throw new Exception("No registryObject found in RIF namespace!");
				}
				$xml = wrapRegistryObjects($registryObjectXML[0]->asXML());

				$this->importer->setXML($xml);
				$this->importer->forceDraft();

				if ($count == $num_records)
				{
					$this->importer->setPartialCommitOnly(FALSE);
				}
				else
				{
					$this->importer->setPartialCommitOnly(TRUE);
				}
				$this->importer->setDatasource($data_source);
				$this->importer->commit();


				$registryObject = $this->ro->getDraftByKey($result->draft_key);
				if ($registryObject)
				{
					$registryObject->record_owner = $result->draft_owner;
					$registryObject->original_status = $result->status;
					$registryObject->status = $result->status;
					
					$registryObject->flag = $result->flag;
					$registryObject->created_who = $result->draft_owner;
					$registryObject->created = strtotime($result->date_created);
					$registryObject->updated = strtotime($result->date_modified);

					// Save without updating the "updated" date...
					$registryObject->save(false);
					unset($registryObject);
				}
			}
			catch (Exception $e)
			{
				echo "Unable to import draft: " . $result->draft_key . "(" . $e->getMessage() . ")" .NL;
			}

		}
		$this->importer->_reset();
	}



	function cleanRIFCSofEmptyTags($rifcs, $removeFormAttributes='true'){
	    if (strlen($rifcs) > 50000) {
		$rifcs = preg_replace('/<relatedObject.*?<\/relatedObject>|\s{2,}/ms', '', $rifcs);
	    }
	    $xslt_processor = Transforms::get_form_to_cleanrif_transformer();
	    $dom = new DOMDocument();
	    //$dom->loadXML($this->ro->getXML());
	    $dom->loadXML(wrapRegistryObjects(unWrapRegistryObjects($rifcs)));
	    //$dom->loadXML($rifcs);
	    $xslt_processor->setParameter('','removeFormAttributes',$removeFormAttributes);
	    return $xslt_processor->transformToXML($dom);
	}



	function deleteAllrecordsForDataSource(_data_source $data_source)
	{
		$this->_CI->load->model("registry_object/registry_objects", "rox");
		$ids = $this->rox->getIDsByDataSourceID($data_source->id, false);
		if($ids)
		{
			foreach($ids as $ro_id){
			$ro = $this->rox->getByID($ro_id);
			if($ro)
				$ro->eraseFromDatabase();
			}
		}
	}

	function __construct()
    {
            parent::__construct();
            
            $this->input = fopen ("php://stdin","r");
            $this->start_time = microtime(true);
			$this->_CI =& get_instance();
            $this->source = $this->load->database('migration', true);

            define('IS_CLI_SCRIPT', true);

    }

    function __destruct() {
       print "Execution finished! Took " . sprintf ("%.3f", (float) (microtime(true) - $this->exec_time)) . "s" . NL;
   	}


   	private function getInput()
	{
		if (is_resource(($this->input)))
		{
			return trim(fgets($this->input));
		}
	}


	function cli_error_handler($number, $message, $file, $line, $vars)
	{
		echo NL.NL.str_repeat("=", 15);
     	echo NL .NL . "An error ($number) occurred on line $line in the file: $file:" . NL;
        echo $message . NL . NL;
        echo str_repeat("=", 15) . NL . NL;
        return true;
       //"<pre>" . print_r($vars, 1) . "</pre>";

        // Make sure that you decide how to respond to errors (on the user's side)
        // Either echo an error message, or kill the entire project. Up to you...
        // The code below ensures that we only "die" if the error was more than
        // just a NOTICE.
        //if ( ($number !== E_NOTICE) && ($number < 2048) ) {
          //  die("Exiting on error...");
        //}

	}
}
