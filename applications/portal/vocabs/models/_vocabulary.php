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
        $single_values = array('id', 'title', 'slug', 'pool_party_id', 'status');
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

        $json = $this->processSubjects($data, $json);

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
        $json['widgetable']=false;
        if ($current_version) {
            $concept_list_path = isset($current_version['concepts_list']) ? $current_version['concepts_list'] : false;
            if($concept_list_path){
                $content = @file_get_contents($concept_list_path);
                $content = json_decode($content, true);
                if ($content) {
                    foreach ($content as $concept) {
                        if (isset($concept['prefLabel'])) {
                            $json['concept'][] = $concept['prefLabel'];
                        }
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
            /* The following fails, if there are no access points for
               the current version. There really should be access points, but
               e.g., if they were supposed to be added by the Toolkit,
               and (for whatever reason) the Toolkit failed to add
               them, then $current_version['version_access_points']
               has the Boolean value false, i.e., it is not an array!
               (See implementations of current_version() and getAccessPoints()
               to see this.) So you get an error:
               "Invalid argument supplied for foreach() on line ..."
               If, in future, we want to be somewhat more defensive,
               put this sort of thing as a wrapper around the foreach:
                if (array_key_exists('version_access_points', $current_version)
                  && is_array($current_version['version_access_points'])) {
               Or, as an alternative, change the way getAccessPoints() works
               so that it doesn't return an array on success and a Boolean
               on failure!
            */
            if (array_key_exists('version_access_points', $current_version)
                && is_array($current_version['version_access_points'])) {
                foreach($current_version['version_access_points'] as $ap)
                {
                    if($ap['type'] == 'sissvoc'){
                        $url = json_decode($ap['portal_data'])->uri;
                        $json['sissvoc_end_point'] = $url;
                        $json['widgetable']=true;
                    }
                }
            }

        }

        return $json;
    }

    /**
     * Process each subject in the data record to produce the subject
     * component of the Solr document for this vocabulary.
     * Find resolvable subjects and include their broader subjects
     * as well in the Solr index.
     * TODO: support getting broader subjects for vocabs that don't
     * have notations (e.g., GCMD). Preliminary work already done
     * in engine/libraries/Vocab.php: new (but not yet completed)
     * functions resolveSubjectByUri() and getBroaderSubjectsByUri().
     * @return The subjects in JSON format for inclusion in the
     *         Solr document.
     */
    function processSubjects($data, $json)
    {

        $subjectsResolved = array();

        if (isset($data['subjects'])) {
            $ci =& get_instance();
            $ci->load->library('vocab');

            foreach ($data['subjects'] as $subject) {
                $value = "";
                $notation = "";
                $iri = "";
                $type = $subject['subject_source'];
                if(isset($subject['subject']))
                    $value = $subject['subject'];
                if(isset($subject['subject_label']))
                    $value = $subject['subject_label'];
                if(isset($subject['subject_iri']))
                    $iri = $subject['subject_iri'];

                if(isset($subject['subject_notation'])
                   && $subject['subject_notation'] != ""){
                    $notation = $subject['subject_notation'];
                    // this type check also occurs in getSubjects it is included to ensure that after release 42 the
                    // anzsrc-xxx-2020 vocabs can be used and resolved after initially being input with a type of anzsrc-xxx
                    if(substr($notation,0,2) > '29' && $type == 'anzsrc-for'){
                        $type = 'anzsrc-for-2020';
                    }
                    if(substr($notation,0,2) < '80' && $type == 'anzsrc-seo'){
                        $type = 'anzsrc-seo-2020';
                    }
                }
                else{
                    try{
                        $subject = $ci->vocab->resolveLabel($value, $type);
                        if(isset($subject['notation'])){
                            $notation = $subject['notation'];
                        }
                        else{
                            $json['subject_types'][] = $type;
                            $json['subject_labels'][] = $value;
                            $json['subject_notations'][] = '';
                            $json['subject_iris'][] = $iri;
                                // 'Label not defined in ' . $type;
                        }
                    }
                    catch(Exception $e){
                        $json['subject_types'][] = $type;
                        $json['subject_labels'][] = $value;
                        $json['subject_notations'][] = '';
                        $json['subject_iris'][] = $iri;
                        // echo( $e->getMessage());
                    }
                }

                if($notation != ""
                   && !array_key_exists($notation, $subjectsResolved))
                {

                    $resolvedValue = $ci->vocab->resolveSubject($notation,
                                                                $type);
                    $json['subject_types'][] = $type;
                    $json['subject_labels'][] = $value;
                    $json['subject_notations'][] = $resolvedValue['notation'];
                    $json['subject_iris'][] = $resolvedValue['about'];
                    $subjectsResolved[$resolvedValue['notation']] = $value;
                    if($resolvedValue['uriprefix'] != 'non-resolvable')
                    {
                        $broaderSubjects =
                            $ci->vocab->getBroaderSubjects(
                                $resolvedValue['uriprefix'], $notation);
                        foreach($broaderSubjects as $broaderSubject)
                        {
                            if(!array_key_exists($broaderSubject['notation'],
                                                 $subjectsResolved)){
                                $json['subject_types'][] = $type;
                                $json['subject_labels'][] =
                                    $broaderSubject['value'];
                                $json['subject_notations'][] =
                                    $broaderSubject['notation'];
                                $json['subject_iris'][] =
                                    $broaderSubject['about'];
                            }
                        }
                    }
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

        if ($allAccessPoints) {
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
                        if (isset($source->source) && $source->source == 'user') {
                            $delete = true;
                        }
                }
                if ($delete) {
                    $db->delete('access_points', array(
                        'id' => $id
                    ));
                }
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
        $sissvoc_end_point = "";
        if ($current_version) {
            $concepts_tree_path = isset($current_version['concepts_tree']) ? $current_version['concepts_tree'] : false;

            foreach($current_version['version_access_points'] as $ap)
            {
                if($ap['type'] == 'sissvoc'){
                    $sissvoc_end_point = json_decode($ap['portal_data'])->uri;
                }
            }




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
                $this->buildTree($tree_data, $sissvoc_end_point);

                return $tree_data;
            }
        } else {
            //no current version
            return false;
        }
    }

    /**
     * Helper function for @display_tree.
     * Recursively called to build the tree when there are child concepts.
     * See Toolkit class ...provider.transform.JsonTreeTransformProvider
     * for a description of the structure of the input tree data.
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @param  array $treeData
     */
    private function buildTree(&$treeData, $sissvoc_end_point = '')
    {
        if (is_array($treeData)) {
            foreach ($treeData as &$concept) {
                $uri = $concept['iri'];
                $title = isset($concept['prefLabel']) ?
                       $concept['prefLabel'] : 'No Title';
                $tipText = '<p><b>'. $title . '<br/>IRI: </b>'. $uri;

                if(isset($concept['definition']))
                    $tipText .= '<br/><b>Definition: </b>'
                             . $concept['definition'];
                if(isset($concept['notation']))
                    $tipText .= '<br/><b>Notation: </b>' .$concept['notation'];
                if($sissvoc_end_point != '')
                    $tipText .= '<br/><a class="pull-right" target="_blank" href="' .$sissvoc_end_point . '/resource?uri=' . $uri . '">View as linked data</a>';
                $concept['value'] = $title;
                $concept['tip'] = $tipText. '</p>';
                if (isset($concept['narrower'])) {
                    $this->buildTree($concept['narrower'],
                                     $sissvoc_end_point);
                    $concept['num_child'] = sizeof($concept['narrower']);
                } else {
                    $concept['num_child'] = 0;
                }
            }
        }
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
                // Legacy: don't use any release_date value from such a column;
                // release_date must come from the data column.
                unset($version['release_date']);
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

            $slug = $this->makeSlug($this->prop['title']);

            //CC-1460
            //check if there's an existing vocab with the same slug in published state
            $result = $db->get_where('vocabularies', array('slug'=> $slug, 'status' => 'published'));

            isset($this->prop['from_vocab_id']) ? $draft_base = $this->prop['from_vocab_id'] :$draft_base = 0 ;

            if ($result->num_rows() > 0) {
                $published_vocab = $result->first_row();
                if($published_vocab->id!=$draft_base){
                    throw new Exception('A vocabulary with the specified title already exists. Please specify a unique title.');
                    return false;
                }
            }

            //check if there's an existing vocab with the same slug in draft state
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
                // Copy over the import log so the UI can use it!
                $new_vocab->import_log = $this->import_log;
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
                        'vocab_id' => $this->prop['id'],
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
                        'vocab_id' => $this->prop['id'],
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

            if (($this->determineAction($version, 'import')) && ($this->isPoolParty())) {
                $import_task = array('type' => 'TRANSFORM', 'provider_type' => 'SesameInsertMetadata');
                array_push($task_array, $import_task);
            }

            if ($this->determineAction($version, 'publish')) {
                $publish_task = array('type' => 'PUBLISH', 'provider_type' => 'SISSVoc');
                array_push($task_array, $publish_task);
            }

            // If both import and publish are done, we also
            // add the resources to the global IRI resolver.
            if ($this->determineAction($version, 'import') &&
                $this->determineAction($version, 'publish')) {
                $publish_task = array('type' => 'TRANSFORM',
                                      'provider_type' => 'ResourceMap');
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

                // CC-1778 CC-1787
                // Provide an alert if there will not be a browse tree.
                if (isset($content['concepts_tree_not_provided'])) {
                    $this->log('Alert: Either a polyhierarchy or cycle ' .
                               'was detected in the vocabulary data.<br />' .
                               'The concept browse tree will not be ' .
                               'visible for this vocabulary.<br />' .
                               'For more information, please see ' .
                               '<a target="_blank" ' .
                               ' href="https://documentation.ardc.edu.au/' .
                               'display/DOC/Support+for+concept+browsing+' .
                               'within+the+portal">' .
                               'Portal concept browsing</a>.');
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