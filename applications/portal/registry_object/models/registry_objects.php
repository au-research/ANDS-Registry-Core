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
	public function getByID($id, $props = array(), $useCache = true) {
		if (empty($props)) {
			$props = array('core', 'descriptions', 'relationships', 'subjects', 'spatial', 'temporal','citations','dates','connectiontrees','relatedInfo', 'identifiers','rights', 'contact','directaccess', 'suggest', 'logo', 'tags','existenceDates', 'identifiermatch');
		}
		return new _ro($id, $props, $useCache);
	}

	/**
	 * get an _ro by SLUG
	 * @param  string $slug 
	 * @todo
	 * @return _ro
	 */
	public function getBySlug($slug, $useCache = true) {
        if($this->checkRecordCount(array('slug'=>$slug)) > 1)
        {
            return 'MULTIPLE';
        }
        $filters = array(
        	'slug' => $slug
        );
        $id = $this->findRecord($filters, true);
        if($id)
        {
            $props = array('core', 'descriptions', 'relationships', 'subjects', 'spatial', 'temporal','citations','dates','connectiontrees','relatedInfo', 'identifiers','rights', 'contact','directaccess', 'suggest', 'logo', 'tags','existenceDates', 'identifiermatch');
            return new _ro($id, $props, $useCache);
        }
        return false;
	}

    /**
     * get an _ro by key
     * @param  string $key
     * @todo
     * @return _ro
     */
    public function getByKey($key, $useCache = true) {
        $id = $this->findRecord(array('key'=>$key), true);
        if($id)
        {
            $props = array('core', 'descriptions', 'relationships', 'subjects', 'spatial', 'temporal','citations','dates','connectiontrees','relatedInfo', 'identifiers','rights', 'contact','directaccess', 'suggest', 'logo', 'tags','existenceDates', 'identifiermatch');
            return new _ro($id, $props, $useCache);
        }
        return false;
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

			if(!isset($result['orcid-profile'])) return false;

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
		} elseif ($type=='doi') {

			//prepare identifier, strip out http
			$identifier = str_replace("http://dx.doi.org/", "", $identifier);

			//Crossref
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://api.crossref.org/works/".$identifier);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
			$result = curl_exec( $ch );
			curl_close($ch);

			$result = json_decode($result, true);


			$title = isset($result['message']['title'][0]) ? $result['message']['title'][0] : false;
			if (!$title) $title = isset($result['message']['container-title'][0]) ? $result['message']['container-title'][0] : 'No Title';

			if($result) {
				return array(
					'title' => $title,
					'publisher' => isset($result['message']['publisher']) ? $result['message']['publisher'] : '',
					'source' => isset($result['message']['source']) ? $result['message']['source'] : '',
					'DOI' => isset($result['message']['DOI']) ? $result['message']['DOI'] : '',
					'type' => isset($result['message']['type']) ? $result['message']['type'] : '',
					'url' => isset($result['message']['URL']) ? $result['message']['URL'] : '',
					'description' => ''
				);
			} else {
				//try get it from Datacite
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "http://search.datacite.org/api?wt=json&fl=doi,creator,resourceTypeGeneral,description,publisher,title&q=doi:".$identifier);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
				$result = curl_exec( $ch );
				curl_close($ch);

				$result = json_decode($result, true);
				if ($result) {
					if($result['response']['numFound'] > 0) {
						$record = $result['response']['docs'][0];
						return array(
							'title' => isset($record['title']) ? $record['title'][0] : 'No Title',
							'publisher' => isset($record['publisher']) ? $record['publisher'] : '',
							'doi' => isset($record['doi']) ? $record['doi'] : '',
							'type' => isset($record['resourceTypeGeneral']) ? $record['resourceTypeGeneral'] : '',
							'url' => 'http://dx.doi.org/'.$identifier,
							'source' => '',
							'description' => isset($record['description']) ? $record['description'][0] : ''
						);
					} else {
						//No result from Datacite
						return false;
					}
				} else {
					//No result from Crossref nor Datacite
					return false;
				}
			}
		}
	}

	public function findRecord($filters = array(), $id_only = false){
		$this->load->library('solr');
		$this->solr->init();
		$this->solr->setFilters($filters);
		$this->solr->setOpt('rows', 1);
		$this->solr->setOpt('fl', 'id');
		$result = $this->solr->executeSearch(true);

		if ($result['response']['numFound'] > 0) {
			$record = $result['response']['docs'][0];
			$id = $record['id'];
            if($id_only){
                return $id;
            }
            else{
                return $this->getByID($id);
            }
		} else {
			return false;
		}
	}

    public function checkRecordCount($filters = array()){
        $this->load->library('solr');
        $this->solr->init();
        $this->solr->setFilters($filters);
        $this->solr->setOpt('rows', 1);
        $this->solr->setOpt('fl', 'id');
        $result = $this->solr->executeSearch(true);
        return $result['response']['numFound'];

    }

	function __construct() {
		parent::__construct();
		include_once("_ro.php");
	}
}