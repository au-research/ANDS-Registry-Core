<?php use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Registry\Events\Event\PrimaryKeyUpdatedEvent;
use ANDS\Registry\Events\EventServiceProvider;
use ANDS\Util\Config;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Core Data Source controller
 *
 *
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 *
 */
class Data_source extends MX_Controller {

	/**
	 * index page, display the angularJS data source app view
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return view
	 */
	public function index() {
		acl_enforce('REGISTRY_USER');
		$data['title'] = 'Manage My Data Sources';
		$data['scripts'] = array('ds_app');
		$data['js_lib'] = array('core', 'ands_datepicker','vocab_widget','rosearch_widget', 'angular', 'socket.io');
		$this->load->view("datasource_app", $data);
	}

	/**
	 * get a JSON presentation of a data source
	 * if there's no data source speficied, get all owned datasource
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param  data_source_id $id
	 * @return json
	 */
	public function get($id=false) {
		//prepare
		acl_enforce('REGISTRY_USER');

		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');

		$jsonData = array();
		$jsonData['status'] = 'OK';

		$this->load->model("data_sources","ds");
		if(!$id){
			// $this->benchmark->mark('code_start');
			$dataSources = $this->ds->getOwnedDataSources();
			// $this->benchmark->mark('code_end');
			// dd($this->benchmark->elapsed_time('code_start', 'code_end'));
		} elseif ($id && $id!=null) {
			// $this->benchmark->mark('code_start');
			ds_acl_enforce($id);
			$ds = $this->ds->getByID($id);
			// Should look at updating stats
            $ds->updateStats();
			$dataSources = array();
			$dataSources[] = $ds;
			// $this->benchmark->mark('code_end');
			// dd($this->benchmark->elapsed_time('code_start', 'code_end'));
		}
		$this->load->model("registry_object/registry_objects", "ro");

		$items = array();
		foreach($dataSources as $ds){
			$item = array();
			$item['title'] = $ds->title;
			$item['id'] = $ds->id;
			$item['counts'] = array();
			foreach ($this->ro->valid_status AS $status){
				if($ds->getAttribute("count_$status")>0){
					array_push($item['counts'], array('status' => $status, 'count' =>$ds->getAttribute("count_$status"), 'name'=>readable($status)));
				}
			}
			$item['qlcounts'] = array();
			foreach ($this->ro->valid_levels AS $level){
				array_push($item['qlcounts'], array('level' => $level, 'title' => ($level==4 ? 'Gold Standard Records' : 'Quality Level '.$level), 'count' =>$ds->getAttribute("count_level_$level")));
			}
			$item['classcounts'] = array();
			foreach($this->ro->valid_classes as $class){
				if($ds->getAttribute("count_$class")>0)array_push($item['classcounts'], array('class' => $class, 'count' =>$ds->getAttribute("count_$class"),'name'=>readable($class)));
			}
			$item['key']=$ds->key;
			$item['record_owner']=$ds->record_owner;
			$item['notes']=$ds->notes;



			if($id && $ds){


                $harvester_methods = get_db_config_item('harvester_methods');
                if($harvester_methods) $item['harvester_methods'] = $harvester_methods;
                
                foreach($ds->attributes as $attrib=>$value){
					$item[$attrib] = $value->value;
				}

				if(isset($item['crosswalks'])) {
					// $item['crosswalks'] = json_decode($item['crosswalks'], true);
				}

				if(isset($item['harvest_date'])) {
					date_default_timezone_set('Australia/Canberra');
					$item['harvest_date'] = date( 'Y-m-d H:i:s', strtotime($item['harvest_date']));
				}

			}

			array_push($items, $item);
		}

		if($id && $ds) {
			$logs = $ds->get_logs(0, 10, null, 'all', 'all');
			$items[0]['logs'] = $logs;
		}

		$jsonData['items'] = $items;
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}

    /**
     * TODO Refactor this to not use CodeIgniter uploads
     *
     * @param bool $id
     * @throws Exception
     */
    public function upload($id=false) {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');

        $config = \ANDS\Util\Config::get('app');
        $path = array_dot($config)['storage.uploads.path'];
        $upload_path = rtrim($path, '/').'/harvester_crosswalks/';

		if(!is_dir($upload_path)) {
			if(!mkdir($upload_path, 0755, true)) throw new Exception('Upload path are not created correctly. Contact server administrator');
		}
		$upload_path = $upload_path.$id.'/';
		if(!is_dir($upload_path)) {
			if(!mkdir($upload_path, 0755, true)) throw new Exception('Upload path are not created correctly. Contact server administrator');
		}

		$config['upload_path'] = $upload_path;
		$config['allowed_types'] = 'xml|xsl';
		$config['overwrite'] = true;
		$config['max_size']	= '500';
		$this->load->library('upload', $config);

		if(!$this->upload->do_upload('file')) {
            $upload_file_exceeds_limit = "The uploaded file exceeds the maximum allowed size in your PHP configuration file.";
            $upload_invalid_filesize  = "The file you are attempting to upload is larger than the permitted size.";
            $upload_invalid_filetype = "The filetype you are attempting to upload is not allowed.";
            $theError = $this->upload->display_errors();
            if(strrpos($theError, $upload_file_exceeds_limit) > 0 || strrpos($theError, $upload_invalid_filesize) > 0){
                $theError = "Maximum file size exceeded. Please select a file smaller than 500KB.";
            }
            elseif(strrpos($theError, $upload_invalid_filetype) > 0){
                $theError = "Unsupported file format. Please select an xml or xsl.";
            }
			echo json_encode(
				array(
					'status'=>'ERROR',
					'message' => $theError
				)
			);
		} else {
			echo json_encode(
				array(
					'status'=>'OK',
					'message' => 'File uploaded successfully!',
					'data' => $this->upload->data()
				)
			);
		}
	}

	/**
	 * get data source log
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param  data_source_id $id
	 * @param  integer $offset
	 * @return json
	 */
	public function get_log($id=false, $offset=0, $limit=10, $logid=null) {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');

		if(!$id) throw new Exception('ID must be specified');
		ds_acl_enforce($id);

		$this->load->model("data_sources","ds");
		$ds = $this->ds->getByID($id);
		$logs = $ds->get_logs($offset, $limit, $logid, 'all', 'all');
		$jsonData['status'] = 'OK';
		$jsonData['items'] = $logs;
		echo json_encode($jsonData);
	}

	/**
	 * get harvester status
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param  data_source_id $id
	 * @return json
     * CC-460 changes
     * since last_run date is saved as UTC we need to convert it into the client's timezone
     * at this stage just use the server's time zone and try to aquire the client's using javascript in the browser
	 */
	public function harvester_status($id=false) {
		//prepare
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');

		if(!$id) throw new Exception('ID must be specified');

		$this->load->model("data_sources","ds");
		$ds = $this->ds->getByID($id);
		$status = $ds->getHarvestStatus();
		$jsonData['status'] = 'OK';
		if($status){
			if($ds->harvest_frequency==''){//once off
				$status[0]['last_run'] = $status[0]['next_run'];
				$status[0]['next_run'] = false;
			}else{
                date_default_timezone_set('UTC');
                $lastRun = strtotime($status[0]['last_run']);
                $server_timezone = \ANDS\Util\Config::get('app.timezone');
                date_default_timezone_set($server_timezone);
                $status[0]['last_run'] = date("Y-m-d H:i:s", $lastRun);
            }
			$jsonData['items'] = $status;
		} else {
			$jsonData['items'] = array(
				array('status' => 'IDLE')
			);
		}

		echo json_encode($jsonData);
	}


	/**
	 * Save a data source
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return [json] response.status
	 */
	public function save() {

		//prepare
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		$this->load->model("data_sources","ds");
		$this->load->model("registry/registry_object/registry_objects","ro");
		$data = file_get_contents("php://input");
		$data = json_decode($data, true);
		$data = $data['data'];
		$id = $data['id'];
		if(!$id) throw new Exception('Data source ID is not specified');

		//access control
		acl_enforce('REGISTRY_USER');
		ds_acl_enforce($id);

		//data source
		$ds = $this->ds->getByID($id);
		if(!$ds) throw new Exception('Data source not found with the ID: '. $id);
		$resetHarvest = false;

		//construct a list of possible attributes, attributes that are not in this list will not get updated
		$valid_attributes = array_merge(array_keys($ds->attributes()), array_keys($ds->harvesterParams));
		$valid_attributes = array_merge($valid_attributes, $ds->primaryRelationship);
		$valid_attributes = array_merge($valid_attributes, array_keys($ds->stockAttributes));
		$valid_attributes = array_merge($valid_attributes, array_keys($ds->extendedAttributes));
		$valid_attributes = array_unique($valid_attributes);

		//unset values based on previous values
		if(isset($data['create_primary_relationships'])){
			if ($data['create_primary_relationships']===0 || $data['create_primary_relationships']===false || $data['create_primary_relationships']==='f') {
                $data['create_primary_relationships'] = 0;
                $data['primary_key_1']='';
				$data['primary_key_2']='';
			}
		}

		if(isset($data['primary_key_1']) && $data['primary_key_1']==''){
			$data['primary_key_1'] = '';
			$data['service_rel_1'] = '';
			$data['activity_rel_1'] = '';
			$data['collection_rel_1'] = '';
			$data['party_rel_1'] = '';
		}
		if(isset($data['primary_key_2']) && $data['primary_key_2']=='') {
			$data['primary_key_2'] = '';
			$data['service_rel_2'] = '';
			$data['activity_rel_2'] = '';
			$data['collection_rel_2'] = '';
			$data['party_rel_2'] = '';
		}
        $this->testAndActOnPrimaryKeyConfigChanges($ds->id, $data);
		if(isset($data['crosswalks'])) {
			$data['crosswalks'] = json_encode($data['crosswalks']);
		}

        if(isset($data['user_defined_params'])) {
            $data['user_defined_params'] = json_encode($data['user_defined_params']);
        }

		$updated_values = array();

		//update each attribute
		foreach($valid_attributes as $attrib) {

			// if($attrib=='primary_key_1') throw new Exception($data['primary_key_1']);
			$new_value = '';
			if(is_integer($attrib) && $attrib == 0) {
				continue;
			} elseif (isset($data[$attrib])) {
				$new_value = trim($data[$attrib]);
			} elseif (in_array($attrib, array_keys($ds->harvesterParams))) {
				$new_value = $ds->harvesterParams[$attrib];
			} elseif (in_array($attrib, $ds->primaryRelationship)){
				$new_value = '';
			}

            if ($attrib == "export_dci") {
                if ($new_value == DB_FALSE || $new_value == "" || $new_value == "0") {
                    \ANDS\Registry\Providers\DCI\DCI::where('registry_object_data_source_id', $ds->id)->delete();
                } else {
                    $this->queueDCIGenerationTask($ds->id);
                }
            }

			//detect qa_flag changed to false
			if($attrib=='qa_flag' && ($new_value=='f' || !$new_value || $new_value==DB_FALSE) && $new_value != $ds->{$attrib}){

				$newStatus = PUBLISHED;
				if ($data['manual_publish']) {
                    $newStatus = APPROVED;
                }

				//update all submitted for assessment records to new status
                $this->updateAllRecordsStatusMatching($ds->id, SUBMITTED_FOR_ASSESSMENT, $newStatus);

				//update all assessment in progress records to new status
                $this->updateAllRecordsStatusMatching($ds->id, ASSESSMENT_IN_PROGRESS, $newStatus);
			}

			if($new_value != $ds->{$attrib} && in_array($attrib, array_keys($ds->harvesterParams))){
			   $resetHarvest = true;
			}

			//detect manual_publish flag changed to false
			if($attrib=='manual_publish' && ($new_value=='f' || !$new_value || $new_value==DB_FALSE) && $new_value!=$ds->{$attrib}){
				//publish all approved record
                $this->updateAllRecordsStatusMatching($ds->id, APPROVED, PUBLISHED);
            }

            // handle dci

      //some value are not meant to be updated
      $blocked_value = array('data_source_id', 'created', 'key');
			//update the actual value
			if(!is_null($new_value) && $new_value != $ds->{$attrib} && !in_array($attrib, $blocked_value)) {
				$ds->{$attrib} = $new_value;
				$updated_values[] = array(
					'key' => $attrib,
					'value' => $new_value
				);
			}
		}

		$updated = '';
		foreach ($updated_values as $kv) {
			if($kv['value']) {
				$updated .= $kv['key']. ' is set to '. $kv['value'].NL;
			} else {
				$updated .= 'unset '. $kv['key'].NL;
			}
		}

		//harvester and primary relationships reset
		try {
			if($resetHarvest && $data['uri'] != '' && $data['uri'] != 'http://') {
				$this->trigger_harvest($ds->id, true);
			}
		} catch (Exception $e) {
			$ds->append_log($e, 'error');
			throw new Exception($e);
		}

		//save the record
		try{
			$ds->save();
			$ds->append_log(
				"The data source settings were updated..." . NL . NL .
				"Data Source was updated by: " . $this->user->name() . " (" . $this->user->localIdentifier() . ") at " . display_date().NL.
				$updated
				);
		} catch (Exception $e) {
			$ds->append_log($e, 'error');
			throw new Exception($e);
		}

        // sync data source with mycelium
        initEloquent();
        $dataSource = \ANDS\Repository\DataSourceRepository::getByID($ds->id);
        $client = new MyceliumServiceClient(Config::get('mycelium.url'));
        $client->updateDataSource($dataSource);

		//if all goes well

		echo json_encode(
			array(
				'status' => 'OK',
				'message' => 'Saved Success'
			)
		);

	}

    /**
     * @param $ds_id
     * @param $data
     * @return void
     * @throws Exception
     */
    private function testAndActOnPrimaryKeyConfigChanges($ds_id, $data){
        $ds = $this->ds->getByID($ds_id);
        $old_attributes = $ds->attributes();
        // if it wasn't set prior and still not set, then nothing to do here
        if((isset($old_attributes['create_primary_relationships']) && $old_attributes['create_primary_relationships'] == 0)
            && (isset($data['create_primary_relationships']) && $data['create_primary_relationships'] == 0)){
            return;
        }
        // check and act if primary_key_1 changed or any of the relationships was set or unset
        $primary_key_indexes = ['1','2'];
        foreach($primary_key_indexes as $pki){
            $event = $this->getEventData($ds_id, $data , $old_attributes, $pki);
            EventServiceProvider::dispatch(PrimaryKeyUpdatedEvent::from($event));
        }
    }

    /**
     * @param $ds_id
     * @param $data
     * @param $old_attributes
     * @param $pki
     * @return array
     */
    private function getEventData($ds_id, $data , $old_attributes, $pki){
        $event = [];
        $event['data_source_id'] = $ds_id;
        if(isset($data['primary_key_'.$pki])) {
            $event['new_primary_key'] = $data['primary_key_'.$pki];
        }
        if(isset($old_attributes['primary_key_'.$pki])) {
            $event['old_primary_key'] = $old_attributes['primary_key_'.$pki];
        }
        // check and set the relationships old and new
        $related_classes = ['service', 'activity', 'party', 'collection'];
        foreach($related_classes as $rc) {
            if(isset($data[$rc.'_rel_'.$pki])) {
                $event['new_'.$rc.'_relationship_type'] = $data[$rc.'_rel_'.$pki];
            }
            if(isset($old_attributes[$rc.'_rel_'.$pki]))
            {
                $event['old_'.$rc.'_relationship_type'] = $old_attributes[$rc.'_rel_'.$pki];
            }
        }
        return $event;
    }

	/**
	 * updating all PUBLISHED records relationship metadata
	 *
	 * @param $dataSourceID
	 */
	public function discover_import_services($dataSourceID)
	{
		initEloquent();
		$now = time();
		$batch_id = strtoupper(sha1($now));
		$dataSource = \ANDS\Repository\DataSourceRepository::getByID($dataSourceID);

		// all published records
		$records = \ANDS\RegistryObject::where('data_source_id', $dataSourceID)->where('status', PUBLISHED)->where('class', 'collection');

		// getting the count
		$total = $records->count();
		if ($total === 0) {
			$message =
				"Discovering Service Objects for Datasource". NL.
				"No PUBLISHED Collections in Datasource";
			$dataSource->appendDataSourceLog($message, 'info', 'SERVICE DISCOVERY');
			return;
		}

		$ids = $records->get()->pluck('registry_object_id')->toArray();

		// task initialisation
		$importTask = new \ANDS\API\Task\ImportTask();

		// FIXME: $dataSource->harvest->harvest_id is NULL if datasource never had a harvest!!

		$importTask->init([
			'name' => "Background Task for $dataSource->title($dataSourceID) ServiceDiscovery",
			'params' => http_build_query([
				'ds_id' => $dataSourceID,
				'harvest_id' => $dataSource->harvest->harvest_id,
				'batch_id' => $batch_id,
				'targetStatus' => 'DRAFT',
				'pipeline' => 'ServiceDiscovery'
			])
		]);
		$importTask
			->skipLoadingPayload()
			->enableRunAllSubTask()
			->setTaskData("imported_collection_ids", $ids);
		$importTask->initialiseTask();

		// sending the task to the background
		$importTask->sendToBackground();

		// returning the ID and log that
		$id = $importTask->getId();
		$message =
			"Service Discovery Task is Scheduled for the Datasource". NL.
			"TaskID: $id";
		$dataSource->appendDataSourceLog($message, 'info', 'SERVICE DISCOVERY');
	}

    /**
     * Update the status of all records that match a particular status
     * @param $dataSourceID
     * @param $oldStatus
     * @param $newStatus
     */
    public function updateAllRecordsStatusMatching($dataSourceID, $oldStatus, $newStatus)
    {
        initEloquent();
        $dataSource = \ANDS\Repository\DataSourceRepository::getByID($dataSourceID);

        //TODO: check dataSource existence?

        // find all records matching the oldStatus
        $records = \ANDS\RegistryObject::where('data_source_id', $dataSourceID)->where('status', $oldStatus);

        // getting the count
        $total = $records->count();
        if ($total === 0) {
            return;
        }

        // get a list of ids of the affected records
        $ids = $records->get()->pluck('registry_object_id')->toArray();

        // task initialisation
        $importTask = new \ANDS\API\Task\ImportTask();
        $importTask->init([
            'name' => "Background Task for $dataSource->title($dataSourceID) Updating $total records to $newStatus",
            'params' => http_build_query([
                'ds_id' => $dataSourceID,
                'targetStatus' => $newStatus,
                'pipeline' => 'PublishingWorkflow'
            ])
        ]);
        $importTask
            ->skipLoadingPayload()
            ->enableRunAllSubTask()
            ->setTaskData("affectedRecords", $ids);
        $importTask->initialiseTask();

        // sending the task to the background
        $importTask->sendToBackground();

        // returning the ID and log that
        $id = $importTask->getId();
        $message =
            "Updating $total records to $newStatus as per data source settings changes". NL.
            "TaskID: $id";
        $dataSource->appendDataSourceLog($message, 'info', 'IMPORTER');
	}

    public function queueDCIGenerationTask($dataSourceID)
    {
        initEloquent();
        $dataSource = \ANDS\Repository\DataSourceRepository::getByID($dataSourceID);

        //TODO: check dataSource existence?

        // find all records matching the oldStatus
        $records = \ANDS\RegistryObject::where('data_source_id', $dataSourceID)
            ->where('status', 'PUBLISHED');

        // getting the count
        $total = $records->count();
        if ($total === 0) {
            return;
        }

        // get a list of ids of the affected records
        $ids = $records->get()->pluck('registry_object_id')->toArray();

        // task initialisation
        $importTask = new \ANDS\API\Task\ImportTask();
        $importTask->init([
            'name' => "Background Task for $dataSource->title($dataSourceID) Generating DCI for $total records",
            'params' => http_build_query([
                'ds_id' => $dataSourceID,
                'targetStatus' => 'PUBLISHED',
                'pipeline' => 'MetadataGenerationWorkflow'
            ])
        ]);
        $importTask
            ->skipLoadingPayload()
            ->enableRunAllSubTask()
            ->setTaskData("affectedRecords", $ids);
        $importTask->initialiseTask();

        // sending the task to the background
        $importTask->sendToBackground();

        // returning the ID and log that
        $id = $importTask->getId();
        $message =
            "Generating DCI for $total records as per data source settings changes". NL.
            "TaskID: $id";
        $dataSource->appendDataSourceLog($message, 'info', 'IMPORTER');
	}

	/**
	 * Same as index
	 */
	public function manage(){
		$this->index();
	}

	/**
	 * Sets the slugs for all datasources
	 *
	 *
	 * @author Liz Woods
	 * @param [
	 * @todo ACL on which data source you have access to, error handling
	 * @return
	 */
	public function setDatasourceSlugs(){

		$this->load->model("data_sources","ds");
	 	$dataSources = $this->ds->getAll(0,0);//get everything  XXX: getOwnedDataSources
		foreach($dataSources as $ds){
			$ds->setSlug($ds->title);
			$ds->save();
		}
	}

	/**
	 * Manage My Records (MMR Screen)
	 *
	 *
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @package ands/registryobject
	 * @param data_source_id | optional
	 * @return [HTML] output
	 */
	public function manage_records($data_source_id=false){
		acl_enforce('REGISTRY_USER');
		ds_acl_enforce($data_source_id);

		$data['title'] = 'Manage My Records';
		$this->load->model('data_source/data_sources', 'ds');
		if($data_source_id){
			$data_source = $this->ds->getByID($data_source_id);
			if(!$data_source) show_error("Unable to retrieve data source id = ".$data_source_id, 404);
			$data_source->updateStats();//TODO: XXX
			$data['ds'] = $data_source;
		}else{
			throw new Exception("Data Source must be provided");
		}
		$data['scripts'] = array('mmr');
		$data['js_lib'] = array('core');
		$this->load->view("manage_my_record", $data);
	}

	public function manage_deleted_records($data_source_id=false, $offset=0, $limit=10){
		acl_enforce('REGISTRY_USER');
		ds_acl_enforce($data_source_id);

		$data['title'] = 'Manage Deleted Records';
		$data['scripts'] = array('ds_history');
		$data['js_lib'] = array('core','prettyprint');
        $deletedRecords = array();
        initEloquent();
        $dataSource = \ANDS\Repository\DataSourceRepository::getByID($data_source_id);

        $data['ds'] = array('id'=>$dataSource->getAttribute('data_source_id'), 'title'=>$dataSource->getAttribute('title'));

        $data['record_count'] = \ANDS\Repository\RegistryObjectsRepository::getCountByDataSourceIDAndStatus($data_source_id, 'DELETED');

        $records = \ANDS\Repository\RegistryObjectsRepository::getRecordsByDataSourceIDAndStatus($data_source_id, 'DELETED', $offset, $limit);
        // all deleted records

		if(sizeof($records) > 0){
            foreach($records as $record){
                // RDA-749 Some records that were deleted using the "wipe" method don't have record data and can not be recovered
                
                $current_data = $record->getCurrentData();
                if($current_data != null){
                    $deletedRecords[$record->key] = array('title'=>$record->title,'key'=>$record->key,
                        'id'=>$record->registry_object_id, 'record_data'=>$current_data->data,
                        'deleted_date'=>timeAgo($record->getRegistryObjectAttributeValue('updated')));
                }else{
                    $data['record_count'] = $data['record_count'] -1;
                }
            }
		}

		$data['deleted_records'] = $deletedRecords;
		$data['offset'] = $offset;
		$data['limit'] = $limit;
		$this->load->view('manage_deleted_records', $data);
	}

	/**
	 * Get MMR AJAX data for MMR
	 *
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param  [int] 	$data_source_id
	 * @return [json]
	 */
	public function get_mmr_data($data_source_id){
		//administrative and loading stuffs
		acl_enforce('REGISTRY_USER');
		ds_acl_enforce($data_source_id);

		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$this->load->model('data_source/data_sources', 'ds');
		$this->load->model('registry_object/registry_objects', 'ro');

		//getting the data source and parse into the jsondata array
		$data_source = $this->ds->getByID($data_source_id);
		foreach($data_source->attributes as $attrib=>$value){
			$jsonData['ds'][$attrib] = $value->value;
		}

		//QA and Auto Publish check, valid_statuses are populated accordingly
		$qa = $data_source->qa_flag=='t' || $data_source->qa_flag==DB_TRUE ? true : false;
		$manual_publish = ($data_source->manual_publish=='t' || $data_source->manual_publish==DB_TRUE) ? true: false;
		$jsonData['valid_statuses'] = array('DRAFT', 'PUBLISHED');
		if($qa) {
			array_push($jsonData['valid_statuses'], 'MORE_WORK_REQUIRED', 'SUBMITTED_FOR_ASSESSMENT', 'ASSESSMENT_IN_PROGRESS');
		}
		if($manual_publish){
			array_push($jsonData['valid_statuses'], 'APPROVED');
		}

		$filters = $this->input->post('filters');
		if(isset($filters['filter']['status'])) $jsonData['valid_statuses'] = array($filters['filter']['status']);

		//statuses is the main result array
		$jsonData['statuses'] = array();
		foreach($jsonData['valid_statuses'] as $s){

			//declarations

			$args = array();//array for filtering
			$no_match = false; //check match on filter

			$st = array('display_name'=>str_replace('_', ' ', $s), 'name'=>$s, 'menu'=>array());
			array_push($st['menu'], array('action'=>'select_all', 'display'=>'Select All'));
			array_push($st['menu'], array('action'=>'select', 'display'=>'Select'));
			array_push($st['menu'], array('action'=>'view', 'display'=>'<i class="icon icon-eye-open"></i> View this Record'));
			array_push($st['menu'], array('action'=>'edit', 'display'=>'<i class="icon icon-edit"></i> Edit this Record'));
			array_push($st['menu'], array('action'=>'flag', 'display'=>'Flag'));
			switch($s){
				case 'DRAFT':
					$st['ds_count']=$data_source->count_DRAFT;
					if($qa){
						$st['connectTo']='SUBMITTED_FOR_ASSESSMENT';
						array_push($st['menu'], array('action'=>'to_submit', 'display'=>'Submit for Assessment'));
					}else{
						if($manual_publish){
							$st['connectTo']='APPROVED';
							array_push($st['menu'], array('action'=>'to_approve', 'display'=>'Approve'));
						}else{
							$st['connectTo']='PUBLISHED';
							array_push($st['menu'], array('action'=>'to_publish', 'display'=>'Publish'));
						}
					}
					break;
				case 'MORE_WORK_REQUIRED':
					$st['ds_count']=$data_source->count_MORE_WORK_REQUIRED;
					$st['connectTo']='DRAFT';
					array_push($st['menu'], array('action'=>'to_draft', 'display'=>'Move to Draft'));
					break;
				case 'SUBMITTED_FOR_ASSESSMENT':
					if ($this->user->hasFunction('REGISTRY_STAFF'))
					{
						$st['ds_count']=$data_source->count_SUBMITTED_FOR_ASSESSMENT;
						$st['connectTo']='DRAFT,ASSESSMENT_IN_PROGRESS';
						array_push($st['menu'], array('action'=>'to_assess', 'display'=>'Asessment In Progress'));
					}
					break;
				case 'ASSESSMENT_IN_PROGRESS':
					$st['ds_count']=$data_source->count_ASSESSMENT_IN_PROGRESS;
					if ($this->user->hasFunction('REGISTRY_STAFF'))
					{
						if($manual_publish){
							$st['connectTo']='APPROVED,MORE_WORK_REQUIRED';
							array_push($st['menu'], array('action'=>'to_approve', 'display'=>'Approve'));
						}else{
							$st['connectTo']='PUBLISHED';
							array_push($st['menu'], array('action'=>'to_publish', 'display'=>'Publish'));
						}
						array_push($st['menu'], array('action'=>'to_moreworkrequired', 'display'=>'More Work Required'));
					}
					break;
				case 'APPROVED':
					$st['ds_count']=$data_source->count_APPROVED;
					$st['connectTo']='PUBLISHED';
					array_push($st['menu'], array('action'=>'to_publish', 'display'=>'Publish'));
					break;
				case 'PUBLISHED':
					$st['ds_count']=$data_source->count_PUBLISHED;
					$st['connectTo']='';
					break;
			}
			array_push($st['menu'], array('action'=>'delete', 'display'=>'Delete'));

			$args['sort'] = isset($filters['sort']) ? $filters['sort'] : array('updated'=>'desc');
			$args['search'] = isset($filters['search']) ? $filters['search'] : false;
			$args['or_filter'] = isset($filters['or_filter']) ? $filters['or_filter'] : false;
			$args['filter'] = array('status'=>$s);
			$args['filter'] = isset($filters['filter']) ? array_merge($filters['filter'], array('status'=>$s)) : array('status'=>$s);

			$offset = 0;
			$limit = 20;

			$st['offset'] = $offset+$limit;

			$filter = array(
				'ds_id'=>$data_source_id,
				'limit'=>20,
				'offset'=>0,
				'args'=>$args
			);
			$ros = $this->get_ros($filter);
			$st['items']=$ros['items'];
			$st['count']=$this->get_ros($filter, true);
			if($st['count']==0) $st['noResult']=true;
			$st['hasMore'] = ($st['count'] > $limit + $offset);
			$st['ds_id'] = $data_source_id;

			$jsonData['statuses'][$s] = $st;
		}
		$jsonData['filters'] = $filters;
		echo json_encode($jsonData);
	}

	public function get_more_mmr_data(){
		acl_enforce('REGISTRY_USER');
		ds_acl_enforce($this->input->post('ds_id'));
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$filters = $this->input->post('filters');
		$args['sort'] = isset($filters['sort']) ? $filters['sort'] : array('updated'=>'desc');
		$args['search'] = isset($filters['search']) ? $filters['search'] : false;
		$args['or_filter'] = isset($filters['or_filter']) ? $filters['or_filter'] : false;
		$args['filter'] = array('status'=>$this->input->post('status'));
		$args['filter'] = isset($filters['filter']) ? array_merge($filters['filter'], array('status'=>$this->input->post('status'))) : array('status'=>$this->input->post('status'));
		$filter = array(
			'ds_id'=>$this->input->post('ds_id'),
			'limit'=>10,
			'offset'=>$this->input->post('offset'),
			'args'=>$args
		);

		$results = $this->get_ros($filter, false);
		if($results){
			echo json_encode($results);
		}else echo json_encode(array('noMore'=>true));
	}

	private function get_ros($filters, $getCount=false){
		$results['items'] = array();
		$this->load->model('registry_object/registry_objects', 'ro');
		$this->load->model('data_source/data_sources', 'ds');

		$filters['args']['data_source_id'] = $filters['ds_id'];
		if(!$getCount){
			//$ros = $this->ro->getByDataSourceID($filters['ds_id'], $filters['limit'], $filters['offset'], $filters['args'], false);
			$ros = $this->ro->filter_by($filters['args'], $filters['limit'], $filters['offset'], true);
		}else{
			//return sizeof($ros = $this->ro->getByDataSourceID($filters['ds_id'], 0, 0, $filters['args'], false));
			return sizeof($ros = $this->ro->filter_by($filters['args'], 0, 0, false));
		}

		//getting the data source and parse into the jsondata array
		$data_source = $this->ds->getByID($filters['ds_id']);
		foreach($data_source->attributes as $attrib=>$value){
			$jsonData['ds'][$attrib] = $value->value;
		}
		//QA and Auto Publish check, valid_statuses are populated accordingly
		$qa = $data_source->qa_flag=='t' || $data_source->qa_flag==DB_TRUE ? true : false;
		$manual_publish = ($data_source->manual_publish=='t' || $data_source->manual_publish==DB_TRUE) ? true: false;

		if($ros){
			foreach($ros as $r){
				$registry_object = $r; //$this->ro->getByID($r['registry_object_id']);

				$item = array(
						'id'=>$registry_object->id,
						'key'=>$registry_object->key,
						'title'=>html_entity_decode($registry_object->title),
						'status'=>$registry_object->status,
						'class'=>$registry_object->class,
						'updated'=>timeAgo($registry_object->updated),
						'error_count'=>$registry_object->error_count,
						'warning_count'=>$registry_object->warning_count,
						'data_source_id'=>$registry_object->data_source_id,
						);
				if($item['error_count']>0) $item['has_error'] = true;
				if($registry_object->flag=='t') $item['has_flag'] = true;
				if($item['error_count']==0){
					$item['quality_level'] = $registry_object->quality_level;
				}
				switch($item['status']){
					case 'DRAFT':
						$item['editable'] = true;
						$item['advance']=true;
						if($qa){
							$item['connectTo']='SUBMITTED_FOR_ASSESSMENT';
						}else{
							if($manual_publish){
								$item['connectTo']='APPROVED';
							}else{
								$item['connectTo']='PUBLISHED';
							}
						}
					break;
					case 'MORE_WORK_REQUIRED':
						$item['editable'] = true;
						$item['advance']=true;
						$item['connectTo']='DRAFT';
					break;
					case 'SUBMITTED_FOR_ASSESSMENT':
						if($this->user->hasFunction('REGISTRY_STAFF')) {
							$item['advance']=true;
							$item['connectTo']='ASSESSMENT_IN_PROGRESS';
						} else {
							$item['noMoreOptions'] = true;
						}
					break;
					case 'ASSESSMENT_IN_PROGRESS':
						if($this->user->hasFunction('REGISTRY_STAFF')) {
							$item['advance']=true;
							if($manual_publish){
								$item['connectTo']='APPROVED';
							}else{
								$item['connectTo']='PUBLISHED';
							}
						} else {
							$item['noMoreOptions'] = true;
						}
					break;
					case 'APPROVED':
						$item['editable'] = true;
						$item['advance']=true;
						$item['connectTo']='PUBLISHED';
						break;
					case 'PUBLISHED':
						$item['editable'] = true;
					break;
				}
				array_push($results['items'], $item);
			}
		}else return false;

		/* This doesn't work, sizeof($ros) is already filtered... */
		if(sizeof($ros)<$filters['limit']){
			$results['hasMore']=false;
		}else{
			$results['hasMore']=true;
		}
		return $results;
	}

	public function get_mmr_menu(){
		// header('Cache-Control: no-cache, must-revalidate');
		// header('Content-type: application/json');
		$this->load->model('data_source/data_sources', 'ds');
		$this->load->model('registry_object/registry_objects', 'ro');

		$data_source_id = $this->input->post('data_source_id');
		ds_acl_enforce($data_source_id);
		$status = $this->input->post('status');
		$selecting_status = $this->input->post('selecting_status') ? $this->input->post('selecting_status') : false;
		$affected_ids = $this->input->post('affected_ids') ? $this->input->post('affected_ids') : array();

		$data_source = $this->ds->getByID($data_source_id);


		if($selecting_status!=$status){
			$affected_ids=array();
		}

		$menu = array();
		if(sizeof($affected_ids) == 0){
			$menu['nothing'] = 'You must first select a record';
		}else if(sizeof($affected_ids) == 1){
			$menu['view'] = 'View Record';
		}

		$hasFlag = false;
		foreach($affected_ids as $id){
			$ro = $this->ro->getByID($id);
			if ($ro)
			{
				if($ro->flag=='t') $hasFlag = true;
			}
		}


		//QA and Auto Publish check
		$qa = $data_source->qa_flag=='t' ? true : false;
		$manual_publish = ($data_source->manual_publish=='t' || $data_source->manual_publish==DB_TRUE) ? true: false;
		if(sizeof($affected_ids)>=1){
			if($hasFlag)
			{
				$menu['un_flag'] = 'Remove Flag';
			}
			else
			{
				$menu['flag'] = 'Flag';
			}
			switch($status){
				case 'DRAFT':
					if($qa){
						$menu['to_submit'] = 'Submit for Assessment';
					}else{
						if($manual_publish){
							$menu['to_approve'] = 'Approve';
						}else{
							$menu['to_publish'] = 'Publish';
						}
					}
					$menu['edit'] = 'Edit Record';
					$menu['delete'] = 'Delete Record';
					$menu['preview'] = 'Preview in RDA';
				break;
				case 'MORE_WORK_REQUIRED':
					$menu['to_draft'] = 'Move to Draft';
					$menu['edit'] = 'Edit Record';
					$menu['delete'] = 'Delete Record';
					$menu['preview'] = 'Preview in RDA';
				break;
				case 'SUBMITTED_FOR_ASSESSMENT':
					if ($this->user->hasFunction('REGISTRY_STAFF'))
					{
						$menu['to_assess'] = 'Assessment In Progress';
						$menu['to_draft'] = 'Revert to Draft';
					}
					if ($this->user->hasFunction('REGISTRY_SUPERUSER'))
					{
						$menu['edit'] = '* Edit Record';
						$menu['delete'] = '* Delete Record';
					}
					$menu['preview'] = 'Preview in RDA';
				break;
				case 'ASSESSMENT_IN_PROGRESS':
					if ($this->user->hasFunction('REGISTRY_STAFF'))
					{
						if($manual_publish){
							$menu['to_approve'] = 'Approve';
						}else{
							$menu['to_publish'] = 'Publish';
						}
						$menu['to_moreworkrequired'] = 'More Work Required';
						if ($this->user->hasFunction('REGISTRY_SUPERUSER'))
						{
							$menu['to_draft'] = '* Revert to Draft';
							$menu['edit'] = '* Edit Record';
							$menu['delete'] = '* Delete Record';
						}
						$menu['preview'] = 'Preview in RDA';
					}
				break;
				case 'APPROVED':

					$menu['edit'] = 'Edit Record';
					$menu['to_publish'] = 'Publish';
					$menu['delete'] = 'Delete Record';
					$menu['preview'] = 'Preview in RDA';
					break;
				case 'PUBLISHED':
					$menu['delete'] = 'Delete Record';
					$menu['rdaview'] = 'View in RDA';
				break;
			}
			$menu['select_none'] = 'Deselect Record(s)';
		}



		$html = '';
		$target = '';
		$html .='<ul class="nav nav-tabs nav-stacked">';
		foreach($menu as $action=>$display){
			if ($action != "nothing")
			{
				if(sizeof($affected_ids)==1 && $action=='view'){
					$ro = $this->ro->getByID($affected_ids[0]);
					$href = base_url('registry_object/view/'.$ro->id);
				}
				elseif(sizeof($affected_ids)==1 && $action=='preview'){
					$ro = $this->ro->getByID($affected_ids[0]);
					$href = portal_url().'view/?id='.$ro->id;
					$target = 'target="_blank"';

				}
				elseif(sizeof($affected_ids)==1 && $action=='rdaview'){
					$ro = $this->ro->getByID($affected_ids[0]);
					$href = portal_url().$ro->slug;
					$target = 'target="_blank"';
				}
				else $href = 'javascript:;';
				$html .='<li><a tabindex="-1" href="'.$href.'" class="op" '.$target.' action="'.$action.'" status="'.$status.'">'.$display.'</a></li>';
			}
			else
			{
				$html .= $display . "<br/><small><em>(the block around the record turns blue when selected)</em></small>";
			}
		}
		$html .='</ul>';
		echo $html;

	}

	/**
	 * Get a list of data sources
	 *
	 *
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [INT] page
	 * @todo ACL on which data source you have access to, error handling
	 * @return [JSON] results of the search
	 */
	public function getDataSources($page=1){
		//$this->output->enable_profiler(TRUE);
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';

		$this->load->model("data_sources","ds");

		//Limit and Offset calculated based on the page
		$limit = 16;
		$offset = ($page-1) * $limit;

		//$dataSources = $this->ds->getAll($limit, $offset);
		$dataSources = $this->ds->getOwnedDataSources();

		$this->load->model("registry_object/registry_objects", "ro");

		$items = array();
		foreach($dataSources as $ds){
			$item = array();
			$item['title'] = $ds->title;
			$item['id'] = $ds->id;

			$item['counts'] = array();
			foreach ($this->ro->valid_status AS $status){
				if($ds->getAttribute("count_$status")>0){
					array_push($item['counts'], array('status' => $status, 'count' =>$ds->getAttribute("count_$status"), 'name'=>readable($status)));
				}
			}

			$item['qlcounts'] = array();
			foreach ($this->ro->valid_levels AS $level){
				array_push($item['qlcounts'], array('level' => $level, 'title' => ($level==4 ? 'Gold Standard Records' : 'Quality Level '.$level), 'count' =>$ds->getAttribute("count_level_$level")));
			}

			$item['classcounts'] = array();
			foreach($this->ro->valid_classes as $class){
				if($ds->getAttribute("count_$class")>0)array_push($item['classcounts'], array('class' => $class, 'count' =>$ds->getAttribute("count_$class"),'name'=>readable($class)));
			}

			$item['key']=$ds->key;
			$item['record_owner']=$ds->record_owner;
			$item['notes']=$ds->notes;

			array_push($items, $item);
		}


		$jsonData['items'] = array_slice($items,$offset,$limit);
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}

	/**
	 * Get a single data source
	 *
	 *
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [INT] Data Source ID
	 * @todo ACL on which data source you have access to, error handling
	 * @return [JSON] of a single data source
	 */
	public function getDataSource($id){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');

		$jsonData = array();
		$jsonData['status'] = 'OK';

		$this->load->model("data_sources","ds");
		$this->load->model("registry_object/registry_objects", "ro");
		$dataSource = $this->ds->getByID($id);
		ds_acl_enforce($id);

		foreach($dataSource->attributes as $attrib=>$value){
			$jsonData['item'][$attrib] = $value->value;
		}

		$jsonData['item']['statuscounts'] = array();
		foreach ($this->ro->valid_status AS $status)
		{
			// Hide some fields if there are no registry objects for that status
			if ($dataSource->getAttribute("count_$status") != 0 OR in_array($status, array(DRAFT, PUBLISHED))){
				array_push($jsonData['item']['statuscounts'], array('status' => $status, 'count' =>$dataSource->getAttribute("count_$status"),'name'=>readable($status)));
			}
		}

		$jsonData['item']['qlcounts'] = array();
		foreach ($this->ro->valid_levels AS $level){
			array_push($jsonData['item']['qlcounts'], array('level' => $level, 'title' => ($level==4 ? 'Gold Standard Records' : 'Quality Level '.$level), 'count' =>$dataSource->getAttribute("count_level_$level")));
		}

		$jsonData['item']['classcounts'] = array();
		foreach($this->ro->valid_classes as $class){
			array_push($jsonData['item']['classcounts'], array('class' => $class, 'count' =>$dataSource->getAttribute("count_$class"),'name'=>readable($class)));
		}

		$harvesterStatus = $dataSource->getHarvestRequest();
		$jsonData['item']['harvester_status'] = $harvesterStatus;
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}

	public function add() {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		$this->load->model('data_sources', 'ds');
		$data = file_get_contents("php://input");
		$data = json_decode($data, true);
		$data = $data['data'];

		if(!$data['key']) throw new Exception('Data Source Key must be specified');
		if(!$data['title']) throw new Exception('Data Source Title must be specified');

		$query = $this->db->get_where('data_sources', array('key'=>$data['key']));
		if($query->num_rows() > 0) throw new Exception('Data Source Key must be unique! Another data source already has the key: '. $data['key']);

		//all validation goes well
		try{
			$ds = $this->ds->create($data['key'], url_title($data['title']));
			$ds->_initAttribute('title', $data['title'], TRUE);
			$ds->_initAttribute('record_owner', $data['record_owner'], TRUE);
            $ds->setAttribute('qa_flag', DB_FALSE);
			foreach($ds->stockAttributes as $key=>$value) {
				if(!isset($ds->attributes[$key]))
				$ds->setAttribute($key, $value);
			}
			foreach($ds->extendedAttributes as $key=>$value) {
				if(!isset($ds->attributes[$key]))
				$ds->setAttribute($key, $value);
			}
			foreach($ds->harvesterParams as $key=>$value) {
				if(!isset($ds->attributes[$key]))
				$ds->setAttribute($key, $value);
			}
			$ds->save();
			$ds->updateStats();
		} catch (Exception $e) {
			throw new Exception ($e);
		}
		if($ds && $ds->id) {

            // create in mycelium
            $dataSource = \ANDS\Repository\DataSourceRepository::getByID($ds->id);
            $client = new MyceliumServiceClient(Config::get('mycelium.url'));
            $client->createDataSource($dataSource);

			$result = array(
				'status'=>'OK',
				'data_source_id' => $ds->id
			);
			echo json_encode($result);
		} else {
			throw new Exception('Data Source could not be created because of some unknown error');
		}
	}

	function trigger_harvest($id=false, $mute = false) {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		if(!$id) throw new Exception('Data source ID is required');

		$this->load->model("data_sources","ds");
		$ds = $this->ds->getByID($id);
		if(!$ds) throw new Exception('Invalid Data source ID');

		try {

			$ds->clearHarvestError();

			$harvestDate = strtotime($ds->getAttribute("harvest_date"));
			$nextRun = getNextHarvestDate($harvestDate, $ds->harvest_frequency);

			$oai_msg = '';
			if($ds->harvest_method=='PMHHarvester' && $ds->oai_set) {
				$oai_msg = 'OAI Set: '. $ds->oai_set;
			}

			$incr_msg = "";
			if($ds->advanced_harvest_mode=='INCREMENTAL') {
				if($ds->last_harvest_run_date){
					$incr_msg = $incr_msg .'From date: '.date('Y-m-d H:i:s', strtotime($ds->last_harvest_run_date)).NL;
				}
				if($nextRun){
					$incr_msg = $incr_msg .'To date: '.date('Y-m-d H:i:s', $nextRun).NL;
				}
			}

			$scheduled_date = date( 'Y-m-d H:i:s P', $nextRun);
			if($ds->harvest_frequency==''){//once off
				$scheduled_date = date('Y-m-d H:i:s', time());
			}

			$logMessage =
                'Harvest scheduled to run at '.$scheduled_date.NL.
                'URI: '.$ds->uri.NL.
                'Harvest Method: '.readable($ds->harvest_method).NL.
                'Provider Type: '.$ds->provider_type.NL.
                'Advanced Harvest Mode: '.$ds->advanced_harvest_mode.NL.
                $incr_msg.
                $oai_msg.NL
            ;

			$ds->append_log($logMessage);
			$ds->setHarvestRequest('HARVEST', false);
			// $ds->setHarvestMessage('Harvest scheduled');
			$ds->updateImporterMessage(array());
		} catch (Exception $e) {
			throw new Exception($e);
		}

		if(!$mute) echo json_encode(
			array(
				'status' => 'OK',
				'message' => 'Harvest Started'
			)
		);
	}

	function stop_harvest($id=false) {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		if(!$id) throw new Exception('Data source ID is required');

		$this->load->model("data_sources","ds");
		$ds = $this->ds->getByID($id);
		if(!$ds) throw new Exception('Invalid Data source ID');

		try {
			$ds->cancelHarvestRequest();
			$ds->setHarvestMessage('Stopped by User');
			$ds->append_log(
				'Scheduled harvest cancelled at '.date('Y-m-d H:i:s', time()).NL.
				'Harvest was cancelled by: ' . $this->user->name() . " (" . $this->user->localIdentifier() . ") at " . date('Y-m-d H:i:s', time())
			);
		} catch (Exception $e) {
			throw new Exception($e);
		}

		echo json_encode(
			array(
				'status' => 'OK',
				'message' => 'Harvest Stopped'
			)
		);
	}

	function clear_logs($id=false){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		if(!$id) throw new Exception('Data source ID is required');

		$this->load->model("data_sources","ds");
		$ds = $this->ds->getByID($id);
		if(!$ds) throw new Exception('Invalid Data source ID');

		try{
			$ds->clear_logs();
		} catch (Exception $e) {
			throw new Exception($e);
		}
	}

	/**
	 * getDataSourceLogs
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [POST] data_source_id [POST] offset [POST] count [POST] log_id
	 *
	 * @return [json] [logs for the data source]
	 */
	public function getDataSourceLogs(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		// date_default_timezone_set('Australia/Canberra');//???

		$this->load->model('data_sources', 'ds');

		$post = $this->input->post();

		$id = isset($post['id']) ? $post['id'] : 0; //data source id
		if($id==0) {
			throw new Exception('Data Source ID must be provided');
			exit();
		}
		$offset = isset($post['offset']) ? (int) $post['offset'] : 0;
		$count = isset($post['count']) ? (int) $post['count'] : 10;
		$logid = isset($post['logid']) ? (int) $post['logid'] : null;
		$log_class = isset($post['log_class']) ? $post['log_class'] : 'all';
		$log_type = isset($post['log_type']) ? $post['log_type'] : 'all';

		$jsonData = array();
		$dataSource = $this->ds->getByID($id);
		$dataSourceLogs = $dataSource->get_logs($offset, $count, $logid, $log_class, $log_type);
		$jsonData['log_size'] = $dataSource->get_log_size($log_type);

		if($jsonData['log_size'] > ($offset + $count)){
			$jsonData['next_offset'] = $offset + $count;
			$jsonData['hasMore'] = true;
		}else{
			$jsonData['next_offset'] = 'all';
			$jsonData['hasMore'] = false;
		}
		$jsonData['last_log_id'] = '';
		$lastLogIdSet = false;
		$items = array();
		if(sizeof($dataSourceLogs) > 0){
			foreach($dataSourceLogs as $log){
				$item = array();
				$item['type'] = $log['type'];
				$item['log_snippet'] = first_line($log['log']);
				$item['log'] = $log['log'];
				$item['id'] = $log['id'];
				if(!$lastLogIdSet)
				{
				$jsonData['last_log_id'] = $log['id'];
				$lastLogIdSet = true;
				}
				$item['date_modified'] = timeAgo($log['date_modified']);
				$item['harvester_error_type'] = $log['harvester_error_type'];
				if($log['harvester_error_type'] != 'BENCHMARK_INFO' || $this->user->hasFunction(AUTH_FUNCTION_SUPERUSER))
					array_push($items, $item);
			}
		}
		$jsonData['count'] = $count;
		$jsonData['items'] = $items;

		echo json_encode($jsonData);
	}





	public function cancelHarvestRequest(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_time_limit(3);
		ignore_user_abort(FALSE);

		$this->load->model('data_sources', 'ds');
		$jsonData = array();
		$post = $this->input->post();
		$id = isset($post['id']) ? $post['id'] : 0; //data source id
		$harvest_id = isset($post['harvest_id']) ? $post['harvest_id'] : 0; //data source id
		if($harvest_id==0 || $id == 0) {
			//throw new Exception('Datasource ID must be provided');
			//exit();
			$jsonData['log'] = $post;
		}


		$dataSource = $this->ds->getByID($id);
		$jsonData['data_source_id'] = $id;
		$jsonData['harvest_id'] = $harvest_id;
		if($dataSource)
		{
			$jsonData['log'] = $dataSource->cancelHarvestRequest($harvest_id, true);
		}

		echo json_encode($jsonData);
	}

	/**
	 * Sets the manual_publish attribute for a datasource based on the auto_publish attribute
	 *
	 *
	 * @author Liz
	 * @param
	 * @todo ACL on which data source you have access to, error handling, new attributes
	 * @return
	 */
	public function change_auto_publish_attribute(){
		$this->load->model("data_sources","ds");
		$all_ds = $this->ds->getAll();
		foreach($all_ds as $a_ds)
		{
			$attributes = $a_ds->attributes;
			if(isset($attributes['auto_publish']))
			{
				print("<pre>");
				print("Auto publish = ".$attributes['auto_publish']);
				print("</pre>");
				print("--------------------------------------------");

				if($attributes['auto_publish']=='auto_publish: f'||$attributes['auto_publish']=='auto_publish: 0')
				{
					$a_ds->setAttribute('manual_publish',DB_TRUE);
					echo "We have set manual publish to true for ds ".$a_ds->id."<br />";
				}else{
					$a_ds->setAttribute('manual_publish',DB_FALSE);
					echo "We have set manual publish to false  for ds ".$a_ds->id."<br />";
				}
			}else{
					$a_ds->setAttribute('manual_publish',DB_FALSE);
					echo "We have set manual publish to false  for ds ".$a_ds->id."<br />";
			}
			$a_ds->setAttribute('auto_publish',null);
			$a_ds->save();
		}
	}

	/**
	 * Save a data source
	 *
	 *
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [POST] Data Source ID [POST] attributes
	 * @todo ACL on which data source you have access to, error handling, new attributes
	 * @return [JSON] result of the saving [VOID]
	 */
	public function updateDataSource(){

		set_exception_handler('json_exception_handler');
		$jsonData = array();
		$dataSource = NULL;
		$id = NULL;

		$jsonData['status'] = 'OK';
		$POST = $this->input->post();
		//print("<pre>");
		//print_r($POST);
		//print("</pre>");

		if (isset($POST['data_source_id'])){
			$id = (int) $this->input->post('data_source_id');
		}

		$this->load->model("data_sources","ds");
		$this->load->model("registry_object/registry_objects", "ro");

		if ($id == 0) {
			 $jsonData['status'] = "ERROR"; $jsonData['message'] = "Invalid data source ID";
		}
		else
		{
			$dataSource = $this->ds->getByID($id);
		}
		// ACL enforcement
		acl_enforce('REGISTRY_USER');
		ds_acl_enforce($id);

		$resetHarvest = false;
		$resetPrimaryRelationships = false; // reindex all records if the primary relationship information has changed!

		// XXX: This doesn't handle "new" attribute creation? Probably need a whilelist to allow new values to be posted. //**whitelist**//
		if ($dataSource)
		{

			$valid_attributes = array_merge(array_keys($dataSource->attributes()), array_keys($dataSource->harvesterParams));
			$valid_attributes = array_merge($valid_attributes, $dataSource->primaryRelationship);
			$valid_attributes = array_merge($valid_attributes, array_keys($dataSource->stockAttributes));
			$valid_attributes = array_merge($valid_attributes, array_keys($dataSource->extendedAttributes));
			$valid_attributes = array_unique($valid_attributes);

			foreach($valid_attributes as $attrib){
				$new_value = null;

				if (is_integer($attrib) && $attrib == 0)
				{
					continue;
				}
				else if (isset($POST[$attrib])){
					$new_value = trim($this->input->post($attrib));
				}
				else if(in_array($attrib, array_keys($dataSource->harvesterParams)))
				{
					$new_value = $dataSource->harvesterParams[$attrib];
				}
				else if(in_array($attrib, $dataSource->primaryRelationship)){
					$new_value = '';
				}



				if($new_value=='true'){$new_value=DB_TRUE;}
				if($new_value=='false'){$new_value=DB_FALSE;}
				if($attrib == 'uri'){$providerURI = $new_value;}
				// If primary relationships are disabled, unset all the relationship settings
				if($this->input->post('create_primary_relationships')=='false')
				{
					switch($attrib){
						case 'primary_key_1':
						case 'service_rel_1':
						case 'activity_rel_1':
						case 'collection_rel_1':
						case 'party_rel_1':
						case 'primary_key_2':
						case 'service_rel_2':
						case 'activity_rel_2':
						case 'collection_rel_2':
						case 'party_rel_2':
							$new_value = '';
							break;
						default:
							break;
					}

				}

				if($this->input->post('primary_key_2')=='')
				{
					switch($attrib){
						case 'primary_key_2':
						case 'service_rel_2':
						case 'activity_rel_2':
						case 'collection_rel_2':
						case 'party_rel_2':
							$new_value = '';
							break;
						default:
							break;
					}
				}
				if($this->input->post('primary_key_1')=='')
				{
					switch($attrib){
						case 'primary_key_1':
						case 'service_rel_1':
						case 'activity_rel_1':
						case 'collection_rel_1':
						case 'party_rel_1':
							$new_value = '';
							break;
						default:
							break;
					}
				}

			/*	this push to nla functionality has been removed as NLA aren't using it and the ds admins were getting confused

				if($this->input->post('push_to_nla')=='false')
				{
					switch($attrib){
						case 'isil_value':
							$new_value = '';
							break;
						default:
							break;
					}
				}

			*/

				//echo $attrib." is the attribute";

				if($new_value != $dataSource->{$attrib} && in_array($attrib, array_keys($dataSource->harvesterParams)))
				{
				   //var_dump(array($attrib, $dataSource->{$attrib}, $new_value));
				   $resetHarvest = true;
				}


				if($new_value != $dataSource->{$attrib} && in_array($attrib, $dataSource->primaryRelationship))
				{
				   $resetPrimaryRelationships = true;
				}


				//we need to check if we have turned it on or off and then change record statuses accordingly
				if($new_value == 'f' && $attrib == 'qa_flag' && $new_value != $dataSource->{$attrib})
				{
					$jsonData['qa_flag'] = "changed from ".$dataSource->{$attrib}." to ".$new_value;
					$newStatus = PUBLISHED;
					$manual_publish = $this->input->post('manual_publish');
					if($manual_publish=="true"||$manual_publish=="t") $newStatus = APPROVED;
					//get all objects with submitted for assessment status for this ds and change status to the new status
					$ros = '';
					$ros = $this->ro->getByAttributeDatasource($dataSource->id, 'status', SUBMITTED_FOR_ASSESSMENT, true);
					$jsonData['ros'] = $ros;
					if($ros)
					foreach($ros as $submitted_ro)
					{
						$ro = $this->ro->getByID($submitted_ro->id);
						$jsonData[$submitted_ro->id]=$ro->status;
						$ro->status = $newStatus;
						$ro->save();

					}
					//get all objects with assessment in progress status for this ds and change status to the new status
					$roa = '';
					$roa = $this->ro->getByAttributeDatasource($dataSource->id, 'status', ASSESSMENT_IN_PROGRESS, true);
					$jsonData['roa'] = $roa;
					if($roa)
					foreach($roa as $progress_ro)
					{
						$ro = $this->ro->getByID($progress_ro->id);
						$jsonData[$progress_ro->id]=$ro->status;
						$ro->status = $newStatus;
						$ro->save();
					}
				}

				//we need to check if we have turned manually publish to NO  - if so set all records of this datasource from Approved to Published
				if($attrib == 'manual_publish' && $new_value == 'f' && $new_value != $dataSource->{$attrib})
				{
					$jsonData['manual_publish'] = "changed from ".$dataSource->{$attrib}." to ".$new_value;
					//so lets get all of the objects for this ds that have a status of "Approved" nad change the status to published
					$jsonData['ds_id'] = $dataSource->id;
					$rop = '';
					$rop = $this->ro->getByAttributeDatasource($dataSource->id, 'status', APPROVED, true);
					$jsonData['rop'] = $rop;
					if($rop)
					foreach($rop as $approved_ro)
					{
						$ro = $this->ro->getByID($approved_ro->id);
						$ro->status = PUBLISHED;
						$ro->save();
					}

				}


				if (!is_null($new_value))
				{
					$changed = $new_value !== $dataSource->{$attrib};
					$dataSource->{$attrib} = $new_value;

				}
				$dataSource->updateStats();
			}

			$dataSource->save();

			$dataSource->append_log("The data source settings were updated..." . NL . NL .
									"Data Source was updated by: " . $this->user->name() . " (" . $this->user->localIdentifier() . ") at " . display_date());

			if($resetHarvest && ($providerURI != '' || $providerURI != 'http://'))
			{
				$dataSource->requestNewHarvest();
			}

			if($resetPrimaryRelationships)
			{
				$dataSource->reindexAllRecords();
			}
		}
		//$jsonData['attributes'] = $dataSource->attributes();
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}

	function exportDataSource($id)
	{
		parse_str($_SERVER['QUERY_STRING'], $_GET);
		$as = 'xml';
        $formatString = 'rif-cs';
		$statusString = '';
        $classString = '';
		$data = json_decode($this->input->get('data'));
		foreach($data as $param)
		{
			if($param->name == 'ro_class')
				$classString .= $param->value;
			if($param->name == 'as')
				$as = $param->value;
			if($param->name == 'ro_status')
				$statusString .= $param->value;
            if($param->name == 'format')
                $formatString = $param->value;
		}
		$this->load->model("data_sources","ds");
		$this->load->model("registry_object/registry_objects", "ro");
		$dataSource = $this->ds->getByID($id);
		$dsSlug = $dataSource->getAttribute('slug');
		$rifcs = '';
        $dci_handler = null;
        $dciOutput = '';
		$ids = $this->ro->getIDsByDataSourceID($id, false, 'All');
		if($ids)
		{
			$i = 0;
            if($formatString == 'dci')
            {
                defined('SERVICES_MODULE_PATH') or define('SERVICES_MODULE_PATH', REGISTRY_APP_PATH . 'services/');
                require_once(SERVICES_MODULE_PATH . 'method_handlers/dci.php');
                $dci_handler = new DCIMethod();
            }
			foreach($ids as $idx => $ro_id){
				try{
					$ro = $this->ro->getByID($ro_id);
					if($formatString == 'dci')
                    {
                        $dci_handler->ro = $ro;
                        $dci_handler->populate_resource($ro_id,true);
                        $dciOutput .= $dci_handler->ro_handle('dci');
                    }
                    elseif($ro && (strpos($classString, $ro->class) !== false) && (strpos($statusString, $ro->status) !== false))
					{
						$rifcs .= unWrapRegistryObjects($ro->getRif()).NL;
					}
				}catch (Exception $e){}

				if ($idx % 100 == 0)
				{
					unset($ro);
					gc_collect_cycles();
				}
			}
		}
        if($formatString == 'dci'){
            $result =  '<?xml version="1.0"?>'.NL;
            $result .= '<DigitalContentData xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="DCI_schema_providers_V4.1.xsd">'.NL;
            $result .= $dciOutput;
            $result .= '</DigitalContentData>';
        }
        else{
        $result = wrapRegistryObjects($rifcs);
        }
		if($as == 'file')
		{
		    $this->load->helper('download');
            force_download($dsSlug.'-'.strtoupper($formatString).'-Export.xml', $result);
		}
		else
		{
		 	header('Cache-Control: no-cache, must-revalidate');
		 	header('Content-type: application/xml');
		 	echo $result;
		}
	}


    /* Printable quality report */
	function quality_report($id, $status_filter = null){
		//$data['report'] = $this->getDataSourceReport($id);
		$data['title'] = 'Data Source Report';
		$data['scripts'] = array();
		$data['less']=array('charts');
		$data['js_lib'] = array('core');

		$this->load->model("data_source/data_sources","ds");
		$this->load->model("registry_object/registry_objects", "ro");

		if ($status_filter)
		{
			$data['filter'] = "Quality report for " . readable($status_filter);
		}

		$report = array();
		$data['ds'] = $this->ds->getByID($id);
		$ids = $this->ro->getIDsByDataSourceID($id, false, 'All');
		acl_enforce('REGISTRY_USER');
		ds_acl_enforce((int)$id);

		if($ids){
			$data['record_count'] = sizeof($ids);
			$problems=0;
			$replacements = array("recommended"=>"<u>recommended</u>", "required"=>"<u>required</u>", "must be"=>"<u>must be</u>");
			foreach($ids as $idx=>$ro_id){
				try{
					$ro=$this->ro->getByID($ro_id);
					if (!$status_filter || $ro->status == $status_filter)
					{
						$report_html = $ro ? str_replace(array_keys($replacements), array_values($replacements), $ro->getMetadata('quality_html')) : '';
						$report[$ro_id] = array('quality_level'=>($ro->quality_level == 4 ? 'Gold Standard' : $ro->quality_level), 'class'=>$ro->class, 'title'=>$ro->title,'status'=>readable($ro->status),'id'=>$ro->id,'report'=>$report_html);
					}
				}catch(Exception $e){
					throw new Exception($e);
				}
				unset($ro);
				clean_cycles();
			}
		}
		uasort($report, array($this, 'cmpByQualityLevel'));
		$data['report'] = $report;
		$this->load->view('detailed_quality_report', $data);
	}

	function cmpByQualityLevel($a, $b)
	{
	    if ($a['quality_level'] == $b['quality_level']) {
	        return ($a['class'] < $b['class']) ? -1 : 1;
	    }
	    return ($a['quality_level'] < $b['quality_level']) ? -1 : 1;
	}

	public function delete() {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		$data = file_get_contents("php://input");
		$data = json_decode($data, true);

		$ds_id = $this->input->post('ds_id');
		if(!$ds_id) {
			$ds_id = $data['id'];
		}
		$response = array();
		$response['success'] = false;
		$response['error'] = '';
		$this->load->model("data_source/data_sources","ds");
		$this->load->library('solr');
		$response['log'] = $this->solr->clear($ds_id);
		try {
			acl_enforce(AUTH_FUNCTION_SUPERUSER);
		} catch(Exception $e) {
			$response['error'] = $e->getMessage();
			echo json_encode($response);
			exit();
		}

        // wipe from mycelium
        $ds = \ANDS\Repository\DataSourceRepository::getByID($ds_id);

		if($ds) {
            \ANDS\Registry\Importer::wipeDataSourceRecords($ds, false);
			$response['log'] .= $ds->delete();
            $client = new MyceliumServiceClient(Config::get('mycelium.url'));
            $client->deleteDataSource($ds);
			$response['success'] = true;
		}
		else{
			$response['error'] = 'No Data Source Found! '. $ds_id;
		}

		echo json_encode($response);
	}

    /**
     * /registry/data_sources/wipe/{id}
     *
     * Wiping a data source content
     * @param $id
     * @return void
     * @throws \Exception
     */
    public function wipe($id) {

        \ANDS\Log\Log::info(__METHOD__. " wiping DataSource[id={$id}]", [
            'user' => [
                'name' => $this->user->name(),
                'identifier' => $this->user->localIdentifier()
            ],
            'ip' => $this->input->ip_address()
        ]);

        // headers
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        set_exception_handler('json_exception_handler');

        // check permission
        acl_enforce('REGISTRY_USER');
        ds_acl_enforce((int) $id);

        $dataSource = \ANDS\Repository\DataSourceRepository::getByID($id);
        if (!$dataSource) {
            ANDS\Log\Log::warning(__METHOD__. " DataSource ");
            throw new Exception("DataSource $id not found");
        }

        try {
            $total = \ANDS\RegistryObject::where('data_source_id', $dataSource->id)->count();
            $dataSource->appendDataSourceLog(
                "Wiping data source contents".NL.
                "$total records will be affected".NL.
                "Initiated: " . $this->user->name() . " (" . $this->user->localIdentifier() . ") at " . display_date().NL
                ,'info', 'IMPORTER');

            \ANDS\Registry\Importer::wipeDataSourceRecords($dataSource, false);

            \ANDS\Log\Log::info(__METHOD__. " wiping DataSource[id={$id}] Completed Successfully");
            $dataSource->appendDataSourceLog(
                "Wiping data source contents Completed Successfully!".NL.
                "Completed: at " . display_date().NL
                ,'info', 'IMPORTER');

        } catch (Exception $e) {
            \ANDS\Log\Log::error(__METHOD__ . " Failed to wipe DataSource[id={$dataSource->id}] Exception[message={$e->getMessage()} , trace={$e->getTraceAsString()}]");
            $dataSource->appendDataSourceLog(
                "An error occur while wiping data source contents".NL.
                "Message: {$e->getMessage()}" .NL.
                "Code: {$e->getCode()}" .NL
                , 'error', 'IMPORTER');
        }

        echo json_encode([
            'data' => 'success',
        ]);
    }

	function getDataSourceReport($id){

		$dataSource = $this->ds->getByID($id);

		// ACL enforcement
		acl_enforce('REGISTRY_USER');
		ds_acl_enforce((int)$id);

		$ids = $this->ro->getIDsByDataSourceID($id, false, 'All');
		$report = "<h3>QUALITY REPORT FOR ".$dataSource->title."</h3>";
		$j = 0;
		$qa_report = '';
		if($ids)
		{
			$report .= "<h4>record count :".sizeof($ids)."</h4>";
			$i = 0;
			foreach($ids as $idx => $ro_id){
				try{
					$ro = $this->ro->getByID($ro_id);
					if($ro)
					{
						$text = $ro->getMetadata('quality_html');
						if($text && $text != '')
						{
							//var_dump($text);
							$j++;
							$qa_report .= "<a id='".$ro_id. "'>".$ro->title."</a><br/>" .$text ."<br/>";
							$qa_report .= "<br/>~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~<br/>";
						}
					}
				}catch (Exception $e){}

				if ($idx % 100 == 0)
				{
					unset($ro);
					gc_collect_cycles();
				}
			}
			$report .= "<h4>records with issues :".$j."</h4>";
			$report .= $qa_report;
		}
		echo $report;

	}
	/**
	 * Get published record for this ds ; AJAX data for edit data_source settings primary links
	 *
	 * @author Liz Woods
	 * @param  [int] 	$data_source_id
	 * @param  [string] $key
	 * @return [json]
	 */
	public function get_datasource_object(){

		$data_source_id = $this->input->post('data_source_id');
		$key = $this->input->post('key');

		//administrative and loading stuffs
		acl_enforce('REGISTRY_USER');
		ds_acl_enforce($data_source_id);
		$jsonData['status'] = "OK";
		$jsonData['message'] = '';
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$this->load->model('data_source/data_sources', 'ds');
		$this->load->model('registry_object/registry_objects', 'ro');
		$data_source = $this->ds->getByID($data_source_id);
		$registry_object = $this->ro->getPublishedByKey($key);
		if($registry_object==null||$data_source->id!=$data_source_id)
			{$jsonData['message'] = "You must provide a published registry object key from within this data source for primary relationship.";}

		echo json_encode($jsonData);
	}

    /**
     * Moved from applications/registry/import/controllers/import.php
     * as part of refactoring RDA-760
     * @param $id. The datasource's id
     * returns json containing the
     * list of the harvested content's directory of the given datasource
     * or
     * the content of the files, if a given "path" of a file is requested
     * @throws Exception
     */
    public function list_files($id=false) {
        if(!$id) throw new Exception('Data Source ID required');
        $dir = \ANDS\Util\config::get('app.harvested_contents_path');
        $dir = rtrim($dir, '/') . '/';
        if(!$dir) throw new Exception('Harvested Contents Path not configured');

        if($this->input->get('path')){
            $path = $this->input->get('path');
            if(!is_file($path)) throw new Exception('Path not found');
            $content = @file_get_contents($path);
            echo json_encode(array(
                'status' => 'OK',
                'content' => $content
            ));
            return;
        }

        $path = $dir.$id;

        if(!is_dir($path)) throw new Exception('Datasource does not have any harvested path');
        $batches = array();
        foreach(scandir($path) as $f){
            if($f!="." && $f!="..") $batches[] = $f;
        }
        if(sizeof($batches) == 0) throw new Exception('Data source does not have any batch harvested');

        $result = array();

        foreach($batches as $b) {
            $link = $path.'/'.$b;
            if(is_file($link)) $result[] = array('type'=>'file', 'link'=>$link, 'name'=>$b);
            if(is_dir($link)) {
                $files = array();
                foreach(scandir($link) as $file){
                    if($file!="." && $file!="..") $files[] = array('type'=>'file', 'link'=>$link.'/'.$file, 'name'=>$file);
                }
                $result[] = array('type'=>'folder', 'link'=>$link, 'files'=>$files, 'name'=>$b);
            }
        }

        echo json_encode(array(
            'status' => 'OK',
            'content' => $result
        ));
    }


	/**
	 * @ignore
	 */
	public function __construct()
	{
		parent::__construct();
	}

}
