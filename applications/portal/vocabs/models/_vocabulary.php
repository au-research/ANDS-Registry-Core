<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Basse Vocabulary model for a single vocabulary object
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class _vocabulary {

	//object properties are all located in the same array
	public $prop;

	function __construct($id = false) {
		//populate the property as soon as the object is constructed
		$this->init();
		if ($id) {
			$this->populate_from_db($id);
		}
	}

	/**
	 * Initialize a registry object
	 * @todo
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return void
	 */
	function init() {
		//nothing here
	}

	/**
	 * Returns a flat array of indexable fields
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return array 
	 */
	public function indexable_json() {
		$this->populate_from_db($this->prop['id']);
		$json = array();

		//index single values
		$single_values = array('id', 'title', 'slug', 'licence', 'pool_party_id');
		foreach ($single_values as $s) {
			$json[$s] = $this->prop[$s];
		}

		if ($this->prop['data']) {
			$data = json_decode($this->prop['data'], true);

			if (isset($data['description'])) {
				$json['description'] = $data['description'];
			}

			if (isset($data['subjects'])) {
				$json['subjects'] = array();
				foreach($data['subjects'] as $subject) {
					$json['subjects'][] = $subject['subject'];
				}
			}
			if (isset($data['top_concept'])) {
				$json['top_concept'] = array();
				if (is_array($data['top_concept'])) {
					foreach($data['top_concept'] as $s) {
						$json['top_concept'][] = $s;
					}
				} else {
					$json['top_concept'] = $data['top_concept'];
				}
			}

			if (isset($data['language'])) {
				$json['language'] = array();
				if (is_array($data['language'])) {
					foreach($data['language'] as $s) {
						$json['language'][] = $s;
					}
				} else {
					$json['language'][] = $data['language'];
				}
				
			}
		}

		//Index concept
		//
		//Find current version
		$current_version = false;
		foreach ($this->versions as $version) {
			if ($version['status']=='current' && !$current_version) {
				$current_version = $version;
			}
		}

		$json['concept'] = array();

		//find the task/file associated with the current version
		$ci =& get_instance();
		$db = $ci->load->database('vocabs', true);
		$query = $db->get_where('task', array('version_id'=>$current_version['id'], 'status'=>'success'));
		$concept_list_path = false;
		if ($query->num_rows() > 0) {
			$result = $query->first_row();
			$response = $result->response;
			$response = json_decode($response, true);
			$concept_list_path = isset($response['concepts_list']) ? $response['concepts_list'] : false;
		}
		

		//read the file and then add the concepts to the index
		if ($concept_list_path) {
			$content = @file_get_contents($concept_list_path);
			$content = json_decode($content, true);
			foreach ($content as $concept) {
				if (isset($concept['prefLabel'])) {
					$json['concept'][] = $concept['prefLabel'];
				}
			}
		}
		
		

		return $json;
	}

	/**
	 * Return the current object as a displayable array, with the data attribute break apart
	 * into PHP array
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return array 
	 */
	public function display_array() {
		$result = json_decode(json_encode($this->prop), true);
		if ($this->data) {
			//dirty hack to convert json into multi dimensional array from an object
			$ex = json_decode(json_encode(json_decode($this->data)), true);
			foreach($ex as $key=>$value) {
				if (!isset($result[$key])) $result[$key] = $value;
			}
			unset($result['data']);
		}
		return $result;
	}

	public function current_version() {
		$current_version = false;
		if ($this->versions) {
			foreach ($this->versions as $version) {
				if ($version['status']=='current' && !$current_version) {
					$current_version = $version;
				}
			}
		}
		return $current_version;
	}


	public function display_tree($raw = false) {
		$current_version = $this->current_version();
		if ($current_version) {
			$data = $this->get_response_data($current_version['id']);
			if (!$data) {
				//no valid data returned, hence no tree
				return false;
			}
			$data = json_decode($data->response, true);
			$concepts_tree_path = isset($data['concepts_tree']) ? $data['concepts_tree'] : false;
			if ($concepts_tree_path) {
				$content = @file_get_contents($concepts_tree_path);
				if (!$content) {
					//file doesn't exist
					return false;
				}

				$tree_data = json_decode($content, true);
				if ($raw) return $tree_data;

				//build a tree a little bit nicer
				$tree = array();
				foreach($tree_data as $key=>$value) {
					$node = array(
						'uri' => $key,
						'value' => isset($value['prefLabel']) ? $value['prefLabel'] : 'No Title',
						'child' => array(),
						'num_child' => 0
					);
					$num_child = 0;
					foreach ($value as $key2=>$value2) {
						if ($key2!='prefLabel') {
							$node['child'][] = array(
								'uri' => $key2,
								'value' => isset($value2['prefLabel']) ? $value2['prefLabel'] : 'No Title',
							);
							$num_child++;
						}
					}
					$node['num_child'] = $num_child;
					$tree[] = $node;
				}
				
				return $tree;
			} else {
				//log: data found but no concept tree
				return false;
			}
		} else {
			//no current version
			return false;
		}
	}

	private function get_response_data($version_id) {
		$ci =& get_instance();
		$db = $ci->load->database('vocabs', true);
		$query = $db->get_where('task', array('status'=>'success', 'version_id'=>$version_id));
		if ($query->num_rows() > 0) {
			$result = $query->first_row();
			return $result;
		} else {
			return false;
		}
	}


	/**
	 * Populate the prop array with an array of key=>value pair
	 * @param  array  $values $key=>$value pair
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return void
	 */
	public function populate($values = array()) {
		foreach ($values as $key=>$value) {
			$this->prop[$key] = $value;
		}
	}

	/**
	 * Populate the _vocabulary props by extracting the data from DB
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param  int $id 
	 * @return void     
	 */
	public function populate_from_db($id) {
		$ci =& get_instance();
		$db = $ci->load->database('vocabs', true);
		if (!$db) throw new Exception('Unable to connect to database');
		if (!$id) throw new Exception('ID required');

		$query = $db->get_where('vocabularies', array('id'=>$id));
		$data = $query->first_row();
		$this->populate($data);

		//replace the versions with the one from the database
		$this->prop['versions'] = array();
		$query = $db->get_where('versions', array('vocab_id'=>$id));
		if ($query && $query->num_rows() > 0) {
			foreach($query->result_array() as $row) {
				$version = $row;

				//break apart version data
				if (isset($version['data'])) {
					$version_data = json_decode($version['data'], true);
					foreach($version_data as $key=>$value) {
						if (!isset($version[$key])) {
							$version[$key] = $value;
						}
					}
					unset($version['data']);
				}

				$this->prop['versions'][] = $version;
			}
		}
	}

	/**
	 * Saving / Adding Vocabulary
	 * Requires the vocabs database connection group to be present
	 * $data is extracted for values to be put into the database and the
	 * rest is encoded within the data field
	 * If an ID is present in the _vocabulary, an update is issued
	 * If there is no ID, this is a new vocabulary and it will be added
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param  $data 
	 * @return boolean
	 */
	public function save($data = false) {
		$ci =& get_instance();
		$db = $ci->load->database('vocabs', true);
		if (!$db) throw new Exception('Unable to connect to database');

		if ($this->id) {
            //if from draft get published id if it exists and override old published
            if($data['status']=='published'){
                $publish_slug = substr($data['slug'],0,strlen($data['slug'])-5);
                $result = $db->get_where('vocabularies', array('slug'=>$publish_slug));
                if ($result->num_rows() > 0) {
                    $published= $result->first_row();
                    $db->where('id', $data['id']);
                    $result = $db->delete('vocabularies');
                    $db->where('vocab_id', $data['id']);
                    $result = $db->delete('versions');

                    $data['id'] = $published->id;
                    $data['slug'] = $publish_slug;
                    $this->prop['id'] = $data['id'];
                }
            }

			//update
			if ($data) {
				$saved_data = array(
					'title' => $data['title'],
					'licence' => isset($data['licence']) ? $data['licence'] : false,
					'description' => isset($data['description']) ? $data['description'] : false,
					'pool_party_id' => isset($data['pool_party_id']) ? $data['pool_party_id'] : false,
					'modified_date' => date("Y-m-d H:i:s"),
                    'status' => $data['status'],
					'data' => json_encode($data)
				);
				$db->where('id', $data['id']);
				$result = $db->update('vocabularies', $saved_data);
				if(!$result) throw new Exception($db->_error_message());

				//deal with versions
				$this->updateVersions($data, $db);

				if ($result) {
					return true;
				} else {
					return $db->_error_message();
				}
			} else {
				return false;
			}
		} else {
			//add new
			
			//check if there's an existing vocab with the same slug
			$slug = url_title($this->prop['title'], '-', TRUE);
            if(isset($this->prop['status']) && $this->prop['status']=='draft'){
                $slug = $slug.'DRAFT';
            }
			$result = $db->get_where('vocabularies', array('slug'=>$slug));
			if ($result->num_rows() > 0) {
  				return false;
			}

			$data = array(
				'title' => $this->prop['title'],
				'slug' => $slug,
				'description' => isset($this->prop['description']) ? $this->prop['description'] : '',
				'licence' => isset($this->prop['licence']) ? $this->prop['licence'] : '',
				'pool_party_id' => isset($this->prop['pool_party_id']) ? $this->prop['pool_party_id'] : '',
				'created_date'=> date("Y-m-d H:i:s"),
				'modified_date' => date("Y-m-d H:i:s"),
                'status' => $this->prop['status'],
                'owner' => $this->prop['owner'],
                'user_owner' => $this->prop['user_owner'],
				'data' => json_encode($this->prop)
			);
			$result = $db->insert('vocabularies', $data);


			$this->prop['id'] = $db->insert_id();
            $data['id'] = $this->prop['id'];
            $newdata = array(
                'data' => json_encode($this->prop)
            );

            //return(json_encode($this->prop)." is the data");
            $db->where('id', $this->prop['id']);
            $result = $db->update('vocabularies', $newdata);

			//deal with versions
            $data = json_decode($data['data'],true);
			$this->updateVersions($data, $db);

			if ($result && $this->prop['id']) {
				$new_vocab = new _vocabulary($this->prop['id']);
				return $new_vocab;
			} else {
				return $db->_error_message();
			}
		}
	}




	/**
	 * Update the versions table according to the data received
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @access private
	 * @param  data $data 
	 * @param  db_obj $db   
	 * @return void
	 */
	private function updateVersions($data, $db) {

		//pre-update the object to make sure the versions are current
		$this->populate_from_db($this->prop['id']);

		//deleting the versions that is not in the income feed and not blank
		$existing = array();
		foreach($this->versions as $version) {
			$existing[] = $version['id'];
		}
		$incoming = array();
		foreach($data['versions'] as $version) {
			if (isset($version['id']) && $version['id']!="") {
				$incoming[] = $version['id'];
			}
		}
		$deleted = array_diff($existing, $incoming);
		foreach($deleted as $id) {
			$db->delete('versions', array('id'=>$id));
		}

		foreach($data['versions'] as $version) {
			if (isset($version['id']) && $version['id']!="" && $version['vocab_id']==$this->prop['id']) {
				//update the existing version
				$saved_data = array(
					'title' => $version['title'],
					'status' => $version['status'],
					'release_date' => date('Y-m-d H:i:s',strtotime($version['release_date'])),
					'vocab_id' => $this->prop['id'],
					'repository_id' => '',
					'data' => json_encode($version)
				);
				$db->where('id', $version['id']);
				$result = $db->update('versions', $saved_data);
				$this->processTask($saved_data,$version['id'],$db);
				if (!$result) throw new Exception($db->_error_message());
			} else {
				//add the version if it doesn't exist
				$version_data = array(
					'title' => $version['title'],
					'status' => $version['status'],
					'release_date' => date('Y-m-d H:i:s',strtotime($version['release_date'])),
					'vocab_id' => $this->prop['id'],
					'repository_id' => '',
					'data' => json_encode($version)
				);
				$result = $db->insert('versions', $version_data);
				$new_id = $db->insert_id();
                if($this->prop['status']=='published')
				$this->processTask($version_data,$new_id,$db);
				//throw new Exception($task_result);
				if (!$result) throw new Exception($db->_error_message());
			}
		}

		//update the object
		$this->populate_from_db($this->prop['id']);
	}

	private function processTask($version,$version_id,$db){

		$version_data = json_decode($version['data'],true);
		if($version_data['status']=='current'){

			//task array construction
			$task_array = array();
			$task_array[0]['type'] = 'HARVEST';

			if(isset($this->prop['pool_party_id'])&& $this->prop['pool_party_id']!=''){
				$task_array[0]['provider_type'] = 'PoolParty';
				$task_array[0]['project_id'] = $this->prop['pool_party_id'];
			}
			$task_array[1]['type'] = 'TRANSFORM';
			$task_array[1]['provider_type'] = 'JsonList';
			$task_array[2]['type'] = 'TRANSFORM';
			$task_array[2]['provider_type'] = 'JsonTree';
			$task_array[3]['type'] = 'IMPORT';
			$task_array[3]['provider_type'] = 'Sesame';
			$task_array[3]['type'] = 'PUBLISH';
			$task_array[3]['provider_type'] = 'SISSVoc';
			$task_params = json_encode($task_array);

			$params = array(
				'vocabulary_id' => $this->prop['id'],
				'version_id' => $version_id,
				'params' => $task_params
			);

			$result = $db->insert('task', $params);
			$task_id = $db->insert_id();
			if (!$result) throw new Exception($db->_error_message());

			//hit Toolkit
			$vocab_config = get_config_item('vocab_config');
			$toolkit_url = $vocab_config['toolkit_url'];
			$content = @file_get_contents($toolkit_url.'runTask/'.$task_id);

			if ($content) {
				$content = json_decode($content, true);

				if (isset($content['concepts']) || isset($concept['sparql_endpoint']) || isset($concept['sissvoc_endpoints'])) {
					//update the access point of type file, apiSparql path to the respective path
					$query = $db->get_where('versions', array('id'=>$version_id));
					if ($query->num_rows() > 0) {
						$vv = $query->first_row();
						$vvdata = json_decode($vv->data, true);
						foreach ($vvdata['access_points'] as &$ap) {
							if ($ap['type']=='file' && $ap['uri']=='TBD') {
								$ap['uri'] = isset($content['concepts']) ? $concent['concepts'] : 'TBD';
							} elseif ($ap['type']=='apiSparql' && $ap['uri']=='TBD') {
								$ap['uri'] = isset($content['sparql_endpoint']) ? $content['sparql_endpoint'] : 'TBD';
							} elseif ($ap['type']=='webPage') {
								$ap['uri'] = isset($content['sissvoc_endpoints']) ? $content['sissvoc_endpoints'] : 'TBD';
							}
						}
						$saved_data = array(
							'data' => json_encode($vvdata)
						);
						$db->where('id', $version_id);
						$result = $db->update('versions', $saved_data);
						if (!$result) throw new Exception($db->_error_message());
					} else {
						//cant find version with the id, handle here
					}
				}

			}

		}
	}
	/**
	 * Magic function to get an attribute, returns property within the $prop array
	 * @param  string $property property name
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return property result           
	 */
	public function __get($property) {
		if(isset($this->prop[$property])) {
			return $this->prop[$property];
		} else return false;
	}

	/**
	 * Magic function to set an attribute
	 * @param string $property property name
	 * @param string $value    property value
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 */
	public function __set($property, $value) {
		$this->prop[$property] = $value;
	}

	/**
	 * Magic function to return the object as a JSON encoded string
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return string
	 */
	public function __toString() {
		return json_encode($this->prop);
	}
}