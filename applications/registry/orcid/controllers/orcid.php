<?php use ANDS\Authenticator\ORCIDAuthenticator;
use ANDS\Registry\API\Request;
use ANDS\Registry\Providers\ORCID\ORCIDRecord;
use ANDS\Util\ORCIDAPI;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//mod_enforce('mydois');

/**
 * ORCID base controller for the orcid integration process
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au> 
 */
class Orcid extends MX_Controller {

	/**
	 * Base Method, requires the user to login
	 * @return view 
	 */
	function index(){
        session_start();
	    if (!ORCIDAuthenticator::isLoggedIn()) {
            redirect(registry_url('orcid/login'));
        }

        // is logged in, obtain bio and open the wizard
        $bio = ORCIDAPI::getBio();
        $this->wiz($bio);

	    return;
		$this->load->library('Orcid_api', 'orcid');
		if($access_token = $this->orcid_api->get_access_token()){
			$bio = $this->orcid_api->getORCIDRecord($this->orcid_api->get_orcid_id());
			if(!$bio) redirect(registry_url('orcid'));
			$bio = json_decode($bio['record_data'], true);
			$this->wiz($bio);
		}else{
			redirect(registry_url('orcid/login'));
		}
	}

	public function login() {
        session_start();
	    $data = [
	        'title' => 'Login to ORCID',
            'js_lib' => ['core'],
            'link' => ORCIDAuthenticator::getOauthLink(registry_url('orcid/auth'))
        ];

	    $this->load->view('login_orcid', $data);
	}

    /**
     * REDIRECT URI set to this method, process the user and provide the relevant view
     * @return view
     * @throws Exception
     */
	public function auth() {
        session_start();

	    // see if there's any error
        if (Request::get('error')) {
            throw new Exception(Request::get('error_description'));
        }

        if (!Request::get('code')) {
            throw new Exception("No valid code returned from ORCID");
        }

        if (ORCIDAuthenticator::isLoggedIn()) {
            $orcid = ORCIDAuthenticator::getSession();
            $this->wiz($orcid);
            return;
        }

        return;

		$this->load->library('Orcid_api', 'orcid');
		if($this->input->get('code')){
			$code = $this->input->get('code');
			$data = json_decode($this->orcid_api->oauth($code),true);
			if(isset($data['access_token'])){
				$this->orcid_api->set_orcid_id($data['orcid']);
				$this->orcid_api->set_access_token($data['access_token']);
				$this->orcid_api->set_refresh_token($data['refresh_token']);
				$bio = $this->orcid_api->get_full();
				$bio = json_decode($bio, true);
				$orcid_id = $bio['path'];
				$this->orcid_api->log($orcid_id);
				$this->wiz($bio);
			}else{

				if($access_token = $this->orcid_api->get_access_token()){
					// var_dump($this->orcid_api->get_orcid_id());
					$bio = $this->orcid_api->get_full();
					if(!$bio) redirect(registry_url('orcid'));
					$bio = json_decode($bio, true);
					$this->wiz($bio);
				}else{
					redirect(registry_url('orcid/login'));
				}
			}
		}else{
			if($access_token = $this->orcid_api->get_access_token()){
				$bio = $this->orcid_api->get_full();
				if(!$bio) redirect(registry_url('orcid'));
				$bio = json_decode($bio, true);
				$this->wiz($bio);
			}else{
				redirect(registry_url('orcid/login'));
			}
		}
	}

	function import_to_orcid(){
		set_exception_handler('json_exception_handler');
		$ro_ids = $this->input->post('ro_ids');
		if(!$ro_ids){
			$data = file_get_contents("php://input");
			$data = json_decode($data, true);
			$ro_ids = $data['ro_ids'];
		}

		$result = [];
		foreach($ro_ids as $id){
			$this->load->library('Orcid_api');
			$result[] = $this->orcid_api->append_work_by_ro_id($id);
		}
		print json_encode($result);
	}

    /**
     * [wiz description]
     * @param ORCIDRecord $orcid
     * @return view
     * @internal param orcid_bio $bio
     */
	function wiz(ORCIDRecord $orcid) {

	    $this->load->view('orcid_app', [
            'title' => 'Import Your Work',
            'scripts' => ['orcid_app'],
            'js_lib' => ['core','prettyprint', 'angular'],
            'orcid' => $orcid
        ]);
//
//		$data['title'] = 'Import Your Work';
//
//		//collect bio stuff
//
//		$data['bio'] = $bio;
//
//		//scripts
//		$data['scripts'] = array('orcid_app');
//		$data['js_lib'] = array('core','prettyprint', 'angular');
//		$data['orcid_id'] = $bio['orcid-identifier']['path'];
//		$data['first_name'] = $bio['person']['name']['given-names']['value'];
//		$data['last_name'] = $bio['person']['name']['family-name']['value'];
//
//		$this->load->view('orcid_app', $data);
	}

	function orcid_works() {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$data = file_get_contents("php://input");
		$data = json_decode($data, true);
		$data = $data['data'];
		set_exception_handler('json_exception_handler');

		//load relevant models and libraries
		$this->load->model('registry_object/registry_objects', 'ro');
		$this->load->library('solr');
		$this->load->library('Orcid_api', 'orcid');

		//suggested
		$result['suggested'] = array();
		$suggested_collections = array();

		$already_checked = array();

		//find parties of similar names
		$this->solr->setOpt('fq', '+class:party');
		$this->solr->setOpt('fq', '+title_search:('.$data['last_name'].')');
		$this->solr->executeSearch();
		if($this->solr->getNumFound() > 0){
			$result = $this->solr->getResult();
			foreach($result->{'docs'} as $d){
				if(!in_array($d->{'id'}, $already_checked)){
					$ro = $this->ro->getByID($d->{'id'});
					$connections = $ro->getConnections(true,'collection');
					if(isset($connections[0]['collection']) && sizeof($connections[0]['collection']) > 0) {
						$suggested_collections=array_merge($suggested_collections, $connections[0]['collection']);
					}
					array_push($already_checked, $d->{'id'});
					unset($ro);
				}
			}
		}

		//find parties that have the same orcid_id
		$this->solr->clearOpt('fq');
		$this->solr->setOpt('fq', '+class:party');
		$this->solr->setOpt('fq', '+identifier_value:('.$data['orcid_id'].')');
		$this->solr->executeSearch();
		if($this->solr->getNumFound() > 0){
			$result = $this->solr->getResult();
			foreach($result->{'docs'} as $d){
				if(!in_array($d->{'id'}, $already_checked)){
					$ro = $this->ro->getByID($d->{'id'});
					$connections = $ro->getConnections(true,'collection');
					if(isset($connections[0]['collection']) && sizeof($connections[0]['collection']) > 0) {
						$suggested_collections=array_merge($suggested_collections, $connections[0]['collection']);
					}
					array_push($already_checked, $d->{'id'});
					unset($ro);
				}
			}
		}

		//find collection that has a relatedInfo/citationInfo like the orcid_id
		$this->solr->clearOpt('fq');
		$this->solr->setOpt('fq', 'fulltext:('.$data['orcid_id'].')');
		$this->solr->setOpt('fq', 'class:(collection)');
		$this->solr->executeSearch();
		if($this->solr->getNumFound() > 0){
			$result = $this->solr->getResult();
			foreach($result->{'docs'} as $d){
				if(!in_array($d->{'id'}, $already_checked)){
					$new = array();
					array_push($new, array(
						'registry_object_id' => $d->{'id'},
						'title' => $d->{'display_title'},
						'key' => $d->{'key'},
						'slug' => $d->{'slug'}
					));
					$suggested_collections=array_merge($suggested_collections, $new);
					array_push($already_checked, $d->{'id'});
					unset($ro);
				}
			}
		}
		

		$result = array();
		
		initEloquent();
		$orcidRecord = ORCIDRecord::find($this->orcid_api->get_orcid_id());

		$orcidExports = $orcidRecord->exports;
		foreach($orcidExports as $oe){
			$oe->load('registryObject');
			$result['works'][] = array(
				'type' => 'imported',
				'id'=>$oe->registryObject->registry_object_id,
				'title'=>$oe->registryObject->title,
				'key'=>$oe->registryObject->key,
				'url'=>portal_url($oe->registryObject->slug),
				'put_code'=>$oe->put_code,
				'date_created'=>$oe->date_created,
				'date_updated'=>$oe->date_updated,
				'response'=>$oe->repsonse,
				'imported'=>true,
				'in_orcid' => true
				);
			$imported_ids[] = $oe->registryObject->registry_object_id;
		}
		
		//suggested
		foreach($suggested_collections as $s) {
			$result['works'][] = array(
				'type' => 'suggested',
				'id'=>$s['registry_object_id'],
				'title'=>$s['title'],
				'key'=>$s['key'],
				'url'=>portal_url($s['slug']),
				'imported'=> in_array($s['registry_object_id'], $imported_ids),
				'in_orcid' => false
			);
		}

//		$bio = json_decode($this->orcid_api->get_full(), true);
//		if($bio && isset($bio['orcid-profile']['orcid-activities']['orcid-works']['orcid-work'])){
//			$works = $bio['orcid-profile']['orcid-activities']['orcid-works']['orcid-work'];
//			foreach($works as $w){
//				$title = $w['work-title']['title']['value'];
//				foreach($result['works'] as &$s) {
//					if($title==$s['title']) {
//						$s['in_orcid'] = true;
//					}
//				}
//			}
//		}

		echo json_encode($result);
	}

	/**
	 * The wizard?
	 * @return view 
	 */
	function wiz_dep($bio){

		// var_dump($bio);

		$data['bio'] = $bio;
		$data['title']='Import Your Work';
		$data['scripts']=array('orcid_wiz');
		$data['js_lib']=array('core','prettyprint', 'bootstro');

		$orcid_id = $bio['path'];
		$first_name = $bio['person']['name']['given-names']['value'];
		$last_name = $bio['personal']['name']['family-name']['value'];
		$name = $first_name.' '.$last_name;

		$suggested_collections = array();

		$this->load->model('registry_object/registry_objects', 'ro');
		$this->load->library('solr');

		//find parties of similar names
		$this->solr->setOpt('fq', '+class:party');
		$this->solr->setOpt('fq', '+display_title:('.$last_name.')');
		$this->solr->executeSearch();

		$already_checked = array();

		if($this->solr->getNumFound() > 0){
			$result = $this->solr->getResult();
			// echo json_encode($result);
			foreach($result->{'docs'} as $d){
				if(!in_array($d->{'id'}, $already_checked)){
					$ro = $this->ro->getByID($d->{'id'});
					$connections = $ro->getConnections(true,'collection');
					// var_dump($connections[0]['collection']);
					if(isset($connections[0]['collection']) && sizeof($connections[0]['collection']) > 0) {
						$suggested_collections=array_merge($suggested_collections, $connections[0]['collection']);
					}
					array_push($already_checked, $d->{'id'});
					unset($ro);
				}
			}
		}

		//find parties that have the same orcid_id
		$this->solr->clearOpt('fq');
		$this->solr->setOpt('fq', '+class:party');
		$this->solr->setOpt('fq', '+identifier_value:("'.$orcid_id.'")');
		$this->solr->executeSearch();
		if($this->solr->getNumFound() > 0){
			$result = $this->solr->getResult();
			foreach($result->{'docs'} as $d){
				if(!in_array($d->{'id'}, $already_checked)){
					$ro = $this->ro->getByID($d->{'id'});
					$ro = $this->ro->getByID($d->{'id'});
					$connections = $ro->getConnections(true,'collection');
					if(isset($connections[0]['collection']) && sizeof($connections[0]['collection']) > 0) {
						$suggested_collections=array_merge($suggested_collections, $connections[0]['collection']);
					}
					array_push($already_checked, $d->{'id'});
					unset($ro);
				}
			}
		}

		//find collection that has a relatedInfo/identifier like the orcid_id
		$relatedByRelatedInfoIdentifier = $this->ro->getByRelatedInfoIdentifier($orcid_id);
		if ( is_array($relatedByRelatedInfoIdentifier) && sizeof($relatedByRelatedInfoIdentifier) > 0 ) {
			foreach ( $relatedByRelatedInfoIdentifier as $ro_id) {
				if( !in_array($ro_id, $already_checked) ) {
					array_push($already_checked, $ro_id);
				}
			}
		}

		initEloquent();
		$orcidRecord = ORCIDRecord::find($this->orcid_api->get_orcid_id());
		$orcidExports = $orcidRecord->exports;
		foreach($orcidExports as $oe){
			$oe->load('registryObject');
			$data['imported'][] = array(
				'type' => 'imported',
				'id'=>$oe->registryObject->registry_object_id,
				'title'=>$oe->registryObject->title,
				'key'=>$oe->registryObject->key,
				'put_code'=>$oe->put_code,
				'date_created'=>$oe->date_created,
				'date_updated'=>$oe->date_updated,
				'response'=>$oe->repsonse,
				'url'=>portal_url($oe->registryObject->slug),
				'imported'=>true,
				'in_orcid' => false
			);
		}

		// echo sizeof($suggested_collections);
		
		$data['tip'] = 'The Suggested Datasets section will list any datasets from Research Data Australia, which are either directly related to your ORCID ID or are related to a researcher matching your surname.';

		$data['name'] = $name;
		$data['orcid_id'] = $orcid_id;
		$data['suggested_collections'] = $suggested_collections;
		$this->load->view('orcid_wiz', $data);
	}

	
	function imported($orcid_id){
		initEloquent();
		$orcidRecord = ORCIDRecord::find($orcid_id);
		$orcidExports = $orcidRecord->exports;
		$im = array();
		foreach($orcidExports as $oe){
			$oe->load('registryObject');
			array_push(
				$im, array(
					'id'=>$oe->registryObject->registry_object_id,
					'title'=>$oe->registryObject->title,
					'slug'=>$oe->registryObject->slug,
					'put_code'=>$oe->put_code,
					'date_created'=>$oe->date_created,
					'date_updated'=>$oe->date_updated,
					'response'=>$oe->repsonse
				)
			);
		}
		$data = array();
		$data['imported'] = $im;
		if(sizeof($orcidExports)==0) $data['no_result'] = "No collections have been imported";
		echo json_encode($data);
	}
	
	function loadrecord($identifier){
		$this->load->library('Orcid_api', 'orcid');
		$or = $this->orcid_api->getORCIDRecord($identifier);
		return $or;
	}
}
	