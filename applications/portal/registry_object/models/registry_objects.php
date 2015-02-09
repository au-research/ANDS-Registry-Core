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

	/**
	 * Resolve an Identifier and return the "pull back" resource
	 * @param  string $type       
	 * @param  string $identifier 
	 * @return array             
	 */
	public function resolveIdentifier($type = 'orcid', $identifier) {
		if (!$identifier) throw new Exception('No Identifier Provided');
		if ($type=='orcid') {
			$ch = curl_init();
			$headers = array('Accept: application/orcid+json');
			curl_setopt($ch, CURLOPT_URL, "http://pub.orcid.org/".$identifier); # URL to post to
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 ); # return into a variable
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers ); # custom headers, see above
			$result = curl_exec( $ch ); # run!
			curl_close($ch);

			$result = json_decode($result, true);

			$first_name = $result['orcid-profile']['orcid-bio']['personal-details']['given-names']['value'];
			$last_name = $result['orcid-profile']['orcid-bio']['personal-details']['family-name']['value'];
			$name = $first_name.' '.$last_name;
			$bio = "";

			if(isset($result['orcid-profile']['orcid-bio']['biography'])){
				$bio = $result['orcid-profile']['orcid-bio']['biography']['value'];
			}

			return array(
				'name' => $name,
				'bio' => $bio,
				'orcid' => $identifier
			);
		}
	}

	function __construct() {
		parent::__construct();
		include_once("_ro.php");
	}
}