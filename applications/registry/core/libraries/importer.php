<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
include_once("applications/registry/registry_object/models/_transforms.php");
/**
 * 
 */
class Importer {
	
	private $CI;
	private $db;

	private $xmlPayload;
	private $nativePayload;
	private $crosswalk;
	private $harvestID;
	private $dataSource;
	private $filePath;
	private $nativePath;
	private $start_time;
	private $partialCommitOnly;
	private $forcePublish; // used when changing from DRAFT to PUBLISHED (ignore the QA flags, etc)
	private $forceDraft; 
	private $maintainStatus;
	public $registryMode;
	public $runBenchMark = false;
	private $status; // status of the currently ingested record

	private $benchMarkLog;
	public $isImporting = false; // flag stating whether the importer is running
	private $importedRecords;

	public $ingest_attempts;
	public $ingest_successes;
	public $ingest_failures;

	public $ingest_new_revision;
	public $ingest_duplicate_ignore;
	public $ingest_new_record;

	public $reindexed_records;
	
	public $imported_record_keys;
	public $affected_record_keys;
	public $deleted_record_keys;

	public $standardLog;
	public $statusAlreadyChanged = false;

	public $error_log = array();
	public $message_log = array();

	private $valid_classes = array("collection","party","activity","service");
	private $solrReqCount;
	private $solrReqTime;
	private $solrTransFormCount;
	private $solrTransFormTime;
	private $roBuildCount;
	private $roBuildTime;
	private $roEnrichCount;
	private $roEnrichTime;
	private $roEnrichS1Time;
	private $roEnrichS2Time;
	private $roEnrichS3Time;
	private $roEnrichS4Time;
	private $roEnrichS5Time;
	private $roEnrichS6Time;
	private $roEnrichS7Time;
	private $roQACount;
	private $roQATime;
	private $roQAS1Time;
	private $roQAS2Time;
	private $roQAS3Time;
	private $roQAS4Time;
	private $cCyclesCount;
	private $cCyclesTime;




	public function Importer()
	{
		$this->CI =& get_instance();

		// This is not a perfect science... the web server can still 
		// reclaim the worker thread and terminate the PHP script execution....
		ini_set('memory_limit', '2048M');
		ini_set('max_execution_time',5*ONE_HOUR);

		set_time_limit(0);
		ignore_user_abort(true);

		// setup the DB connection
		$this->db = $this->CI->db;

		// Initialise our variables
		$this->_reset();
	}


	/**
	 * 
	 */
	public function commit($finalise = true)
	{
		// Enable memory profiling...
		//ini_set('xdebug.profiler_enable',1);
		//xdebug_enable();
		if($this->registryMode == "read-only")
		{
			throw new Exception("The Registry currently set to Read-Only mode, no changes will be saved!");
		}

		if($this->runBenchMark){
			$this->CI->benchmark->mark('ingest_start');
		}

		$this->isImporting = true;

		//$this->CI->output->enable_profiler(TRUE);
		if (is_null($this->start_time))
		{
			$this->start_time = microtime(true);
		}

		// Some sanity checks
		if (!($this->dataSource instanceof _data_source))
		{
			throw new Exception("No valid data source selected before import commit.");
			$this->isImporting = false;
		}

		if($this->runBenchMark){
			$this->CI->benchmark->mark('crosswalk_execution_start');
		}
			// Apply the crosswalk (if applicable)
		$this->_executeCrosswalk();

		if($this->runBenchMark){
			$this->CI->benchmark->mark('crosswalk_execution_end');
		}

		// Set a a default HarvestID if necessary
		if (is_null($this->harvestID)) 
		{
			$this->harvestID = "MANUAL-".time();
		}

		// Decide on the default status for these records
            $this->status = $this->_getDefaultRecordStatusForDataSource($this->dataSource);

            if($this->runBenchMark){
                $this->CI->benchmark->mark('ingest_stage_1_start');
            }
			//foreach ($this->xmlPayload AS $idx => $payload)
			//{
				// Clean up non-UTF8 characters by trying to translate them

				// If we have php-mbstring enabled, convert to UTF-8 (fixes crash on curly quotes!)
				if (function_exists('mb_convert_encoding'))
				{
					$payload = mb_convert_encoding($this->xmlPayload,"UTF-8");
				}
				else
				{
					die('php mbstring must be installed.');
				}

                // unescape (some entities are double escaped) first
                while(strpos($payload,'&amp;') !== false)
                {
                    $payload = str_replace("&amp;", "&", $payload);
                }
                $payload = str_replace("&", "&amp;", $payload);


				$continueIngest = true;				
				// Build a SimpleXML object from the converted data
				// We will throw an exception here if the payload isn't well-formed XML (which, by now, it should be)
				try
				{
					$payload = $this->cleanNameSpace($payload);
					$sxml = $this->_getSimpleXMLFromString($payload);
				}
				catch (Exception $e)
				{
					//$this->isImporting = false;
					$this->ingest_failures++;
					$continueIngest = false;
					//$this->error_log[] = "Unable to parse XML into object (registryObject #".($idx+1)."): " . NL . $e->getMessage();
					throw new Exception("Unable to parse XML into object".NL.$e->getMessage());
				}
				if($continueIngest)
				{
				// Last chance to check valid format of the payload
					$reValidateBeforeIngest = false;
				    try{
						$this->validateRIFCS($payload);
					}
					catch(Exception $e)
					{
						$reValidateBeforeIngest = true;
						$this->error_log[] = "Error whilst ingesting payload" . ": " . $e->getMessage() .NL;
					}

					$sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);

					// Right then, lets start parsing each registryObject & importing! 
					foreach($sxml->xpath('//ro:registryObject') AS $registryObject)
					{

						$this->ingest_attempts++;
						$continueWithIngest = true;
						if($reValidateBeforeIngest)
						{
							try{
								$this->validateRIFCS(wrapRegistryObjects($registryObject->asXML()));
							}
							catch(Exception $e)
							{
								$continueWithIngest = false;
								$this->error_log[] = "Error whilst ingesting record #" . $this->ingest_attempts . ": " . $e->getMessage() .NL;
							}
						}

						if($continueWithIngest)
						{
							if((string)$registryObject->key == '')
							{
								$this->ingest_failures++;
								$this->error_log[] = "Error whilst ingesting record #" . $this->ingest_attempts . ": " . "Registry Object 'key' must have a value";
							}
							elseif((string)$registryObject->originatingSource == '')
							{
								$this->ingest_failures++;
								$this->error_log[] = "Error whilst ingesting record #" . $this->ingest_attempts . ": " . "Registry Object 'originatingSource' must have a value";
							}
							elseif((string)$registryObject['group'] == '')
							{
								$this->ingest_failures++;
								$this->error_log[] = "Error whilst ingesting record #" . $this->ingest_attempts . ": " . "Registry Object '@group' must have a value";

							}
							else
							{
								try
								{
									$this->_ingestRecord($registryObject);
								}
								catch (Exception $e)
								{
									$this->ingest_failures++;
									$this->error_log[] = "Error whilst ingesting record #" . $this->ingest_attempts . ": " . $e->getMessage();
								}
							}
						}
					}
				}

			//}
		if($this->runBenchMark){
			$this->CI->benchmark->mark('ingest_stage_1_end');
		}

		$this->isImporting = false;
		if($this->runBenchMark){
			$this->CI->benchmark->mark('ingest_end');
		}

		if($finalise)
		{
			$taskLog = $this->finishImportTasks();
			if($this->runBenchMark){
				// $this->dataSource->append_log($taskLog, IMPORT_INFO, "importer","IMPORT_INFO");
			}
		}

	}

	private function populateMessageLog() {
		$time_taken = sprintf ("%.3f", (float) (microtime(true) - $this->start_time));
		$this->message_log[] = NL;
		$this->message_log[] = "Import complete! Took " . ($time_taken) . "s...";
		$this->message_log[] = "Registry Object(s) in feed: " . $this->ingest_attempts;
		$this->message_log[] = "Registry Object(s) created: " . $this->ingest_new_record;
		$this->message_log[] = "Registry Object(s) updated: " . $this->ingest_new_revision;
		$this->message_log[] = "Registry Object(s) deleted: " . count($this->deleted_record_keys);

		if ($this->ingest_failures) {
			$this->message_log[] = "Registry Object(s) failed : " . $this->ingest_failures;
		}
		if ($this->ingest_duplicate_ignore) {
			$this->message_log[] = "Registry Object duplicates: " . $this->ingest_duplicate_ignore;
		}
		
		if($this->CI->user->hasFunction('REGISTRY_SUPERUSER')) {
			$this->message_log[] = "Reindexed record count: " . $this->reindexed_records;
		}

		$this->message_log[] = $this->standardLog;
	}


	/**
	 * 
	 */
	public function _ingestRecord($registryObject)
	{
		$this->CI->load->model('registry_object/registry_objects', 'ro');
		$this->CI->load->model('data_source/data_sources', 'ds');

		foreach ($this->valid_classes AS $class)
		{
			if (property_exists($registryObject, $class))
			{
				
				$ro_xml =& $registryObject->{$class}[0];
	

				// Choose whether or not to harvest this record and whether this should overwrite 
				// the existing entry or just create a new revision
				list($reharvest, $revision_record_id) = $this->decideHarvestability($registryObject);

				if($reharvest)
				{

					// Clean up crosswalk XML if applicable
					$ro_xml->registerXPathNamespace("ro", RIFCS_NAMESPACE);

					$nativeHarvestIdx = null;
					$idx = 0;

					foreach($ro_xml->relatedInfo AS $relatedInfo)
					{
						if((string)$relatedInfo['type'] == NATIVE_HARVEST_FORMAT_TYPE)
						{
							$nativeHarvestIdx = $idx;
						}
						$idx++;
					}

					// This is a post-crosswalk record, lets extract the native data and store it!
					if(!is_null($nativeHarvestIdx))
					{
						// Extract
						$nativeSchemaFormat = (string)$ro_xml->relatedInfo[$nativeHarvestIdx]->identifier[0];
						$nativeData = trim((string) $ro_xml->relatedInfo[$nativeHarvestIdx]->notes[0]);

						// Delete the temporary node from the registry object
						unset($ro_xml->relatedInfo[$nativeHarvestIdx]);
					}

					//  Record owner should only be system if this is a harvest
					$record_owner = "SYSTEM";
					if($this->CI->user->isLoggedIn())
					{
						$record_owner = $this->CI->user->name() . " (" . $this->CI->user->localIdentifier() . ")";
					}

					if (is_null($revision_record_id))
					{
						// We are creating a new registryObject
						$ro = $this->CI->ro->create($this->dataSource, (string)$registryObject->key, $class, "", $this->status, "temporary_slug-" . md5((string) $registryObject->key) . "-" . time(), $record_owner, $this->harvestID);

						// if this is ds has the qa flag set we need to check if this is the first submitted for assesmment record and if so email the notify address
						if($this->dataSource->qa_flag===DB_TRUE && $this->ingest_new_record<1 && !$this->forceDraft && $this->dataSource->assessment_notify_email_addr)
						{
							$this->CI->ro->emailAssessor($this->dataSource);
						}
                        $this->ingest_new_record++;
					}
					else
					{
						// The registryObject exists, just add a new revision to it?
						$ro = $this->CI->ro->getByID($revision_record_id);

						if (!$this->maintainStatus)
						{	
							// GEt rid of status change recursion on DRAFT->PUBLISHED
							if($this->statusAlreadyChanged)
							{
								$ro->original_status = $this->status;
							}
							
							// Records which have already progressed through the QA workflow will be reharvested back to existing status
							// i.e. if the record already exists, leave it's status unchanged
							$ro->status = $this->status;

							// Trigger a save on the new registryObject (to make handleStatusChange get called here)
							$ro->save(); // this will cause the $ro pointer to be updated to the "active" version of the record
						}

						$ro->record_owner = $record_owner;
                        $this->ingest_new_revision++;
					}

					$ro->class = $class;
					$ro->created_who = $record_owner;
					$ro->data_source_key = $this->dataSource->key;
					$ro->group = (string) $registryObject['group'];
					$ro->type = (string) $ro_xml['type'];

					if($this->filePath) $ro->file_path = $this->filePath;
					if($this->nativePath) $ro->native_path = $this->nativePath;

					// Clean up all previous versions (set = FALSE, "prune" extRif)
					$ro->cleanupPreviousVersions();

					// Store the native format, if we had one
					if (isset($nativeSchemaFormat) && isset($nativeData))
					{
						$ro->updateXML($nativeData, TRUE, $nativeSchemaFormat);
						unset($nativeSchemaFormat);
						unset($nativeData);
					}

					// Order is important here!
					$changed = $ro->updateXML(wrapRegistryObjects($registryObject->asXML()));

					// Generate the list and display titles first, then the SLUG
					$ro->updateTitles($ro->getSimpleXML());

					// Only generate SLUGs for published records
					$ro->setAttribute("harvest_id", $this->harvestID);
					$ro->generateSlug();
					if (in_array($this->status, getPublishedStatusGroup()))
					{
						$ro->generateSlug();
					}
					else
					{
						$ro->slug = DRAFT_RECORD_SLUG . $ro->id;
						$ro->setAttribute("manually_assessed", 'no');
					}

					// Save all our attributes to the object
                    //TODO:
					$ro->save($changed);
                    /*
					//All related objects by any means are affected regardless
					$related_objects = $ro->getAllRelatedObjects(false, true, true);
					$related_keys = array();
					foreach($related_objects as $rr){
						$related_keys[] = $rr['key'];
					}
					$this->addToAffectedList($related_keys);

					// Also treat identifier matches as affected records which need to be enriched
					// (to increment their extRif:matching_identifier_count)
					$related_ids_by_identifier_matches = $ro->findMatchingRecords(); // from ro/extensions/identifiers.php
					$related_keys = array();
					foreach($related_ids_by_identifier_matches AS $matching_record_id){
						$matched_ro = $this->CI->ro->getByID($matching_record_id);
						$related_keys[] = $matched_ro->key;
					}
					if (count($related_keys)){
						$this->addToAffectedList($related_keys);
					}
*/
                    // if the rif-cs didn't change we don't need to enrich record.
                    if($changed)
                    {
					    $ro->processIdentifiers();
					    // Add this record to our counts, etc.
					    $this->importedRecords[] = $ro->id;
                    }
                    $this->ingest_successes++;
					// Memory management...
					unset($ro);
					clean_cycles();
				}

			}
		}

		unset($sxml);
		unset($xml);
		//gc_collect_cycles();
	}

	/**
	 * 
	 */
	public function _enrichRecords($directly_affected_records = array())
	{
		$this->CI->load->model('registry_object/registry_objects', 'ro');

		if($this->runBenchMark){
			$this->CI->benchmark->mark('ingest_enrich_stage1_start');
		}

		if (is_array($directly_affected_records) && count($directly_affected_records) > 0)
		{
			foreach ($directly_affected_records AS $ro_key)
			{
				$registryObjects = $this->CI->ro->getAllByKey($ro_key);

				if (is_array($registryObjects))
				{
					foreach ($registryObjects AS $ro)
					{
						$ro->addRelationships();
						$ro->update_quality_metadata();
						// $ro->enrich();
						unset($ro);
						clean_cycles();
					}
				}
			}
		}
		
		else if(is_array($this->importedRecords) && count($this->importedRecords) > 0)
		{

			foreach ($this->importedRecords AS $ro_id)
			{

				$ro = $this->CI->ro->getByID($ro_id);

				// add reverse relationships
				// previous relationships are reset by this call
				if($ro)
				{
					$ro->addRelationships();
					$related_keys = $ro->getRelatedKeys();
					// directly affected records are re-enriched below (and reindexed...)
					// we consider any related record keys to be directly affected and reindex them...
					//$this->addToAffectedList($related_keys);

					// All related objects by any means are affected
					//$related_objects = $ro->getAllRelatedObjects(false, true, true);
					//$related_keys = array();
					//foreach($related_objects as $rr){
					//	$related_keys[] = $rr['key'];
					//}
					//$this->addToAffectedList($related_keys);

					// Also treat identifier matches as affected records which need to be enriched
					// (to increment their extRif:matching_identifier_count)
					//$related_ids_by_identifier_matches = $ro->findMatchingRecords(); // from ro/extensions/identifiers.php
					//$related_keys = array();
					//foreach($related_ids_by_identifier_matches AS $matching_record_id){
					//	$matched_ro = $this->CI->ro->getByID($matching_record_id);
					//	$related_keys[] = $matched_ro->key;
					//}
					if (count($related_keys)){
						$this->addToAffectedList($related_keys);
					}



					// Keep track of our imported keys...
					$this->imported_record_keys[] = $ro->key;
					unset($ro);
				}
				clean_cycles();
			}

		}

		if($this->runBenchMark){
			 $this->CI->benchmark->mark('ingest_enrich_stage1_end');
		}

	}

	public function finishImportTasks() {
		$this->populateMessageLog();
		$importedRecCount = count($this->importedRecords);
		$this->_enrichRecords();
		$deletedCount = count($this->deleted_record_keys);
		$this->_enrichAffectedRecords();
		$this->_indexAllAffectedRecords();
		$indexedAllCount = count($this->affected_record_keys);
		return "Finished Import Tasks".NL."imported: ".$importedRecCount.NL."affected/related ".($indexedAllCount - $importedRecCount).NL."total: ".$indexedAllCount.NL."deleted: ".$deletedCount;
	}

	public function _enrichAffectedRecords()
	{
		$this->CI->load->model('registry_object/registry_objects', 'ro');
		if($this->runBenchMark){
			$this->CI->benchmark->mark('enrich_affected_records_start');
		}
		$this->affected_record_keys = array_unique(array_merge($this->imported_record_keys, $this->affected_record_keys));
		$this->affected_record_keys = array_unique(array_diff($this->affected_record_keys, $this->deleted_record_keys));
		foreach ($this->affected_record_keys AS $ro_key)
		{
			$registryObjects = $this->CI->ro->getAllByKey($ro_key);
			if (is_array($registryObjects))
			{
				foreach ($registryObjects AS $ro)
				{
                    //imported records already got their relationships handled
                    if(!in_array($ro->key, $this->imported_record_keys))
                    {
                        try {
                            $ro->addRelationships();
                        } catch (Exception $e) {
                            throw new Exception($e);
                        }
                    }
					if($this->runBenchMark)
					{
						$this->roQACount++;
						$this->CI->benchmark->mark('ro_qa_start');
					}

					$ro->update_quality_metadata($this->runBenchMark);

					if($this->runBenchMark)
					{
						$this->CI->benchmark->mark('ro_qa_end');
						$this->roQATime += $this->CI->benchmark->elapsed_time('ro_qa_start','ro_qa_end');
						$this->roQAS1Time += $this->CI->benchmark->elapsed_time('ro_qa_start','ro_qa_s1_end');
						$this->roQAS2Time += $this->CI->benchmark->elapsed_time('ro_qa_s1_end','ro_qa_s2_end');
						$this->roQAS3Time += $this->CI->benchmark->elapsed_time('ro_qa_s2_end','ro_qa_s3_end');
						$this->roQAS4Time += $this->CI->benchmark->elapsed_time('ro_qa_s3_end','ro_qa_end');
					}

					if($this->runBenchMark)
					{
						$this->roEnrichCount++;
						$this->CI->benchmark->mark('ro_enrich_start');
					}

					try{
						$ro->enrich($this->runBenchMark);
					}catch (Exception $e){
						throw new Exception($e);
					}

					if($this->runBenchMark)
					{
						$this->CI->benchmark->mark('ro_enrich_end');
						$this->roEnrichTime += $this->CI->benchmark->elapsed_time('ro_enrich_start','ro_enrich_end');
						$this->roEnrichS1Time += $this->CI->benchmark->elapsed_time('ro_enrich_start','ro_enrich_s1_end');
						$this->roEnrichS2Time += $this->CI->benchmark->elapsed_time('ro_enrich_s1_end','ro_enrich_s2_end');
						$this->roEnrichS3Time += $this->CI->benchmark->elapsed_time('ro_enrich_s2_end','ro_enrich_s3_end');
						$this->roEnrichS4Time += $this->CI->benchmark->elapsed_time('ro_enrich_s3_end','ro_enrich_s4_end');
						$this->roEnrichS5Time += $this->CI->benchmark->elapsed_time('ro_enrich_s4_end','ro_enrich_s5_end');
						$this->roEnrichS6Time += $this->CI->benchmark->elapsed_time('ro_enrich_s5_end','ro_enrich_s6_end');
						$this->roEnrichS7Time += $this->CI->benchmark->elapsed_time('ro_enrich_s6_end','ro_enrich_end');
					}		

					unset($ro);
					clean_cycles();
				}
			}
		}

		if($this->runBenchMark){
			$this->CI->benchmark->mark('enrich_affected_records_end');
		}

	}


	public function _indexAllAffectedRecords()
	{
		$this->CI->load->model('registry_object/registry_objects', 'ro');
		$this->affected_record_keys = array_unique(array_merge($this->imported_record_keys, $this->affected_record_keys));
		$this->affected_record_keys = array_unique(array_diff($this->affected_record_keys, $this->deleted_record_keys));
		if($this->runBenchMark){
			$this->CI->benchmark->mark('ingest_reindex_start');
		}

		foreach ($this->affected_record_keys AS $ro_key)
		{
			
			if($this->runBenchMark)
			{
				$this->roBuildCount++;
				$this->CI->benchmark->mark('ro_build_start');
			}

			$registryObjects = $this->CI->ro->getAllByKey($ro_key);

			if($this->runBenchMark)
			{
				$this->CI->benchmark->mark('ro_build_end');
				$this->roBuildTime += $this->CI->benchmark->elapsed_time('ro_build_start','ro_build_end');
			}

			if (is_array($registryObjects))
			{
				foreach ($registryObjects AS $ro)
				{
					if ($ro->status == PUBLISHED)
					{
						if($this->runBenchMark)
						{
							$this->solrTransFormCount++;
							$this->CI->benchmark->mark('solr_transform_start');
						}
						
						$this->queueSOLRAdd($ro->indexable_json());
						
						if($this->runBenchMark)
						{
							$this->CI->benchmark->mark('solr_transform_end');
							$this->solrTransFormTime += $this->CI->benchmark->elapsed_time('solr_transform_start','solr_transform_end');
						}
					}
					if($this->runBenchMark)
					{
						$this->gcCyclesCount++;
						$this->CI->benchmark->mark('gc_cycles_start');
					}
				
					unset($ro);
			//gc_collect_cycles();
				
					if($this->runBenchMark)
					{
						$this->CI->benchmark->mark('gc_cycles_end');
						$this->gcCyclesTime += $this->CI->benchmark->elapsed_time('gc_cycles_start','gc_cycles_end');
					}
				}
			}
		}

		// Push through the last chunk...
		$this->flushSOLRAdd();
		if($this->runBenchMark)
		{
			$this->CI->benchmark->mark('solr_commit_start');
		}
		
		$this->commitSOLR();
		
		if($this->runBenchMark)
		{
			$this->CI->benchmark->mark('solr_commit_end');
			$this->CI->benchmark->mark('ingest_reindex_end');
		}
	}

	/**
	 *
	 */
	function _reindexRecords($specific_target_keys = array())
	{
		

		$this->CI->load->model('registry_object/registry_objects', 'ro');
		$this->CI->load->model('data_source/data_sources', 'ds');

		$this->CI->load->library('solr');

		// Called from outside the importer (i.e. $this->importer->_reindexRecords(array_of_keys...))
		if (is_array($specific_target_keys) && count($specific_target_keys) > 0)
		{
			/// Called from outside the Importer
			if($this->runBenchMark){
				$this->CI->benchmark->mark('ingest_reindex_start');
			}

			foreach ($specific_target_keys AS $key)
			{				
				if($this->runBenchMark)
				{
					$this->roBuildCount++;
					$this->CI->benchmark->mark('ro_build_start');
				}
				
				$ro = $this->CI->ro->getPublishedByKey($key);
				
				if($this->runBenchMark)
				{
					$this->CI->benchmark->mark('ro_build_end');
					$this->roBuildTime += $this->CI->benchmark->elapsed_time('ro_build_start','ro_build_end');
				}			
				if ($ro)
				{
					if($this->runBenchMark)
					{
						$this->solrTransFormCount++;
						$this->CI->benchmark->mark('solr_transform_start');
					}
					
					$this->queueSOLRAdd($ro->indexable_json());
					
					if($this->runBenchMark)
					{
						$this->CI->benchmark->mark('solr_transform_end');
						$this->solrTransFormTime += $this->CI->benchmark->elapsed_time('solr_transform_start','solr_transform_end');
					}
				}

				if($this->runBenchMark)
				{
					$this->gcCyclesCount++;
					$this->CI->benchmark->mark('gc_cycles_start');
				}
					
				unset($ro);

				gc_collect_cycles();

				if($this->runBenchMark)
				{
					$this->CI->benchmark->mark('gc_cycles_end');
					$this->gcCyclesTime += $this->CI->benchmark->elapsed_time('gc_cycles_start','gc_cycles_end');
				}


			}
			
			$this->flushSOLRAdd();
			
			if($this->runBenchMark)
			{
				$this->CI->benchmark->mark('solr_commit_start');
			}
			
			$this->commitSOLR();
			
			if($this->runBenchMark)
			{
				$this->CI->benchmark->mark('solr_commit_end');
			}
			
			if($this->runBenchMark){
				$this->CI->benchmark->mark('ingest_reindex_end');
			}

			return array("count"=>$this->reindexed_records, "errors"=>array());
		}		
		//gc_collect_cycles();	
		return true;
	}

	/**
	 * 
	 */
	public function decideHarvestability($registryObject)
	{
		$reharvest = true;
		$revision_record_id = null;
		$existingRegistryObject = null;

		// If there is a draft, add to this one
		if (isDraftStatus($this->status))
		{
			$existingRegistryObject = $this->CI->ro->getDraftByKey((string)$registryObject->key);
			if (!$existingRegistryObject)
			{
				$existingRegistryObject = $this->CI->ro->getPublishedByKey((string)$registryObject->key);
			}
		}
		else if (isPublishedStatus($this->status))
		{
			$existingRegistryObject = $this->CI->ro->getPublishedByKey((string)$registryObject->key);
			if (!$existingRegistryObject)
			{
				$existingRegistryObject = $this->CI->ro->getDraftByKey((string)$registryObject->key);
			}
		}

		if ($existingRegistryObject)
		{

			// Check for duplicates: Reject this record if it is already in the feed
			if ($existingRegistryObject->harvest_id == $this->harvestID)
			{
				$reharvest = false;
				$this->message_log[] = "Ignored a record received twice in this harvest: " . $registryObject->key;
				$this->ingest_duplicate_ignore++;
			}

			if($existingRegistryObject->data_source_id == $this->dataSource->id)
			{	
				if ($this->statusAlreadyChanged || 
					((isDraftStatus($this->status) && isDraftStatus($existingRegistryObject->status)) || 
					(isPublishedStatus($this->status) && isPublishedStatus($existingRegistryObject->status))))
				{
					// Add a new revision to this existing registry object
					$revision_record_id = $existingRegistryObject->id;
				}
				else
				{
					$revision_record_id = null;
				}
			}
			else
			{
				// Duplicate key in alternate data source
				$reharvest = false;
				$this->message_log[] = "Ignored a record already existing in a different data source: " . $registryObject->key;
				$this->ingest_duplicate_ignore++;
			}

		}
		else
		{
			// Harvest this as a new registry object
			$revision_record_id = null;
		}
	
	
		return array($reharvest, $revision_record_id);

	}



	/**
	 * 
	 */
	private function _executeCrosswalk()
	{
		// Apply the crosswalk (if applicable)
		if (!is_null($this->crosswalk))
		{
			// At this point, $this->xmlPayload is actually the native payload (which might
			// not even be XML!) -- the crosswalk should implement a validate method (throwing
			// an exception on failure -- the entire harvest will be aborted as a partial 
			// transform might be erroneous if assumed at this point.

			// Throws an exception up if unable to validate in the payload's native schema
			$this->crosswalk->validate(utf8_encode($this->xmlPayload));

			// Crosswalk will create <registryObjects> with a <relatedInfo> element appended with the native format
			$this->xmlPayload = $this->crosswalk->payloadToRIFCS($this->xmlPayload, $this->message_log);

			$temp_crosswalk_name = $this->crosswalk->metadataFormat();
			unset($this->crosswalk);
			$this->setCrosswalk($temp_crosswalk_name);
            $fp = fopen($this->filePath.'.xwlk', 'w');
            fwrite($fp, $this->xmlPayload);
            fclose($fp);
		}
	}

	/**
	 * 
	 */
	public function setXML($payload)
	{
		$this->xmlPayload = $payload;
		return;
	}

	/**
	 * 
	 */
	public function setDataSource(_data_source $data_source)
	{
		$this->dataSource = $data_source;
		return;
	}

	public function setFilePath($path) {
		$this->filePath = $path;
		return;
	}

	public function setNativeFile($path) {
		$this->nativePath = $path;
		return;
	}


	/**
	 * 
	 */
	public function setHarvestID($harvestID)
	{
		$this->harvestID = $harvestID;
		return;
	}

	/**
	 * 
	 */
	public function setCrosswalk($crosswalk_metadata_format)
	{
        $crosswalk_identity = '';
        if (!$crosswalk_metadata_format) { return; }

        $predefinedProviderTypes = $this->CI->config->item('provider_types');

        foreach($predefinedProviderTypes as $ppt)
        {
            if($ppt['prefix'] == $crosswalk_metadata_format)
                $crosswalk_identity = $ppt['cross_walk'];
        }

        $crosswalks= getCrossWalks();
		foreach ($crosswalks AS $crosswalk)
		{
			if ($crosswalk->metadataFormat() == $crosswalk_metadata_format || $crosswalk->identify() == $crosswalk_identity)
			{
				$this->crosswalk = $crosswalk;
			}
		}

        return;
        /*if($crosswalk_identity == '') { return;}
		if (!$this->crosswalk)
		{
			throw new Exception("Unable to load crosswalk: " . $crosswalk_metadata_format);
		}*/
	}


	/**
	 * 
	 */
	public function setPartialCommitOnly($bool)
	{
		$this->partialCommitOnly = (boolean) $bool;
		return;
	}

	/**
	 * 
	 */
	public function validateRIFCS($xml)
	{
		$doc = new DOMDocument('1.0','utf-8');
		$doc->loadXML($xml);

		if(!$doc)
		{
			//$this->dataSource->append_log("Unable to parse XML. Perhaps your XML file is not well-formed?", HARVEST_ERROR, "importer","DOCUMENT_LOAD_ERROR");
			throw new Exception("Unable to parse XML. Perhaps your XML file is not well-formed?");
		}

		// TODO: Does this cache in-memory?
		libxml_use_internal_errors(true);
		$validation_status = $doc->schemaValidate(REGISTRY_APP_PATH . "registry_object/schema/registryObjects.xsd");

		if ($validation_status === TRUE) 
		{
			libxml_use_internal_errors(false);
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
			libxml_use_internal_errors(false);

			//$this->dataSource->append_log("Unable to validate XML document against schema: ".$error_string, HARVEST_ERROR, "importer","DOCUMENT_VALIDATION_ERROR");
			throw new Exception("Unable to validate XML document against schema: " . NL . $error_string);
		}
	}


	private function _getSimpleXMLFromString($xml)
	{
		libxml_use_internal_errors(true);

        if(!defined('LIBXML_PARSEHUGE')){
            $xml = simplexml_load_string($xml, 'SimpleXMLElement');
        }
        else{
            $xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_PARSEHUGE);

        }

		if ($xml === false)
		{
			$exception_message = "Could not parse Registry Object XML" . NL;
			foreach(libxml_get_errors() as $error) {
        		$exception_message .= "    " . $error->message;
			}
			libxml_use_internal_errors(false);
			throw new Exception($exception_message);	
		}
		return $xml;
	}


	/**
 	 * 
 	 */
	private function _getDefaultRecordStatusForDataSource(_data_source $data_source)
	{

		/*
		 * Harvest to the correct record mode
		 * QA = SUBMIT FOR ASSESSMENT
		 * !QA, AP = PUBLISHED
		 * !QA, !AP = APPROVED
		 */
		if ($this->forcePublish) {
			return PUBLISHED;
		} else if ($this->forceDraft) {
			return DRAFT;
		}

		if ($data_source->qa_flag == DB_TRUE) {
			$status = SUBMITTED_FOR_ASSESSMENT;
		} else {
			if ($data_source->manual_publish == DB_TRUE) {
				$status = APPROVED;
			} else {
				$status = PUBLISHED;
			}
		}

		return $status;
	}


	public function forcePublish()
	{
		$this->forcePublish = TRUE;
	}

	public function maintainStatus()
	{
		$this->maintainStatus = TRUE;
	}

	public function forceDraft()
	{
		$this->forceDraft = TRUE;
	}

	//public function cleanSchemaLocation($string)
	//{
	//	return preg_replace('/ xsi:schemaLocation=".*?"/sm','', $string);
	//}

	public function getBenchMarkLogArray(){
		$result = array();
		if($this->runBenchMark){
			$result = array(
				'totalTime' => $this->CI->benchmark->elapsed_time('enrich_affected_records_start', 'ingest_reindex_end'),
				'enrichTime' => $this->CI->benchmark->elapsed_time('enrich_affected_records_start', 'enrich_affected_records_end'),
				'reindexTime' => $this->CI->benchmark->elapsed_time('ingest_reindex_start', 'ingest_reindex_end'),
				'peakMemoryUsage' => memory_get_peak_usage() .' bytes',
				'error'=>$this->getErrors()
			);
			return $result;
		}
		return false;
	}

	public function getBenchMarkLogs()
	{	
		if($this->runBenchMark)
		{
			$totalTime = $this->CI->benchmark->elapsed_time('ingest_start', 'ingest_end');
			$enrichTime = $this->CI->benchmark->elapsed_time('enrich_affected_records_start', 'enrich_affected_records_end');
			$reIndexTime = $this->CI->benchmark->elapsed_time('ingest_reindex_start', 'ingest_reindex_end');
			$ingestTime = $this->CI->benchmark->elapsed_time('ingest_stage_1_start','ingest_stage_1_end');
			$crosswalkTime = $this->CI->benchmark->elapsed_time('crosswalk_execution_start','crosswalk_execution_end');
			$this->benchMarkLog .= NL.'Importing ran for : '.$totalTime;
			$this->benchMarkLog .= NL.'Ingest ran for : '.$ingestTime;
			$this->benchMarkLog .= NL.'Crosswalk ran for : '.$crosswalkTime;
			$this->benchMarkLog .= NL.'Enrich ran for : '.$enrichTime;

			$enrichS1Time = $this->CI->benchmark->elapsed_time('ingest_enrich_stage1_start', 'ingest_enrich_stage1_end');
			$enrichS2Time = $this->CI->benchmark->elapsed_time('ingest_enrich_stage2_start', 'ingest_enrich_stage2_end');
			$this->benchMarkLog .= NL.'.......Stage 1 : '.$enrichS1Time;
			$this->benchMarkLog .= NL.'.......Stage 2 : '.$enrichS2Time;
			if($this->roEnrichCount > 0 && $this->roQACount > 0)
			{
				$this->benchMarkLog .= NL.'______________Ro. Enrich Count: '.$this->roEnrichCount. ' total time: '.$this->roEnrichTime. ' avrg: ' .number_format($this->roEnrichTime/$this->roEnrichCount,4);
				$this->benchMarkLog .= NL.'____________________________S1 (xml) total time: '.$this->roEnrichS1Time. ' avrg: ' .number_format($this->roEnrichS1Time/$this->roEnrichCount,4);
				$this->benchMarkLog .= NL.'____________________________S2 (description) total time: '.$this->roEnrichS2Time. ' avrg: ' .number_format($this->roEnrichS2Time/$this->roEnrichCount,4);
				$this->benchMarkLog .= NL.'____________________________S3 (subjects) total time: '.$this->roEnrichS3Time. ' avrg: ' .number_format($this->roEnrichS3Time/$this->roEnrichCount,4);
				$this->benchMarkLog .= NL.'____________________________S4 (Licence) total time: '.$this->roEnrichS4Time. ' avrg: ' .number_format($this->roEnrichS4Time/$this->roEnrichCount,4);
				$this->benchMarkLog .= NL.'____________________________S5 (spatial) total time: '.$this->roEnrichS5Time. ' avrg: ' .number_format($this->roEnrichS5Time/$this->roEnrichCount,4);
				$this->benchMarkLog .= NL.'____________________________S6 (temporal) total time: '.$this->roEnrichS6Time. ' avrg: ' .number_format($this->roEnrichS6Time/$this->roEnrichCount,4);
				$this->benchMarkLog .= NL.'____________________________S7 (relatedObject) total time: '.$this->roEnrichS7Time. ' avrg: ' .number_format($this->roEnrichS7Time/$this->roEnrichCount,4);
				$this->benchMarkLog .= NL.'______________Ro. QA Count: '.$this->roQACount. ' total time: '.$this->roQATime. ' avrg: ' .number_format($this->roQATime/$this->roQACount,4);
				$this->benchMarkLog .= NL.'____________________________S1 (related str) total time: '.$this->roQAS1Time. ' avrg: ' .number_format($this->roQAS1Time/$this->roQACount,4);
				$this->benchMarkLog .= NL.'____________________________S2 (quality) total time: '.$this->roQAS2Time. ' avrg: ' .number_format($this->roQAS2Time/$this->roQACount,4);
				$this->benchMarkLog .= NL.'____________________________S3 (level) total time: '.$this->roQAS3Time. ' avrg: ' .number_format($this->roQAS3Time/$this->roQACount,4);
				$this->benchMarkLog .= NL.'____________________________S4 (save) total time: '.$this->roQAS4Time. ' avrg: ' .number_format($this->roQAS4Time/$this->roQACount,4);

			}

			$this->benchMarkLog .= NL.'ReIndex ran for : '. $reIndexTime;
			if($this->roBuildCount > 0)
			{
				$this->benchMarkLog .= NL.'.......Ro. Build Count: '.$this->roBuildCount. ' total time: '.$this->roBuildTime. ' avrg: ' .number_format($this->roBuildTime/$this->roBuildCount,4);
			}
			if($this->gcCyclesCount > 0)
			{
				$this->benchMarkLog .= NL.'.......GC Cycles Count: '.$this->gcCyclesCount. ' total time: '.$this->gcCyclesTime. ' avrg: ' .number_format($this->gcCyclesTime/$this->gcCyclesCount,4);
			}
			if($this->solrTransFormCount > 0)
			{
				$this->benchMarkLog .= NL.'.......Ro. Transform Count: '.$this->solrTransFormCount. ' total time: '.$this->solrTransFormTime. ' avrg: ' .number_format($this->solrTransFormTime/$this->solrTransFormCount,4);
			}
			if($this->solrReqCount > 0)
			{
				$this->benchMarkLog .= NL.'.......SOLR_CHUNK_SIZE: '.self::SOLR_CHUNK_SIZE;
				$this->benchMarkLog .= NL.'.......Solr Request Count : '.$this->solrReqCount. ' total time: '.$this->solrReqTime. ' avrg: ' .number_format($this->solrReqTime/$this->solrReqCount,4);
			}
			$solrCommitTime = $this->CI->benchmark->elapsed_time('solr_commit_start','solr_commit_end');
			$this->benchMarkLog .= NL.'.......Solr Commit took : '.$solrCommitTime;
			$this->benchMarkLog .= NL.'.......Memory Peak Usage : '.memory_get_peak_usage().' bytes';
		}
		return $this->benchMarkLog;
	}

	/**
	 * 
	 */
	public function getRifcsFromFeed($oai_feed)
	{

		$result = '';
		try{
		//$sxml = simplexml_load_string($oai_feed);
			$sxml = $this->_getSimpleXMLFromString($oai_feed);
			if($sxml)
			{
				
				@$sxml->registerXPathNamespace("oai", OAI_NAMESPACE);
				@$sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);

				$registryObjects = $sxml->xpath('//ro:registryObject');
				foreach ($registryObjects AS $ro)
				{
					$result .= $ro->asXML();
				}

				$result = wrapRegistryObjects($result);

			}
		}
		catch(Exception $e)
		{
			return $e;
		}
		return $result;
	}


	public function updateDeletedList($oai_feed, $xs)
	{
		$gXPath = new DOMXpath($oai_feed);
		$defaultNamespace = $gXPath->evaluate('/*')->item(0)->namespaceURI;
		$gXPath->registerNamespace('oai', $defaultNamespace);
		$deletedRegistryObjectList = $gXPath->evaluate("//oai:header[@status='deleted']");
		for( $i=0; $i < $deletedRegistryObjectList->length; $i++ )
		{
			$deletedRegistryObject = $deletedRegistryObjectList->item($i);
			$registryObjectKey = substr($gXPath->evaluate($xs.":identifier", $deletedRegistryObject)->item(0)->nodeValue, 0, 512);
			$this->addToDeletedList($registryObjectKey);			
		}
	}

	public function addToDeletedList($ro_keys)
	{
		if(is_array($ro_keys) && count($ro_keys) > 0)
		{
			$this->deleted_record_keys = array_unique(array_merge($this->deleted_record_keys, $ro_keys));
		}
		else if($ro_keys && $ro_keys != '' && !array_key_exists($ro_keys, $this->deleted_record_keys))
		{
			$this->deleted_record_keys[] = $ro_keys;
		}
	}


	public function addToAffectedList($ro_keys)
	{
		if(is_array($ro_keys) && count($ro_keys) > 0)
		{
			$this->affected_record_keys = array_unique(array_merge($this->affected_record_keys, $ro_keys));
		}
	}


	public function addToImportedIDList($ro_ids)
	{
		if(is_array($ro_ids) && count($ro_ids) > 0)
		{
			$this->importedRecords = array_unique(array_merge($this->importedRecords, $ro_ids));
		}
	}

	public function getImportedIDListAsJson()
	{

		return json_encode($this->importedRecords);
	}

	public function getErrors()
	{
		$log = '';
		if (count($this->error_log) > 0)
		{
			foreach ($this->error_log AS $error)
			{
				$log .= "  $error" . NL;
			}
			$log .= NL;
			
		}

		if ($log) return $log; else return FALSE;
	}

	public function getMessages()
	{
		$log = '';
		if (count($this->message_log) > 0)
		{
			foreach ($this->message_log AS $msg)
			{
				$log .= "  $msg" . NL;
			}			
		}

		if ($log) return $log; else return FALSE;
	}


	/* * * * 
	 * SOLR UPDATE FUNCTIONS 
	 * * * */

	var $solr_queue = array();
	const SOLR_CHUNK_SIZE = 400;
	const SOLR_RESPONSE_CODE_OK = 0;
	//var $solrReqCount = 0;
	//var $solrReqTime = 0;
	/**
	 * Queue up a request to send to SOLR ("chunking" of <add><doc> statements)
	 */
	function queueSOLRAdd($doc_statement)
	{
		$this->solr_queue[] = $doc_statement;
		if (count($this->solr_queue) > self::SOLR_CHUNK_SIZE)
		{
			$this->solrReqCount++;
			if($this->runBenchMark)
			{
				$this->CI->benchmark->mark('solr_flush_start');
			}
			$this->flushSOLRAdd();
			if($this->runBenchMark)
			{
				$this->CI->benchmark->mark('solr_flush_end');
				$this->solrReqTime += $this->CI->benchmark->elapsed_time('solr_flush_start','solr_flush_end');
			}
		}
	}

	/**
	 * Send an update request to SOLR for all <add><doc> statements in the queue...
	 */
	function flushSOLRAdd()
	{
		if (sizeof($this->solr_queue) == 0) return;

		$solrUrl = get_config_item('solr_url');
		$solrUpdateUrl = $solrUrl.'update/?wt=json';

		$this->CI->load->library('solr');

		try{
			// $result = json_decode(curl_post($solrUpdateUrl, json_encode($this->solr_queue)), true);
			$result = json_decode($this->CI->solr->add_json(json_encode($this->solr_queue)), true);
			if($result['responseHeader']['status'] == self::SOLR_RESPONSE_CODE_OK) {
				$this->reindexed_records += sizeof($this->solr_queue);
			} else {
				// Throw back the SOLR response...
				throw new Exception(var_export((isset($result['error']['msg']) ? $result['error']['msg'] : $result),true));
			}

		}
		catch (Exception $e)
		{
			$this->error_log[] = "[INDEX] Error during reindex of registry object..." . BR . "<pre>" . nl2br($e) . "</pre>";	
		}

		$this->solr_queue = array();
		return true;
	}

	function commitSOLR()
	{
		$solrUrl = get_config_item('solr_url');
		$solrUpdateUrl = $solrUrl.'update/?wt=json';
		return curl_post($solrUpdateUrl.'?commit=true', '<commit waitSearcher="false"/>');
	}

	/**
	 * 
	 */
	public function _reset()
	{
		$this->harvestID = null;
		$this->crosswalk = null;
		$this->xmlPayload = '';
		$this->dataSource = null;
		$this->filePath = false;
		$this->nativePath = false;
		$this->importedRecords = array();
		$this->imported_record_keys = array();
		$this->affected_record_keys = array();
		$this->deleted_record_keys = array();
		$this->partialCommitOnly = false;
		$this->isImporting = false;
		$this->solr_queue = array();
		$this->forcePublish = false;
		$this->forceDraft = false; 
		$this->statusAlreadyChanged = false;
		$this->ingest_attempts = 0;
		$this->ingest_successes = 0;
		$this->ingest_failures = 0;
		$this->ingest_duplicate_ignore = 0;
		$this->ingest_new_revision = 0;
		$this->ingest_new_record = 0;
		$this->reindexed_records = 0;
		$this->maintainStatus = false;
		$this->error_log = array();
		$this->message_log = array();
		$this->benchMarkLog = '';
		$this->start_time = null;
		$this->solrReqCount = 0;
		$this->solrReqTime = 0;
		$this->solrTransFormCount = 0;
		$this->solrTransFormTime = 0;
		$this->roBuildCount = 0;
		$this->roBuildTime = 0;
		$this->roEnrichCount = 0;
		$this->roEnrichTime = 0;
		$this->roEnrichS1Time = 0;
		$this->roEnrichS2Time = 0;
		$this->roEnrichS3Time = 0;
		$this->roEnrichS4Time = 0;
		$this->roEnrichS5Time = 0;
		$this->roEnrichS6Time = 0;
		$this->roEnrichS7Time = 0;
		$this->roQACount = 0;
		$this->roQATime = 0;
		$this->roQAS1Time = 0;
		$this->roQAS2Time = 0;
		$this->roQAS3Time = 0;
		$this->roQAS4Time = 0;
		$this->gcCyclesCount = 0;
		$this->gcCyclesTime = 0;
		$this->runBenchMark = $this->CI->config->item('importer_benchmark_enabled');
		$this->registryMode = $this->CI->config->item('registry_mode');
		error_reporting(E_ALL);
	}


	function cleanNameSpace($rifcs){
		try{
			$xslt_processor = Transforms::get_clean_ns_transformer();
			$dom = new DOMDocument();
			$dom->loadXML($rifcs);
			return $xslt_processor->transformToXML($dom);
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}


}