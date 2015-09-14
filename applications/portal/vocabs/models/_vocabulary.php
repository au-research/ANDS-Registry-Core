<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Basse Vocabulary model for a single vocabulary object
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class _vocabulary
{

    //object properties are all located in the same array
    public $prop;

    //import log, useful for logging the saving process
    public $import_log = array();

    // Temporary workaround for storing "groupings" of licence identifiers
    // XXX: Long term solution should use a vocabulary service (such as ANDS's)
    private static $licence_groups = array(
        "GPL" => "Open Licence",
        "CC-BY-SA" => "Open Licence",
        "CC-BY-ND" => "Non-Derivative Licence",
        "CC-BY-NC-SA" => "Non-Commercial Licence",
        "CC-BY-NC-ND" => "Non-Derivative Licence",
        "CC-BY-NC" => "Non-Commercial Licence",
        "CC-BY" => "Open Licence",
        "CSIRO Data Licence" => "Non-Commercial Licence",
        "AusGoalRestrictive" => "Restrictive Licence",
        "NoLicence" => "No Licence"
    );

    function __construct($id = false)
    {
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
    function init()
    {
        //nothing here
    }

    /**
     * Returns a flat array of indexable fields
     * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @return array
     */
    public function indexable_json()
    {
        $this->populate_from_db($this->prop['id']);
        $json = array();

        //index single values
        $single_values = array('id', 'title', 'slug', 'pool_party_id');
        foreach ($single_values as $s) {
            if (isset($this->prop[$s])) $json[$s] = $this->prop[$s];
        }

        //licence is done differently
        if ($this->prop['licence']) {
            $value = trim($this->prop['licence']);
            $json['licence'] = (isset(self::$licence_groups[$value])) ? self::$licence_groups[$value] : 'Unknown/Other';
        }

        $data = $this->display_array();

        if (isset($data['acronym'])) $json['acronym'] = $data['acronym'];

        if (isset($data['description'])) {
            $json['description'] = $data['description'];
        }

        if (isset($data['language'])) {
            $json['language'] = array();
            if (is_array($data['language'])) {
                foreach ($data['language'] as $s) {
                    $json['language'][] = readable_lang($s);
                }
            } else {
                $json['language'][] = readable_lang($data['language']);
            }
        }

        if (isset($data['subjects'])) {
            $json['subjects'] = array();
            foreach ($data['subjects'] as $subject) {
                $json['subjects'][] = $subject['subject'];
            }
        }
        if (isset($data['top_concept'])) {
            $json['top_concept'] = array();
            if (is_array($data['top_concept'])) {
                foreach ($data['top_concept'] as $s) {
                    $json['top_concept'][] = $s;
                }
            } else {
                $json['top_concept'] = $data['top_concept'];
            }
        }

        if (isset($data['owner'])) {
            $json['owner'] = $data['owner'];
        }

        //Index publisher
        //Publisher is a related entity of type party with the relationship of publishedBy
        $json['publisher'] = array();
        if (isset($data['related_entity'])) {
            foreach ($data['related_entity'] as $re) {
                if ($re['type'] == 'party') {
                    if (isset($re['relationship'])) {
                        if (
                            (is_array($re['relationship']) && in_array('publishedBy', $re['relationship'])) || ($re['relationship'] == 'publishedBy')
                        ) {
                            $json['publisher'][] = $re['title'];
                        }
                    }

                }
            }
        }

        //Index concept

        //Find current version
        $current_version = false;
        foreach ($this->versions as $version) {
            if ($version['status'] == 'current' && !$current_version) {
                $current_version = $version;
            }
        }

        $json['concept'] = array();

        if ($current_version) {
            $concept_list_path = isset($current_version['concepts_list']) ? $current_version['concepts_list'] : false;
            if($concept_list_path){
                $content = @file_get_contents($concept_list_path);
                $content = json_decode($content, true);
                foreach ($content as $concept) {
                    if (isset($concept['prefLabel'])) {
                        $json['concept'][] = $concept['prefLabel'];
                    }
                }
            }
            //accessibility
            $json['access'] = array();
            $json['format'] = array();
            foreach ($current_version['access_points'] as $ap) {
                $json['access'][] = vocab_readable($ap['type']);
                $json['format'][] = $ap['format'];
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
    public function display_array()
    {
        $result = json_decode(json_encode($this->prop), true);
        if ($this->data) {
            //dirty hack to convert json into multi dimensional array from an object
            $ex = json_decode(json_encode(json_decode($this->data)), true);
            foreach ($ex as $key => $value) {
                if (!isset($result[$key])) $result[$key] = $value;
            }
            unset($result['data']);
        }
        return $result;
    }

    /**
     * Return the current version (if exists) of the vocabulary
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @return array $current_version
     */
    public function current_version()
    {
        $current_version = false;
        if ($this->versions) {
            foreach ($this->versions as $version) {
                if ($version['status'] == 'current' && !$current_version) {
                    $version['version_access_points'] = $this->getAccessPoints($version['id']);
                    $current_version = $version;
                }
            }
        }
        return $current_version;
    }

    /**
     * Returns a access points for a specific vocab version
     * Helper function
     * @param  int $versionId
     * @return array of accesspoints
     */
    public function getAccessPoints($versionId, $type = '')
    {

        $ci =& get_instance();
        $db = $ci->load->database('vocabs', true);
        $db->select('id, version_id, type, portal_data');
        if($type == 'all' || $type == '' )
            $query = $db->get_where('access_points', array('version_id' => $versionId));
        else
            $query = $db->get_where('access_points', array('version_id' => $versionId, 'type' => $type));
        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return false;
        }
    }


    /**
     * Delete the access points for a specific vocab version
     * that the user entered themself. Can be webPage, apiSparql, sissvoc.
     * @param  int $versionId
     */

    public function removeUserDefinedAccessPoints($versionId){
        $ci =& get_instance();
        $db = $ci->load->database('vocabs', true);

        $allAccessPoints = $this->getAccessPoints($versionId);

        foreach ($allAccessPoints as $ap) {

            $delete = false;
            $id = $ap['id'];
            switch ($ap['type']) {
            case 'file':
                // File is considered system-entered, as the endpoint
                // is added by the Toolkit.
                break;
            case 'sesameDownload':
                break;
            case 'webPage':
                $delete = true;
                break;
            default:
                $source = json_decode($ap['portal_data']);
                if(isset($source->source) && $source->source == 'user'){
                    $delete = true;
                }
            }
            if($delete){
                $db->delete('access_points', array('id' => $id));
            }
        }
    }

    public function addPortalDataAccessPoint($versionId, $type , $portal_data, $toolkit_data="{}")
    {
        $data = array('version_id'=>$versionId, 'type' => $type , 'portal_data'=>$portal_data, 'toolkit_data'=>$toolkit_data);
        $ci =& get_instance();
        $db = $ci->load->database('vocabs', true);
        $db->insert('access_points', $data);
    }
    /**
     * Return the tree representation of the current version
     * requires the concepts_tree already harvested and transformed
     * Recursive to with the BuilTree function
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @param  boolean $raw raw form of the tree as returned from Toolkit
     * @return array $tree
     */
    public function display_tree($raw = false)
    {
        $current_version = $this->current_version();
        if ($current_version) {
            $concepts_tree_path = isset($current_version['concepts_tree']) ? $current_version['concepts_tree'] : false;
            if (!$concepts_tree_path) {
                //no valid data returned, hence no tree
                return false;
            }
            else {
                $content = @file_get_contents($concepts_tree_path);
                if (!$content) {
                    //file doesn't exist
                    return false;
                }

                $tree_data = json_decode($content, true);
                if ($raw) return $tree_data;

                //build a tree a little bit nicer
                $tree = $this->buildTree($tree_data);

                return $tree;
            }
        } else {
            //no current version
            return false;
        }
    }

    /**
     * Helper function for @display_tree
     * Recursively called to build the tree when childs exist
     * prefLabel and notation child are not considered children
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @param  array $treeData
     * @return array child Tree
     */
    private function buildTree($treeData)
    {
        $tree = array();
        if (is_array($treeData)) {
            foreach ($treeData as $key => $value) {
                if ($key != 'prefLabel' && $key != 'notation') {
                    $node = array(
                        'uri' => $key,
                        'value' => isset($value['prefLabel']) ? $value['prefLabel'] : 'No Title',
                        'child' => array(),
                        'num_child' => 0
                    );
                    $childs = $this->buildTree($value);
                    $node['child'] = $childs;
                    $node['num_child'] = sizeof($childs);
                    $tree[] = $node;
                }
            }
        }
        return $tree;
    }

    /**
     * Return the response data of a version already successfully processed by the Toolkit
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @param  int $version_id
     * @return obj response_data
     */
    private function get_response_data($version_id, $task_id)
    {
        $ci =& get_instance();
        $db = $ci->load->database('vocabs', true);
        $query = $db->get_where('task', array('status' => 'success', 'version_id' => $version_id));
        if ($query->num_rows() > 0) {
            $result = $query->first_row();
            return $result;
        } else {
            return false;
        }
    }


    /**
     * Populate the prop array with an array of key=>value pair
     * @param  array $values $key=>$value pair
     * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @return void
     */
    public function populate($values = array())
    {
        foreach ($values as $key => $value) {
            $this->prop[$key] = $value;
        }
    }

    /**
     * Populate the _vocabulary props by extracting the data from DB
     * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @param  int $id
     * @return void
     */
    public function populate_from_db($id)
    {
        $ci =& get_instance();
        $db = $ci->load->database('vocabs', true);
        if (!$db) throw new Exception('Unable to connect to database');
        if (!$id) throw new Exception('ID required');

        $query = $db->get_where('vocabularies', array('id' => $id));
        $data = $query->first_row();
        $this->populate($data);

        //replace the versions with the one from the database
        $this->prop['versions'] = array();
        $query = $db->get_where('versions', array('vocab_id' => $id));
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $version = $row;
                $version['version_access_points'] = $this->getAccessPoints($version['id']);
                //break apart version data
                if (isset($version['data'])) {
                    $version_data = json_decode($version['data'], true);
                    foreach ($version_data as $key => $value) {
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
     * @public Allow writing of importing logs
     * @param  string $message
     * @return void
     */
    public function log($message)
    {
        $this->import_log[] = $message;
    }

    /**
     * Create a slug for a vocabulary title. The resulting slug
     * will be no more than 50 characters long. This limit is not
     * entirely arbitrary; the toolkit implements the same truncation
     * for version titles. The truncation is needed because (for now,
     * but for how much longer?) the toolkit uses slugs as directory
     * names, and we have come up against a limit on the total length
     * of a path to a file.
     * @param string $title The title from which to make the slug
     * @return string The generated slug
     */
    public function makeSlug($title)
    {
        $slug = url_title($title, '-', TRUE);
        // Now truncate it.
        $slug = substr($slug, 0, 50);
        // Trim again, just in case the truncation left a trailing hyphen.
        $slug = trim($slug, "-");
        return $slug;
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
    public function save($data = false)
    {
        $ci =& get_instance();
        $db = $ci->load->database('vocabs', true);
        if (!$db) throw new Exception('Unable to connect to database');
        if ($this->id) {

            //if from draft get published id if it exists and override old published
            if ($data['status'] == 'published') {
                $this->log('Publishing Vocabulary ' . $data['title'] . '(' . $data['id'] . ')');
                $result = $db->get_where('vocabularies', array('slug' => $data['slug'], 'status' => 'published'));
                if ($result->num_rows() > 0) {
                    $published = $result->first_row();
                    if ($data['id'] != $published->id) {
                        $db->where('id', $data['id']);
                        $result = $db->delete('vocabularies');
                        $db->where('vocab_id', $data['id']);
                        $result = $db->delete('versions');

                        $data['id'] = $published->id;
                        $data['slug'] = $data['slug'];
                        $this->prop['id'] = $data['id'];
                    }
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
                    'owner' => isset($data['owner']) ? $data['owner'] : '',
                    'data' => json_encode($data)
                );
                $db->where('id', $data['id']);
                $result = $db->update('vocabularies', $saved_data);
                if (!$result) throw new Exception($db->_error_message());
                $this->log('Successfully updated ' . $data['title']);

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
            //check if there's an existing vocab with the same slug in draft state
            $slug = $this->makeSlug($this->prop['title']);
            if (isset($this->prop['status']) && $this->prop['status'] == 'draft') {
                $result = $db->get_where('vocabularies', array('slug' => $slug, 'status' => 'draft'));
                if ($result->num_rows() > 0) {
                    $draft_vocab = $result->first_row();
                    $this->prop['id'] = $draft_vocab->id;
                }
            }

            if (!isset($this->prop['owner'])) $this->prop['owner'] = $ci->user->localIdentifier();
            $data = array(
                'title' => $this->prop['title'],
                'slug' => $slug,
                'description' => isset($this->prop['description']) ? $this->prop['description'] : '',
                'licence' => isset($this->prop['licence']) ? $this->prop['licence'] : '',
                'pool_party_id' => isset($this->prop['pool_party_id']) ? $this->prop['pool_party_id'] : '',
                'created_date' => date("Y-m-d H:i:s"),
                'modified_date' => date("Y-m-d H:i:s"),
                'status' => $this->prop['status'],
                'owner' => isset($this->prop['owner']) ? $this->prop['owner'] : '',
                'user_owner' => isset($this->prop['user_owner']) ? $this->prop['user_owner'] : '',
                'data' => json_encode($this->prop)
            );

            if (!isset($this->prop['id'])) {
                $db->insert('vocabularies', $data);
                $this->prop['id'] = $db->insert_id();
            }

            $data['id'] = $this->prop['id'];
            $newdata = array(
                'data' => json_encode($this->prop)
            );

            $db->where('id', $this->prop['id']);
            $result = $db->update('vocabularies', $newdata);

            //deal with versions
            $data = json_decode($data['data'], true);
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
    private function updateVersions($data, $db)
    {

        //pre-update the object to make sure the versions are current

        $this->populate_from_db($this->prop['id']);

        //deleting the versions that is not in the income feed and not blank
        $existing = array();
        foreach ($this->versions as $version) {
            $existing[] = $version['id'];
        }
        $incoming = array();
        if (isset($data['versions'])) {
            foreach ($data['versions'] as $version) {
                if (isset($version['id']) && $version['id'] != "") {
                    $incoming[] = $version['id'];
                }
            }
            $deleted = array_diff($existing, $incoming);
            if(sizeof($deleted) > 0){
                $ci =& get_instance();
                $vocab = $ci->load->model('vocabularies', 'vocab');
                foreach ($deleted as $id) {
                    $result = $vocab->removeVersion($this->prop['id'], $id);
                    $this->log('Removed version: ' . $result);
                }
            }
            //if (sizeof($deleted) > 0) $this->log('Removed versions: ' . implode(',', $deleted));

            foreach ($data['versions'] as $version) {
                if (isset($version['id']) && $version['id'] != "" && $version['vocab_id'] == $this->prop['id']) {
                    //update the existing version
                    $saved_data = array(
                        'title' => $version['title'],
                        'status' => $version['status'],
                        'release_date' => date('Y-m-d H:i:s', strtotime($version['release_date'])),
                        'vocab_id' => $this->prop['id'],
                        'repository_id' => '',
                        'data' => json_encode($version)
                    );
                    $db->where('id', $version['id']);
                    $result = $db->update('versions', $saved_data);
                    if ($this->prop['status'] == 'published') $this->processTask($saved_data, $version['id'], $db);
                    if (!$result) throw new Exception($db->_error_message());
                    $this->log('Updated version ' . $saved_data['title'] . ' successfully');
                } else {
                    //add the version if it doesn't exist
                    $version_data = array(
                        'title' => $version['title'],
                        'status' => $version['status'],
                        'release_date' => date('Y-m-d H:i:s', strtotime($version['release_date'])),
                        'vocab_id' => $this->prop['id'],
                        'repository_id' => '',
                        'data' => json_encode($version)
                    );
                    $result = $db->insert('versions', $version_data);
                    $new_id = $db->insert_id();
                    if ($this->prop['status'] == 'published') $this->processTask($version_data, $new_id, $db);
                    //throw new Exception($task_result);
                    if (!$result) throw new Exception($db->_error_message());
                    $this->log('Added version ' . $version_data['title'] . ' successfully');
                }
            }
        }
        //update the object
        $this->populate_from_db($this->prop['id']);
    }

    /**
     * Helper function to determine if vocabulary is a PoolParty project or not
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @return boolean
     */
    private function isPoolParty()
    {
        if (isset($this->prop['pool_party_id']) && $this->prop['pool_party_id'] != '') {
            return true;
        } else return false;
    }

    /**
     * Helper function to determine if a version requires a specific action
     * Helper for @processTask
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @param  obj $version
     * @param  string $action [import|publish]
     * @return boolean
     */
    private function determineAction($version, $action)
    {
        $result = false;
        $version_data = json_decode($version['data'], true);

        foreach ($version_data['access_points'] as $ap) {
            if ($ap['type'] == 'apiSparql' && $action == 'import') {
                // $this->log('Found an apiSparql with action import');
                $result = true;
            } elseif ($ap['type'] == 'webPage' && $action == 'publish') {
                // $this->log('Found a webPage with action publish');
                $result = true;
            }
        }
        return $result;
    }

    /**
     * Process a possible task given for a version
     * Usually for the current version of a vocabulary
     * Interaction with PoolParty requires
     * Reading the response and writes to the import log
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @param  obj $version
     * @param  int $version_id
     * @param  obj $db Database object so we don't have to recreate it
     * @return void
     */
    private function processTask($version, $version_id, $db)
    {
        // $this->log('Task set for version '.$version['title']);
        $version_data = json_decode($version['data'], true);
        if ($version_data['status'] == 'current') {

            //task array construction
            $task_array = array();
            $this->removeUserDefinedAccessPoints($version_id);
            if ($this->isPoolParty()) {
                $harvest_task = array('type' => 'HARVEST', 'provider_type' => 'PoolParty', 'project_id' => $this->prop['pool_party_id']);
                array_push($task_array, $harvest_task);
            }

            if(isset($version_data['access_points']))
            {
                foreach ($version_data['access_points'] as $ap) {

                    if ($ap['type'] == 'file') {
                        $harvest_task = array('type' => 'HARVEST', 'provider_type' => 'File', 'file_path' => vocab_uploaded_url($ap['uri']));
                        array_push($task_array, $harvest_task);
                    }
                    else if($ap['uri'] != 'TBD'){ // user defined
                      $portal_data = array('source'=>'user', 'uri'=>$ap['uri'], 'format'=>$ap['format']);
                      $this->addPortalDataAccessPoint($version_id, $ap['type'] , json_encode($portal_data));
                    }
                }

             }

            $transform_task = array('type' => 'TRANSFORM', 'provider_type' => 'JsonList');
            array_push($task_array, $transform_task);
            $transform_task = array('type' => 'TRANSFORM', 'provider_type' => 'JsonTree');
            array_push($task_array, $transform_task);

            if ($this->determineAction($version, 'import')) {
                $import_task = array('type' => 'IMPORT', 'provider_type' => 'Sesame');
                array_push($task_array, $import_task);
            }

            if ($this->determineAction($version, 'publish')) {
                $publish_task = array('type' => 'PUBLISH', 'provider_type' => 'SISSVoc');
                array_push($task_array, $publish_task);
            }

            //add task array to the task table
            $task_params = json_encode($task_array);
            $params = array(
                'vocabulary_id' => $this->prop['id'],
                'version_id' => $version_id,
                'params' => $task_params
            );
            $result = $db->insert('task', $params);
            $task_id = $db->insert_id();
            if (!$result) throw new Exception($db->_error_message());
            $this->log('Task ' . $task_id . ' added and waiting for toolkit to process');
            $ci =& get_instance();
            $vocab = $ci->load->model('vocabularies', 'vocab');
            $content = $vocab->runToolkitTask($task_id);

            //deal with content return
            if ($content) {
                $content = json_decode($content, true);
                $this->log('Task ' . $task_id . ' completed with status: ' . $content['status']);
                if (isset($content['exception'])) $this->log('Task ' . $task_id . ' has exception:' . $content['exception']);
                //pool party stuffs filling

                if (isset($content['concepts_tree']) || isset($content['concepts_list'])) {
                    //update the access point of type file, apiSparql path to the respective path
                    $query = $db->get_where('versions', array('id' => $version_id));
                    if ($query->num_rows() > 0) {
                        $vv = $query->first_row();
                        $vvdata = json_decode($vv->data, true);
                        if(isset($content['concepts_tree'])){
                            $vvdata['concepts_tree'] = $content['concepts_tree'];
                        }
                        if(isset($content['concepts_list'])){
                            $vvdata['concepts_list'] = $content['concepts_list'];
                        }
                        $saved_data = array(
                            'data' => json_encode($vvdata)
                        );
                        $db->where('id', $version_id);
                        $result = $db->update('versions', $saved_data);
                        if (!$result) throw new Exception($db->_error_message());
                    } else {
                        //cant find version with the id, handle here
                        $this->log('Version with ID: ' . $version_id . ' not found');
                    }
                }
            }

        }
    }

    /**
     * Returns a specific version given the ID
     * Helper function
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @param  int $id version id
     * @param  boolean|obj $db Database object so we don't have to recreate it
     * @return obj version
     */
    private function getVersion($id, $db = false)
    {
        if (!$db) {
            $ci =& get_instance();
            $db = $ci->load->database('vocabs', true);
        }
        $query = $db->get_where('versions', array('id' => $id));
        if ($query->num_rows() > 0) {
            $version = $query->first_row();
            return $version;
        } else {
            return false;
        }
    }


    /**
     * Magic function to get an attribute, returns property within the $prop array
     * @param  string $property property name
     * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @return property result
     */
    public function __get($property)
    {
        if (isset($this->prop[$property])) {
            return $this->prop[$property];
        } else return false;
    }

    /**
     * Magic function to set an attribute
     * @param string $property property name
     * @param string $value property value
     * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
     */
    public function __set($property, $value)
    {
        $this->prop[$property] = $value;
    }

    /**
     * Magic function to return the object as a JSON encoded string
     * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->prop);
    }
}