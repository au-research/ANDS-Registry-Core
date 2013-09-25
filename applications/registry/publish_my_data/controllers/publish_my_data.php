<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//mod_enforce('mydois');

/**
 *  
 */
class Publish_my_data extends MX_Controller {

	function index(){
		if(!$this->user->loggedIn()){
			$this->user->redirectLogin();
		}else{
			if(sizeof($this->user->affiliations())==0){
				$data['title'] = 'Publish My Data';
				$data['scripts'] = array('pmd');
				$data['js_lib'] = array('core');
				// var_dump($this->user->localIdentifier());
				$this->load->view('pmd_create', $data);
			}else{
				//has affiliations, redirect
				redirect('registry_object/add');
			}
		}
	}

	function publish(){
		if($this->input->post()){
			$org_role = 'PMD_'.url_title($this->input->post('ds_title'));
			// echo 'creating org role: PMD_'. $this->input->post('name').'<br/>';
			$this->load->model($this->config->item('authentication_class'), 'role');
			$this->role->createOrganisationalRole($org_role, $this->user->localIdentifier(), $this->input->post('ds_title'));
			if(!$this->user->hasFunction('REGISTRY_USER') && sizeof($this->user->affiliations()==0)){
				$this->role->registerAffiliation($this->user->localIdentifier(), 'REGISTRY_USER');
				$this->role->registerAffiliation($this->user->localIdentifier(), $org_role);
			}else{
				throw Exception('an error has occured, please contact services@ands.org.au for support');
			}
			$this->user->refreshAffiliations($this->user->localIdentifier());
			$this->user->appendFunction(array('REGISTRY_USER'));
			// echo 'creating data source with title: '. $this->input->post('ds_title').' assign QA on and owner to the previous org role with the given notes<br/>';
			$this->load->model('data_source/data_sources', 'ds');
			$ds = $this->ds->create(url_title($this->input->post('ds_title')), url_title($this->input->post('ds_title')));
			$ds->setAttribute('title', $this->input->post('ds_title'));
			$ds->setAttribute('contact_name', $this->input->post('name'));
			$ds->setAttribute('contact_email', $this->input->post('email'));
			$ds->setAttribute('assessment_notify_email_addr', 'services@ands.org.au');
			$ds->setAttribute('record_owner', $org_role);
			$ds->setAttribute('qa_flag', DB_TRUE);
			if($this->input->post('notes')) $ds->setAttribute('notes', $this->input->post('notes'));		
			$ds->save();

			//send email
			$this->load->library('user_agent');
			$data['user_agent']=$this->agent->browser();
			$name = $this->input->post('name');
			$email = $this->input->post('email');
			$content = "A new Publish My Data Datasource has been added to the registry. ". base_url()."data_source#!/view/".$ds->id;
			$this->load->library('email');
			$this->email->from($email, $name);
			$this->email->to('services@ands.org.au');
			$this->email->subject('Registry Publish My Data');
			$this->email->message($content);
			$this->email->send();

			// echo 'create a new blank rifcs with data source belongs to this one';
			// echo '. redirect to edit that rifcs
			redirect('registry_object/add');
		}else{
			throw Exception('an error has occured, please contact services@ands.org.au for support.');
		}
	}
}
	