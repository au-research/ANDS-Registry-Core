<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * RO search endpoint; written to drive the "find related objects"
 * registry object search widget.
 *
 *
 * @author Steven McPhillips <steven.mcphillips@gmail.com>
 *
 */
class Registry_object_search extends MX_Controller {


	function demo()
	{
		$data['title'] = 'Registry Object search widget';
		$data['scripts'] = array('rosearch_loader');
		$data['js_lib'] = array('core', 'rosearch_widget', 'prettyprint');

		$this->load->view("demo", $data);
	}


	/**
	 * @var The only (HTTP Request) parameters we're going to be interested in
	 * for the main `search` function.
	 */
	private static $parameters = array(
		//we don't seem to need this anymore: search does key and title, indiscriminately indiscriminantly.
		//array('name' => 'field',
		//      'conditions' => 'key|title',
		//      'required' => true),
		array('name' => 'term',
		      'conditions' => '.*',
		      'required' => true),
		array('name' => 'onlyPublished',
		      'conditions' => 'yes|no',
		      'required' => false,
		      'default' => "no")
		);

	private function json_header() {
		// header('Content-Type: application/json');
	}

	/**
	 * Simple helper to return JSON encoded responses. Makes use of fancy
	 * encode options if PHP version >= 5.4 to unescape slashes. When PHP
	 * version < 5.4, reverts to `str_replace`
	 * @see json_encode for additional requirements on the data param
	 * @param the data to encode as JSON.
	 * @return JSON-encoded data
	 */
	private static function to_json($data) {
		return (defined(PHP_VERSION_ID) && PHP_VERSION_ID >= 50400) ?
			json_encode($data, JSON_UNESCAPED_SLASHES) :
			str_replace('\/','/', json_encode($data));
	}

	public function index() {
		header('Content-type: text/plain');
		//I suppose this could dump some usage documentation or something...
		echo "Nothing to see here";
	}

	/**
	 * Retrieve a list of known registry object types. Request is made as a GET,
	 * with no additional data parameters. Response is JSON.
	 * @return an array of RO type structs
	 */
	public function types() {
		$this->json_header();

		$this->load->model('registry/registry_object/registry_objects', 'ro');
		echo self::to_json(array_map(function($t) {
					return array('key' => $t,
						     'label' => ucfirst($t));
				},
				$this->ro->valid_classes));
	}

	/**
	 * Retrieve a list of known datasources. Request is made as a GET, with no
	 * additional data parameters. Response is JSON.
	 * @return an array of RO datasource structs, sorted by title (case insensitive)
	 */
	public function sources() {
	    $this->json_header();

	    $this->load->model('registry/data_source/data_sources', 'ds');
	    $source = (array)$this->ds->getAll(0);
	    usort($source, function($a,$b) {
		    return (strtolower($a->title) < strtolower($b->title)) ? -1 : 1;
		});
	    echo self::to_json(array_map(function($ds) {
			return array('key' => $ds->getID(),
				     'label' => $ds->title);
		    }, $source));
	}

	/**
	 * Search for registry objects. Request is made as a POST, with
	 * parameters referenced in self::$parameters:
	 *  - key: search key. can be 'title', 'key', or 'all'
	 *  - value: search term.
	 *  - onlyPublished: boolean; true == return only published records,
	 *    false == return all records (BUT, if a record has a published *and*
	 *    non-published version, return the published version)
	 * @param the type of objects we're interested in. defaults to all
	 * @param the datasource we want to search. defatuls to all
	 * @return a search response struct:
	 *  - status: OK|ERROR
	 *  - msg: [descriptive request message on OK, error message on ERROR]
	 *  - limit: hard limit of search responses (we don't do paging, sorry)
	 *  - items: a array of registry object structs:
	 *      - key
	 *      - id
	 *      - label
	 */
	public function search($type=false, $source=false) {
		$this->json_header();
		$this->load->model('registry/registry_object/registry_objects', 'ro');

		$params = false;
		try {
			$params = $this->_search_params($type, $source);
		}
		catch (Exception $e) {
			$this->_throw($e->getMessage());
			return;
		}


		//for completeness, ensure params is an array before continuing.
		if (is_array($params)) {
			$args = array();
			$filter = array();

			if (isset($params['class']) &&
			   $params['class'] !== 'all') {
				$filter['class'] = $params['class'];
			}

			if (isset($params['onlyPublished']) &&
			    $params['onlyPublished'] === 'yes') {
			    $filter['status'] = "PUBLISHED";
			}

			if (isset($params['ds'])) {
				$args['data_source_id'] = $params['ds'];
			}

			$args['search'] = $params['term'];

			$args['filter'] = $filter;
		
			$ros = $this->ro->filter_by($args, 100);


			$results = array();
			$results['params'] = $params;
			if($ros){
				foreach($ros as $ro){
					$results['results'][] = array(
						'id'=>$ro->id,
						'title'=>$ro->title,
						'key'=>$ro->key,
						'class'=>$ro->class,
						'status'=> ucwords(strtolower(str_replace('_', ' ', $ro->status))),
						'color'=>Registry_objects::$status_colors[$ro->status]
					);
				}
			}else{
				$results['results'] = array();
			}

			echo self::to_json($results);
			/**
			 * depending on parameters, we'll do some different gets
			 * to generate results. The best way to approach this
			 * is probably by building up a `$pipeline` array that
			 * can be fed to $this->ro->_get()...
			 *  - field: where_like($params['key'], $params['term'])
			 *  - onlyPublished->yes: where('status', "PUBLISHED")
			 *  - class: where('class', $params['class'])
			 *  - ds: where('data_source_id', $this->ds->getByKey($params['ds'])->id)
			 */
		}
	}

	/**
	 * Internal helper to parse search parameters
	 * @see self::search
	 * @param the registry object class to limit on. if false, no class limit
	 * @param the datasource (key) to limit on. if false, no datasource limit
	 * @return an array of search parameters:
	 *  - class (iff limit present)
	 *  - type (iff limit present)
	 *  - field (key|title|all)
	 *  - term (the search term)
	 *  - onlyPublished (yes|no)
	 */
	private function _search_params($class, $ds)
	{
		if ($class !== false && $class !== 'all') {
			$params['class'] = $class;
		}

		if ($ds !== false && $ds !== 'all') {
			$params['ds'] = $ds;
		}

		foreach (self::$parameters as $param) {
			$cond = $param['conditions'];
			$name = $param['name'];
			$reqd = $param['required'];
			$default = array_key_exists('default', $param) ? $param['default'] : false;

			$value = $this->input->get_post($name);
			if ($value === false && $reqd === true) {
				throw new Exception("Search parameter '$name' " .
						    "is required but wasn't " .
						    "found.");
			}
			else if ($value !== false &&
				 preg_match("/$cond/",$value) === 0) {
				throw new Exception("Search parameter '$name' " .
						    "was found, but didn't " .
						    "meet precondition " .
						    "criteria /$cond/.");
			}
			else if ($value === false &&
				 $reqd === false &&
				 !empty($default)) {
				$value = $default;
			}
			//if we're still here, we've got a valid input parameter
			$params[$name] = $value;
		}
		return $params;
	}

	/**
	 * A very simple error handler. Note that it expects HTTP header
	 * information to have already been se[n]t.
	 * @param the error message to display;
	 */
	public function _throw($error) {
		echo self::to_json(array('status' => 'ERROR',
					 'msg' => $error));
	}
}
?>
