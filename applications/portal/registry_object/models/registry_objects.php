<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Registry Objects Encapsulate Model
 * Allow interaction between the application and the _ro object
 * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 */
class Registry_objects extends CI_Model {

    //array of properties required from the Registry point for RDA purpose
    public $rdaProperties = array('core', 'descriptions', 'relationships', 'subjects', 'spatial', 'temporal','citations','dates','relatedInfo',
        'identifiers','rights', 'contact','directaccess', 'suggest', 'logo', 'tags','existenceDates',
        'identifiermatch', 'accessPolicy', 'connectiontrees','jsonld', 'altmetrics','ARDC_campaign');
	/**
	 * get an _ro by ID
	 * @param  int $id registry object id
	 * @return _ro
	 */
	public function getByID($id, $props = array(), $useCache = true) {
		if (empty($props)) {
			$props = $this->rdaProperties;
		}
		return new _ro($id, $props, $useCache);
	}

    public function canUserPreview($ds_id){
        $_ci =& get_instance();
        $_ci->load->model('registry/data_source/data_sources', 'ds');
        $ds = $_ci->ds->getByID($ds_id);
        if($ds){
            if (!$_ci->user->hasAffiliation($ds->record_owner)){
                return false;
            }
        }else{
            return false;
        }
        return true;
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

        if (!$id) $id = $this->findOldMapping($slug);

        if ($id) {
            $props = $this->rdaProperties;
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
            $props = $this->rdaProperties;
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
     * return just the analytical record fields
     *
     * @param _ro $ro
     * @return array
     */
    public function getRecordFields(_ro $ro)
    {
        $result = [];
        $recordFields = ['id', 'key', 'class', 'type', 'data_source_id', 'slug', 'group'];
        foreach ($recordFields as $field) {
            $result[$field] = $ro->core[$field];
        }
        return $result;
    }

	/**
	 * Resolve an Identifier and return the "pull back" resource
	 * @param  string $type
	 * @param  string $identifier
	 * @return array
	 */
	public function resolveIdentifier($type = 'orcid', $identifier) {
		if (!$identifier) throw new Exception('No Identifier Provided');

        $myceliumServiceClient = new \ANDS\Mycelium\MyceliumServiceClient(\ANDS\Util\Config::get('mycelium.url'));
        $result = $myceliumServiceClient->resolveIdentifier($identifier, $type);
        $result = json_decode((String) $result->getBody(), true);

		if ($type=='orcid') {
			return array(
				'name' => isset($result['title']) ? $result['title'] : $result['meta']['rawTitle'],
				'bio' => isset($result['meta']['biography']) ? $result['meta']['biography'] : '',
				'orcid' => $identifier,
                'url' => $result['url'],
                'relatedInfo_type' => $type
			);
		} elseif ($type=='doi') {

 			if($result) {
				return array(
					'title' => isset($result['title']) ? $result['title'] : $result['meta']['rawTitle'],
					'publisher' => isset($result["meta"]['publisher']) ? $result["meta"]['publisher'] : '',
					'source' => isset($result["meta"]['source']) ? $result["meta"]['source'] : '',
					'DOI' => isset($result["meta"]['DOI']) ? $result["meta"]['DOI'] : '',
					'type' => isset($result["meta"]['type']) ? $result["meta"]['type'] : '',
					'url' => isset($result['url']) ? $result['url'] : '',
					'description' => isset($result["meta"]['abstract']) ? $result["meta"]['abstract'] : '',
                    'relatedInfo_type' => $type
				);
			}
        } elseif ($type=='ror') {
            if($result) {
                return array(
                    'name' => isset($result['title']) ? $result['title'] : $result['meta']['rawTitle'],
                    'url' => isset($result['url']) ? $result['url'] : '',
                    'types' => isset($result["meta"]['types']) ? $result["meta"]['types'] : 'Other',
                    'links' => isset($result["meta"]['links']) ? $result["meta"]['links'] : '',
                    'country' => isset($result["meta"]['country']) ? $result["meta"]['country'] : '',
                    'moreinfo' => isset($result['moreinfo']) ? $result['moreinfo'] : '',
                    'relatedInfo_type' => $type
                );
            }
        }
        return [];
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

	public function findOldMapping($slug) {
		$result = $this->db->order_by('registry_object_id desc')->get_where('url_mappings', array('slug'=>$slug));
		if ($result->num_rows() > 0) {
			$r = $result->first_row();
            $id = $r->registry_object_id;

            // if registry_object_id = null, attempt to match via search title
            // match record with exactly the same title
            // todo business rules when there are multiple match of different class
            if ($r && $r->registry_object_id === null && $r->search_title) {
                $titleSearch = $this->db->get_where('registry_objects', ['title' => $r->search_title]);
                if ($titleSearch->num_rows() > 0) {
                    $titleMatch = $titleSearch->first_row();
                    $id = $titleMatch->registry_object_id;
                }
            }

			return $id;
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
