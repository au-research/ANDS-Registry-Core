<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Data Sources PHP object
 *
 * This class defines the PHP object representation of
 * data sources. Objects can be initialised, modified
 * and saved, abstracting away the underlying attribute
 * structure.
 *
 * "Core" attributes must be initialised before a registry
 * object can be created.
 *
 * <code>
 * 	// Creating a new data source
$ds = new _data_source();

// Compulsory attributes
$ds->_initAttribute("key","test.test3", TRUE);
$ds->_initAttribute("slug","testtest3", TRUE);

// Some extras
$ds->setAttribute("record_owner","Tran");

$ds->create();
print "New DS received ID " . $ds->getID();


// Updating a data source

$ds = new _data_source(5);
$ds->record_owner = "Bob";
print $ds->save();
 * </code>
 *
 * @author Ben Greenwood <ben.greenwood@ardc.edu.au>
 * @package ands/datasource
 * @subpackage helpers
 */
class _data_source {
	
	private $id; 	// the unique ID for this data source
	private $_CI; 	// an internal reference to the CodeIgniter Engine
	private $db; 	// another internal reference to save typing!
	
	public $attributes = array();		// An array of attributes for this Data Source
	const MAX_NAME_LEN = 32;
	const MAX_VALUE_LEN = 255;

	public $stockAttributes = array('title'=>'','record_owner'=>'','contact_name'=>' ', 'contact_email'=>' ', 'provider_type'=>RIFCS_SCHEME,'notes'=>'','acronym'=>'');
	public $extendedAttributes = array('allow_reverse_internal_links'=>DB_TRUE,'allow_reverse_external_links'=>DB_TRUE,'manual_publish'=>DB_FALSE,'qa_flag'=>DB_TRUE,'create_primary_relationships'=>DB_FALSE,'assessment_notify_email_addr'=>'','created'=>'','updated'=>'', 'export_dci'=>DB_FALSE, 'crosswalks'=>'');
	public $harvesterParams = array('service_discovery_enabled'=>DB_FALSE,'provider_type'=>'rif','uri'=>'http://','harvest_method'=>'GETHarvester','harvest_date'=>'','oai_set'=>'','advanced_harvest_mode'=>'STANDARD','harvest_frequency'=>'', 'metadataPrefix'=>'', 'xsl_file'=>'', 'user_defined_params' => '');
	public $primaryRelationship = array('primary_key_1','primary_key_2','collection_rel_1','collection_rel_2','activity_rel_1','activity_rel_2','party_rel_1','party_rel_2','service_rel_1','service_rel_2');
	
	function __construct($id = NULL, $core_attributes_only = FALSE)
	{
		if (!is_numeric($id) && !is_null($id)) 
		{
			throw new Exception("Data Source Wrapper must be initialised with a numeric Identifier");
		}
		
		$this->id = $id;				// Set this object's ID
		$this->_CI =& get_instance();	// Get a pointer to the framework's instance
		$this->db =& $this->_CI->db;	// Shorthand pointer to database
		
		if (!is_null($id))
		{
			$this->init($core_attributes_only);
		}
	}
	
	
	function getID()
	{
		return $this->id;
	}
	
	function init($core_attributes_only = FALSE)
	{
		/* Initialise the "core" attributes */
		$query = $this->db->get_where("data_sources", array('data_source_id' => $this->id));
		
		if ($query->num_rows() == 1)
		{
			$core_attributes = $query->row();	
			foreach($core_attributes AS $name => $value)
			{
				$this->_initAttribute($name, $value, TRUE);
			}
		}
		else 
		{
			throw new Exception("Unable to select Data Source from database");
		}
			
		// If we just want more than the core attributes
		if (!$core_attributes_only)
		{
			// Lets get all the rest of the data source attributes
			$query = $this->db->get_where("data_source_attributes", array('data_source_id' => $this->id));
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() AS $row)
				{
					$this->_initAttribute($row->attribute, $row->value);

				}		
			}
		}
		return $this;
    }

    function setAttribute($name, $value = NULL)
    {
        if (strlen($name) > self::MAX_NAME_LEN || strlen($value) > self::MAX_VALUE_LEN)
        {
            // $value = substr($value, 0, self::MAX_VALUE_LEN);
            //throw new Exception("Attribute name exceeds " . self::MAX_NAME_LEN . " chars or value exceeds " . self::MAX_VALUE_LEN . ". Attribute not set");
        }

        // setAttribute
        if ($value !== NULL)
        {
            if (isset($this->attributes[$name]))
            {
                if ($this->attributes[$name]->value != $value)
                {
                    // Attribute already exists, we're just updating it
                    $this->attributes[$name]->value = $value;
                    $this->attributes[$name]->dirty = TRUE;
                }
            }
            else
            {
                // This is a new attribute that needs to be created when we save
                $this->attributes[$name] = new _data_source_attribute($name, $value);
                $this->attributes[$name]->dirty = TRUE;
                $this->attributes[$name]->new = TRUE;
            }
        }
        else
        {
            if (isset($this->attributes[$name]))
            {
                $this->attributes[$name]->value = NULL;
                $this->attributes[$name]->dirty = TRUE;
            }
        }

        return $this;
    }

    function create()
    {
        $this->db->insert("data_sources", array("data_source_id" => $this->id, "key" => $this->getAttribute("key"), "slug" => $this->getAttribute("slug")));
        $this->id = $this->db->insert_id();
        $this->save();
        return $this;
    }

    function save()
    {
        // Mark this record as recently updated
        $this->setAttribute("updated", time());

        foreach($this->attributes AS $attribute)
        {

            if($attribute->name=='title')
            {
                //if the user has changed the datasource's title then we need to update the datsources slug based on that title;
                $this->setSlug($attribute->value);
            }

            if ($attribute->dirty)
            {

                if ($attribute->core)
                {
                    $theUpdate=array();
                    $theUpdate[$attribute->name] =$attribute->value;
                    $this->db->where("data_source_id", $this->id);
                    $this->db->update("data_sources", $theUpdate);
                    $attribute->dirty = FALSE;
                }
                else
                {

                    if ($attribute->value !== NULL)
                    {
                        if ($attribute->new)
                        {
                            $this->db->insert("data_source_attributes", array("data_source_id" => $this->id, "attribute" => $attribute->name, "value"=>$attribute->value));
                            $attribute->dirty = FALSE;
                            $attribute->new = FALSE;
                        }
                        else
                        {
                            $this->db->where(array("data_source_id" => $this->id, "attribute" => $attribute->name));
                            $this->db->update("data_source_attributes", array("value"=>$attribute->value));
                            $attribute->dirty = FALSE;
                        }
                    }
                    else
                    {
                        $this->db->where(array("data_source_id" => $this->id, "attribute" => $attribute->name));
                        $this->db->delete("data_source_attributes");
                        unset($this->attributes[$attribute->name]);
                    }
                }


            }
        }
        return $this;
    }

    function setSlug($title)
    {

        $result = strtolower($title);
        $result = preg_replace("/[^a-z0-9\s-]/", "", $result);
        $result = trim(preg_replace("/[\s-]+/", " ", $result));
        $result = trim(substr($result, 0, self::MAX_VALUE_LEN));
        $result = preg_replace("/\s/", "-", $result);

        $query_ds_slugs = $this->db->select('data_source_id')->get_where('data_sources',array("slug"=> $result));

        if($query_ds_slugs->num_rows==0){

            $this->setAttribute("slug", $result);

        }
        else if($query_ds_slugs->num_rows>0)
        {
            $results = $query_ds_slugs->result_array();
            $existing_slug = array_pop($results);

            if($existing_slug['data_source_id']!=$this->id)
            {
                $this->setAttribute("slug", $result."-");
            }

        }

    }

    function getAttribute($name, $graceful = TRUE)
    {
        if (isset($this->attributes[$name]) && $this->attributes[$name] != NULL)
        {
            return $this->attributes[$name]->value;
        }
        else if (!$graceful)
        {
            throw new Exception("Unknown/NULL attribute requested by getAttribute($name) method");
        }
        else
        {
            return NULL;
        }
    }

    function unsetAttribute($name)
    {
        $this->setAttribute($name, NULL);
    }


    function attributes()
    {
        $attributes = array();
        foreach ($this->attributes AS $attribute)
        {
            $attributes[$attribute->name] = $attribute->value;
        }
        return $attributes;
    }

    function _initAttribute($name, $value, $core=FALSE)
    {
        $this->attributes[$name] = new _data_source_attribute($name, $value);
        if ($core)
        {
            $this->attributes[$name]->core = TRUE;
            $this->attributes[$name]->dirty = TRUE;
        }
    }
    /*
     * CONTRIBUTOR PAGES
     */

    function get_groups()
    {

        $groups = array();

        $this->db->select('value');
        $this->db->from('registry_object_attributes');
        $this->db->join('registry_objects', 'registry_objects.registry_object_id = registry_object_attributes.registry_object_id');
        $this->db->where(array('registry_objects.data_source_id'=>$this->id, 'registry_object_attributes.attribute'=>'group'));
        $query = $this->db->get();

        if ($query->num_rows() == 0)
        {
            return $groups;
        }
        else
        {
            foreach($query->result_array() AS $group)
            {
                $groups[] =  $group['value'];
            }
        }

        return array_unique($groups);

    }

    function reindexAllRecords() {

        $targetRecords = array();

        $this->db->select('key');
        $this->db->from('registry_objects');
        $this->db->where(array('data_source_id'=>$this->id));

        $query = $this->db->get();
        if ($query->num_rows())
        {
            foreach($query->result_array() AS $i)
            {
                $targetRecords[] =  $i['key'];
            }
        }

        // $this->_CI->importer->_enrichRecords($targetRecords);
        $this->_CI->importer->_reindexRecords($targetRecords);
    }

    /*
     * LOGS
     */
    function append_log($log_message, $log_type = "info", $log_class="data_source", $harvester_error_type=NULL)
    {
        $logContent = [
            "data_source_id" => $this->id,
            "date_modified" => time(),
            "type" => $log_type,
            "log" => $this->clean_log_message($log_message),
            "class" => $log_class,
            "harvester_error_type" => $harvester_error_type
        ];
        \ANDS\Util\NotifyUtil::notify(
            'datasource.'.$this->id.'.log',
            json_encode($logContent, true)
        );
        $this->db->insert("data_source_logs",$logContent);
        return $this->db->insert_id();
    }

    private function clean_log_message($log_message)
    {
        // Some crude logic to try and clean up the log message if we have a heap of duplicate records (rubbish Geonetwork OAI providers)
        if(is_array($log_message))
            $log_message = var_export($log_message, true);
        if (strlen($log_message) > 500)
        {
            $log_message = preg_replace('/Ignored a record received twice in this harvest.*\n/', '',$log_message,-1, $replacements);
            if ($replacements)
            {
                $log_message .= NL .$replacements . " duplicate record(s) were ignored in the harvest feed.";
            }
        }
        return $log_message;
    }

    function get_logs($offset = 0, $count = 10, $logid=null, $log_class='all', $log_type='all')
    {
        $logs = array();
        $this->db->from('data_source_logs');
        $this->db->limit($count, $offset);
        $this->db->where('data_source_id', $this->id);
        if($logid) $this->db->where('id >', $logid);
        if($log_class!='all') $this->db->where('class', $log_class);
        if($log_type!='all') $this->db->where('type', $log_type);
        $this->db->order_by("id", "desc");
        $query = $this->db->get();
        if ($query->num_rows() > 0){
            foreach ($query->result_array() AS $row)
            {
                $logs[] = $row;
            }
        }
        return $logs;
    }

    function get_log_size($log_type)
    {
        $this->db->from("data_source_logs");
        $this->db->where(array("data_source_id"=>$this->id));
        if($log_type!='all') $this->db->where('type', $log_type);
        return $this->db->count_all_results();
    }

    function clear_logs() {
        $this->db->where(array("data_source_id" => $this->id));
        $this->db->delete("data_source_logs");
        return;
    }

	function getHarvestStatus() {
		$query = $this->db->get_where('harvests', array('data_source_id'=>$this->id));
		if($query->num_rows()>0){
			return $query->result_array();
		}
	}

	function requestNewharvest()
	{
		$this->cancelAllharvests();
		$this->requestHarvest();
	}

    // HARVESTER SPECIFIC FUNCTIONS //


    function setHarvestRequest($mode='HARVEST', $scheduled=true)
    {
        date_default_timezone_set('Australia/Canberra');
        $harvestId = $this->getHarvestRequest('harvest_id');
        if($scheduled)
        {
            $harvestDate = strtotime($this->getAttribute("harvest_date"));
            $nextRun = getNextHarvestDate($harvestDate, $this->harvest_frequency);
        }
        else
        {
            $nextRun = time();
        }

        $status = 'SCHEDULED';
        $batchNumber = strtoupper(sha1($nextRun));
        if($harvestId)
        {
            $this->db->where("harvest_id", $harvestId);
            $result = $this->db->update("harvests", array('status'=>$status, 'next_run'=>date( 'Y-m-d H:i:s', $nextRun), 'batch_number'=>$batchNumber, 'mode'=>$mode));
            if (!$result) {
                throw new Exception($this->db->_error_message());
            }
            return $harvestId;
        }
        else
        {
            $result = $this->db->insert("harvests", array("data_source_id" => $this->id, 'status'=>$status, 'next_run'=>date( 'Y-m-d H:i:s', $nextRun), 'batch_number'=>$batchNumber, 'mode'=>$mode));
            if (!$result) {
                throw new Exception($this->db->_error_message());
            }
            return $this->db->insert_id();
        }
    }

    function setHarvestMessage($msg) {
        $harvest = $this->db->get_where('harvests', array('data_source_id'=>$this->id));
        if($harvest->num_rows() > 0) {
            $harvest = $harvest->result();
            $harvest = $harvest[0];
            $message = $harvest->message;
            $message = json_decode($message, true);
            $message['message'] = $msg;
            $message = json_encode($message);
            $this->db->where("data_source_id", $this->id);
            $this->db->update('harvests', array('message'=>$message));
        }
    }

    function clearHarvestError() {
        $harvest = $this->db->get_where('harvests', array('data_source_id'=>$this->id));
        if($harvest->num_rows() > 0) {
            $harvest = $harvest->result();
            $harvest = $harvest[0];
            $message = $harvest->message;
            $message = json_decode($message, true);
            if(isset($message['error']['log'])) {
                $message['error']['log'] = '';
            }            
            $message = json_encode($message);
            $this->db->where("data_source_id", $this->id);
            $this->db->update('harvests', array('message'=>$message));
        }
    }

    function getHarvestErrorLog() {
        $harvest = $this->db->get_where('harvests', array('data_source_id'=>$this->id));
        if($harvest->num_rows() > 0) {
            $harvest = $harvest->result();
            $harvest = $harvest[0];
            $message = $harvest->message;
            $message = json_decode($message, true);
            $error_log = $message['error']['log'];
            return $error_log;
        }
    }

    function updateImporterMessage($msg) {
        if(is_array($msg)) $msg = json_encode($msg);
        $this->db->where("data_source_id", $this->id);
        $this->db->update('harvests', array('importer_message'=>$msg));
    }

    function setNextHarvestRun($harvestId) {
        $harvestDate = strtotime($this->getAttribute("harvest_date"));
        date_default_timezone_set('Australia/Canberra');
        $previousRun = date( 'Y-m-d\TH:i:s.uP', time());
        $nextRun = getNextHarvestDate($harvestDate, $this->harvest_frequency);
        if($nextRun){
            $status = 'SCHEDULED';
            $batchNumber = strtoupper(sha1($nextRun));
            $this->db->where("harvest_id", $harvestId);
            try{
                $this->db->update('harvests', array(
                        'status' => $status,
                        'last_run' => $previousRun,
                        'next_run' => date( 'Y-m-d\TH:i:s.uP', $nextRun),
                        'batch_number' => $batchNumber,
                        'mode' => 'HARVEST',
                        'message' => null,
                    )
                );
            } catch (Exception $e) {
                throw new Exception('Cannot update harvest requests '.$e);
            }
        }
        
    }

    function updateHarvestStatus($harvestId, $status)
    {
        $this->db->where("harvest_id", $harvestId);
        $this->db->update("harvests", array('status'=>$status));
    }

    function getHarvestRequest($index=null)
    {
        $query = $this->db->get_where("harvests", array("data_source_id"=>$this->id));
        if($query->num_rows()>0){
            $row = $query->result_array();
            if($index)
                return $row[0][$index];
            else
                return $row[0];
        }
        else
            return null;
    }

    function cancelHarvestRequest(){
        $harvestId = $this->getHarvestRequest('harvest_id');
        $this->db->where("harvest_id", $harvestId);
        $this->db->update("harvests", array('status'=>"STOPPED"));
        $query = $this->db->get_where("harvests", array("harvest_id"=>$harvestId));
        if($query->num_rows()>0){
            $row = $query->result_array();
            return $row[0];
        }
        else
            return null;
    }


    /*
     * 	STATS
     */

    function updateStats()
    {
        $this->_CI->load->model("registry_object/registry_objects", "ro");

        $this->db->where(array('data_source_id'=>$this->id));
        $this->setAttribute("count_total", ($this->db->count_all_results('registry_objects') ?: "0"));

        foreach ($this->_CI->ro->valid_classes AS $class)
        {
            $this->db->where(array('data_source_id'=>$this->id, 'class'=>$class))->where('status !=', 'DELETED');
            $this->setAttribute("count_$class", ($this->db->count_all_results('registry_objects') ?: "0"));
        }

        foreach ($this->_CI->ro->valid_status AS $status)
        {
            $this->db->where(array('data_source_id'=>$this->id, 'status'=>$status));
            $this->setAttribute("count_$status", ($this->db->count_all_results('registry_objects') ?: "0"));
        }
        foreach ($this->_CI->ro->valid_levels AS $attribute_name => $level)
        {
            // SO MUCH repetitiveness ;-(
            $this->db->join('registry_object_attributes', 'registry_object_attributes.registry_object_id = registry_objects.registry_object_id');
            $this->db->where(array('data_source_id'=>$this->id, 'attribute'=>'quality_level', 'value'=>$level))->where('status !=', 'DELETED');
            $this->setAttribute("count_$attribute_name", ($this->db->count_all_results('registry_objects') ?: "0"));
        }
        $this->save();
        return $this;
    }

    /**
     * Get the SOLR Indexed count for this data source
     * @return mixed
     * @throws Exception
     */
    public function getIndexedCount()
    {
        $this->_CI->load->library('solr');
        $this->_CI->solr->init()->setOpt('fq', '+data_source_id:'.$this->getID());
        $result = $this->_CI->solr->executeSearch(true);
        if ($result && isset($result['response'])) {
            return $result['response']['numFound'];
        } else {
            throw new Exception ("Failed getting indexed count for data source ". $this->getID());
        }
    }

    /*
     * magic methods
     */
    function __toString()
    {
        $return = sprintf("%s (%s) [%d]", $this->getAttribute("key", TRUE), $this->getAttribute("slug", TRUE), $this->id) . BR;
        foreach ($this->attributes AS $attribute)
        {
            $return .= sprintf("%s", $attribute) . BR;
        }
        return $return;
    }

    /**
     * This is where the magic mappings happen (i.e. $data_source->record_owner)
     *
     * @ignore
     */
    function __get($property)
    {
        if($property == "id")
        {
            return $this->id;
        }
        else
        {
            return call_user_func_array(array($this, "getAttribute"), array($property));
        }
    }

    /**
     * This is where the magic mappings happen (i.e. $data_source->record_owner)
     *
     * @ignore
     */
    function __set($property, $value)
    {
        if($property == "id")
        {
            $this->id = $value;
        }
        else
        {
            return call_user_func_array(array($this, "setAttribute"), array($property, $value));
        }
    }

    function consolidateHarvestLogs($harvestId, $prepended_message = '')
    {
        $this->db->select('log')->from('data_source_logs')->where(array('data_source_id'=>$this->id, 'class'=>'oai'))->like('log', 'harvest ID: ' . $harvestId, 'both')->order_by('id','DESC');
        $query = $this->db->get();

        $accumulated_log = '';
        if ($query->num_rows() > 0)
        {
            foreach ($query->result_array() AS $result)
            {
                $result['log'] = preg_replace('/Received .*? new records from the OAI provider.*\n.*---.*\n/sm', '',$result['log'],-1, $replacements);
                $accumulated_log .= $result['log'] . NL;
            }
        }

        $this->db->delete('data_source_logs', array('data_source_id'=>$this->id, 'class'=>'oai'));
        return $accumulated_log;
    }

}
/**
 * Data Source Attribute
 *
 * A representation of attributes of a data source, allowing
 * the state of the attribute to be mainted, so that calls
 * to ->save() only write dirty data to the database.
 *
 * @author Ben Greenwood <ben.greenwood@ardc.edu.au>
 * @version 0.1
 * @package ands/datasource
 * @subpackage helpers
 *
 */
class _data_source_attribute
{
    public $name;
    public $value;
    public $core = FALSE; 	// Is this attribute part of the core table or the attributes annex
    public $dirty = FALSE;	// Have we changed it since it was read from the DB
    public $new = FALSE;	// Is this new since we read from the DB

    function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @ignore
     */
    function __toString()
    {
        return sprintf("%s: %s", $this->name, $this->value) . ($this->dirty ? " (Dirty)" : "") . ($this->core ? " (Core)" : "") . ($this->new ? " (New)" : "");
    }
}