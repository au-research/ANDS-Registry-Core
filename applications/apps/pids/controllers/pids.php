<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//mod_enforce('mydois');

/**
 *  PIDs primary controller
 *  @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Pids extends MX_Controller {

	/**
	 * Default function for pids, list all pids
	 * @return view 
	 */
	function index(){
		$data['title'] = 'My Identifiers';
		$data['scripts'] = array('pids');
		$data['js_lib'] = array('core');


		$data['orgRole'] = $this->user->affiliations();
        $data['registry_super_user'] = $this->user->isSuperAdmin();
        $data['batch_pid_files'] = $this->pids->getBatchPidsCSVforIdentifier();
		array_unshift($data['orgRole'], 'My Identifiers');


		$data['identifier'] = $this->input->get('identifier');
		$this->load->view('pids_index', $data);
	}

	public function view(){
		$handle = $this->input->get('handle');
		if($handle){
			$handle = $this->pids->getHandlesDetails(array($handle));
			$pid = array();
			foreach($handle as $h){
				$pid['handle'] = $h['handle'];

				if($h['type']=='DESC') {
					$pid['desc'][$h['idx']] = $h['data'];
					//$pid['desc_index'] = $h['idx'];
				}
				if($h['type']=='URL') {
					$pid['url'][$h['idx']] = $h['data'];
					//$pid['url_index'] = $h['idx'];
				}
			}
			if(!isset($pid['desc'])) $pid['desc'] = array();
			if(!isset($pid['url'])) $pid['url'] = array();
			$data['pid'] = $pid;
			$data['pid_owners'] = $this->pids->getPidOwners();
			$data['title'] = 'View Handle: '.$pid['handle'];
			$data['resolver_url'] = $this->pids->pidsGetHandleURI($pid['handle']);
			$data['scripts'] = array('pid');
			$data['js_lib'] = array('core');
			$this->load->view('pid_view', $data);
		}else{
			$this->index();
		}
	}

	function list_trusted(){
		acl_enforce('SUPERUSER');
		$data['title'] = 'List Trusted Clients';
		$data['scripts'] = array('trusted_clients');
		$data['js_lib'] = array('core', 'dataTables');
		$data['all_app_id'] = $this->pids->getAllAppID();
		$this->load->view('trusted_clients_index', $data);
	}

	function list_trusted_clients(){
		$trusted_clients = $this->pids->getTrustedClients();
		echo json_encode($trusted_clients);
	}

	function add_trusted_client(){
		acl_enforce('SUPERUSER');
		$posted = $this->input->post('jsonData');
		$ip = trim(urlencode($posted['ip']));
		$desc = trim(urlencode($posted['desc']));
		$appId = trim(urlencode($posted['app_id']));
		$response = $this->pids->addTrustedClient($ip, $desc, $appId);
		echo json_encode($response);
	}

	function remove_trusted_client(){
		acl_enforce('SUPERUSER');
		$ip = $this->input->post('ip');
		$appId = $this->input->post('app_id');
		$response = $this->pids->removeTrustedClient($ip, $appId);
		echo json_encode($response);
	}

	function mint(){

		$url = urlencode($this->input->post('url'));
		$desc = urlencode($this->input->post('desc'));

		if($url && $desc){
			//do desc -> update with url
			$response = $this->pids->pidsRequest('mint', 'type=DESC&value='.$desc);
			if($this->pids->pidsGetResponseType($response) == 'SUCCESS'){
				$responseArray['handle'] = $this->pids->pidsGetHandleValue($response);
				$updateResponse = $this->pids->pidsRequest('addValue', 'type=URL&value='.$url.'&handle='.$responseArray['handle']);
				if($this->pids->pidsGetResponseType($updateResponse) != 'SUCCESS'){
					$this->handleResponse($updateResponse);
				}
				$this->handleResponse($response);
			}else{
				$this->handleResponse($response);
			}
		}else if($url){
			//do url only
			$response = $this->pids->pidsRequest('mint', 'type=URL&value='.$url);
			$this->handleResponse($response);
		}else if($desc){
			//do desc
			$response = $this->pids->pidsRequest('mint', 'type=DESC&value='.$desc);
			$this->handleResponse($response);
		}else{
			$responseArray['result']='error';
			$responseArray['message']='Either URL or DESC must be specified';
			echo json_encode($responseArray);
		}
	}

    function upload_csv()
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        set_exception_handler('json_exception_handler');
        $fileName = preg_replace('-\W-','_',$this->pids->getFilePrefixForCurrentIdentifier())."_".date('Y-m-d_H_i_s').'_upload';
        $upload_path = './assets/uploads/pids/';
        $config['upload_path'] = $upload_path;
        $config['file_name'] = $fileName;
        $config['allowed_types'] = 'csv|text';
        $config['overwrite'] = true;
        $config['max_size']	= '4000';
        $this->load->library('upload', $config);
        if(!$this->upload->do_upload('file')) {
            echo json_encode(
                array(
                    'status'=>'ERROR',
                    'error' => $this->upload->display_errors('','')
                )
            );
        } else {
            $result = $this->pids->processUploadedFile($upload_path, $fileName);
            $file = fopen($upload_path.$fileName.'.log','x+');
            fwrite($file, $result['message'].NL.$result['log']);
            fclose($file);
            echo json_encode(array('status'=> $result['status'], 'message' => $result['message'], 'log_file' => $fileName.'.log'));
        }
    }

    function batch_mint(){
        acl_enforce('SUPERUSER');
        set_exception_handler('json_exception_handler');
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        $counter = $this->input->post('counter');
        $url = urlencode($this->input->post('url'));
        $desc = urlencode($this->input->post('desc'));
        if($counter && ($desc || $url)){
            $counter = intval($counter);
            if($counter > 100){
                $responseArray['result']='error';
                $responseArray['error']='no more than 100 please!!';
                echo json_encode($responseArray);
            }
            elseif($counter <= 0){
                $responseArray['result']='error';
                $responseArray['error']='positive integer please!!';
                echo json_encode($responseArray);
            }
            else{
                $responseArray['result']='success';
                $upload_path = './assets/uploads/pids/';
                $fileName = preg_replace('-\W-','_',$this->pids->getFilePrefixForCurrentIdentifier())."_".date('Y-m-d_H_i_s')."_batch_mint";
                $responseArray['csv_file_path'] = $upload_path.$fileName.'.csv';
                $file = fopen($upload_path.$fileName.'.csv','x+');
                $responseArray['file'] = $fileName.'.csv';
                $responseArray['file_path'] = asset_url('uploads/pids/'.$fileName.'.csv', 'base');
                fputcsv($file,  array("NUMBER",'HANDLE','DESC','URL'), ',', '"');
                for($i = 1 ; $i <= $counter; $i++ ){
                    if($url && $desc){
                        //do desc -> update with url
                        $response = $this->pids->pidsRequest('mint', 'type=DESC&value='.$desc);
                        if($this->pids->pidsGetResponseType($response) == 'SUCCESS'){
                            $handle = $this->pids->pidsGetHandleValue($response);
                            $responseArray[$i]['handle'] = $handle;
                            if(preg_match("/^https?:\/\/.*/",urldecode($url))){
                                $updateResponse = $this->pids->pidsRequest('addValue', 'type=URL&value='.$url.'&handle='.urlencode($handle));
                                if($this->pids->pidsGetResponseType($updateResponse) != 'SUCCESS'){
                                    $responseArray['result']='error';
                                    $responseArray['error'] =  $this->pids->pidsGetUserMessage($response);
                                    $responseArray[$i]['error'] =  $this->pids->pidsGetUserMessage($response);
                                }
                                else{
                                    fputcsv($file, array($i, $responseArray[$i]['handle'],urldecode($desc),urldecode($url)), ',',  '"');
                                    $responseArray[$i]['message'] =  $this->pids->pidsGetUserMessage($response);
                                }
                            }
                            else{
                                fputcsv($file, array($i, $responseArray[$i]['handle'],urldecode($desc),urldecode($url)), ',',  '"');
                                $responseArray[$i]['message'] =  $this->pids->pidsGetUserMessage($response);
                            }
                        }else{
                            $responseArray['result']='error';
                            $responseArray['error'] =  $this->pids->pidsGetUserMessage($response);
                            $responseArray[$i]['error'] =  $this->pids->pidsGetUserMessage($response);
                        }
                    }
                    else if($url){
                        //do url only
                        $response = $this->pids->pidsRequest('mint', 'type=URL&value='.$url);
                        if($this->pids->pidsGetResponseType($response) == 'SUCCESS'){
                            $responseArray[$i]['handle'] = $this->pids->pidsGetHandleValue($response);
                            fputcsv($file, array($i, $responseArray[$i]['handle'],urldecode($desc),urldecode($url)), ',',  '"');
                            $responseArray[$i]['message'] =  $this->pids->pidsGetUserMessage($response);
                        }else{
                            $responseArray['result']='error';
                            $responseArray['error'] =  $this->pids->pidsGetUserMessage($response);
                            $responseArray[$i]['error'] =  $this->pids->pidsGetUserMessage($response);
                        }
                    }
                    else if($desc){
                        //do desc
                        $response = $this->pids->pidsRequest('mint', 'type=DESC&value='.$desc);
                        if($this->pids->pidsGetResponseType($response) == 'SUCCESS'){
                            $responseArray[$i]['handle'] = $this->pids->pidsGetHandleValue($response);
                            fputcsv($file, array($i, $responseArray[$i]['handle'],urldecode($desc),urldecode($url)), ',',  '"');
                            $responseArray[$i]['message'] =  $this->pids->pidsGetUserMessage($response);
                        }else{
                            $responseArray['result']='error';
                            $responseArray['error'] =  $this->pids->pidsGetUserMessage($response);
                            $responseArray[$i]['error'] =  $this->pids->pidsGetUserMessage($response);
                        }
                    }
                }
                fclose($file);
                echo json_encode($responseArray);
            }
        }else{
            $responseArray['result']='error';
            $responseArray['error']='Both counter and description must be specified';
            echo json_encode($responseArray);
        }

    }
	/**
	 * Webservice for updating a single handle
	 * @return json response 
	 */
	function update_handle(){
		$index = $this->input->post('idx');
		$type = strtoupper($this->input->post('type'));
		$value = $this->input->post('value');
		$handle = $this->input->post('handle');
		$response = array();
		if($index > 0 && $value!=''){
            $message = $this->pids->modify_value_by_index($handle, $value, $index);
        }else if($index < 0 && $value!=''){
            $message = $this->pids->pidsRequest('addValue', 'type='.$type.'&value='.urlencode($value).'&handle='.urlencode($handle));
        }else{
            $message = $this->pids->delete_value_by_index($handle, $index);
        }
		$response['result'] = $this->pids->pidsGetResponseType($message);
		$response['message'] = $this->pids->pidsGetUserMessage($message);
		echo json_encode($response);
	}

	function update_ownership()
	{
		$post = $this->input->post('jsonData');
		$response = $this->pids->setOwnerHandle($post['current'],$post['reassign']);
		echo json_encode($response);
	}

	function get_pid_owners()
	{
		$response = $this->pids->getPidOwners();
		echo json_encode($response);
	}

	function handleResponse($response){
		$responseArray = array();
		if($response){
			if($this->pids->pidsGetResponseType($response) == 'SUCCESS'){
				$responseArray['handle'] = $this->pids->pidsGetHandleValue($response);
			}else{
				$responseArray['error'] = $this->pids->pidsGetUserMessage($response);
			}
		}else{	
			$responseArray['error'] = 'There was an error communicating with the pids service.';
		}
		echo json_encode($responseArray);
	}

	/**
	 * list all pids web service for the pids dashboard
	 * @return json 
	 */
	function list_pids(){
		$handles = array();
		$pidsDetails = array();
		$response = array();
		
		$params = $this->input->post('params');
		$offset = (isset($params['offset'])? $params['offset']: 0);
		$limit = (isset($params['limit'])? $params['limit']: 10);
		$searchText = (isset($params['searchText'])? $params['searchText']: null);
		$authDomain = (isset($params['authDomain'])? $params['authDomain']: $this->user->authDomain());
		$identifier = (isset($params['identifier'])? $params['identifier']: $this->user->localIdentifier());

		$ownerHandle = $this->pids->getOwnerHandle($identifier,$authDomain);

		if($ownerHandle)
		{
			$handles = $this->pids->getHandles($ownerHandle, $searchText);
			$response['result_count'] = sizeof($handles);
			$response['owner_handle'] = $ownerHandle;
			if($response['result_count'] > 0){
				$result = $this->pids->getHandlesDetails(array_slice($handles, $offset, $limit));
				foreach($result as $r)
				{
					$pidsDetails[$r['handle']]['resolver_url'] = $this->pids->pidsGetHandleURI($r['handle']);
					$pidsDetails[$r['handle']]['handle'] = $r['handle'];
					if($r['type'] == 'DESC' || $r['type'] == 'URL')
					{
						// $pidsDetails[] = array(
						// 	'handle'=>$r['handle'],
						// 	$r['type']=>$r['data']
						// );
						if($r['type']=='DESC') $pidsDetails[$r['handle']]['hasDESC'] = true;
						if($r['type']=='URL') $pidsDetails[$r['handle']]['hasURL'] = true;
						//$pidsDetails[$r['handle']]['handle'] = $r['handle'];
						// $pidsDetails[$r['handle']][$r['type']] = array($r['idx']=>$r['data']);
						$pidsDetails[$r['handle']][$r['type']][] = $r['data'];

					}
				}
				$result = array();
				foreach($pidsDetails as $r){
					array_push($result, $r);
				}

				$response['pids'] = $result;
			}else{
				$response['no_result'] = true;
			}
		}

		if(isset($response['result_count'])  && ($offset + $limit) < $response['result_count']){
			$response['hasMore'] = true;
			$response['next_offset'] = $offset + $limit;
		}
		if($searchText) $response['search_query'] = $searchText;
		echo json_encode($response);
	}


    function my_pids(){


        $pidsDetails = array();
        $params = $this->input->post('params');
        $searchText = (isset($params['searchText'])? $params['searchText']: null);
        $authDomain = (isset($params['authDomain'])? $params['authDomain']: $this->user->authDomain());
        $identifier = (isset($params['identifier'])? $params['identifier']: $this->user->localIdentifier());
        $fileName = preg_replace('-\W-','_',$this->pids->getFilePrefixForCurrentIdentifier())."_".date('Y-m-d')."_all_pids.csv";
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/csv');
        header("Content-Disposition: attachment; filename={$fileName}");
        header("Pragma: no-cache");
        header("Expires: 0");

        $ownerHandle = $this->pids->getOwnerHandle($identifier,$authDomain);

        if($ownerHandle)
        {
            $handles = $this->pids->getHandles($ownerHandle, $searchText);
            if(sizeof($handles) > 0){
                $result = $this->pids->getHandlesDetails($handles);
                foreach($result as $r)
                {
                    $pidsDetails[$r['handle']]['HANDLE'] = $r['handle'];
                    if($r['type'] == 'DESC')
                    {
                        $pidsDetails[$r['handle']]['DESC']  = $r['data'];
                    }
                    if($r['type'] == 'URL')
                    {
                        $pidsDetails[$r['handle']]['URL']  = $r['data'];

                    }
                }
            }
        }
        echo "COUNTER,HANDLE,DESC,URL".NL;
        $i = 0;
        foreach($pidsDetails as $r) {
        	$desc = isset($r['DESC']) ? $r['DESC'] : '';
        	$url = isset($r['URL']) ? $r['URL'] : '';
            echo ++$i.','.$r['HANDLE'].',"'.$desc.'",'.$url.NL;
        }
    }

	function get_handler($handler)
	{
		$serviceName = "getHandle";
		$parameters = "handle=".urlencode($handler);
		$response = $this->pids->pidsRequest($serviceName, $parameters);
		echo $response;
	}

	function __construct(){
		acl_enforce('PIDS_USER');
		$this->load->model('_pids', 'pids');
	}
	//function updateBy

}
	