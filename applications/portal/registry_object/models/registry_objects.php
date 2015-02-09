<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Registry Objects Encapsulate Model
 * Allow interaction between the application and the _ro object
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Registry_objects extends CI_Model {

	/**
	 * get an _ro by ID
	 * @param  int $id registry object id
	 * @return _ro
	 */
	public function getByID($id) {
		return new _ro($id, array('core', 'descriptions', 'relationships', 'subjects', 'spatial', 'temporal','citations','dates','connectiontrees','relatedInfo', 'identifiers','rights', 'contact','directaccess', 'suggest', 'logo', 'tags','existenceDates', 'identifiermatch'));
	}

	/**
	 * get an _ro by SLUG
	 * @param  string $slug 
	 * @todo
	 * @return _ro
	 */
	public function getBySlug($slug) {

	}

	/**
	 * get an _ro by ANY
	 * detects the query to see if it's a slug or an id and then handle accordingly
	 * @param  string $slug 
	 * @todo
	 * @return _ro
	 */
	public function getByAny($query) {

	}

	function __construct() {
		parent::__construct();
		include_once("_ro.php");
	}
}