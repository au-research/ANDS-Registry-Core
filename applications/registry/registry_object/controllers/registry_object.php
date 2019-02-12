<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
define('SERVICES_MODULE_PATH', REGISTRY_APP_PATH.'services/');

include_once("applications/registry/registry_object/models/_transforms.php");
use ANDS\DataSource;
use ANDS\Registry\Providers\Quality\Types;
use \Transforms as Transforms;
/**
 * Registry Object controller
 *
 *
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @package ands/registryobject
 *
 */
class Registry_object extends MX_Controller {

	private $maxVisibleRevisions = 15;

	public function index(){
		redirect(registry_url());
	}

    /**
     * @param $ro_id
     * @param string $revision
     * @throws Exception
     */
    public function view($ro_id, $revision=''){

		$this->load->model('registry_object/registry_objects', 'ro');
		$ro = $this->ro->getByID($ro_id);
		if($ro){
			$this->load->model('data_source/data_sources', 'ds');
			$ds = $this->ds->getByID($ro->data_source_id);

			$data['scripts'] = array('view_registry_object', 'registry_tag');
			$data['js_lib'] = array('core','prettyprint', 'angular');
			$data['title'] = $ro->title;
			$data['ro'] = $ro;
			$data['ro_id'] = $ro_id;
			$data['ds'] = $ds;
			$data['revision'] = $revision;
			$data['action_bar'] = array(); // list of status actions which can be performed

			$data['tags'] = $ro->getTags();

			$data['own_themepages'] = $ro->getThemePages();
			$data['themepages'] = $this->ro->getAllThemePages();

			if($revision!=''){
				$data['viewing_revision'] = true;
				$data['rif_html'] = $ro->transformForHtml($revision, $ds->title);
				$revRecord = $ro->getRevision($revision);
				$time = date("F j, Y, g:i a", $revRecord[0]['timestamp']);
				$data['currentRevision'] = ($revRecord[0]['current'] === 'TRUE' ? "TRUE": '');
				if($revRecord[0]['current'] === 'TRUE')
					$data['revisionInfo'] = 'Current Version: '.$time;
				else
					$data['revisionInfo'] = 'Revision: '.$time;
			}
			else
			{
				$data['viewing_revision'] = false;
				$data['rif_html'] = $ro->transformForHtml('', $ds->title);

				if($this->user->hasAffiliation($ds->record_owner))
				{
					$data['action_bar'] = $this->generateStatusActionBar($ro, $ds);
				}
			}

            $data['native_format'] = "BLAH BLAH";
			$data['naitive_text'] = "JHKJGKJHKJHKJHKJHKJHKJHK";


           // $generatedContent = \ANDS\RegistryObject\AltSchemaVersion::where('registry_object_id', $ro_id )->get();
           // $harvestedNativeContent = \ANDS\RegistryObject\AltSchemaVersionByIdentifier::where('registry_object_id', $ro_id )->get();


			$data['revisions'] = array_slice($ro->getAllRevisions(),0,$this->maxVisibleRevisions);

//			initEloquent();
			$record = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($ro_id);
//			$quality_html = \ANDS\Registry\Providers\Quality\QualityMetadataProvider::getQualityReportHTML($record);
//			$data['quality_text'] = $quality_html;

            $report = \ANDS\Registry\Providers\Quality\QualityMetadataProvider::getMetadataReport($record);
			$data['quality_text'] = $this->load->view('quality_report', ['report' => $report], true);

			//var_dump($data);
			//exit();
			$this->load->view('registry_object_index', $data);
		}else{
			show_404('Unable to Find Registry Object ID: '.$ro_id);
		}
	}

	public function preview($ro_id, $format='html'){
		$this->load->model('registry_object/registry_objects', 'ro');
		$ro = $this->ro->getByID($ro_id);
		$data['ro']=$ro;
		if($format=='pane'){
			$this->load->view('registry_object_preview_pane', $data);
		}
	}

	public function add(){
		$data['title'] = 'Add Registry Objects';
		$data['scripts'] = array('add_registry_objects');
		$data['js_lib'] = array('core','prettyprint','orcid_widget', 'vocab_widget');
		$data['content'] = "ADD NEW";

		$this->load->model("data_source/data_sources","ds");

		$data['ownedDatasource'] = $this->ds->getOwnedDataSources();

		acl_enforce('REGISTRY_USER');
		if(count($data['ownedDatasource']) == 0)
		{
			// XXX: This should redirect to DS affiliation screen!
			throw new Exception("Unable to Add Records - you are not yet affiliated with any data sources! Contact the registry owner.");
		}

		$this->load->view("add_registry_objects", $data);
	}

    /**
     * @param $registry_object_id
     * @throws Exception
     */
    public function edit($registry_object_id){
        acl_enforce('REGISTRY_USER');

        initEloquent();

        $record = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($registry_object_id);
        ds_acl_enforce($record->data_source_id);
        if (!$record) {
            throw new Exception("This Registry Object ID does not exist!");
        }

        $data_source = \ANDS\Repository\DataSourceRepository::getByID($record->data_source_id);
        $draftRecord = \ANDS\Repository\RegistryObjectsRepository::getMatchingRecord($record->key, 'DRAFT');

        if (!$draftRecord) {

            // import Task creation
            $importTask = new \ANDS\API\Task\ImportTask();
            $importTask
                ->setCI($this)->setDb($this->db)
                ->init([
                    'name' => 'ARO',
                    'params' => http_build_query([
                        'pipeline' => 'ManualImport',
                        'source' => 'manual',
                        'ds_id' => $record->data_source_id,
                        'user_name' => $this->user->name(),
                        'targetStatus' => 'DRAFT'
                    ])
                ])
                ->skipLoadingPayload()
                ->enableRunAllSubTask();

            // write the xml payload to the file system
            $xml = $record->getCurrentData()->data;
            $batchID = 'MANUAL-ARO-' . md5($record->key).'-'.time();
            $path = \ANDS\Payload::write($record->data_source_id, $batchID, $xml);
            $payload = new \ANDS\Payload($path);

            $importTask->setPayload("customPayload", $payload);
            $importTask->initialiseTask();
            $importTask->run();

            $errorLog = $importTask->getError();
            if ($errorLog == null) {
                $draftRecord = \ANDS\Repository\RegistryObjectsRepository::getMatchingRecord($record->key, 'DRAFT');
            } else {
                throw new Exception("Draft Record creation failed. ". join(' ', $errorLog));
            }
		}

		if (!$draftRecord) {
            throw new Exception("Draft Record creation failed for record {$record->id}");
        }

        if ($draftRecord->registry_object_id != $registry_object_id) {
            header("Location: " . registry_url('registry_object/edit/' . $draftRecord->registry_object_id));
            return;
        }

        $extRif = $draftRecord->getCurrentData()->data;

        $data = [
            'content' => ANDS\Util\XMLUtil::getHTMLForm($extRif, [
                "base_url" => base_url(),
                "registry_object_id" => $registry_object_id,
                "data_source_id" => $draftRecord->data_source_id,
                "data_source_title" => $data_source->title,
                "ro_title" => $draftRecord->title,
                "ro_class" => $draftRecord->class
            ]),
            'extrif' => $extRif,
            'ds' => $data_source,
            'title' => 'Edit: ' . $draftRecord->title,
            'scripts' => ['add_registry_object'],
            'js_lib' => [
                'core',
                'tinymce',
                'ands_datepicker',
                'prettyprint',
                'vocab_widget',
                'orcid_widget',
                'google_map',
                'location_capture_widget'
            ]
        ];
		$this->load->view("add_registry_object", $data);
	}

    /**
     * @param $registry_object_id
     * @throws Exception
     */
    public function validate($registry_object_id){
		set_exception_handler('json_exception_handler');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$xml = $this->input->post('xml');
		$this->load->model('registry_object/registry_objects', 'ro');
		$ro = $this->ro->getByID($registry_object_id);

        $xml = $ro->cleanRIFCSofEmptyTags($xml, 'false', true);
        $result = $ro->transformForQA(wrapRegistryObjects($xml));

		$this->load->model('data_source/data_sources', 'ds');
		$ds = $this->ds->getByID($ro->data_source_id);

		$qa = $ds->qa_flag==DB_TRUE ? true : false;
		$manual_publish = ($ds->manual_publish==DB_TRUE) ? true: false;

        $record = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($registry_object_id);
        $report = \ANDS\Registry\Providers\Quality\QualityMetadataProvider::getMetadataReport($record);
        $quality_html = $this->load->view('quality_report', ['report' => $report], true);
        $response["qa"] = $quality_html;

		$response['title'] = 'QA Result';
		$scripts = preg_split('/(\)\;)|(\;\\n)/', $result, -1, PREG_SPLIT_NO_EMPTY);
		$response["ro_status"] = "DRAFT";
		$response["title"] = $ro->title;
		$response["ro_id"] = $ro->id;
		$response["data_source_id"] = $ro->data_source_id;
		$response["qa_required"] = $qa;
		$response["ro_quality_level"] = $ro->quality_level;
		$response["approve_required"] = $manual_publish;


		$response["ro_quality_class"] = ($ro->quality_level >= 2 ? "success" : "important");
		$response["qa_$ro->quality_level"] = true;

        $error_count = 0;
        $warning_count = 0;
		foreach($scripts as $script)
		{
			$matches = preg_split('/(\"\,\")|(\(\")|(\"\))/', $script.")", -1, PREG_SPLIT_NO_EMPTY);

			if(sizeof($matches) > 2)
			{
				$match_response = array('field_id'=>$matches[1],'message'=>$matches[2]);
                if($matches[0] == 'SetErrors')
                    $error_count++;
                if($matches[0] == 'SetWarnings')
                    $warning_count++;
				if (isset($matches[3]))
				{
					if (strtoupper($matches[3]) != $matches[3])
					{
						$match_response['sub_field_id'] = $matches[3];
					}
				}
				$response[$matches[0]][] = $match_response;
			}
		}

        $ro->error_count = $error_count;
        $ro->warning_count = $warning_count;
        $ro->save();

        $response["error_count"] = $error_count;

        // CC-2256. Replacing SetWarnings and SetInfos with fail rule from the report
        // Leaving SetErrors because they are important
        $response['SetWarnings'] = [];
        $response['SetInfos'] = [];

        // Removing the SetInfos as well, only errors exist for the tabs now
        // $response = $this->getInfosTabMessages($response, $report);

		echo json_encode($response);
	}

    /**
     * Helper method to generate the SetInfos from a metadata quality reports
     *
     * @param $response
     * @param $report
     * @return mixed
     */
    private function getInfosTabMessages($response, $report)
    {
        $rule2TabMapping = [
            Types\CheckIdentifier::class => 'tab_identifiers',
            Types\CheckDescription::class => 'tab_descriptions_rights',
            Types\CheckRights::class => 'tab_descriptions_rights',
            Types\CheckLocation::class => 'tab_locations',
            Types\CheckLocationAddress::class => 'tab_locations',
            Types\CheckSubject::class => 'tab_subjects',
            Types\CheckCoverage::class => 'tab_coverages',
            Types\CheckRelatedCollection::class => 'tab_relatedObjects',
            Types\CheckRelatedParties::class => 'tab_relatedObjects',
            Types\CheckRelatedActivity::class => 'tab_relatedObjects',
            Types\CheckRelatedService::class => 'tab_relatedObjects',
            Types\CheckRelatedActivityOutput::class => 'tab_relatedObjects',
            Types\CheckRelatedInformation::class => 'tabs_relatedInfos',
            Types\CheckCitationInfo::class => 'tab_citationInfos',
            Types\CheckRelatedOutputs::class => 'tab_relatedinfos',
            Types\CheckExistenceDate::class => 'tab_existencedates',
        ];

        $fails = collect($report)
            ->where('status', \ANDS\Registry\Providers\Quality\Types\CheckType::$FAIL);

        $response['SetInfos'] = $fails
            ->map(function($rule) use ($rule2TabMapping){
                $fieldID = array_key_exists($rule['name'], $rule2TabMapping)
                    ? $rule2TabMapping[$rule['name']]
                    : 'tab_admin';

                return [
                    'field_id' => $fieldID,
                    'message' => $rule['message']
                ];
            });

        $response['SetInfos'] = $response['SetInfos']->unique()->toArray();

        $response['fails'] = $fails->toArray();

        return $response;
	}

    /**
     * API registry/registry_object/save/:id
     * Updated to using the Pipeline for inserting DRAFT record
     * Save as Draft functionality
     *
     * @param $registry_object_id
     * @throws Exception
     */
    public function save($registry_object_id)
    {
        set_exception_handler('json_exception_handler');
        $this->load->model('registry_objects', 'ro');
        $this->load->model('data_source/data_sources', 'ds');

        // capture the registry object
        $ro = $this->ro->getByID($registry_object_id);
        if (!$ro) {
            throw new Exception("No registry object exists with that ID!");
        }
        acl_enforce('REGISTRY_USER');
        ds_acl_enforce($ro->data_source_id);

        // capture the data source
        $ds = $this->ds->getByID($ro->data_source_id);

        // prepare XML
        $xml = $this->input->post('xml');
        $xml = $ro->cleanRIFCSofEmptyTags($xml, 'true', true);
        $xml = \ANDS\Util\XMLUtil::wrapRegistryObject($xml);

        // write the xml payload to the file system
        $batchID = 'MANUAL-ARO-' . md5($ro->key).'-'.time();
        \ANDS\Payload::write($ds->id, $batchID, $xml);

        // import Task creation
        $importTask = new \ANDS\API\Task\ImportTask();
        $importTask
            ->setCI($this)->setDb($this->db)
            ->init([
                'name' => 'ARO',
                'params' => http_build_query([
                    'pipeline' => 'ManualImport',
                    'source' => 'manual',
                    'ds_id' => $ro->data_source_id,
                    'batch_id' => $batchID,
                    'user_name' => $this->user->name(),
                    'targetStatus' => 'DRAFT'
                ])
            ])
            ->enableRunAllSubTask()
            ->initialiseTask();

        $importTask->run();

        $errorLog = $importTask->getError();

        // capture ro again and return result
        $ro = $this->ro->getByID($registry_object_id);
        initEloquent();
        $record = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($registry_object_id);
        $quality_html = \ANDS\Registry\Providers\Quality\QualityMetadataProvider::getQualityReportHTML($record);
        $result = [
            "status" => 'success',
            "ro_status" => "DRAFT",
            "title" => $ro->title,
            "qa_required" => $ds->qa_flag == DB_TRUE ? true : false,
            "data_source_id" => $ro->data_source_id,
            "approve_required" => $ds->manual_publish == DB_TRUE ? true : false,
            "error_count" => 0,
            "ro_id" => $ro->id,
            "ro_quality_level" => $ro->quality_level,
            "ro_quality_class" => ($ro->quality_level >= 2 ? "success" : "important"),
            "qa_$ro->quality_level" => true,
            "message" => implode(NL, $errorLog),
            "qa" => $quality_html
        ];

        echo json_encode($result);
    }

    // TODO: Remove after pipeline implementation
	public function save_deprecated($registry_object_id){
		set_exception_handler('json_exception_handler');

		$xml = $this->input->post('xml');
		$this->load->library('importer');

		$this->load->model('registry_objects', 'ro');
		$this->load->model('data_source/data_sources', 'ds');
		$ro = $this->ro->getByID($registry_object_id);

		if (!$ro){
			throw new Exception("No registry object exists with that ID!");
		}

		acl_enforce('REGISTRY_USER');
		ds_acl_enforce($ro->data_source_id);

		$ds = $this->ds->getByID($ro->data_source_id);



		$this->importer->forceDraft();

		$error_log = '';
		$status = 'success';
		//echo wrapRegistryObjects($xml);
		//exit();
		try{
			$xml = $ro->cleanRIFCSofEmptyTags($xml, 'true', true);
            $xml = wrapRegistryObjects($xml);
            $this->importer->validateRIFCS($xml);
            $this->importer->setXML($xml);
			$this->importer->setDatasource($ds);
			$this->importer->commit();
		}
		catch(Exception $e)
		{
			$status = 'error';
			$error_log = $e->getMessage();
		}
		//if ($error_log){
		//	throw new Exception("Errors during saving this registry object! " . BR . implode($error_log, BR));
		//}
		//else{
		// Fetch updated registry object!
		// $ro = $this->ro->getByID($registry_object_id);
		$ro = $this->ro->getByID($registry_object_id);

		//if the key has changed
		if($ro->key != $this->input->post('key')){
			$ro = $this->ro->getAllByKey($this->input->post('key'));
			$ro = $ro[0];
		}

		$qa = $ds->qa_flag==DB_TRUE ? true : false;
		$manual_publish = $ds->manual_publish==DB_TRUE ? true: false;

		$result =
			array(
				"status"=>$status,
				"ro_status"=>"DRAFT",
				"title"=>$ro->title,
				"qa_required"=>$qa,
				"data_source_id" => $ro->data_source_id,
				"approve_required"=>$manual_publish,
				"error_count"=> (int) $ro->error_count,
				"ro_id"=>$ro->id,
				"ro_quality_level"=>$ro->quality_level,
				"ro_quality_class"=>($ro->quality_level >= 2 ? "success" : "important"),
				"qa_$ro->quality_level"=>true,
				"message"=>$error_log,
				"qa"=>$ro->get_quality_text()
				);
			//if($qa) $result['qa'] = true;
			echo json_encode($result);
		//}
	}


	public function add_new(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');

		$this->load->library('importer');
		$data = $this->input->post('data');

		acl_enforce('REGISTRY_USER');

        // bootstrap result
        $result = [
            'success' => false,
            'message' => '',
            'ro_id' => null
        ];

        // check data source
        if (!array_key_exists('data_source_id', $data)) {
            throw new Exception("DataSource ID must be provided");
        }
        $dataSource = DataSource::find($data['data_source_id']);
        if (!$dataSource) {
            throw new Exception("Invalid DataSource ID");
        }
        ds_acl_enforce($data['data_source_id']);

        // check existence of key
        $key = $data['registry_object_key'];
        if ($draftRecord = \ANDS\Repository\RegistryObjectsRepository::getDraftByKey($key)) {
            $status = $draftRecord->status;
            throw new Exception("A RegistryObject with key $key already exists in status $status");
        }

        // prepare XML
        $xml = "<registryObject group='".$data['group']."'>".NL;
        $xml .= "<key>".$data['registry_object_key']."</key>".NL;
        $xml .= "<originatingSource type=''>".$data['originating_source']."</originatingSource>".NL;
        $xml .= "<".$data['ro_class']." type='".$data['type']."'>".NL;
        $xml .= "<description type=''></description>";
        $xml .= "<identifier type=''></identifier>";
        if($data['ro_class']=='collection') $xml .="<dates type=''></dates>";
        $xml .= "<location></location>";
        $xml .= "<relatedObject><key></key><relation type=''></relation></relatedObject>";
        $xml .= "<subject type=''></subject>";
        $xml .= "<relatedInfo></relatedInfo>";
        $xml .= "</".$data['ro_class'].">".NL;
        $xml .= "</registryObject>";
        $xml = \ANDS\Util\XMLUtil::wrapRegistryObject($xml);

        // write the xml payload to the file system
        $batchID = 'MANUAL-ARO-' . md5($data['registry_object_key']).'-'.time();
        \ANDS\Payload::write($dataSource->data_source_id, $batchID, $xml);

        // import Task creation
        $importTask = new \ANDS\API\Task\ImportTask();
        $importTask
            ->setCI($this)->setDb($this->db)
            ->init([
                'name' => 'ARO',
                'params' => http_build_query([
                    'pipeline' => 'ManualImport',
                    'source' => 'manual',
                    'ds_id' => $dataSource->data_source_id,
                    'batch_id' => $batchID,
                    'user_name' => $this->user->name(),
                    'targetStatus' => 'DRAFT'
                ])
            ])
            ->enableRunAllSubTask()
            ->initialiseTask();

        $importTask->run();
        $errorLog = $importTask->getError();
        if ($errorLog) {
            $result['message'] = implode(', ', $errorLog);
            echo json_encode($result);
            return;
        }

        // no error
        $jsondata['success'] = true;
        $key = $data['registry_object_key'];
        $draftRecord = \ANDS\Repository\RegistryObjectsRepository::getByKeyAndStatus($key, "DRAFT");
        if (!$draftRecord) {
            throw new Exception("Error getting DRAFT record for $key");
        }
        $result['success'] = true;
        $result['ro_id'] = $draftRecord->id;
        $jsondata['message'] = 'new Registry Object with id ' . $draftRecord->id . ' was created';

        echo json_encode($result);
	}

	public function related_object_search_form(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		$jsonData = array();
		$jsonData['success'] = true;
		$jsonData['html_data'] = $this->load->view('related_object_search_form', '', true);
		echo json_encode($jsonData);
	}

	//TODO:XXX
	public function fetch_related_object_aro(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		$jsonData['request'] = $this->input->post('related');
		$this->load->model('registry_objects', 'ro');

		$jsonData['result'] = array();
		if($this->input->post('related')){
			foreach($this->input->post('related') as $key){
				$ro = $this->ro->getPublishedByKey($key);
				if(!$ro) $ro = $this->ro->getDraftByKey($key);
				if($ro){
					$jsonData['result'][$key] = array('title'=>$ro->title, 'status'=>$ro->status, 'key'=>$ro->key, 'id'=>$ro->id, 'class'=>$ro->class, 'link'=>base_url('registry_object/view/'.$ro->id), 'readable_status'=>readable($ro->status));
				}else{
					$jsonData['result'][$key] = array('title'=>'Registry Object Not Found', 'status'=>'notfound');
				}
			}
		}
		$jsonData['success'] = true;
		echo json_encode($jsonData);
	}

	public function getGroupSuggestor(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		$jsonData = array();

		$this->load->model("data_source/data_sources","ds");
	 	$dataSources = $this->ds->getOwnedDataSources(true);

		// Use SOLR if we have loads of data sources (prevent performance issues for SUPERUSERS
		// or data source admins with very many data sources). Note: this means that SUPERUSERs
		// will no longer get group name suggestions for DRAFT records
		if(count($dataSources) > 10)
		{
			$dataSourceIdString = implode(' ',$dataSources);

			$this->load->library('Solr');
			$this->solr->init();
			$this->solr->setOpt('q','data_source_id:(' . $dataSourceIdString .')');
			$this->solr->setFacetOpt('field','group');
			$this->solr->executeSearch();
			$groupNames = $this->solr->getFacetResult('group');
			foreach($groupNames AS $g => $_)
			{
				$jsonData[] = array('value'=>$g, 'subtext'=>'');
			}
	 	}
		else
		{
			$this->load->model("registry_objects","ro");
			$groups = $this->ro->getGroupSuggestor($dataSources);
			foreach($groups->result() as $g){
				$jsonData[] = array('value'=>$g->value, 'subtext'=>'');
			}
		}

		echo json_encode($jsonData);
	}

	public function manage_table($data_source_id = false){
		acl_enforce('REGISTRY_USER');
		ds_acl_enforce($data_source_id);
		$data['title'] = 'Manage My Records';

		$this->load->model('data_source/data_sources', 'ds');
		if($data_source_id){
			$data_source = $this->ds->getByID($data_source_id);
			if(!$data_source) show_error("Unable to retrieve data source id = ".$data_source_id, 404);

			$data_source->updateStats();//TODO: XXX

			//$data['data_source'] = $data_source;
			$data['data_source'] = array(
				'title'=>$data_source->title,
				'id'=>$data_source->id,
				'count_total'=>$data_source->count_total,
				'count_APPROVED'=>$data_source->count_APPROVED,
				'count_SUBMITTED_FOR_ASSESSMENT'=>$data_source->count_SUBMITTED_FOR_ASSESSMENT,
				'count_PUBLISHED'=>$data_source->count_PUBLISHED
			);

			//MMR
			//$this->load->model('registry_object/registry_objects', 'ro');
			//$ros = $this->ro->getByDataSourceID($data_source_id);

		}else{
			//showing all registry objects for all datasource
			//TODO: check for privileges
			$this->load->model('maintenance/maintenance_stat', 'mm');
			$total = $this->mm->getTotalRegistryObjectsCount('db');
			$data['data_source'] = array(
				'title'=>'Viewing All Registry Object',
				'id'=>'0',
				'count_total'=>$total,
				'count_APPROVED'=>0,
				'count_SUBMITTED_FOR_ASSESSMENT'=>0,
				'count_PUBLISHED'=>0
			);
			//show_error('No Data Source ID provided. use all data source view for relevant roles');

		}
		$data['scripts'] = array('manage_my_record');
		$data['js_lib'] = array('core', 'tinymce', 'datepicker', 'dataTables');


		$this->load->view("manage_my_record", $data);
	}

	public function getData($data_source_id, $filter='', $value=''){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$jsonData = array();
		$jsonData['aaData'] = array();

		//ahmagerd shorthand
		$limit = ($this->input->post('iDisplayLength') ? (int) $this->input->post('iDisplayLength') : 10);
		$offset = ($this->input->post('iDisplayStart') ? (int) $this->input->post('iDisplayStart') : 0);

		//filters
		$filters = array();
		$filters['filter'] = $filter!='' ? array($filter=>$value) : false;
		$filters['search'] = ($this->input->post('sSearch') ? $this->input->post('sSearch') : false);

		//sort
		/*$filters['sort'] = array();
		$aColumns=array('key', 'title', 'status');
		for($i=0; $i<intval($this->input->post('iSortingCols')); $i++){//black magic
			if($this->input->post('bSortable_'.intval($this->input->post('iSortCol_'.$i)))=='true'){
				$filters['sort'][] = array(
					$aColumns[intval($this->db->escape_str($this->input->post('iSortCol_'.$i)))] => $this->db->escape_str($this->input->post('sSortDir_'.$i))
				);
			}
        }*/

        $this->load->model('data_source/data_sources', 'ds');
        $data_source = $this->ds->getByID($data_source_id);

		//Get Registry Objects
		$this->load->model('registry_object/registry_objects', 'ro');
		if($data_source_id >0) {
			$ros = $this->ro->getByDataSourceID($data_source_id,$limit,$offset,$filters);
			$total = (int) $data_source->count_total;
		}else{
			$this->load->model('registry_object/registry_objects', 'ro');
			$ros = $this->ro->getAll($limit, $offset, $filters);
			$this->load->model('maintenance/maintenance_stat', 'mm');
			$total = $this->mm->getTotalRegistryObjectsCount('db');
		}

		if($ros){
			foreach($ros as $ro){
				$jsonData['aaData'][] = array(
					'key'=>anchor('registry_object/view/'.$ro->registry_object_id, $ro->key),
					'id'=>$ro->registry_object_id,
					'Title'=>$ro->list_title,
					'Status'=>$ro->status,
					'Options'=>'Options'
				);
			}
		}

		//Data Source
		$this->load->model('data_source/data_sources', 'ds');
		$data_source = $this->ds->getByID($data_source_id);

		$jsonData['sEcho']=(int)$this->input->post('sEcho');
		$jsonData['iTotalRecords'] = $total;
		$hasFilter = false;
		$jsonData['iTotalDisplayRecords'] = $filters['search'] ? sizeof($ros) : $total;
		$jsonData['filters'] = $filters;

        echo json_encode($jsonData);
	}


	/**
	 * Get A Record
	 *
	 *
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @package ands/registryobject
	 * @param registry object ID
	 * @return [JSON] of a single registry object
	 *
	 */
	public function get_record($id){
		$this->load->model('registry_objects', 'ro');
		$ro = $this->ro->getByID($id);
		$ro->enrich();
		$data['xml'] = html_entity_decode($ro->getRif());
		$data['extrif'] = html_entity_decode($ro->getExtRif());
        initEloquent();
		$data['solr'] = json_encode($ro->indexable_json());
		$data['view'] = $ro->transformForHtml();
		$data['id'] = $ro->id;
		$data['title'] = $ro->getAttribute('list_title');
		$data['attributes'] = $ro->getAttributes();
		$data['revisions'] = $ro->getAllRevisions();

		//preview link for iframe in preview, show published view if published, show draft preview if in draft
		$data['preview_link'] = portal_url() . $ro->slug;

		$jsonData = array();
		$jsonData['status'] = 'OK';
		$jsonData['ro'] = $data;

		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}

    public function get_record_data($id){
        initEloquent();
        $record = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($id);
        $data['xml'] = html_entity_decode($record->getCurrentData()->data);
        $jsonData = array();
        $jsonData['status'] = 'OK';
        $jsonData['ro'] = $data;
        $jsonData = json_encode($jsonData);
        echo $jsonData;
    }


    /**
     * @throws Exception
     */
    public function get_quality_view(){
		$record = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($this->input->post('ro_id'));
		$report = \ANDS\Registry\Providers\Quality\QualityMetadataProvider::getMetadataReport($record);
		$html = $this->load->view('quality_report', ['report' => $report], true);
		echo $html;
	}

	public function get_quality_html(){
		initEloquent();
		$record = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($this->input->post('ro_id'));
		$quality_html = \ANDS\Registry\Providers\Quality\QualityMetadataProvider::getQualityReportHTML($record);
		echo $quality_html;
	}


	public function get_validation_text(){
        $this->load->model('registry_objects', 'ro');
        $ro = $this->ro->getByID($this->input->post('ro_id'));

        $record = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($this->input->post('ro_id'));
        $xml = $record->getCurrentData()->data;

        $xml = \ANDS\Util\XMLUtil::unwrapRegistryObject($xml);
//        $xml = $ro->cleanRIFCSofEmptyTags($xml, 'false', true);
        $result = $ro->transformForQA(wrapRegistryObjects($xml), null, "html");

        echo $result;
	}

	public function get_native_record($id){
		$this->load->model('registry_objects', 'ro');
		$ro = $this->ro->getByID($id);
		$data['txt'] = $ro->getNativeFormatData($id);
		$jsonData = json_encode($data);
		echo $jsonData;
	}

	public function tag($action){
		set_exception_handler('json_exception_handler');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$this->load->model('registry_objects', 'ro');
		$ro_id = $this->input->post('ro_id');
		$tag = $this->input->post('tag');
		$ro = $this->ro->getByID($ro_id);

		if($action=='add' && $tag!=''){
			if($e = $ro->addTag($tag)){
				$jsonData['status'] = 'success';
			}else {
				$jsonData['status'] = 'error';
				$jsonData['msg'] = $e;
			}
		}else if($action=='remove'){
			if($ro->removeTag($tag)){
				$jsonData['status'] = 'success';
			}else $jsonData['status'] = 'error';
		}

		echo json_encode($jsonData);
	}

	function update($all = false)
    {
        set_exception_handler('json_exception_handler');
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        $this->load->model('registry_objects', 'ro');
        $this->load->model('data_source/data_sources', 'ds');

        $dataSourceID = $this->input->post('data_source_id');
        $ds = $this->ds->getByID($dataSourceID);
        if (!$ds) {
            throw new Exception("Invalid Data Source ID specified");
        }
        ds_acl_enforce($ds->id);

        $attributes = $this->input->post('attributes');
        $affected_ids = $this->input->post('affected_ids');

        $result = [
            'status' => 'success',
            'error' => [],
            'success' => [],
            'error_count' => 0,
            'success_count' => 0
        ];

        if($all){
            $filters = $this->input->post('filters');
            $excluded_records = $this->input->post('excluded_records') ?: [];
            $args = [
                'sort' => isset($filters['sort']) ? $filters['sort'] : ['updated'=>'desc'],
                'search' => isset($filters['search']) ? $filters['search'] : false,
                'or_filter' => isset($filters['or_filter']) ? $filters['or_filter'] : false,
                'filter' => isset($filters['filter']) ? array_merge($filters['filter'], ['status'=>$this->input->post('select_all')]) : ['status'=>$this->input->post('select_all')],
                'data_source_id' => $dataSourceID
            ];
            $affected_ros = $this->ro->filter_by($args, 0, 0, true);
            $affected_ids = [];
            if (is_array($affected_ros)) {
                foreach ($affected_ros as $r) {
                    if (!in_array($r->registry_object_id, $excluded_records)) {
                        $affected_ids[] = $r->registry_object_id;
                    }
                }
            }
        }

        $statusChange = false;
        foreach ($attributes as $attr) {
            if ($attr["name"] == "status") {
                $statusChange = true;
                $targetStatus = $attr['value'];

                // TODO: SUBMITTED FOR ASSESSMENT (maybe in Pipeline instead)
                continue;
            }

            foreach ($affected_ids as $id) {
                $ro = $this->ro->getByID($id);
                try {
                    $ro->setAttribute($attr['name'], $attr['value']);
                    $result['success_count']++;
                } catch (Exception $e) {
                    $result['status'] = 'error';
                    $result['error'][] = $e->getMessage();
                }
            }
        }

        // if there's a status changed, use the handleStatusChange pipeline
        if ($statusChange && isset($targetStatus)) {

            // for ARO screen
            $result['message_code'] = $targetStatus;

            $importTask = new \ANDS\API\Task\ImportTask();
            $importTask->init([
                'name' => "HandleStatusChange Pipeline",
                'params' => http_build_query([
                    'pipeline' => 'PublishingWorkflow',
                    'ds_id' => $dataSourceID,
                    'user_name' => $this->user->name(),
                    'targetStatus' => $targetStatus,
                    'source' => 'manual'
                ])
            ]);
            $importTask
                ->skipLoadingPayload()
                ->enableRunAllSubTask()
                ->setCI($this)
                ->setDb($this->db);

            $importTask
                ->setTaskData('affectedRecords', $affected_ids)
                ->initialiseTask();

            // send the task to background to obtain a task ID
            $importTask->sendToBackground();

            // append data source log
            $dataSource = ANDS\DataSource::find($dataSourceID);
            $count = count($affected_ids);
            $importStartMessage = [
                "Manual Status Change Started for $count records",
                "Task ID: ". $importTask->getId()
            ];
            $dataSource->appendDataSourceLog(
                implode(NL, $importStartMessage),
                "info", "IMPORTER"
            );
            $importTask->run();

            $result['error'] = array_merge($result['error'], $importTask->getError());

            // works for single record publish through the line
            // uses for ARO screen and View screen to redirect to the new record ID
            $result['message_code'] = $targetStatus;
            $importedRecords = $importTask->getTaskData('importedRecords');
            if ($importedRecords && count($importedRecords) == 1) {
                $result['new_ro_id'] = array_first($importedRecords);
            }

            // in case the data is not updated, the ID would be the same and will be in harvestedRecordIDs
            if (count($affected_ids) == 1) {
                $harvestedRecordIDs = $importTask->getTaskData('harvestedRecordIDs');
                if ($harvestedRecordIDs && count($harvestedRecordIDs) == 1) {
                    $result['new_ro_id'] = array_first($harvestedRecordIDs);
                }
            }

            // TODO: Carry success message here
        }

        // format result
        $result['error_count'] = count($result['error']);
        $result['error_message'] = '<ul class="error_message">';
        foreach ($result['error'] as $error) {
            $result['error_message'] .= "<li>$error</li>";
        }
        $result['error_message'] .= '</ul>';
        $result['success_message'] = '<ul class="success_message">';
        foreach ($result['success'] as $success) {
            $result['success_message'] .= "<li>$success</li>";
        }

        echo json_encode($result, true);

    }

    // TODO: Remove After Pipeline
	function update_deprecated($all = false){
		set_exception_handler('json_exception_handler');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$jsonData = array();
		$jsondata['status'] = 'success';
		$jsondata['error_message'] = '<ul class="error_mesage">';
		$jsondata['success_message'] = '<ul class="success_mesage">';
		$jsondata['success_count'] = 0;
		$jsondata['error_count'] = 0;
		$this->load->model('registry_objects', 'ro');

		$this->load->model('data_source/data_sources', 'ds');
		$data_source_id = $this->input->post('data_source_id');
		$ds = $this->ds->getByID($data_source_id);
		if (!$ds)
		{
			throw new Exception("Invalid Data Source ID specified");
		}
		ds_acl_enforce($ds->id);

		$attributes = $this->input->post('attributes');

		if(!$all)
		{

			$affected_ids = $this->input->post('affected_ids');
			$attributes = $this->input->post('attributes');

		}
		else
		{

			/* SELECT ALL-style update -- must use the filters to determine what's on-screen */
			$select_all = $this->input->post('select_all');
			$excluded_records = $this->input->post('excluded_records') ?: array();
			$filters = $this->input->post('filters');

			$args = array();
			$args['sort'] = isset($filters['sort']) ? $filters['sort'] : array('updated'=>'desc');
			$args['search'] = isset($filters['search']) ? $filters['search'] : false;
			$args['or_filter'] = isset($filters['or_filter']) ? $filters['or_filter'] : false;
			$args['filter'] = isset($filters['filter']) ? array_merge($filters['filter'], array('status'=>$this->input->post('select_all'))) : array('status'=>$this->input->post('select_all'));
			$args['data_source_id'] = $data_source_id;

			$registryObjects = $this->ro->filter_by($args, 0, 0, true);

			$affected_ids = array();
			foreach($registryObjects as $ro){
				if (!in_array($ro->registry_object_id, $excluded_records))
				{
					array_push($affected_ids, $ro->registry_object_id);
				}
			}
		}


		$sentMail = false;

		// this array contains the published IDs that need taking care of
		$published = array();

		foreach($affected_ids as $id){
			$ro = $this->ro->getByID($id);

			foreach($attributes as $a){
				if($a['name']=='status' && $ro->status == 'DRAFT' && $ro->error_count > 0)
				{
					$jsondata['error_count']++;
					$jsondata['error_message'] .= "<li>Registry Object contains error(s): ".$ro->title."</li>";
				}
				elseif($a['name']=='status' && ($a['value']=='APPROVED' || $a['value']=='PUBLISHED') && $ro->error_count > 0)
				{
					$jsondata['error_count']++;
					$jsondata['error_message'] .= "<li>Registry Object contains error(s): ".$ro->title."</li>";
				}
				else
				{
					try{
						$ro->setAttribute($a['name'], $a['value']);
						if($a['name']=='status')
						{
							$ro->flag = 'f';
						}
						$old_ro_id = $ro->id;
						if($ro->save())
						{
							// ID may have changed if a DRAFT record overwrites a PUBLISHED one
							if ($ro->id != $old_ro_id)
							{
								// This will be used to redirect from the registry view page
								$jsondata['new_ro_id'] = $ro->id;
							}

							if($a['name']=='status')
							{
								// Message Code for single-record status updates (from ARO screen)
								$jsondata['message_code'] = $a['value'];
								if($a['value']=='SUBMITTED_FOR_ASSESSMENT')
								{
									if(($ds->count_SUBMITTED_FOR_ASSESSMENT == 0) && !$sentMail){
										// If there is a notification email set, send a mail...
										if ($ds->assessment_notify_email_addr)
										{
											$this->ro->emailAssessor($ds);
											$jsondata['message_code'] = 'SUBMITTED_FOR_ASSESSMENT_EMAIL_SENT';
											$jsondata['success_message'] .= '<strong>Note:</strong> An ANDS Quality Assessor has been notified of your submitted record(s).</li>';
											$sentMail = true;
										}
										else
										{
											// Otherwise prompt to contact the CLO
											$jsondata['success_message'] .= '<strong>Note:</strong> You should contact your ANDS Client Liaison Officer to let them know your records are ready for assessment.</li>';
											$sentMail = true;
										}
									}
									elseif ($ds->count_SUBMITTED_FOR_ASSESSMENT > 0 && !$sentMail)
									{
										$jsondata['success_message'] .= '<strong>Note:</strong> You should contact your ANDS Client Liaison Officer to let them know your records are ready for assessment.</li>';
										$sentMail = true;
									}
								}
							}

							// add to the published list if not exists already
							if (!in_array($ro->id, $published) && $a['name']=='status' && isPublishedStatus($a['value'])) {
								array_push($published, $ro->id);
							}

							$jsondata['success_count']++;
							//$jsondata['success_message'] .= '<li>Updated '.$ro->title.' set '.$a['name'].' to value:'.$a['value']."</li>";

						}
						else
						{
							$jsondata['error_count']++;
							$jsondata['error_message'] .= '<li>Failed to update '.$ro->title.' set '.$a['name'].' to value:'.$a['value']."</li>";
							$jsondata['status'] = 'error';
						}
					}
					catch(Exception $e){
						$jsondata['status'] = 'error';
						$jsondata['error_count']++;
						$jsondata['error_message'] .= "<li>".$e->getMessage()."</li>";
					}
				}
			}
		}

		// set a background task to fix the relationship of the published records and sync them
		if (sizeof($published) > 0) {
			require_once BASE . 'vendor/autoload.php';
			$params = [
				'class' => 'fixRelationship',
				'type' => 'ro',
				'id' => join(',', $published)
			];
			$task = [
				'name' => "fixRelationship of " . sizeof($published). " records",
				'type' => 'POKE',
				'frequency' => 'ONCE',
				'priority' => 5,
				'params' => http_build_query($params)
			];
			$taskManager = new \ANDS\API\Task\TaskManager($this->db, $this);
			$taskManager->addTask($task);
		}

		$ds->updateStats();

		$jsondata['error_message'] .= '</ul>';
		$jsondata['success_message'] .= '</ul>';
		echo json_encode($jsondata);
	}


    function reinstate(){
        set_exception_handler('json_exception_handler');
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        $deletedRegistryObjectID = $this->input->post('deleted_registry_object_id');
        $dataSourceID = $this->input->post('data_source_id');
        initEloquent();
        $record = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($deletedRegistryObjectID);

        $xml = $record->getCurrentData()->data;
        // write the xml payload to the file system
        $batchID = 'UNDELETE-' . md5($record->key).'-'.time();
        \ANDS\Payload::write($dataSourceID, $batchID, $xml);

        // import Task creation
        $importTask = new \ANDS\API\Task\ImportTask();
        $importTask
            ->setCI($this)->setDb($this->db)
            ->init([
                'name' => 'Reinstate',
                'params' => http_build_query([
                    'pipeline' => 'ManualImport',
                    'source' => 'manual',
                    'ds_id' => $dataSourceID,
                    'batch_id' => $batchID,
                ])
            ])
            ->enableRunAllSubTask()
            ->initialiseTask();

        $importTask->run();

        $errorLog = $importTask->getError();
        $message = $importTask->getMessage();

        $result['response'] = 'error';
        $result['message'] = "Unable to Reinstate Record";


        if($errorLog)
        {
            $result['log'] = $errorLog;
        }
        elseif($importTask->getTaskData("recordsExistOtherDataSourceCount") > 0)
        {
            $result['log'] = "Record key:(".$record->key.NL.") exists in a different data source".NL;
            $result['log'] .= str_replace("," , NL, implode("," , $message['log']));
        }
        else
        {
            $result['response'] = 'success';
            $result['message'] = "Record Reinstated as ". $importTask->getTaskData("targetStatus");
            $result['target_status'] = $importTask->getTaskData("targetStatus");
            $result['log'] = str_replace("," , NL, implode("," , $message['log']));
        }
        echo json_encode($result);

    }
    
    
    
    function delete(){
        set_exception_handler('json_exception_handler');
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        $this->load->model('registry_objects', 'ro');
        $this->load->model('data_source/data_sources', 'ds');

        $affectedIDs = $this->input->post('affected_ids');

        // select_all is the status
        $select_all = $this->input->post('select_all');

        $dataSourceID = $this->input->post('data_source_id');
        $excludedRecords = $this->input->post('excluded_records') ?: [];

        // capture affected_ros mainly for select_all when affected_ids does not capture all;
        if($select_all && $select_all != "false"){
            $filters = $this->input->post('filters');
            $args = [
                'sort' => isset($filters['sort']) ? $filters['sort'] : ['updated'=>'desc'],
                'search' => isset($filters['search']) ? $filters['search'] : false,
                'or_filter' => isset($filters['or_filter']) ? $filters['or_filter'] : false,
                'filter' => isset($filters['filter']) ? array_merge($filters['filter'], ['status'=>$this->input->post('select_all')]) : ['status'=>$this->input->post('select_all')],
                'data_source_id' => $dataSourceID
            ];
            $affected_ros = $this->ro->filter_by($args, 0, 0, true);
            $affectedIDs = [];
            if (is_array($affected_ros)) {
                foreach ($affected_ros as $r) {
                    if (!in_array($r->registry_object_id, $excludedRecords)) {
                        $affectedIDs[] = $r->registry_object_id;
                    }
                }
            }
        }

        // The affected_ids list should be good now
        // Running delete pipeline
        $importTask = new \ANDS\API\Task\ImportTask();

        $importTask->init([
            'name' => "Manual Delete",
			'type' => "PHPSHELL",
            'params' => http_build_query([
                'ds_id' => $dataSourceID,
                'pipeline' => 'PublishingWorkflow',
                'source' => 'manual'
            ])
        ]);

        $importTask
            ->setCI($this)
            ->skipLoadingPayload()
            ->enableRunAllSubTask();

        $importTask
            ->setTaskData('deletedRecords', $affectedIDs)
            ->setCI($this)
            ->setDb($this->db)
            ->initialiseTask();

        // send the task to background to obtain a task ID
        $importTask->sendToBackground();

        // append data source log
        $dataSource = ANDS\DataSource::find($dataSourceID);
        $count = count($affectedIDs);
        $importStartMessage = [
            "Deleting $count records",
            "Task ID: ". $importTask->getId()
        ];
        $dataSource->appendDataSourceLog(
            implode(NL, $importStartMessage),
            "info", "IMPORTER"
        );

        $importTask->run();

        // update the stats of the records
        $ds = $this->ds->getByID($dataSourceID);
        $ds->updateStats();

        echo json_encode([
            "status" => "success"
        ]);
    }

    // TODO: Remove after Pipeline Integration
	function delete_deprecated(){
		set_exception_handler('json_exception_handler');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$affected_ids = $this->input->post('affected_ids');
		// $select_all is the status, not a boolean?
		//$select_all = $this->input->post('select_all')=='true' ? true : false;

		$select_all = $this->input->post('select_all');
		$data_source_id = $this->input->post('data_source_id');
		$excluded_records = $this->input->post('excluded_records') ?: array();
		$this->load->model('registry_objects', 'ro');
		$this->load->model('data_source/data_sources', 'ds');


		if($select_all && $select_all != "false"){
			$filters = $this->input->post('filters');


			$args = array();

			$args['sort'] = isset($filters['sort']) ? $filters['sort'] : array('updated'=>'desc');
			$args['search'] = isset($filters['search']) ? $filters['search'] : false;
			$args['or_filter'] = isset($filters['or_filter']) ? $filters['or_filter'] : false;
			$args['filter'] = isset($filters['filter']) ? array_merge($filters['filter'], array('status'=>$this->input->post('select_all'))) : array('status'=>$this->input->post('select_all'));
			$args['data_source_id'] = $data_source_id;
			$affected_ros = $this->ro->filter_by($args, 0, 0, true);

			$affected_ids = array();
			if(is_array($affected_ros))
			{
				foreach($affected_ros as $r){
					if(!in_array($r->registry_object_id, $excluded_records)) {
						array_push($affected_ids, $r->registry_object_id);
					}
				}
			}
		}
		$ds = $this->ds->getByID($data_source_id);

		if (is_array($affected_ids) && sizeof($affected_ids)>0){
			$this->load->library('importer');
			$deleted_and_affected_record_keys = $this->ro->deleteRegistryObjects($affected_ids, false);
			$this->importer->addToDeletedList($deleted_and_affected_record_keys['deleted_record_keys']);
			$this->importer->addToAffectedList($deleted_and_affected_record_keys['affected_record_keys']);
			$taskLog = $this->importer->finishImportTasks();
			if($this->importer->runBenchMark){
				$ds->append_log('delete Log '.NL.$taskLog, "IMPORTER_INFO", "harvester", "IMPORTER_INFO");
			}
		}


		// set a background task to fix the relationship of the deleted records by removing them
		if (sizeof($affected_ids) > 0) {
			require_once BASE . 'vendor/autoload.php';
			$params = [
				'class' => 'fixRelationship',
				'type' => 'ro',
				'id' => join(',', $affected_ids)
			];
			$task = [
				'name' => "fixRelationship of " . sizeof($affected_ids). " records",
				'type' => 'POKE',
				'frequency' => 'ONCE',
				'priority' => 5,
				'params' => http_build_query($params)
			];
			$taskManager = new \ANDS\API\Task\TaskManager($this->db, $this);
			$taskManager->addTask($task);
		}

		$ds->updateStats();

		echo json_encode(array("status"=>"success"));
	}



	function get_solr_doc($id, $limit=null){
        set_exception_handler('json_exception_handler');
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
		$this->load->model('registry_objects', 'ro');
		$ro = $this->ro->getByID($id);
		$solrDoc = $ro->indexable_json();
        echo json_encode($solrDoc);
	}

	//-----------DEPRECATED AFTER THIS LINE -----------------------//

	/**
	 * Get the edit form of a Record
	 *
	 *
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @package ands/registryobject
	 * @param registry object ID
	 * @return [HTML] transformed form from extrif
	 *
	 */

	public function get_edit_form($id){
		// ro is the alias for the registry object model
		$this->load->model('registry_objects', 'ro');
		$ro = $this->ro->getByID($id);
		$data['extrif'] = $ro->getExtRif();

		$data['preview_link'] = 'http://demo.ands.org.au/'.$ro->slug;
		$data['transform'] = $ro->transformForFORM();
		echo $data['transform'];
		//$this->load->view('registry_object_edit', $data);
	}


	/**
	 * Get the edit form of a Record
	 *
	 *
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @package ands/registryobject
	 * @param registry object ID, [POST] custom RIFCS
	 * @return [HTML] transformed form from extrif
	 *
	 */
	public function get_edit_form_custom($id){
		$this->load->model('registry_objects', 'ro');
		$ro = $this->ro->getByID($id);
		$rifcs = $this->input->post('rifcs');

		$data['transform'] = $ro->transformCustomForFORM($rifcs);
		echo $data['transform'];
	}

	/**
	 * Get a list of records based on the filters
	 *
	 *
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @package ands/registryobject
	 * @param [POST] Filters(Fields), [POST] sorts, [POST] page
	 * @return [JSON] results of the search
	 * @todo ACL, reponse error handling
	 */
	public function get_records(){
		$fields = $this->input->post('fields');
		$sorts = $this->input->post('sorts');
		$page = $this->input->post('page');

		//Construct the search query
		$q = '';$i = 0;//counter
		if($fields){
			foreach($fields as $field=>$val){
				if($i!=0)$q.=' AND ';

				if($field=='list_title'){
					$q .=$field.':(*'.$val.'*)';
				}else{
					$q .=$field.':('.$val.')';
				}
				$i++;
			}
		}
		if($q=='')$q='*:*';

		//Calculate the start and row based on the page, row will be 15 by default
		$start = 0; $row = 15;
		if($page!=1) $start = ($page - 1) * $row;

		//Fire the SOLR search
		/*$this->load->model('solr');
		$fields = array(
			'q'=>$q,'start'=>$start,'indent'=>'on', 'wt'=>'json', 'fl'=>'*', 'rows'=>$row
		);
		if($sorts && $sorts!=''){
			$fields['sort']=$sorts;
		}
		$facets = '&facet=true&facet.sort=index&facet.mincount=1&facet.field=class&facet.field=status&facet.field=quality_level';
		$solr_search_result = $this->solr->fireSearch($fields, $facets);*/

		$this->load->library('solr');
		$this->solr->setOpt('q',$q);
		$this->solr->setOpt('start',$start);
		$this->solr->setOpt('rows',$row);
		$this->solr->setOpt('sort',$sorts);
		$this->solr->setOpt('q',$q);
		$this->solr->setFacetOpt('field', 'class');
		$solr_search_result = $this->solr->executeSearch();

		//Analyze the result
		$solr_header = $solr_search_result->{'responseHeader'};
		$solr_response = $solr_search_result->{'response'};
		$num_found = $solr_response->{'numFound'};
		$facet_fields = $solr_search_result->{'facet_counts'}->{'facet_fields'};


		//Construct the return [JSON] array
		$jsonData = array();

		$items = array();
		if($num_found>0){
			$jsonData['no_more'] = false;
			$solr_result = $solr_response->{'docs'};
			//echo '<pre>';
			foreach($solr_result as $doc){
				$item = array();

				//get all stuffs in there so that we don't miss anything
				foreach($doc as $key=>$attrib){
					$item[$key] = $attrib;
				}

				//fix multi-valued description
				//LOGIC: only if there's a description if there's a brief, use it, if there's none, use first one
				if(isset($doc->{'description_value'})){
					foreach($doc->{'description_type'} as $key=>$type){
						if($type=='brief'){//use it
							$item['description'] = $doc->{'description_value'}[$key];
						}
					}
					if(!isset($item['description'])){
						$item['description'] = $doc->{'description_value'}[0];
					}
				}
				if(!isset($item['description'])){
					$item['description'] = '';
				}
				array_push($items, $item);
			}
			//var_dump($items);
		}else{
			$jsonData['no_more'] = true;//there is no more data, tell the client that
		}

		//Construct the Facet JSON bit
		$facets = array();
		foreach($facet_fields as $field=>$array){
			for($i=0;$i<sizeof($array)-1;$i=$i+2){
				$field_name = $array[$i];
				$value = $array[$i+1];
				$facets[$field][$field_name] = $value;
			}
		}

		//Putting them all together and return
		$jsonData['status'] = 'OK';
		$jsonData['q'] = $solr_header;
		$jsonData['items'] = $items;
		$jsonData['num_found'] = $num_found;
		$jsonData['facets'] = $facets;

		$jsonData = json_encode($jsonData);
		echo $jsonData;

	}

	public function getConnections($ro_id, $limit=null)
	{
		$connections = array();
		$status = array();
        if($limit && (int)$limit > 0)
            $party_conn_limit = $limit;
        else
            $party_conn_limit = 20;
		$this->load->model('registry_object/registry_objects', 'ro');
		$ro = $this->ro->getByID($ro_id);
		if($ro){
            //$connections = $ro->getConnections();
            if($ro->class == 'party')
			    $connections = $ro->getAllRelatedObjects(true, false, false, $party_conn_limit); // allow drafts
            else
                $connections = $ro->getAllRelatedObjects(true);
			foreach($connections AS &$link)
			{
				// Reverse the relationship description (note: this reverses to the "readable" version (i.e. not camelcase))
				if ($link['registry_object_id'] && in_array($link['origin'], array('REVERSE_EXT','REVERSE_INT')))
				{
					$link['relation_type'] = format_relationship($link['class'], $link['relation_type'], $link['origin'], $ro->class);
				}
				if($link['status']) $link['readable_status'] = readable($link['status']);
			}
		}
		$status['count'] = sizeof($connections);
		echo json_encode(array("status"=>$status,"connections"=>$connections));
	}


	/* Generate a list of actions which can be performed on the record (based on your role/status) */
	private function generateStatusActionBar(_registry_object $ro, _data_source $data_source)
	{
		$actions = array();
		$qa = $data_source->qa_flag == 1 ? true : false;
		$manual_publish = $data_source->manual_publish == 1 ? true: false;
		if ($this->user->hasFunction('REGISTRY_USER'))
		{

			switch($ro->status){

				case 'DRAFT':
					if($qa)
					{
						$actions[] = 'SUBMITTED_FOR_ASSESSMENT';
					}
					elseif ($manual_publish)
					{
						$actions[] = 'APPROVED';
					}
					else
					{
						$actions[] = 'PUBLISHED';
					}
				break;

				case 'MORE_WORK_REQUIRED':
					$actions[] = 'DRAFT';
				break;

				case 'SUBMITTED_FOR_ASSESSMENT':
					if($this->user->hasFunction('REGISTRY_STAFF')) {
						$actions[] = 'DRAFT';
						$actions[] = 'ASSESSMENT_IN_PROGRESS';
					}
				break;
				case 'ASSESSMENT_IN_PROGRESS':
					if($this->user->hasFunction('REGISTRY_STAFF')) {
						$actions[] = 'MORE_WORK_REQUIRED';
						if ($manual_publish)
						{
							$actions[] = 'APPROVED';
						}
						else
						{
							$actions[] = 'PUBLISHED';
						}
					}
				break;
				case 'APPROVED':
					$actions[] = 'PUBLISHED';
					break;
				case 'PUBLISHED':
				break;
			}
		}
		return $actions;
	}

	/**
	 * Export to ENDNote
	 * Returns an ENDNote file to use for download
	 * with right content-type header
	 *
	 * @param $registry_object_id
     */
	public function exportToEndnote($registry_object_id)
	{
		$registry_object_id = str_replace(".ris", "", $registry_object_id);
		$citations = '';

		$CI =& get_instance();
		$CI->load->model('registry_object/registry_objects', 'rom');

		// works for single registry_object_id or a list of registry_object_id separated by "-"
		$registry_objects = explode("-", $registry_object_id);
		foreach ($registry_objects as $id) {
			$cite_ro = $CI->rom->getByID($id);
			if ($cite_ro) {
				$citations .= use_citation_handle($id, $cite_ro);
			}
		}
		header('Content-type: application/x-research-info-systems');
		print($citations);
	}

}


/**
 * Returns the citation of a given registry_object
 * using the citation handler object used by the registry API
 *
 * @param $registry_object_id
 * @param $cite_ro
 * @return string
 */
function use_citation_handle($registry_object_id, $cite_ro)
{
	require_once(REGISTRY_APP_PATH . '/services/method_handlers/registry_object_handlers/citations.php');
	$xml = $cite_ro->getSimpleXML();
	$xml = addXMLDeclarationUTF8(($xml->registryObject ? $xml->registryObject->asXML() : $xml->asXML()));
	$xml = simplexml_load_string($xml);
	$xml = simplexml_load_string(addXMLDeclarationUTF8($xml->asXML()));
	if ($xml) {
		$rifDom = new DOMDocument();
		$rifDom->loadXML($cite_ro->getRif());
		$gXPath = new DOMXpath($rifDom);
		$gXPath->registerNamespace('ro', 'http://ands.org.au/standards/rif-cs/registryObjects');
	}

	$ci =& get_instance();
	$ci->load->library('solr');
    $ci->solr->init();
	$ci->solr->clearOpt('fq');
	$ci->solr->setOpt('fq', '+id:' . $registry_object_id);
	$ci->solr->setOpt('fl', 'id,key,slug,title,class,type,data_source_id,group,created,status,subject_value_resolved');
	$result = $ci->solr->executeSearch(true);

	if (sizeof($result['response']['docs']) == 1) {
		$index = $result['response']['docs'][0];
	}

	$resource = array(
			'index' => $index,
			'xml' => $xml,
			'gXPath' => $gXPath,
			'ro' => $cite_ro,
			'params' => '',
			'default_params' => ''
	);
	$citation_handler = new citations($resource);
	return $citation_handler->getEndnoteText();
}