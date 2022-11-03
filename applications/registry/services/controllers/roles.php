<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Roles Services controller
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 * @package ands/services/registry
 * 
 */
class Roles extends MX_Controller {

	//formatResponse is a helper function in engine/helper/presentation_function

	/**
	 * returns a list of data sources an organisational role has access to
	 * @return [type] [description]
	 */
	public function get_datasources(){
		$record_owner = $this->input->get('record_owner');
		$record_owner = rawurldecode($record_owner);
		if($record_owner){
			$this->load->model('data_source/data_sources', 'ds');
			$data_sources = $this->ds->getByAttribute('record_owner', $record_owner);
			if($data_sources){
				$response['status']='OK';
				$response['numFound']=sizeof($data_sources);
				$response['result'] = array();
				foreach($data_sources as $ds){
					$response['result'][] = array(
						'id'=>$ds->id,
						'key'=>$ds->key,
						'title'=>$ds->title,
						'registry_url'=>registry_url('data_source/manage#!/view/'.$ds->id)
					);
				}
			}else{
				$response['status']='OK';
				$response['numFound']=0;
				$response['message']='No data sources found';
			}
		}else{
			$response['status'] = 'WARNING';
			$response['message'] = 'Missing record_owner identifier';
		}
		formatResponse($response, 'json');
	}

	public function current_user() {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		// dd($this->session->sess_cookie_name());
		// dd($this->session->all_userdata());
		if ($this->user->isLoggedIn()) {
			$result = array(
				'role_id' => $this->user->localIdentifier(),
				'identifier' => $this->user->identifier(),
				'auth_domain' => $this->user->authDomain(),
				'auth_method' => $this->user->authMethod(),
				'affiliations' => $this->user->affiliations()
				// 'owned_ds' => $this->user->ownedDataSourceIDs()
			);
			echo json_encode(
				array(
					'status' => 'OK',
					'message' => $result
				)
			);
		} else {
			dd('not logged in');
		}
	}

	public function logout() {
		$redirect = $this->input->get('redirect') ? $this->input->get('redirect') : false;
	}
}	