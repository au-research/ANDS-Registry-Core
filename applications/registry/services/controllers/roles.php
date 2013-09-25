<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Roles Services controller
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
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
}	