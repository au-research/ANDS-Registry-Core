<?php
namespace ANDS\API\Task\ImportSubTask;

use \Exception as Exception;

include_once("applications/registry/registry_object/models/_transforms.php");

use \Transforms as Transforms;
use \DOMDocument as DOMDocument;

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
    private $ingest_attempts;
    private $ingest_failures;
    private $ingest_successes;
    private $ingest_new_revision;
    private $ingest_new_record;
    private $ingest_duplicate_ignore;
    private $maintainStatus;
    private $forceDraft;
    private $ds; // data_source Object (single ds)
    private $roM; // registry_objects_model (multiple ro so use the model)
    private $payloads;
    private $ds_id;
    private $default_record_status;
    private $record_owner;
    private $batch_id;
    private $valid_classes = ['collection', 'party', 'activity', 'service'];


    public function run_task()
    {
        $this->log('Ran Insert');
        //throw new NonFatalException("Random Error");
        parse_str($this->params, $aParams);
        $this->ds_id = $aParams['ds_id'];
        $this->batch_id = $aParams['batch_id'];
        if (!$this->batch_id) throw new Exception('Batch ID expected');
        if ($this->_CI->config->item('registry_mode') == "read-only") {
            throw new Exception("The Registry currently set to Read-Only mode, no changes will be saved!");
        }
        $dsM = $this->_CI->load->model('registry/data_source/data_sources', 'ds');
        $this->ds = $dsM->getByID($this->ds_id);

        if (!$this->ds) throw new Exception('Data Source not found');

        $this->setTaskData('batch_id', $this->batch_id);
        $this->default_record_status = $this->getDefaultRecordStatusForDataSource();
        $this->loadContent();
        $this->processPayload();
        $this->insertRecords();

        $this->setTaskData('ingest_new_record', $this->ingest_new_record);
        $this->setTaskData('ingest_attempts', $this->ingest_attempts);
        $this->setTaskData('ingest_failures', $this->ingest_failures);
        $this->setTaskData('ingest_successes', $this->ingest_successes);
        $this->setTaskData('ingest_new_revision', $this->ingest_new_revision);
        $this->setTaskData('ingest_duplicate_ignore', $this->ingest_duplicate_ignore);
        $this->setTaskData('default_record_status', $this->default_record_status);
        $this->setTaskData('maintainStatus', $this->maintainStatus);
        // if this is ds has the qa flag set  email the notify address
        if ($this->ds->qa_flag === DB_TRUE && $this->ingest_new_record < 1 && $this->ds->assessment_notify_email_addr) {
            $this->roM->emailAssessor($this->ds);
        }
        $this->ds->append_log('INSERTING RECORDS:' . NL . $this->printTaskData(), "IMPORTER_INFO", "importer", "IMPORTER_INFO");


    }

    /**
     * Insert constructor.
     */
    public function __construct()
    {
        $this->setName('Insert');
        if (!function_exists('mb_convert_encoding')) {
            throw new Exception("'php mbstring must be installed.");
        }
        $this->_CI = & get_instance();
        $this->_DB = $this->_CI->load->database('registry', true);
        $this->importedRecords = [];
        $this->ingest_new_record = 0;
        $this->ingest_attempts = 0;
        $this->ingest_failures = 0;
        $this->ingest_successes = 0;
        $this->ingest_new_revision = 0;
        $this->ingest_duplicate_ignore = 0;
        $this->maintainStatus = true;
        $this->forceClone = false;
        $this->record_owner = "SYSTEM";
        $this->payloads = [];
        $this->roM = $this->_CI->load->model('registry/registry_object/registry_objects', 'ro');
    }

    /**
     * Import into a data source via editor
     * @throws Exception
     * @return json
     */
    public function saveDraft($ro_id, $xml, $key, $ds_id ){
        $this->ds_id = $ds_id;
        $this->default_record_status = "DRAFT";
        $this->batch_id = "MANUAL-".time();
        $dsM = $this->_CI->load->model('registry/data_source/data_sources', 'ds');
        $this->ds = $dsM->getByID($this->ds_id);
        $package = array("registry_object" => $xml, "file_path" => $key, "ro_id"=>$ro_id);
        if ($this->_CI->user->isLoggedIn()) {
            $this->record_owner  = $this->_CI->user->name() . " (" . $this->_CI->user->localIdentifier() . ")";
        }
        $this->ingestRecord($package);

        $this->setTaskData('ingest_new_record', $this->ingest_new_record);
        $this->setTaskData('ingest_attempts', $this->ingest_attempts);
        $this->setTaskData('ingest_failures', $this->ingest_failures);
        $this->setTaskData('ingest_successes', $this->ingest_successes);
        $this->setTaskData('ingest_new_revision', $this->ingest_new_revision);
        $this->setTaskData('ingest_duplicate_ignore', $this->ingest_duplicate_ignore);
        $this->setTaskData('default_record_status', $this->default_record_status);
        $this->setTaskData('maintainStatus', $this->maintainStatus);
        $this->ds->append_log('INSERTING RECORDS:' . NL . $this->printTaskData(), "IMPORTER_INFO", "importer", "IMPORTER_INFO");

    }


    private function loadContent()
    {
        $dir = get_config_item('harvested_contents_path');
        if (!$dir) throw new Exception('Harvested Contents Path not configured');
        $batch_query = $this->_DB->get_where('harvests', array('data_source_id' => $this->ds_id));
        if ($batch_query->num_rows() > 0) {
            $batch_array = $batch_query->result_array();
            $harvest_id = $batch_array[0]['harvest_id'];
            $path = $dir . $this->ds_id . '/' . $this->batch_id;

            $this->datasourceDefaultStatus = $this->getDefaultRecordStatusForDataSource();
            $this->setTaskData('datasourceDefaultStatus', $this->datasourceDefaultStatus);
            $this->setTaskData('datasourceRecordCountBefore', $this->ds->count_total);
            if (!is_dir($path)) {
                //is not directory, it's a file
                $path = $path . '.xml';
                if (is_file($path)) {
                    $this->payloads[$path] = file_get_contents($path);
                }
            } else {
                //is a directory
                $directory = scandir($path);
                $files = array();

                foreach ($directory as $f) {
                    if (endsWith($f, '.xml')) {
                        $files[] = $f;
                    }
                }
                foreach ($files as $index => $f) {
                    $this->payloads[$f] = file_get_contents($path . '/' . $f);
                }
            }

        }
    }


    private function processPayload()
    {

        $ingest_failures = 0;
        $continueIngest = true;
        $fileProgress = 0;
        foreach ($this->payloads as $key => $payload) {
            $payload = mb_convert_encoding($payload, "UTF-8");
            // unescape (some entities are double escaped) first
            while (strpos($payload, '&amp;') !== false) {
                $payload = str_replace("&amp;", "&", $payload);
            }
            $payload = str_replace("&", "&amp;", $payload);

            try {
                $payload = $this->cleanNameSpace($payload);
                $sxml = $this->_getSimpleXMLFromString($payload);
            } catch (NonFatalException $e) {
                $continueIngest = false;
                $this->setTaskData('ingest_failures', ++$ingest_failures);
                //throw new Exception("Unable to parse XML into object".NL.$e->getMessage());
            }
            if ($continueIngest) {
                // Last chance to check valid format of the payload
                $reValidateBeforeIngest = false;
                try {
                    $this->validateRIFCS($payload);
                } catch (NonFatalException $e) {
                    $reValidateBeforeIngest = true;
                    // no need to report validation error here!!
                }

                $sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);

                // Right then, lets start parsing each registryObject & importing!
                foreach ($sxml->xpath('//ro:registryObject') AS $registryObject) {

                    $this->ingest_attempts++;
                    $continueWithIngest = true;
                    if ($reValidateBeforeIngest) {
                        try {
                            $this->validateRIFCS(wrapRegistryObjects($registryObject->asXML()));
                        } catch (NonFatalException $e) {
                            $continueWithIngest = false;
                            $this->error_log[] = "Error whilst ingesting record #" . $this->ingest_attempts . ": " . $e->getMessage() . NL;
                        }
                    }
                    if ($continueWithIngest) {
                        $key = (string)$registryObject->key;
                        if ($key == '') {
                            $this->ingest_failures++;
                            $this->error_log[] = "Error whilst ingesting record #" . $this->ingest_attempts . ": " . "Registry Object 'key' must have a value";
                        } elseif (isset($this->importedRecords[$key])) {
                            $this->ingest_duplicate_ignore++;
                            $this->message_log[] = "Ignored a record already exists in import list: " . $key;

                        } elseif ((string)$registryObject->originatingSource == '') {
                            $this->ingest_failures++;
                            $this->error_log[] = "Error whilst ingesting record #" . $this->ingest_attempts . ": " . "Registry Object 'originatingSource' must have a value";
                        } elseif ((string)$registryObject['group'] == '') {
                            $this->ingest_failures++;
                            $this->error_log[] = "Error whilst ingesting record #" . $this->ingest_attempts . ": " . "Registry Object '@group' must have a value";

                        } else {
                            $this->importedRecords[$key] = array("registry_object" => $registryObject, "file_path" => $key);
                        }
                    }
                }
            }
        }
    }


    private function insertRecords()
    {
        foreach ($this->importedRecords as $package) {

            $this->ingestRecord($package);
        }
    }

    private function ingestRecord($package)
    {

        $registryObject = $package['registry_object'];
        $filePath = $package['file_path'];
        $existing_record_id = null;
        if(isset($package['ro_id'])){
            $preferred_record_id = $package['ro_id'];
        }
        try{
            $ro_id = decideHarvestability($registryObject);
            if($preferred_record_id != $ro_id)
            {
                $this->error_log[] = "given RO_ID:".$preferred_record_id." doesn't mach given RO_ID".$ro_id;
            }
            foreach ($this->valid_classes AS $class) {
                if (property_exists($registryObject, $class)) {

                    $ro_xml =& $registryObject->{$class}[0];

                    $ro_xml->registerXPathNamespace("ro", RIFCS_NAMESPACE);

                    if (is_null($ro_id)) {
                        // We are creating a new registryObject
                        $ro = $this->roM->create($this->ds, (string)$registryObject->key, $class, "", $this->default_record_status, "temporary_slug-" . md5((string)$registryObject->key) . "-" . time(), $this->record_owner, $this->batch_id);

                        $ro->class = $class;
                        $ro->data_source_key = $this->ds->key;
                        $ro->created_who = $this->record_owner;
                        $ro->record_owner = $this->record_owner;

                        $this->ingest_new_record++;
                    } else {
                        // The registryObject exists, just add a new revision to it?

                        $ro = $this->roM->getByID($ro_id);
                        $ro->cleanupPreviousVersions();
                        $ro->status = $this->default_record_status;
                    }

                    $changed = $ro->createVersion(wrapRegistryObjects($registryObject->asXML()), 'rif', $this->default_record_status, $this->batch_id);


                    $ro->group = (string)$registryObject['group'];
                    $ro->type = (string)$ro_xml['type'];
                    $ro->file_path = $filePath;
                    $ro->setAttribute("harvest_id", $this->batch_id);


                    // Clean up all previous versions (set = FALSE, "prune" extRif)


                    // Order is important here!

                    // Generate the list and display titles first, then the SLUG
                    $ro->updateTitles($ro->getSimpleXML());

                    // Only generate SLUGs for published records

                    $ro->generateSlug();
                    if (isPublishedStatus($this->default_record_status)) {
                        $ro->generateSlug();
                    } else {
                        $ro->slug = DRAFT_RECORD_SLUG . $ro->id;
                        $ro->setAttribute("manually_assessed", 'no');
                    }

                    $ro->save($changed);

                    if ($changed) {

                        $this->setAffectedRecords($ro->key);
                        $this->ingest_new_revision++;
                    }

                    $this->ingest_successes++;
                    // Memory management...
                    unset($ro);
                    clean_cycles();
                }
                unset($ro_xml);
            }
        }
        catch(NonFatalException $e){
            $this->error_log[] = $e->getMessage . NL;
        }
    }
    // HELPER FUNCTIONS



    /**
     *
     */
    public function getExistingRecordId($registryObject)
    {
        $record_id = null;
        $existingRegistryObjectRecords = $this->getRegistryObjectByKey((string)$registryObject->key);
        if ($existingRegistryObjectRecords) {
            // should be ONLY 1 since key must be unique!!
            foreach ($existingRegistryObjectRecords as $registryObjectRecord) {
                if ($registryObjectRecord['data_source_id'] != $this->ds->id) {
                    throw new NonFatalException("Record with key ".(string)$registryObject->key." already exist in a different data source");
                } else {
                    $record_id = $registryObjectRecord['registry_object_id'];
                }
            }
        }
        return $record_id;
    }


    private function getDefaultRecordStatusForDataSource()
    {

        /*
         * Harvest to the correct record mode
         * QA = SUBMIT FOR ASSESSMENT
         * !QA, AP = PUBLISHED
         * !QA, !AP = APPROVED
         */
        $status = '';

        if ($this->ds->qa_flag === DB_TRUE) {
            $status = 'SUBMITTED_FOR_ASSESSMENT';
        } else {
            if ($this->ds->manual_publish === DB_TRUE) {
                $status = 'APPROVED';
            } else {
                $status = 'PUBLISHED';
            }
        }

        return $status;
    }

    function cleanNameSpace($rifcs)
    {
        try {
            $xslt_processor = Transforms::get_clean_ns_transformer();
            $dom = new DOMDocument();
            $dom->loadXML($rifcs);
            return $xslt_processor->transformToXML($dom);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function _getSimpleXMLFromString($xml)
    {
        libxml_use_internal_errors(true);

        if (!defined('LIBXML_PARSEHUGE')) {
            $xml = simplexml_load_string($xml, 'SimpleXMLElement');
        } else {
            $xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_PARSEHUGE);

        }

        if ($xml === false) {
            $exception_message = "Could not parse Registry Object XML" . NL;
            foreach (libxml_get_errors() as $error) {
                $exception_message .= "    " . $error->message;
            }
            libxml_use_internal_errors(false);
            throw new Exception($exception_message);
        }
        return $xml;
    }

    public function validateRIFCS($xml)
    {
        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->loadXML($xml);

        if (!$doc) {
            //$this->dataSource->append_log("Unable to parse XML. Perhaps your XML file is not well-formed?", HARVEST_ERROR, "importer","DOCUMENT_LOAD_ERROR");
            throw new Exception("Unable to parse XML. Perhaps your XML file is not well-formed?");
        }

        // TODO: Does this cache in-memory?
        libxml_use_internal_errors(true);
        $validation_status = $doc->schemaValidate(REGISTRY_APP_PATH . "registry_object/schema/registryObjects.xsd");

        if ($validation_status === TRUE) {
            libxml_use_internal_errors(false);
            return TRUE;
        } else {
            $errors = libxml_get_errors();
            $error_string = '';
            foreach ($errors as $error) {
                $error_string .= TAB . "Line " . $error->line . ": " . $error->message;
            }
            libxml_clear_errors();
            libxml_use_internal_errors(false);

            //$this->dataSource->append_log("Unable to validate XML document against schema: ".$error_string, HARVEST_ERROR, "importer","DOCUMENT_VALIDATION_ERROR");
            throw new Exception("Unable to validate XML document against schema: " . NL . $error_string);
        }
    }

    function getRegistryObjectByKey($key)
    {
        $query = $this->_DB->get_where('registry_objects', array('key' => $key));
        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return false;
        }
    }

}