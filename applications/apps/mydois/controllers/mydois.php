<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//mod_enforce('mydois');

/**
 * Services controller
 * 
 * Abstract services controller allows for easy extension of the
 * services module and logging and access management of requests
 * via the API key system. 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @package ands/services
 * 
 */
class Mydois extends MX_Controller {

	function testing()
	{
		$authstr =  '313ba574c47f1cdd8f626942dd8b6509441f23a9:2959ce543222';
		$context  = array('Content-Type: application/xml;charset=UTF-8','Authorization: Basic '.base64_encode($authstr));
		//$context = array('Content-Type: application/xml;charset=UTF-8');		
		$requestURI = 'http://devl.ands.org.au/workareas/liz/ands/apps/mydois/mint/?url=sdfjds.sdfds.dsfdsf&app_id=313ba574c47f1cdd8f626942dd8b6509441f23a9';	

		$postdata = 'xml=<xml><identifier>10.5046/dghjfgg</identifier></xml>';
	
		$newch = curl_init();
		curl_setopt($newch, CURLOPT_URL, $requestURI);
		curl_setopt($newch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($newch, CURLOPT_POST, 1);
		curl_setopt($newch, CURLOPT_POSTFIELDS,$postdata);				
		curl_setopt($newch, CURLOPT_HTTPHEADER,$context);

		$result = curl_exec($newch);
		$curlinfo = curl_getinfo($newch);
		curl_close($newch);
		echo $result;

	}

	function index()
	{
		acl_enforce('DOI_USER');

		$data['js_lib'] = array('core');
		$data['title'] = 'My DOIs List';
		$data['associated_app_ids'] = array();

		if($this->user->loggedIn())
		{

			if (count($this->user->affiliations()))
			{
				$roles_db = $this->load->database('roles', TRUE);
				$roles_db->distinct()->select('parent_role_id')
						->where_in('child_role_id', $this->user->affiliations())
						->where('role_type_id', 'ROLE_DOI_APPID      ', 'after')
						->join('roles', 'role_id = parent_role_id')
						->from('role_relations');
				$query = $roles_db->get();

				if ($query->num_rows() > 0)
				{
					foreach ($query->result() AS $result)
					{
						$data['associated_app_ids'][] = $result->parent_role_id;
					}
				}
			}
            if(count($data['associated_app_ids'])===1){
                $this->show($data['associated_app_ids'][0]);
            }else{
			    $this->load->view('input_app_id', $data);
            }
		}else{
			$this->load->view('login_required', $data);
		}
	}

	function remove_trusted_client(){
		acl_enforce('SUPERUSER');
		$data['title'] = 'Remove Trusted Client';
		$client_id = $this->input->post('client_id');
		$response = $this->mydois->removeTrustedClient($client_id);
	}

	function list_trusted(){
		acl_enforce('SUPERUSER');
		$data['title'] = 'List Trusted Clients';
		$data['scripts'] = array('trusted_clients');
		$data['js_lib'] = array('core', 'dataTables');
		$data['all_app_id'] = $this->mydois->getAllDoiAppID();
		$data['datacite_prefixs'] = $this->mydois->buildPrefixOptions();
		$this->load->view('trusted_clients_index', $data);
	}

	function list_trusted_clients(){
		$trusted_clients = $this->mydois->getTrustedClients();
		echo json_encode($trusted_clients);
	}

	function add_trusted_client(){
		acl_enforce('SUPERUSER');
		$posted = $this->input->post('jsonData');
		$ip = trim(urlencode($posted['ip_address']));
		$client_name = trim(urlencode($posted['client_name']));
		$client_contact_name = trim(urlencode($posted['client_contact_name']));	
		$client_contact_email = trim(urlencode($posted['client_contact_email']));
		$domainList = trim(urlencode($posted['domainList']));
		$datacite_prefix = 	trim(urlencode($posted['datacite_prefix']));
		$shared_secret = trim(urlencode($posted['shared_secret']))	;			
		$response = $this->mydois->addTrustedClient($ip, $client_name, $client_contact_name, $client_contact_email, $domainList, $datacite_prefix, $shared_secret);
		echo json_encode($response);
	}

	function get_trusted_client(){
		acl_enforce('SUPERUSER');
		$client_id = $this->input->post('id');
		$response = $this->mydois->getTrustedClient($client_id);
		$response[0]['domain_list'] = $this->mydois->getTrustedClientDomains($client_id);
		echo json_encode($response);
	}

	function edit_trusted_client(){
		acl_enforce('SUPERUSER');
		$posted = $this->input->post('jsonData');
		$ip = trim(urlencode($posted['ip_address']));
		$client_id = trim(urlencode($posted['client_id']));
		$client_name = trim(urlencode($posted['client_name']));
		$client_contact_name = trim(urlencode($posted['client_contact_name']));	
		$client_contact_email = trim(urlencode($posted['client_contact_email']));
		$domainList = trim(urlencode($posted['domainList']));
		$datacite_prefix = 	trim(urlencode($posted['datacite_prefix']));
		$shared_secret = trim(urlencode($posted['shared_secret']))	;						
		$response = $this->mydois->editTrustedClient($ip, $client_id, $client_name, $client_contact_name, $client_contact_email, $domainList, $datacite_prefix, $shared_secret);
		echo json_encode($response);
	}

	function show($app_id=null)
	{
		acl_enforce('DOI_USER');

		$data['js_lib'] = array('core');
		$data['scripts'] = array('mydois','datacite_form');
		$data['title'] = 'DOI Query Tool';
		
		// Validate the appId
		$appId = $this->input->get_post('app_id');
		$doi_update = $this->input->get_post('doi_update');
		$error = $this->input->get_post('error');
		if (!$appId)
		{
			$appId = $this->input->get_post('app_id_select');
		}
        if (!$appId & isset($app_id)){
            $appId = $app_id;

        }
		$doiStatus = $this->input->get_post('doi_status');
		$data['doi_appids'] = $this->user->doiappids();
		if($doi_update)
		{
				$data['doi_update'] = $doi_update;	
		}
		if($error)
		{
				$data['error'] = $error;	
		}
		if (!$appId) throw new Exception ('Invalid App ID');  
		
		if(!in_array($appId, $data['doi_appids'] ))
		{
			throw new Exception ('You do not have authorisation to view dois associated with application id '.$appId);  
		}

		$doi_db = $this->load->database('dois', TRUE);
		
		$query = $doi_db->where('app_id',$appId)->select('*')->get('doi_client');
		if (!$client_obj = $query->result()) throw new Exception ('Invalid App ID');  
		$client_obj = array_pop($client_obj);
		
		// Store the recently used app id in the client cookie
		$this->input->set_cookie('last_used_doi_appid', $appId, 9999999);
		//087391e742ee920e4428aa6e4ca548b190138b89

		$query = $doi_db->order_by('updated_when', 'desc')->order_by('created_when', 'desc')->where('client_id',$client_obj->client_id)->where('status !=','REQUESTED')->select('*')->get('doi_objects');

		
		$data['dois'] = array();
		foreach ($query->result() AS $doi)
		{
			$data['dois'][] = $doi;
		}

		$query = $doi_db->order_by('timestamp', 'desc')->where('client_id',$client_obj->client_id)->select('*')->limit(50)->get('activity_log');
		$data['activities'] = $query->result();

		$query = $doi_db->where('client_id',$client_obj->client_id)->select('client_domain')->get('doi_client_domains');
		foreach ($query->result_array() AS $domain) {
			$client_obj->permitted_url_domains[] = $domain['client_domain'];
		}
        if($client_obj->client_id<10)
        {
            $doi_client_id = "0".$client_obj->client_id;
        }else{
            $doi_client_id = $client_obj->client_id;
        }
        $data['client_id'] = $client_obj->client_id;
        $data['doi_id'] = $client_obj->datacite_prefix.$doi_client_id."/".uniqid();
        $data['app_id'] = $appId;
		
		$data['client'] = $client_obj;
		$this->load->view('list_dois', $data);
	}

	function getActivityLog()
	{
		acl_enforce('DOI_USER');
		
		$doi_db = $this->load->database('dois', TRUE);
		
		// Validate the appId
		$appId = $this->input->get_post('app_id');
		if (!$appId) throw new Exception ('Invalid App ID');  
		
		$query = $doi_db->where('app_id',$appId)->select('*')->get('doi_client');
		if (!$client_obj = $query->result()) throw new Exception ('Invalid App ID');  
		$client_obj = array_pop($client_obj);
		
		$query = $doi_db->order_by('timestamp', 'desc')->where('client_id',$client_obj->client_id)->select('*')->limit(50)->get('activity_log');
		$this->load->view('view_activity_log',array("activities"=>$query->result()));
		

	}

	function runDoiLinkChecker()
	{
		header('Content-Type: application/json');
		acl_enforce('DOI_USER');
		$appId = $this->input->get_post('app_id');
		$doi_db = $this->load->database('dois', TRUE);
		if (!$appId) throw new Exception ('Invalid App ID'); 
		$query = $doi_db->where('app_id',$appId)->select('*')->get('doi_client');
		if (!$client_obj = $query->result()) throw new Exception ('Invalid App ID');  
		$client_obj = array_pop($client_obj);
		$client_id = $client_obj->client_id;
		$pythonBin = $this->config->item('PYTHON_BIN');
		$doiLinkCheckerScript = $this->config->item('DOI_LINK_CHECKER_SCRIPT');
		$command = escapeshellcmd($pythonBin.' '.$doiLinkCheckerScript.' -c '.$client_id);
		$result = shell_exec($command);
		$message = '<div>'.$result.'</div>';
		$message .=  '<p class="alert">An Email was sent to: ('.$client_obj->client_contact_email.') and an activity was logged containing the result.</p>';
		$data['status'] = 'SUCCESS';
		$data['message'] = $message;
		echo json_encode($data);
	}

	function getDoiXml()
	{
		acl_enforce('DOI_USER');
		
		$doi_db = $this->load->database('dois', TRUE);
		
		// Validate the doi_id
		$doi_id = rawurldecode($this->input->get_post('doi_id'));
		if (!$doi_id) throw new Exception ('Invalid DOI ID');  
		
		$query = $doi_db->where('doi_id',$doi_id)->select('doi_id, datacite_xml')->get('doi_objects');
		if (!$doi_obj = $query->result_array()) throw new Exception ('Invalid DOI ID');  
		$doi_obj = array_pop($doi_obj);

		$this->load->view('view_datacite_xml',$doi_obj);
		
	}
	
	function updateDoi()
	{
		acl_enforce('DOI_USER');
		
		$doi_db = $this->load->database('dois', TRUE);
		// Validate the doi_id
		$doi_id = rawurldecode($this->input->get_post('doi_id'));
        $appId = $this->input->get_post('app_id');
        if (!$appId) throw new Exception ('Invalid App ID');

		if (!$doi_id) throw new Exception ('Invalid DOI ID');  
		
		$query = $doi_db->where('doi_id',$doi_id)->select('doi_id, url,client_id, datacite_xml')->get('doi_objects');
		if (!$doi_obj = $query->result_array()) throw new Exception ('Invalid DOI ID');  
		$doi_obj = array_pop($doi_obj);
        $doi_obj['app_id'] = $appId;
		$this->load->view('update_doi',$doi_obj);
		
	}


	function getAppIDConfig()
	{
		acl_enforce('DOI_USER');
		
		$doi_db = $this->load->database('dois', TRUE);
		
		// Validate the appId
		$appId = $this->input->get_post('app_id');
		if (!$appId) throw new Exception ('Invalid App ID');  
		
		$query = $doi_db->where('app_id',$appId)->select('*')->get('doi_client');
		if (!$client_obj = $query->result_array()) throw new Exception ('Invalid App ID');  
		$client_obj = array_pop($client_obj);
		
		$query = $doi_db->where('client_id',$client_obj['client_id'])->select('client_domain')->get('doi_client_domains');
		foreach ($query->result_array() AS $domain)
		{
			$client_obj['permitted_url_domains'][] = $domain['client_domain'];
		}

		$this->load->view('view_app_id_config', $client_obj);
		
		
	}

    /*function manualMintForm(){

        acl_enforce('DOI_USER');
        $doi_db = $this->load->database('dois', TRUE);
        $appId = $this->input->get_post('app_id');
        if (!$appId) throw new Exception ('Invalid App ID');

        $query = $doi_db->where('app_id',$appId)->select('*')->get('doi_client');
        if (!$client_obj = $query->result()) throw new Exception ('Invalid App ID');
        $client_obj = array_pop($client_obj);
        $data['client_id'] = $client_obj->client_id;
        if($client_obj->client_id<10){
            $client_obj->client_id = "0".$client_obj->client_id;
        }
        $data['doi_id'] = $client_obj->datacite_prefix.$client_obj->client_id."/".uniqid();
        $data['app_id'] = $appId;
        $this->load->view('mint_doi',$data);
    } */

    function uploadFile(){
        if ( isset($_FILES['file']) ) {
            $filename = time().basename($_FILES['file']['name']);
            $error = true;

            $path = '/tmp/'.$filename;
            $error = move_uploaded_file($_FILES['file']['tmp_name'], $path);

            $rsp = array(
                'error' => $error, // Used in JS
                'filename' => $filename,
                'filepath' => '/tmp/' . $filename, // Web accessible
                'xml' =>file_get_contents($path),
            );
            unlink($path);
            echo json_encode($rsp);
            exit;
        }else{
            echo json_encode("File not uploaded");

        }
    }

  	function __construct(){
		acl_enforce('DOI_USER');
		$this->load->model('_mydois', 'mydois');
	}
		
}
	