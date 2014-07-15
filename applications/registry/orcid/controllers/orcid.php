<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
		$this->load->library('Orcid_api', 'orcid');
		if($access_token = $this->orcid_api->get_access_token()){
			$bio = $this->orcid_api->get_full();
			if(!$bio) redirect(registry_url('orcid'));
			$bio = json_decode($bio, true);
			$this->wiz($bio);
		}else{
			redirect(registry_url('orcid/login'));
		}
	}

	function login(){
		$data['title'] = 'Login to ORCID';
		$data['js_lib'] = array('core');
		$data['link'] = $this->config->item('gORCID_SERVICE_BASE_URI').'oauth/authorize?client_id='.$this->config->item('gORCID_CLIENT_ID').'&response_type=code&scope=/orcid-profile/read-limited /orcid-works/create&redirect_uri=';
		$data['link'].=registry_url('orcid/auth');
		$this->load->view('login_orcid', $data);
	}

	/**
	 * REDIRECT URI set to this method, process the user and provide the relevant view
	 * @return view 
	 */
	function auth(){
		$this->load->library('Orcid_api', 'orcid');
		if($this->input->get('code')){
			$code = $this->input->get('code');
			$data = json_decode($this->orcid_api->oauth($code),true);
			if(isset($data['access_token'])){
				$this->orcid_api->set_access_token($data['access_token']);
				$this->orcid_api->set_orcid_id($data['orcid']);
				$bio = $this->orcid_api->get_full();
				$bio = json_decode($bio, true);
				$orcid_id = $bio['orcid-profile']['orcid-identifier']['path'];
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
		$this->load->model('registry_object/registry_objects', 'ro');
		$xml = '';
		foreach($ro_ids as $id){
			$ro = $this->ro->getByID($id);
			if($ro){
				$xml .= $ro->transformToORCID();
			}
			unset($ro);
		}
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<orcid-message
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.orcid.org/ns/orcid https://github.com/ORCID/ORCID-Source/blob/master/orcid-model/src/main/resources/orcid-message-1.1.xsd"
    xmlns="http://www.orcid.org/ns/orcid">
<message-version>1.1</message-version>
<orcid-profile>
  <orcid-activities>
    <orcid-works> 
      '.$xml.'
    </orcid-works>
  </orcid-activities>
</orcid-profile>
</orcid-message>';
		$this->load->library('Orcid_api');
		$result = $this->orcid_api->append_works($xml);
		if($result==1){
			foreach($ro_ids as $id){
				$ro = $this->ro->getByID($id);
				$ro->setAttribute('imported_by_orcid', $this->orcid_api->get_orcid_id());
				$ro->save();
				unset($ro);
			}
		}
		echo $result;
	}

	/**
	 * returns the orcid xml
	 * @param array(ro_id)
	 * @return xml 
	 */
	function get_orcid_xml(){
		$ro_ids = $this->input->post('ro_ids');
		$this->load->model('registry_object/registry_objects', 'ro');
		$xml = '';
		foreach($ro_ids as $id){
			$ro = $this->ro->getByID($id);
			if($ro){
				$xml .= $ro->transformToORCID();
			}
		}
		
	echo '<?xml version="1.0" encoding="UTF-8"?>
<orcid-message
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.orcid.org/ns/orcid http://orcid.github.com/ORCID-Parent/schemas/orcid-message/1.0.7/orcid-message-1.0.7.xsd"
    xmlns="http://www.orcid.org/ns/orcid">
<message-version>1.0.7</message-version>
<orcid-profile>
  <orcid-activities>
    <orcid-works> 
      '.$xml.'
    </orcid-works>
  </orcid-activities>
</orcid-profile>
</orcid-message>';
// echo $msg;
	}

	/**
	 * Push an orcid xml to append to the orcid works profile
	 * @return true 
	 */
	function push_orcid_xml(){
		$this->load->library('Orcid_api');
		$xml = $this->input->post('xml');
		$result = $this->orcid_api->append_works($xml);
		echo $result;
	}

	/**
	 * [wiz description]
	 * @param  orcid_bio $bio
	 * @return view
	 */
	function wiz($bio) {
		$data['title'] = 'Import Your Work';

		//collect bio stuff
		$data['bio'] = $bio['orcid-profile'];
		$orcid_id = $data['bio']['orcid-identifier']['path'];

		//scripts
		$data['scripts'] = array('orcid_app');
		$data['js_lib'] = array('core','prettyprint', 'angular');

		$data['bio'] = $bio['orcid-profile'];
		$data['orcid_id'] = $data['bio']['orcid-identifier']['path'];
		$data['first_name'] = $data['bio']['orcid-bio']['personal-details']['given-names']['value'];
		$data['last_name'] = $data['bio']['orcid-bio']['personal-details']['family-name']['value'];

		$this->load->view('orcid_app', $data);
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

		//imported
		$imported = $this->ro->getByAttribute('imported_by_orcid', $data['orcid_id']);
		$imported_ids = array();
		if($imported && is_array($imported)){
			foreach ($imported as $i) {
				$result['works'][] = array(
					'type' => 'imported',
					'id'=>$i->id,
					'title'=>$i->title,
					'key'=>$i->key,
					'url'=>portal_url($i->slug),
					'imported'=>true,
					'in_orcid' => false
				);
				$imported_ids[] = $i->id;
			}
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

		$bio = json_decode($this->orcid_api->get_full(), true);
		if($bio && isset($bio['orcid-profile']['orcid-activities']['orcid-works']['orcid-work'])){
			$works = $bio['orcid-profile']['orcid-activities']['orcid-works']['orcid-work'];
			foreach($works as $w){
				$title = $w['work-title']['title']['value'];
				foreach($result['works'] as &$s) {
					if($title==$s['title']) {
						$s['in_orcid'] = true;
					}
				}
			}
		}

		echo json_encode($result);
	}

	/**
	 * The wizard?
	 * @return view 
	 */
	function wiz_dep($bio){

		// var_dump($bio);

		$data['bio'] = $bio['orcid-profile'];
		$data['title']='Import Your Work';
		$data['scripts']=array('orcid_wiz');
		$data['js_lib']=array('core','prettyprint', 'bootstro');

		$orcid_id = $data['bio']['orcid-identifier']['path'];
		$first_name = $data['bio']['orcid-bio']['personal-details']['given-names']['value'];
		$last_name = $data['bio']['orcid-bio']['personal-details']['family-name']['value'];
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

		$data['imported'] = $this->ro->getByAttribute('imported_by_orcid', $orcid_id);
		// echo sizeof($suggested_collections);
		
		$data['tip'] = 'The Suggested Datasets section will list any datasets from Research Data Australia, which are either directly related to your ORCID ID or are related to a researcher matching your surname.';

		$data['name'] = $name;
		$data['orcid_id'] = $orcid_id;
		$data['suggested_collections'] = $suggested_collections;
		$this->load->view('orcid_wiz', $data);
	}

	function imported($orcid_id){
		$this->load->model('registry_object/registry_objects', 'ro');
		$imported = $this->ro->getByAttribute('imported_by_orcid', $orcid_id);
		$im = array();
		if(sizeof($imported)>0){
			foreach($imported as $ro){
				array_push(
					$im, array(
						'id'=>$ro->registry_object_id,
						'title'=>$ro->title,
						'slug'=>$ro->slug
					)
				);
			}
		}
		$data = array();
		$data['imported'] = $im;

		if(sizeof($imported)==0) $data['no_result'] = "No collections have been imported";
		echo json_encode($data);
	}
}
	