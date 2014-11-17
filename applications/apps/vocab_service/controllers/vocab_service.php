<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Core Vocab controller
 * 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */
class Vocab_service extends MX_Controller {

	/**
	 * Manage My Vocabs
	 * 
	 * 
	 * @author Liz Woods <liz.woods@ands.org.au>
	 * @param 
	 * @todo everything :)
	 * @return [HTML] output
	 */
	public function index(){
		$this->load->database('vocabs');
		$data['title'] = 'Browse Vocabularies';
		$data['small_title'] = '';

		$this->load->model("vocab_services","vocab");
		$vocabs = $this->vocab->getAll(0,0);//get everything

		//if logged in
		if($this->user->loggedIn()){
			//$data['my_vocabs'] = $this->vocab->getOwnedVocabs(false);
			$data['group_vocabs'] = $this->vocab->getGroupVocabs();
		}else{
			$data['group_vocabs'] = array();
		}

		$items = array();
		foreach($vocabs as $vocab){
			$item = array();
			$item['title'] = $vocab->title;
			$item['id'] = $vocab->id;
			$item['description'] = $vocab->description;
			array_push($items, $item);
		}
		$data['vocabs'] = $items;
		$data['scripts'] = array('vocab_services');
		$data['js_lib'] = array('core');
		$this->load->view("vocab_service_index", $data);
	}

	/**
	 * Same as index
	 */
	public function manage(){
		$this->index();
	}

	public function publish(){
		$data['title']='Publish on ANDS Vocabularies Services';
		$data['js_lib'] = array('core');
		$this->load->view("publish", $data);
	}

	public function addVocabulary(){
		
		if(sizeof($this->user->affiliations()>0)){
			$hasOrg = true;
		}else{
			$hasOrg = false;
		}

		if($this->user->loggedIn() && $hasOrg){
			redirect('vocab_service/#!/add');
		}else{
			$data['title']='Publish on ANDS Vocabularies Services';
			$data['js_lib'] = array('core');
			$this->load->view("publish", $data);
		}
		
	}

	public function support($action = 'form'){
		if($action=='form'){
			$data['title']='Support ANDS Vocabularies Services';
			$data['js_lib'] = array('core');
			$this->load->view("support", $data);
		}else if($action=='submit'){
			$email = $this->input->get('from_email');
			$title = $this->input->get('from_title');
			$message = $this->input->get('message');
			$to_email = $this->config->item('vocab_admin_email');
			//sending email
			$this->load->library('email');
			$this->email->from($email, $title);
			$this->email->to($to_email); //$publisher_email
			$this->email->subject('Message from the ANDS Vocabulary Discovery Portal');
			$this->email->message($message);
			$this->email->send();
			$data['title']='Support ANDS Vocabularies Services';
			$data['success'] = 'Your message has been successfully sent';
			$data['js_lib'] = array('core');
			$this->load->view("support", $data);
		}
	}

	public function about(){
		$data['title']='About ANDS Vocabularies Services';
		$data['js_lib'] = array('core');
		$this->load->view("about", $data);
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
	public function getVocabs($page=1){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';


		$this->load->model("vocab_services","vocab");


		//get owned vocabs permission
		$ownedVocabsID = array();
		if($this->user->loggedIn()){
			$ownedVocabs = $this->vocab->getAllOwnedVocabs();
			foreach($ownedVocabs as $v){
				array_push($ownedVocabsID, $v->id);
			}
		}

		//Limit and Offset calculated based on the page
		$limit = 9;
		$offset = ($page-1) * $limit;

		$vocabs = $this->vocab->getAllPublished($limit, $offset);

		if(sizeof($vocabs)<$limit){
			$jsonData['more'] = false;
		}else $jsonData['more'] = true;
		$items = array();
		foreach($vocabs as $vocab){
			$item = array();
			$item['title'] = $vocab->title;
			$item['id'] = $vocab->id;
			$item['description'] = $vocab->description;
			$item['counts'] = array();
			
			if(in_array($item['id'], $ownedVocabsID)){
				$item['owned'] = true;
			}

			array_push($items, $item);
		}
		
		$jsonData['items'] = $items;
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}

	/**
	 * Get a single vocab
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [INT] vocab ID
	 * @todo ACL on which vocab you have access to, error handling
	 * @return [JSON] of a single vocab
	 */
	public function getVocab($id, $view='view'){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';

		$this->load->model("vocab_services","vocab");

		$vocab= $this->vocab->getByID($id);


		if($vocab)
		{
			$jsonData['item']['view'] = $view;
			
			//get owned vocabs permission
			$ownedVocabsID = array();
			if($this->user->loggedIn()){
				$ownedVocabs = $this->vocab->getAllOwnedVocabs(true);
				foreach($ownedVocabs as $v){
					array_push($ownedVocabsID, $v->id);
				}
			}

			foreach($vocab->attributes as $attrib=>$value){
				$jsonData['item'][$attrib] = $value->value;
			}

			if($vocab->contact_name || $vocab->contact_number) $jsonData['item']['contact']=true;
			if(in_array($vocab->id, $ownedVocabsID)){
				$jsonData['item']['owned']=true;
				if($view=='edit'){
					$jsonData['item']['editable'] = true;
				}
			}else{//not owned
				if($view=='edit'){
					$jsonData['status']='ERROR';
					$jsonData['message']='Access Denied. You are not allowed to edit this vocabulary';
				}
			}

			//vocab versions
			$versions= $this->vocab->getVersionsByID($id);
			$items = array();
			$currentVersion ='';
			if($versions)
			{
				$jsonData['item']['hasVersions']=true;
				foreach($versions as $version){
					$item=array();
					$item['status']=$version->status;
					$item['title']=$version->title;
					$item['id']=$version->id;
					array_push($items, $item);
				}
				$jsonData['item']['versions']=$items;
			}else{
				$jsonData['item']['noVersions']=true;
			}

			//vocab formats
			$formats = $this->vocab->getAvailableFormatsByID($id);
			unset($items);
			$items = array();
			if($formats){
				$jsonData['item']['hasFormats']=true;
				foreach($formats as $m){
					array_push($items, $m->format);
				}
				$jsonData['item']['available_formats']=$items;
			}else{
				$jsonData['item']['noFormats']=true;
			}

			//vocab changes
			$changes= $this->vocab->getChangesByID($id);
			unset($items);
			$items = array();
			if($changes)
			{
				$jsonData['item']['hasChanges']=true;
				foreach($changes as $change){
					$item = array();
					$item['change_date'] = $change->change_date;
					$item['change_id'] = $change->id;
					$item['change_description'] = $change->description;
					array_push($items, $item);
				}
			}else{
				$jsonData['item']['noChanges']=true;
			}
			$jsonData['item']['changes']=$items;
		}else{
			$jsonData['status'] = 'ERROR';
			$jsonData['message'] = 'Non Existing Vocab Specified';
		}

		
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}

	public function getDownloadableByFormat($vocab_id, $format){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';
		$this->load->model("vocab_services","vocab");
		$downloadables = $this->vocab->getDownloadableByFormat($vocab_id, $format);
		$items = array();
		if($downloadables){
			$jsonData['hasItems']=true;
			foreach($downloadables as $d){
				$item = array();
				$item['title']=$d->title;
				$item['format']=$d->format;
				$item['type']=$d->type;
				$item['value']=$d->value;
				$item['version_id']=$d->version_id;
				$item['status']=$d->status;
				$item['id']=$d->id;
				$version = $this->vocab->getVersionByID($d->version_id);

				if($d->type=='file'){
					$item['tip']='Download this file';
				}else if($d->type=='uri'){
					$item['tip']='Open this URI';
				}

				$item['version_name']=$version->title;
				array_push($items, $item);
			}
			$jsonData['items']=$items;
		}else{
			$jsonData['noItems']=true;
			$jsonData['requestFor']=$vocab_id .' and '.$format;

		}
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}

	public function getFormatByVersion($version_id, $view='view'){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';
		$this->load->model("vocab_services","vocab");
		$formats = $this->vocab->getFormatByVersion($version_id);
		$version = $this->vocab->getVersionByID($version_id);
		$items = array();
		if($formats){
			$jsonData['hasItems']=true;
			foreach($formats as $f){
				$item = array();
				$item['id']=$f->id;
				$item['type']=$f->type;
				$item['value']=$f->value;
				$item['format']=$f->format;
				$item['version_name']=$version->title;
				if($f->type=='file'){
					$item['tip']='Download this file';
				}else if($f->type=='uri'){
					$item['tip']='Open this URI';
				}
				array_push($items, $item);
			}
			$jsonData['items']=$items;
		}else{
			$jsonData['noItems']=true;
			$jsonData['requestFor']='version_id = '. $version_id;
		}

		//get owned vocabs permission
		$version = $this->vocab->getVersionByID($version_id);
		$jsonData['id']=$version->id;
		$jsonData['title']=$version->title;
		$jsonData['vocab_id']=$version->vocab_id;
		if($version->status=='current'){
			$jsonData['current']=true;
		}else{
			$jsonData['notCurrent']=true;
		}

		if($this->user->loggedIn()){
			$ownedVocabsID = array();
			$ownedVocabs = $this->vocab->getAllOwnedVocabs();
			foreach($ownedVocabs as $v) array_push($ownedVocabsID, $v->id);
			if(in_array($jsonData['vocab_id'], $ownedVocabsID)){
				$jsonData['owned']=true;
				$jsonData['view'] = $view;
				if($view=='edit'){
					$jsonData['editable']=true;
				}
			}
		}

		$jsonData['id']=$version_id;

		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
	
	/**
	 * Get a set of vocab versions
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [INT] vocab ID
	 * @todo ACL on which vocabs you have access to, error handling
	 * @return [JSON] of a list of vocab versions
	 */	
	public function getVersions($vocab_id, $view='view'){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';
		$jsonData['id']=$vocab_id;
		//vocab versions
		$this->load->model('vocab_services', 'vocab');
		$versions= $this->vocab->getVersionsByID($vocab_id);


		//get owned vocabs permission
		if($this->user->loggedIn()){
			$ownedVocabsID = array();
			$ownedVocabs = $this->vocab->getAllOwnedVocabs(true);
			foreach($ownedVocabs as $v){
				array_push($ownedVocabsID, $v->id);
			}
			if(in_array($vocab_id, $ownedVocabsID)) {
				$jsonData['item']['owned']=true;
				if($view=='edit'){
					$jsonData['item']['editable']=true;
				}
			}
		}

		$items = array();
		$currentVersion ='';
		if($versions)
		{
			$jsonData['item']['hasVersions']=true;
			foreach($versions as $version){
				$item=array();
				$item['status']=$version->status;
				$item['title']=$version->title;
				$item['id']=$version->id;
				array_push($items, $item);
			}
			$jsonData['item']['versions']=$items;
		}else{
			$jsonData['noVersions']=true;
		}

		
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
	
	/**
	 * Get a set of vocab versions
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [INT] vocab ID
	 * @todo ACL on which vocabs you have access to, error handling
	 * @return [JSON] of a list of vocab versions
	 */	
	public function getVocabVersion($id){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';

		$this->load->model("vocab_services","vocab");

		$versions= $this->vocab->getVersionByID_old($id);
		$items = array();
		$currentVersion ='';
		if($versions)
		{
			foreach($versions as $version){
					//print_r($version);
				
				if($currentVersion!=$version->version_id)
				{
					if($currentVersion!='')
					{
						array_push($items, $item);
					}
					$currentVersion = $version->version_id;
					$item = array();
					$item['formats'] = array();									
					$item['status'] = $version->status;
					$item['id'] = $version->version_id;
					$item['title'] = $version->title;
					
				} 
					
				$formats['format'][] = $version->format;
				$formats['format_id'] = $version->id;
				$formats['type'][] = $version->type;
				$formats['value'][] = $version->value;
				array_push($item['formats'], $formats);
				unset($formats);
			
			}
			array_push($items, $item);
		}

		$jsonData['items'] = $items;
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}	

		
	/**
	 * delete a format from a vocab version 
	 * 
	 * 
	 * @author Liz Woods <liz.woods@ands.org.au>
	 * @param [INT] version format ID
	 * @todo ACL on which vocabs you have access to, error handling
	 * @return NIL
	 */	
	
	public function deleteFormat($format_id){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$this->load->model("vocab_services","vocab");
		$jsonData = array();
		if($this->vocab->deleteFormat($format_id)){
			$jsonData['status']='OK';
			$jsonData['message']='format id = '.$format_id.' deleted successfully';
		}else{
			$jsonData['status']='ERROR';
			$jsonData['message']='there is a problem deleting format id = '.$format_id;
		}
		echo json_encode($jsonData);
	}	

	public function deleteVersion(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$version_id = $this->input->post('version_id');
		$this->load->model("vocab_services","vocab");
		$this->vocab->deleteVersion($version_id);
	}

	public function deleteVocab(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$vocab_id = $this->input->post('vocab_id');
		$this->load->model('vocab_services', 'vocab');
		$this->vocab->deleteVocab($vocab_id);
	}

	public function uploadFile(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$config['upload_path'] = $this->config->item('upload_path');

		if(!file_exists($config['upload_path'])){
   			mkdir($config['upload_path']);
		}

		$config['allowed_types'] = $this->config->item('allowed_types');
		$this->load->library('upload', $config);
		$jsonData = array();
		if ( ! $this->upload->do_upload()){
			$error = array('error' => $this->upload->display_errors());
			$jsonData['status']='ERROR'; 
			$jsonData['message']=$error['error'];
		}
		else{
			$data = array('upload_data' => $this->upload->data());
			$jsonData['status']='OK'; 
			$jsonData['message']='File uploaded successfully!';
			$uploadData = $this->upload->data();
			$jsonData['fileName'] = $uploadData['file_name'];
		}
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}

	public function addFormat($version_id){
		$type = $this->input->post('type');
		$format = $this->input->post('format');
		$value = $this->input->post('value');
		
		$jsonData=array();
		$this->load->model("vocab_services","vocab");

		if($this->vocab->addFormat($version_id,$format,$type,$value)){
			$jsonData['status']='OK';
			$jsonData['message']='format added to the database';
		}else{
			$jsonData['status']='ERROR';
			$jsonData['message']='problem adding format to the database';
		}
		$jsonData=json_encode($jsonData);
		echo $jsonData;
	}

	public function downloadFormat($format_id){
		$this->load->model('vocab_services','vocab');		
		$format = $this->vocab->getFormatByID($format_id);
		$this->load->helper('url');
		if($format){
			$f = $format[0];//first format
			if($f->type=='uri'){
				$url = prep_url($f->value);
				redirect($url);
			}else if($f->type=='file'){
				echo 'is a file';

				$filename = $f->value;

				$upload_path = $this->config->item('upload_path');
				$this->load->helper('download');
				$data = file_get_contents($upload_path.'/'.$filename); // Read the file's contents
				$name = $f->value;
				force_download($name, $data);
			}
		}else{
			echo 'bad format';
		}
	}

	public function addChangeHistory(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$vocab_id = $this->input->post('vocab_id');
		$description = $this->input->post('description');
		$this->load->model('vocab_services', 'vocab');
		$jsonData = array();
		if($this->vocab->addChangeHistory($vocab_id, $description)){
			$jsonData['status']='OK';
			$jsonData['message']='change history updated';
		}else{
			$jsonData['status']='ERROR';
			$jsoNData['message']='change history update failed';
		}
		echo json_encode($jsonData);
	}
	
	/**
	 * add a format to a vocab version 
	 * 
	 * 
	 * @author Liz Woods <liz.woods@ands.org.au>
	 * @param [INT] version ID
	 * @todo ACL on which vocabs you have access to, error handling
	 * @return NIL
	 */	
	
	

	public function addVersion($vocab_id){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		//add version
		$this->load->model('vocab_services', 'vocab');
		$version = array(
			'title'=>$this->input->post('title'),
		);
		if($this->input->post('makeCurrent')) {
			$version['makeCurrent']=true;
		}else $version['makeCurrent']=false;
		$version_id = $this->vocab->addVersion($vocab_id, $version);



		//add initial format
		$type = $this->input->post('type');
		$format = $this->input->post('format');
		$value = $this->input->post('value');


		$jsonData=array();
		if($this->vocab->addFormat($version_id,$format,$type,$value)){
			$jsonData['status']='OK';
			$jsonData['message']='format added to the database';
		}else{
			$jsonData['status']='ERROR';
			$jsonData['message']='problem adding format to the database';
		}
		$jsonData=json_encode($jsonData);
		echo $jsonData;
	}

	public function updateVersion(){
		$this->load->model('vocab_services', 'vocab');
		$version = array(
			'title'=>$this->input->post('title'),
			'id'=>$this->input->post('id')
		);
		$this->vocab->updateVersion($version);
	}

	public function undoVocab($vocab_id){
		$this->load->model('vocab_services', 'vocab');
		$this->vocab->undoVersions($vocab_id);
	}

	public function contactPublisher(){
		$name = $this->input->post('name');
		$email = $this->input->post('email');
		$message = $this->input->post('description');
		$vocab_id = $this->input->post('vocab_id');

		$this->load->model("vocab_services","vocab");
		$vocab= $this->vocab->getByID($vocab_id);

		$jsonData = array();
		if($vocab){
			$publisher_email = $vocab->contact_email;
			//sending email
			$this->load->library('email');
			$this->email->from($email, $name);
			$this->email->to($publisher_email); //$publisher_email
			$this->email->subject('Message from the ANDS Vocabulary Discovery Portal regarding the '.$vocab->title.' vocabulary');
			$message = 'The following message was submitted via the Contact Publisher link in the ANDS Vocabulary Discovery Portal : '.$message;
			$this->email->message($message);	
			$this->email->send();
		}else{
			echo 'bad vocab';
		}
	}
	
	/**
	 * Get a set of vocab change histrory
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [INT] vocab ID
	 * @todo ACL on which vocabs you have access to, error handling
	 * @return [JSON] of a list of vocab changes
	 */	
	public function getVocabChanges($id){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';

		$this->load->model("vocab_services","vocab");

		$changes= $this->vocab->getChangesByID($id);
		$items = array();
		if($changes)
		{
			$jsonData['hasChanges']=true;
			foreach($changes as $change){
				$item = array();
				$item['change_date'] = $change->change_date;
				$item['id'] = $change->id;
				$item['description'] = $change->description;
				array_push($items, $item);
			}
		}else{
			$jsonData['noChanges']=true;
		}
		
		$jsonData['items'] = $items;
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
			
	/**
	 * Save a vocab
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [POST] Vocab ID [POST] attributes
	 * @todo ACL on which vocab you have access to, error handling, new attributes
	 * @return [JSON] result of the saving [VOID] 
	 */
	public function updateVocab(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$vocab = NULL;
		$id = NULL; 
		
		
		$POST = $this->input->post();
		if (isset($POST['vocab_id'])){
			$id = (int) $this->input->post('vocab_id');
		}
		
		$this->load->model("vocab_services","vocab");

		
		if ($id == 0) {
			$jsonData['status']='ERROR';
			$jsonData['message'] = "ERROR: Invalid vocab ID"; 
		}
		else{
			$vocab = $this->vocab->getByID($id);
		}

		if ($vocab)
		{
			if($this->vocab->getAvailableFormatsByID($vocab->id)){
				foreach($vocab->attributes() as $attrib=>$value){						
					if ($new_value = $this->input->post($attrib)) {
						if($new_value=='true') $new_value=DB_TRUE;
						if($new_value=='false') $new_value=DB_FALSE;
						$vocab->setAttribute($attrib, $new_value);
					}
				}
				$vocab->setAttribute('status','PUBLISHED');
				$vocab->save();
				$this->vocab->cleanUpVersions($vocab->id);
				$jsonData['status']='OK';
				$jsonData['message']=' Your Vocabulary was successfully updated <a href="#!/view/'.$id.'">View the vocabulary</a>';
			}else{
				$vocab->setAttribute('status','DRAFT');
				$vocab->save();
				$jsonData['status']='WARNING';
				$jsonData['message']=' A vocabulary version must be added before the vocabulary can be saved.';
			}
			
		}
		
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}


	public function createBlankVocab(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$this->load->model('vocab_services', 'vocab');

		$affiliations = $this->user->affiliations();
		if(sizeof($affiliations)>0){
			$record_owner = $affiliations[0];
			if($id = $this->vocab->addBlankVocab($record_owner)){
				$jsonData['status']='OK';
				$jsonData['message']='blank vocab created sucessfully';
				$jsonData['id']=$id;
			}else{
				$jsonData['status']='ERROR';
				$jsonData['message']='blank vocab created unsucessfully';
			}
		}else{
			$jsonData['status']='WARNING';
			$jsonData['redirect']=base_url().'vocab_service/affiliation';
			$jsonData['message']='You have to be an affiliation with an existing organisation to add vocab. 
									<p>'.anchor('', 'Go to Dashboard').' or '.anchor('vocab_service/index', 'Browse Vocabularies').'</p>';
		}
		
		echo json_encode($jsonData);
	}

	public function affiliation(){

		$this->load->model($this->config->item('authentication_class'), 'role');
		$data['available_organisations'] = $this->role->getAllOrganisationalRoles();
		asort($data['available_organisations']);
		

		$data['title'] = 'ANDS Vocabularies - Account Setup';
		$data['js_lib'] = array('core');
		$this->load->view('affiliation', $data);
	}
	
	
	/**
	 * @ignore
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
}

/* End of file vocab_service.php */
/* Location: ./application/models/vocab_services/controllers/vocab_service.php */