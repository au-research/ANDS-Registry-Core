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

		return $json;
	}

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
	}

	/**
	 * Saving / Adding Vocabulary
	 * Requires the vocabs database connection group to be present
	 * $data is extracted for values to be put into the database and the
	 * rest is encoded within the data field
	 * If an ID is present in the _vocabulary, an update is issued
	 * If there is no ID, this is a new vocabulary and it will be added
	 * @param  $data 
	 * @return boolean
	 */
	public function save($data = false) {
		$ci =& get_instance();
		$db = $ci->load->database('vocabs', true);
		if (!$db) throw new Exception('Unable to connect to database');

		if ($this->id) {
			//update
			if ($data) {
				$saved_data = array(
					'title' => $data['title'],
					'licence' => isset($data['licence']) ? $data['licence'] : false,
					'description' => isset($data['description']) ? $data['description'] : false,
					'pool_party_id' => isset($data['pool_party_id']) ? $data['pool_party_id'] : false,
					'modified_date' => date("Y-m-d H:i:s"),
					'data' => json_encode($data)
				);
				$db->where('id', $data['id']);
				$result = $db->update('vocabularies', $saved_data);
				if ($result) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			//add new
			
			//check if there's an existing vocab with the same slug
			$slug = url_title($this->prop['title'], '-', TRUE);
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
				'data' => json_encode($this->prop)
			);
    		$result = $db->insert('vocabularies', $data);
    		$new_id = $db->insert_id();
    		if ($result && $new_id) {
    			$new_vocab = new _vocabulary($new_id);
    			return $new_vocab;
    		} else {
    			return false;
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