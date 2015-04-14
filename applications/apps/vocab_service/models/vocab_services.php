<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Core Data Sources model
 * 
 * This model allows the reference and initialisation 
 * of Data Sources. All instances of the _data_source 
 * PHP class should be invoked through this model. 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */

class Vocab_services extends CI_Model {
		
	
	private $vocab_db = null;

	/**
	 * Returns exactly one vocab by ID (or NULL)
	 * 
	 * @param the vocab ID
	 * @return _vocab object or NULL
	 */
	
	function getByID($id)
	{

		$query = $this->vocab_db->select()->get_where('vocab_metadata', array('id'=>$id));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$vocab = $query->result_array();
			return new _vocab($vocab[0]['id']);
		}
	} 	

	/**
	 * Returns all versions of a vocab by vocab  ID 
	 * 
	 * @param the vocab ID
	 * @return vocab versions or NULL
	 */	
	function getVersionsByID($id)
	{
		$query = $this->vocab_db->order_by('date_added desc')->select()->get_where('vocab_versions', array('vocab_id'=>$id,'status !='=>'RETIRED'));
		
		if ($query->num_rows() == 0){
			return NULL;
		}
		else{
			$vocab_versions = $query->result();
			//print_r($vocab_versions);
			return $vocab_versions;
		}
	}

	function getVersionByID($id){
		$query = $this->vocab_db->get_where('vocab_versions', array('id'=>$id), 1, 0);
		if($query->num_rows()==0){
			return NULL;
		}else{
			$result = $query->result();
			return $result[0];
		}
	}


	/**
	 * Returns all distinct formats of a vocab by vocab  ID 
	 * 
	 * @param the vocab ID
	 * @return vocab formats or NULL
	 */	
	function getAvailableFormatsByID($id)
	{
		$qry = 'SELECT distinct(format) FROM dbs_vocabs.vocab_version_formats  WHERE version_id IN(SELECT id FROM dbs_vocabs.vocab_versions WHERE vocab_id = '.$id.' AND (status = "current" OR status = "superseded" OR status="pending-add"));';
		$query = $this->vocab_db->query($qry);
		
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$vocab_formats = $query->result();
			return $vocab_formats;
		}	
	}

	/**
	 * Returns all downloadable file of a certain format belongs to a certain vocab 
	 * 
	 * @param the vocab ID, the format
	 * @return vocab formats or NULL
	 */	
	function getDownloadableByFormat($id, $format)
	{
		$qry = 'SELECT f.*, v.title, v.status from dbs_vocabs.vocab_version_formats f, dbs_vocabs.vocab_versions v WHERE f.format=\''.$format.'\' AND v.vocab_id='.$id.' AND f.version_id = v.id order by status asc;';
		$query = $this->vocab_db->query($qry);
		
		if ($query->num_rows() == 0)
		{
			return NULL;
			
		}
		else
		{
			$vocab_formats = $query->result();
			return $vocab_formats;
		}
	}

	/**
	 * Returns all versions of a vocab by vocab  ID (or NULL)
	 * 
	 * @param the vocab ID
	 * @return vocab versions or NULL
	 */	
	function getVersionByID_old($id)
	{
		$qry = 'SELECT * FROM vocab_versions, vocab_version_formats WHERE vocab_versions.id = '.$id.' AND vocab_version_formats.version_id = vocab_versions.id';
		$query = $this->vocab_db->query($qry);
		
		if ($query->num_rows() == 0)
		{
			return NULL;
			
		}
		else
		{
			$vocab_version = $query->result();
			//print_r($vocab_versions);
			return $vocab_version;
		}	
		
	}





	function getVocabIDbyVersion($version_id){
		$query = $this->vocab_db->select()->get_where('vocab_versions', array('id'=>$version_id));
		if($query->num_rows()==0){
			return null;
		}else{
			$results = $query->result();
			return $results[0]->vocab_id;
		}
	}
	
	/**
	 * Returns all formats of a version (or NULL)
	 * 
	 * @param the version ID
	 * @return formats or NULL
	 */	
	function getFormatByVersion($id)
	{
		$query = $this->vocab_db->select()->get_where('vocab_version_formats', array('version_id'=>$id));
		
		if ($query->num_rows() == 0){
			return NULL;
		}
		else{
			$formats = $query->result();
			return $formats;
		}	
		
	}	

	function getFormatByID($format_id){
		$query = $this->vocab_db->get_where('vocab_version_formats', array('id'=>$format_id));
		if($query->num_rows()==0){
			return false;
		}else{
			$formats = $query->result();
			return $formats;
		}
	}

	/**
	 * deletes a given format from a vocab version
	 * 
	 * @param the vocab version format ID
	 * @return NULL
	 */	
	function deleteFormat($id)
	{
		$qry = 'DELETE FROM vocab_version_formats WHERE id = '.$id;
		$query = $this->vocab_db->query($qry);
		
		if ($query){
			return true;
		}else return false;
	}	

	function deleteVersion($id){
		$version = $this->getVersionByID($id);
		$vocab_id = $version->vocab_id;

		$data = array(
			'status'=>'pending-delete'
		);
		$this->vocab_db->where('id', $id);
		$this->vocab_db->update('vocab_versions', $data);
		
		return true;
	}

	function cleanUpVersions($vocab_id){
		$versions = $this->getVersionsByID($vocab_id);
		foreach($versions as $version){

			//make all current superseded
			if($version->status=='current'){
				$data = array(
					'status'=>'superseded'
				);
				$this->vocab_db->where('id', $version->id);
				$this->vocab_db->update('vocab_versions', $data);
			}

			//delete all pending delete
			if($version->status=="pending-delete"){
				$data = array(
					'status'=>'RETIRED'
				);
				$this->vocab_db->where('id', $version->id);
				$this->vocab_db->update('vocab_versions', $data);
			}

			//make all pending-add superseded
			if($version->status=="pending-add"){
				$data = array(
					'status'=>'superseded'
				);
				$this->vocab_db->where('id', $version->id);
				$this->vocab_db->update('vocab_versions', $data);
			}
		}

		//now we have a set of all superseded, find the latest date_added to become the current
		$latestVersionQuery = $this->vocab_db->order_by('date_added', 'desc')->get_where('vocab_versions', array('vocab_id'=>$vocab_id, 'status'=>'superseded'),1,0);
		if($latestVersionQuery->num_rows()>0){
			//there is a latest version
			$result = $latestVersionQuery->result();
			$latestVersion = $result[0];
			$latestVersion_id = $latestVersion->id;

			//make it current
			$data = array(
				'status'=>'current'
			);
			$this->vocab_db->where('id', $latestVersion_id);
			$this->vocab_db->update('vocab_versions', $data);
		}
	}

	function undoVersions($vocab_id){
		$versions = $this->getVersionsByID($vocab_id);
		if(is_array($versions) && sizeof($versions) > 0){
			foreach($versions as $version){

				//make all current superseded
				if($version->status=='current'){
					$data = array(
						'status'=>'superseded'
					);
					$this->vocab_db->where('id', $version->id);
					$this->vocab_db->update('vocab_versions', $data);
				}

				//supersede pending delete
				if($version->status=="pending-delete"){
					$data = array(
						'status'=>'superseded'
					);
					$this->vocab_db->where('id', $version->id);
					$this->vocab_db->update('vocab_versions', $data);
				}

				//delete all pending add
				if($version->status=="pending-add"){
					$this->vocab_db->delete('vocab_versions', array('id' => $version->id)); 
				}
			}
		}

		//now we have a set of all superseded, find the latest date_added to become the current
		$latestVersionQuery = $this->vocab_db->order_by('date_added', 'desc')->get_where('vocab_versions', array('vocab_id'=>$vocab_id, 'status'=>'superseded'),1,0);
		if($latestVersionQuery->num_rows()>0){
			//there is a latest version
			$result = $latestVersionQuery->result();
			$latestVersion = $result[0];
			$latestVersion_id = $latestVersion->id;

			//make it current
			$data = array(
				'status'=>'current'
			);
			$this->vocab_db->where('id', $latestVersion_id);
			$this->vocab_db->update('vocab_versions', $data);
		}
	}


	function deleteVocab($vocab_id){
		//set the vocab to retire
		$data = array(
			'status'=>'RETIRED'
		);
		$this->vocab_db->where('id', $vocab_id);
		$this->vocab_db->update('vocab_metadata', $data);

		//set all version to retire
		$data = array(
			'status'=>'RETIRED'
		);
		$this->vocab_db->where('vocab_id', $vocab_id);
		$this->vocab_db->update('vocab_versions', $data);

		//delete all formats
	}
	
	/**
	 * adds a  format to a vocab version
	 * 
	 * @param the vocab version format ID
	 * @return NULL
	 */	
	function addFormat($version_id,$format,$type,$value) {
		$insert = $this->vocab_db->insert('vocab_version_formats', 
			array(
				'version_id' => $version_id,
				'format' => $format,
				'type' => $type,
				'value' => $value
			)
		);
		if ($insert) {
			return true;
		} else return false;
	}	

	function addVersion($vocab_id, $version){
		$data = array(
			'title'=>$version['title'],
			'status'=>'pending-add',
			'vocab_id'=>$vocab_id
		);

		$this->vocab_db->insert('vocab_versions', $data);
		return $this->vocab_db->insert_id();
	}

	function addBlankVocab($record_owner){
		$data = array(
			'record_owner'=>$record_owner,
			'status'=>'DRAFT'
		);
		$this->vocab_db->insert('vocab_metadata', $data);

		$last_id = $this->vocab_db->insert_id();

		/*$data = array(
			'vocab_id'=>$last_id,
			'description'=>'Initial vocabulary creation'
		);
		$this->vocab_db->insert('vocab_change_history', $data);*/

		return $last_id;
	}

	function addChangeHistory($vocab_id, $description){
		$data = array(
			'vocab_id'=>$vocab_id,
			'description'=>$description
		);

		$this->vocab_db->insert('vocab_change_history', $data);
		return true;
	}

	function updateVersion($version){
		$data = array(
			'title' => $version['title']
        );
        $this->vocab_db->where('id', $version['id']);
		$this->vocab_db->update('vocab_versions', $data); 

	}

	
	/**
	 * Returns all changes of a vocab by vocab  ID (or NULL)
	 * 
	 * @param the vocab ID
	 * @return vocab changes or NULL
	 */	
	function getChangesByID($id)
	{

		$query = $this->vocab_db->select()->order_by('change_date desc')->get_where('vocab_change_history', array('vocab_id'=>$id));

		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$vocab_changes = $query->result();
			return $vocab_changes;
		}	
		
	}	
	

	/**
	 * Get all vocabularies
	 * 
	 * @param limit by value
	 * @param the offset value
	 * @return array(_data_source) or empty array
	 */
	function getAll($limit = 16, $offset =0)
	{
	 	$matches = array();
		if($limit==0){
			$query = $this->vocab_db->order_by('title', 'asc')->select("title, id")->get('vocab_metadata');
		}else{
			$query = $this->vocab_db->order_by('title', 'asc')->select("title, id")->get('vocab_metadata', $limit, $offset);
		}
		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() AS $result)
			{
				$matches[] = new _vocab($result['id']);
			}
		}
		
		return $matches;
	} 	


	function getAllPublished($limit = 16, $offset =0)
	{
	 	$matches = array();
		if($limit==0){
			$query = $this->vocab_db->order_by('title','asc')->get_where('vocab_metadata', array('status'=>'PUBLISHED'));
		}else{
			$query = $this->vocab_db->order_by('title','asc')->get_where('vocab_metadata', array('status'=>'PUBLISHED'), $limit, $offset);
		}

		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() AS $result)
			{
				$matches[] = new _vocab($result['id']);
			}
		}
		
		return $matches;
	}
	
	/**
	 * Get all datasources
	 * 
	 * @param limit by value
	 * @param the offset value
	 * @return array(_data_source) or empty array
	 */
	function getGroupVocabs($limit = 16, $offset =0)
	{
		$vocabs = array();
		$affiliations = $this->user->affiliations();
		if (is_array($affiliations) && count($affiliations) > 0)
		{
			if($limit == 0){
				$query = $this->vocab_db->select('id')->where_in('record_owner',$affiliations)->get('vocab_metadata');
			}
			else{
				$query = $this->vocab_db->select('id')->where_in('record_owner',$affiliations)->where('status !=','DRAFT')->where('status !=','RETIRED')->get('vocab_metadata', $limit, $offset);
			}
			
			if ($query->num_rows() == 0){
				return $vocabs;
			}
			else
			{
				foreach($query->result_array() AS $v){
					$vocabs[] =  new _vocab($v['id']);
				}
			}
		}	
		return $vocabs;
	}

	function getGroupVocabsDrafts(){
		$vocabs = array();
		$affiliations = $this->user->affiliations();
		if (is_array($affiliations) && count($affiliations) > 0){
			$query = $this->vocab_db->select('id')->where_in('record_owner',$affiliations)->where('status', 'DRAFT')->get('vocab_metadata');
			if ($query->num_rows() == 0){
				return $vocabs;
			}
			else{
				foreach($query->result_array() AS $v){
					$vocabs[] =  new _vocab($v['id']);
				}
			}
		}
		return $vocabs;
	}

	//retired
	function getGroupUsersVocabs(){
		$vocabs = array();
		$users = array();
		$affiliations = $this->user->affiliations();
		$this->load->model($this->config->item('authentication_class'), 'role');
		foreach($affiliations as $a){
			$users = array_merge($users, $this->role->getRolesInAffiliate($a));
		}
		$users = array_unique($users);
		foreach($users as $u){
			$vocabs = array_merge($vocabs, $this->getVocabsByRole($u));
		}
		return $vocabs;
	}

	//retired
	function getOwnedVocabs($getDrafts){
		return $this->getVocabsByRole($this->user->localIdentifier(), $getDrafts);
	}

	//retired
	function getVocabsByRole($role_id, $getDrafts = false){
		$vocabs = array();
		$localIdentifier = $role_id;
		if($getDrafts){
			$query = $this->vocab_db->get_where('vocab_metadata', array('record_owner' => $localIdentifier));
		}else{
			$query = $this->vocab_db->get_where('vocab_metadata', array('record_owner' => $localIdentifier,'status'=>'PUBLISHED'));
		}
		if($query->num_rows()==0){
			return $vocabs;
		}else{
			foreach($query->result_array() as $v){
				$vocabs[] = new _vocab($v['id']);
			}
		}
		return $vocabs;
	}

	function getAllOwnedVocabs($getDrafts = false){
		$vocabs = array();
		$vocabs = array_merge($vocabs, $this->getGroupVocabs());
		$vocabs = array_merge($vocabs, $this->getGroupVocabsDrafts());
		return $vocabs;
	}

	
	/**
	 * XXX: 
	 * @return array(_vocab) or NULL
	 */
	function create()
	{
		$vocab = new _vocab();
		
		$vocab->create();
		return $vocab;
	} 	
	
	/**
	 * @ignore
	 */
	function __construct()
	{

		parent::__construct();
		$this->vocab_db = $this->load->database('vocabs',TRUE);
		include_once("_vocab.php");

	}	
		
}
