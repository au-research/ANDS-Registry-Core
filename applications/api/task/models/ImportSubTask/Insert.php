<?php
namespace ANDS\API\Task\ImportSubTask;

use \Exception as Exception;

/**
 * Class:  Insert
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Insert extends ImportSubTask
{
    private $_CI;
    private $_DB;
    private $importedRecords;
    private $dsM; // data_source_model
    private $roM; // registry_objects_model

    public function run_task()
    {
        $this->log('Ran Insert');
        throw new NonFatalException("Random Error");
        $this->getData();
        $this->setAffectedRecords([1, 2, 3]);
    }

    /**
     * Insert constructor.
     */
    public function __construct()
    {
        $this->setName('Insert');
        $this->_CI = &get_instance();
        $this->_DB = $this->_CI->load->database('registry', true);
        $this->importedRecords = [];
        $this->dsM = $this->_CI->load->model('registry/registry_object/registry_objects', 'ro');
        $this->roM = $this->_CI->load->model('registry/data_source/data_sources', 'ds');
    }

    private function getData()
    {
        parse_str($this->params, $aParams);
        $ds_id = $aParams['ds_id'];
        $batch_id = $aParams['batch_id'];
        if(!$batch_id) throw new Exception('Batch ID expected');

//        $this->import_via_path($ds_id, $batch_id);
    }

    /**
     * Import into a data source via downloaded file
     * @param  data_source_id $id
     * @param  string $batch batch_id of the current batch
     * @throws Exception
     * @return json
     */
    private function import_via_path($id, $batch)
    {
        $dir = get_config_item('harvested_contents_path');
        if(!$dir) throw new Exception('Harvested Contents Path not configured');

        $batch_query = $this->_DB->get_where('harvests', array('data_source_id'=>$id));
        if($batch_query->num_rows() > 0) {
            $batch_array = $batch_query->result_array();
            $harvest_id = $batch_array[0]['harvest_id'];
            $path = $dir.$id.'/'.$batch;
            $ds = $this->dsM->getByID($id);
            dd($ds);
            if(!$ds) throw new Exception('Data Source not found');

            $ds->updateHarvestStatus($harvest_id, 'IMPORTING');
            $count_before = $ds->count_total;
            $this->log('DS count_before:'.$count_before);
            if(!is_dir($path)) {
                //is not directory, it's a file
                $path = $path.'.xml';
                if(is_file($path)) {
                    $xml = file_get_contents($path);
                }
            }
            else {
                //is a directory
                $directory = scandir($path);
                $files = array();

                foreach($directory as $f){
                    if(endsWith($f, '.xml')) {
                        $files[] = $f;
                    }
                }

                foreach($files as $index=>$f) {
                    $xml = file_get_contents($path.'/'.$f);
                }
            }

        }
    }

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


    private function ingestRecord($registryObject)
    {

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
                    if($this->_CI->user->isLoggedIn())
                    {
                        $record_owner = $this->CI->user->name() . " (" . $this->CI->user->localIdentifier() . ")";
                    }

                    if (is_null($revision_record_id) || $this->forceClone)
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
                        $this->addToAffectedList([$ro->key]);
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


}