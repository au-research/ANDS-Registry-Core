<?php

/**
 * Class Relationships_Extension
 * An extension to provide relationships structure for Registry Object
 */
class Relationships_Extension extends ExtensionBase
{
    /**
     * Relationships_Extension constructor.
     *
     * @param $ro_pointer
     */
    public function __construct($ro_pointer)
    {
        parent::__construct($ro_pointer);
    }

    /**
     * Primary function to process all relationships
     *
     * @return array
     */
    public function addRelationships()
    {
        $this->_CI->load->model('registry/data_source/data_sources', 'ds');
        $this->_CI->load->model('registry/registry_object/registry_objects', 'ro');
        $ds = $this->_CI->ds->getByID($this->ro->data_source_id);
        $sxml = $this->ro->getSimpleXml();

        /* Explicit relationships */
        $sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
        $explicit_keys = array();
        // related objects (from past)
        $existing_relatinships = $this->getExistingRelationships();
        $new_relationships = array();
        foreach ($sxml->xpath('//ro:relatedObject') AS $related_object) {
            $explicit_keys[] = (string)$related_object->key;
            $relatedObjectKey = trim((string)$related_object->key);
            $class = $this->getRelatedObjectClass(trim($relatedObjectKey));
            foreach ($related_object->relation as $arelation) {
                $relationship = array(
                    "registry_object_id" => (string)$this->ro->id,
                    "related_object_key" => $relatedObjectKey,
                    "related_object_class" => (string)$class,
                    "relation_type" => (string)$arelation['type'],
                    "relation_description" => (string)$arelation->description,
                    "relation_url" => (string)$arelation->url,
                    "origin" => 'EXPLICIT'
                );
                $new_relationships[] = json_encode($relationship);
            }
        }

        if ($ds->create_primary_relationships == DB_TRUE) {
            if ($ds->primary_key_1 && $ds->primary_key_1 != $this->ro->key && !in_array($ds->primary_key_1,
                    $explicit_keys)
            ) {
                $explicit_keys[] = (string)$ds->primary_key_1;
                $relatedClass = (string)$this->getRelatedObjectClass((string)$ds->primary_key_1);
                $this_relationship = format_relationship($this->ro->class,
                    $ds->{$relatedClass . "_rel_1"}, PRIMARY_RELATIONSHIP, strtolower($this->ro->class));
                $relationship = array(
                    "registry_object_id" => (string)$this->ro->id,
                    "related_object_key" => (string)$ds->primary_key_1,
                    "related_object_class" => (string)$this->getRelatedObjectClass((string)$ds->primary_key_1),
                    "relation_type" => (string)$this_relationship,
                    "relation_description" => "",
                    "relation_url" => "",
                    "origin" => PRIMARY_RELATIONSHIP
                );
                $new_relationships[] = json_encode($relationship);
            }
            if ($ds->primary_key_2 && $ds->primary_key_2 != $this->ro->key && !in_array($ds->primary_key_2,
                    $explicit_keys)
            ) {
                $explicit_keys[] = (string)$ds->primary_key_2;
                $relatedClass = (string)$this->getRelatedObjectClass((string)$ds->primary_key_2);
                $this_relationship = format_relationship($this->ro->class,
                    $ds->{$relatedClass . "_rel_2"}, PRIMARY_RELATIONSHIP, strtolower($this->ro->class));
                $relationship = array(
                    "registry_object_id" => $this->ro->id,
                    "related_object_key" => (string)$ds->primary_key_2,
                    "related_object_class" => (string)$this->getRelatedObjectClass((string)$ds->primary_key_2),
                    "relation_type" => (string)$this_relationship,
                    "relation_description" => "",
                    "relation_url" => "",
                    "origin" => PRIMARY_RELATIONSHIP
                );
                $new_relationships[] = json_encode($relationship);
            }

            if ($ds->primary_key_1 && $ds->primary_key_1 == $this->ro->key && !in_array($ds->primary_key_1,
                    $explicit_keys)
            ) {
                $explicit_keys[] = (string)$ds->primary_key_1;
                $all = $this->_CI->ro->getIDsByDataSourceID($ds->id, true);
                foreach ($all as $r) {
                    if ($r->key != $this->ro->key) {
                        $this_relationship = $ds->{(string)$r->class . "_rel_1"};
                        $relationship = array(
                            "registry_object_id" => $this->ro->id,
                            "related_object_key" => (string)$r->key,
                            "related_object_class" => (string)$r->class,
                            "relation_type" => (string)$this_relationship,
                            "relation_description" => "",
                            "relation_url" => "",
                            "origin" => PRIMARY_RELATIONSHIP
                        );
                        $new_relationships[] = json_encode($relationship);
                    }
                }
            }

            if ($ds->primary_key_2 && $ds->primary_key_2 == $this->ro->key && !in_array($ds->primary_key_2,
                    $explicit_keys)
            ) {
                $explicit_keys[] = (string)$ds->primary_key_2;
                $all = $this->_CI->ro->getIDsByDataSourceID($ds->id, true);
                foreach ($all as $r) {
                    if ($r->key != $this->ro->key) {
                        $this_relationship = $ds->{(string)$r->class . "_rel_2"};
                        $relationship = array(
                            "registry_object_id" => $this->ro->id,
                            "related_object_key" => (string)$r->key,
                            "related_object_class" => (string)$r->class,
                            "relation_type" => (string)$this_relationship,
                            "relation_description" => "",
                            "relation_url" => "",
                            "origin" => PRIMARY_RELATIONSHIP
                        );
                        $new_relationships[] = json_encode($relationship);
                    }
                }
            }

        }

        // this variable determine the type of relatedInfo, allowing publication and website
        $processedTypesArray = array('collection', 'party', 'service', 'activity', 'publication', 'website');

        $this->db->where(array('registry_object_id' => $this->ro->id));
        $this->db->delete('registry_object_identifier_relationships');

        foreach ($sxml->xpath('//ro:relatedInfo') AS $related_info) {

            $related_info_type = (string)$related_info['type'];
            if (in_array($related_info_type, $processedTypesArray)) {
                $related_info_title = (string)$related_info->title;
                $relation_type = "";
                $related_description = "";
                $related_url = "";
                $relation_type_disp = "";
                $connections_preview_div = "";
                if ($related_info->relation) {
                    foreach ($related_info->relation as $r) {
                        $relation_type .= (string)$r['type'] . ", ";
                        $relation_type_disp .= format_relationship($this->ro->class, (string)$r['type'], 'IDENTIFIER',
                                $related_info_type) . ", ";

                        // Need to find out if $relateddescription is used here, it is unused so commented out
                        //$relateddescription = (string)$r->description."<br/>";

                        if ($related_url == '' && (string)$r->url != '') {
                            $related_url = (string)$r->url;
                        }
                        $urlStr = trim((string)$r->url);
                        if ((string)$r->description != '' && (string)$r->url != '') {
                            $connections_preview_div .= "<div class='description'><p>" . (string)$r->description . '<br/><a href="' . $urlStr . '">' . (string)$r->url . "</a></p></div>";
                        }
                    }
                    $relation_type = substr($relation_type, 0, strlen($relation_type) - 2);
                    $relation_type_disp = substr($relation_type_disp, 0, strlen($relation_type_disp) - 2);
                    //$connections_preview_div .= '<p>('.$relation_type.')</p>';
                }
                $identifiers_div = "";
                $identifier_count = 0;
                foreach ($related_info->identifier as $i) {
                    $identifiers_div .= $this->getResolvedLinkForIdentifier((string)$i['type'], trim((string)$i));
                    $identifier_count++;
                }
                $identifiers_div = "<h5>Identifier" . ($identifier_count > 1 ? 's' : '') . ": </h5>" . $identifiers_div;
                if ($related_info->notes) {
                    $connections_preview_div .= '<p>Notes: ' . (string)$related_info->notes . '</p>';
                }
                $imgUrl = asset_url('img/' . $related_info_type . '.png', 'base');
                $classImg = '<img class="icon-heading" src="' . $imgUrl . '" alt="' . $related_info_type . '" style="width:24px; float:right;">';
                $connections_preview_div = '<div class="previewItemHeader">' . $relation_type_disp . '</div>' . $classImg . '<h4>' . $related_info_title . '</h4><div class="post">' . $identifiers_div . "<br/>" . $connections_preview_div . '</div>';

                foreach ($related_info->identifier as $i) {
                    if (trim((string)$i) != '') {
                        $this->db->insert('registry_object_identifier_relationships',
                            array(
                                "registry_object_id" => $this->ro->id,
                                "related_object_identifier" => trim((string)$i),
                                "related_info_type" => $related_info_type,
                                "related_object_identifier_type" => (string)$i['type'],
                                "relation_type" => $relation_type,
                                "related_title" => $related_info_title,
                                "related_description" => $related_description,
                                "related_url" => $related_url,
                                "connections_preview_div" => $connections_preview_div,
                                "notes" => (string) $related_info->notes
                            )
                        );
                    }
                }
            }
        }

        // do we even used unchanged relationships, commenting it out because it is not used in the code
        // $unchanged_relationships = array_intersect($existing_relatinships, $new_relationships); // leave them

        $removed_relationships = array_diff($existing_relatinships, $new_relationships);
        $new_or_changed_relationships = array_diff($new_relationships, $existing_relatinships); //
        $inserted_keys = $this->insertNewRelationships($new_or_changed_relationships);

        $deleted_keys = $this->removeNonRenewedRelationships($removed_relationships);

        // Cache relationship metadata so that they can be retrieved from the metadata table instead of regenerate all the time
        $this->cacheRelationshipMetadata();

        if (is_array($inserted_keys) && is_array($deleted_keys)) {
            return array_merge($inserted_keys, $deleted_keys);
        } elseif (is_array($deleted_keys)) {
            return $deleted_keys;
        } elseif (is_array($inserted_keys)) {
            return $inserted_keys;
        } else {
            return array();
        }
    }

    /**
     * Cache the relationship metadata through the metadata extension
     */
    public function cacheRelationshipMetadata()
    {
        $this->ro->deleteMetadata("allRelationships");
        $allRelationships = $this->ro->getAllRelatedObjects();
        if ($allRelationships) {
            $this->ro->setMetadata("allRelationships", json_encode($allRelationships));
        }
    }

    /**
     * Returns the cached version of default getAllRelationships()
     *
     * @return mixed
     */
    public function getCachedRelationshipMetadata() {
        $cachedData = $this->ro->getMetadata("allRelationships");
        $decodedData = json_decode($cachedData, true);
        return $decodedData;
    }

    /**
     * Returns the cached version of default getConnections()
     *
     * @return mixed
     */
    public function getCachedConnectionsMetadata(){
        $cachedData = $this->ro->getMetadata("connections");
        $decodedData = json_decode($cachedData, true);
        return $decodedData;
    }

    public function getAllRelationships($includes = ['relatedObjects', 'grantsNetwork']
    )
    {
        $relationships = [];

        if (in_array('relatedObjects', $includes)) {
            $relationships = $this->ro->getAllRelatedObjects();
        }
        if (in_array('grantsNetwork', $includes)) {
            if (!in_array('relatedObjects', $includes)) {
                // generate relatedObjects only when only grantsNetwork is required
                $relationships = $this->ro->getAllRelatedObjects();
            }
            if ($this->ro->isValidGrantNetworkNode($relationships)) {
                $relationships = array_merge($relationships, $this->ro->_getGrantsNetworkConnections($relationships, false));
            }
        }

        return $relationships;
    }

    public function getAllAffectedRecordsID()
    {
        $affectedIDs = [];
        $relationships = $this->getAllRelationships();
        foreach ($relationships as $rel) {
            $id = $rel['registry_object_id'];
            if (!in_array($id, $affectedIDs)) {
                $affectedIDs[] = $id;
            }
        }
        return $affectedIDs;
    }

    /**
     * Returns an indexable relationship index for this record
     *
     * @param array $includes [relatedObjects|grantsNetwork]
     * @return mixed
     */
    public function getRelationshipIndex($includes = array('relatedObjects', 'grantsNetwork')){

        $relationships = array();

        if (in_array('relatedObjects', $includes)) {
            $relationships = $this->ro->getAllRelatedObjects();
        }
        if (in_array('grantsNetwork', $includes)) {
            if (!in_array('relatedObjects', $includes)) {
                // generate relatedObjects only when only grantsNetwork is required
                $relationships = $this->ro->getAllRelatedObjects();
            }
            if ($this->ro->isValidGrantNetworkNode($relationships)) {
                $relationships = array_merge($relationships, $this->ro->_getGrantsNetworkConnections($relationships, false));
            }
        }

        $docs = [];
        foreach ($relationships as $rel) {

            $doc = [
                'from_id' => $this->ro->id,
                'from_key' => $this->ro->key,
                'from_status' => $this->ro->status,
                'from_title' => $this->ro->title,
                'from_class' => strtolower(trim($this->ro->class)),
                'from_type' => strtolower(trim($this->ro->type)),
                'from_slug' => $this->ro->slug,
                'relation_notes' => isset($rel['relation_notes']) ? $rel['relation_notes'] : '',
                'to_id' => isset($rel['registry_object_id']) && $rel['registry_object_id']!='' ? $rel['registry_object_id'] : false,
                'to_key' => isset($rel['key']) ? $rel['key'] : false,
                'to_class' => isset($rel['class']) ? strtolower(trim($rel['class'])) : false,
                'to_type' => isset($rel['type']) ? strtolower(trim($rel['type'])) : false,
                'to_title' => isset($rel['title']) ? $rel['title'] : false,
                'to_slug' => isset($rel['slug']) ? $rel['slug'] : false
            ];

            // sanity check, for relation to an object, this shouldn't execute too much if the type is set
            // @todo remove once all types have already been set in the core attributes
            if ($doc['to_id'] && $doc['to_class'] && !$doc['to_type']) {
                $toRO = $this->_CI->ro->getByID($rel['registry_object_id']);
                if ($toRO) {
                    $doc['to_type'] = strtolower(trim($toRO->type));
                }
            }

            // getting the funders, only 1
            if (isset($rel['registry_object_id']) && $rel['class'] == 'activity') {
                if (($rel['relation_type'] == 'funds') || ($rel['relation_type']=='isFundedBy')) {
                    $doc['to_funder'] = $this->ro->title;
                } else {
                    $toRO = $this->_CI->ro->getByID($rel['registry_object_id']);
                    if ($toRO) {
                        $funders = $toRO->getFunders(false, false, false, array());
                        if ($funders && is_array($funders) && sizeof($funders) > 0) {
                            $doc['to_funder'] = $funders[0];
                        }
                    }
                }
            }

            // to_status, to_identifier, from_identifier

            // does notformat relation_type because it is handled at the front end
            $doc['relation'] = [$rel['relation_type']];
            $doc['relation_description'] = isset($rel['relation_description']) ? [$rel['relation_description']]: [];
            $doc['relation_url'] = isset($rel['relation_url']) ? [$rel['relation_url']]: [];
            $doc['relation_origin'] = isset($rel['origin']) ? [$rel['origin']]: [];

            // this relation needs a unique id
            $doc['id'] = $this->ro->key.$doc['to_key'];

            if (!$doc['to_id']) {
                // is not a real object, but something from relatedInfo
                $doc['relation_identifier_identifier'] = $rel['related_object_identifier'];
                $doc['relation_identifier_type'] = $rel['related_object_identifier_type'];
                $doc['relation_identifier_id'] = $rel['identifier_relation_id'];
                $doc['to_type'] = $rel['related_info_type'];
                //add uniqueness to the relation because to_key does not exist
                $doc['id'] .= $doc['relation_identifier_identifier'];
                $doc['relation_identifier_url'] = getIdentifierURL($rel['related_object_identifier_type'], $rel['related_object_identifier']);
            }

            // hash the id to make it easier to locate
            $doc['id'] = md5($doc['id']);

            if ($doc['to_id']) {
                //is a real object in our system
                if (array_key_exists($doc['to_id'], $docs)) {
                    $docs[$doc['to_id']]['relation'] = array_merge($docs[$doc['to_id']]['relation'], $doc['relation']);
                    $docs[$doc['to_id']]['relation_description'] = array_merge($docs[$doc['to_id']]['relation_description'], $doc['relation_description']);
                    $docs[$doc['to_id']]['relation_url'] = array_merge($docs[$doc['to_id']]['relation_url'], $doc['relation_url']);
                    $docs[$doc['to_id']]['relation_origin'] = array_merge($docs[$doc['to_id']]['relation_origin'], $doc['relation_origin']);
                } else {
                    $docs[$doc['to_id']] = $doc;
                }
            } else {
                // is relatedInfo data, add it anyway, accept duplicates if exist
                $docs[] = $doc;
            }

        }

        $docs = array_values($docs);
        return $docs;
    }

    /**
     * A fast way to return related objects
     * For reading, after the relationships have been indexed correctly
     *
     * @param array $byClass
     * @param array $byRelations
     * @param int   $limit
     * @return array
     */
    public function getRelatedObjectsIndex($byClass = array(), $byRelations = array(), $limit = 20000){
        $this->_CI->load->library('solr');
        $this->_CI->solr
            ->init()->setCore('relations');

        $this->_CI->solr->setOpt('fq','+from_id:'.$this->ro->id);

        $classFq = "";
        foreach ($byClass as $class) {
            $classFq .= ' to_class:("'.$class.'")';
        }
        $this->_CI->solr->setOpt('fq', $classFq);

        $relationFq = "";
        foreach ($byRelations as $relation) {
            $relationFq .= ' relation:('.$relation.')';
        }
        $this->_CI->solr->setOpt('fq', $classFq);

        $this->_CI->solr->setOpt('fq', $relationFq);

        $this->_CI->solr->setOpt('rows', $limit);

        $result = $this->_CI->solr->executeSearch(true);
        if ($result && array_key_exists('response', $result) && $result['response']['numFound'] > 0) {
            $response = $result['response']['docs'];
            $this->_CI->solr->init();
            $result = null;
            return $response;
        } else {
            return array();
        }
    }

    /**
     * Return a list of all registry object keys that this registry object is related to
     *
     * @return array
     */
    public function getRelatedKeys()
    {
        $related_keys = array();
        $result = $this->db->select('related_object_key')->get_where('registry_object_relationships',
            array('registry_object_id' => (string)$this->ro->id));
        foreach ($result->result_array() AS $row) {
            $related_keys[] = $row['related_object_key'];
        }
        return array_merge($related_keys, $this->getRelatedKeysByIdentifier());
    }

    /**
     * Return the class of the related object by key
     * Possibly could replace this with a generic getClass/getAttribute function from the registry objects model
     *
     * @param $related_key
     * @return null
     */
    public function getRelatedObjectClass($related_key)
    {
        $result = $this->db->select('class')->get_where('registry_objects', array('key' => $related_key));
        $class = null;
        if ($result->num_rows() > 0) {
            $record = $result->result_array();
            $record = array_shift($record);
            $result->free_result();
            $class = $record['class'];
        }
        return $class;
    }

    /**
     * Inserting new relationships
     * Should update existing relationships as well
     *
     * @param $new_or_changed_relationships
     * @return array
     */
    private function insertNewRelationships($new_or_changed_relationships)
    {
        $insertArray = array();
        $inserted_keys = array();
        foreach ($new_or_changed_relationships as $rel) {
            $thisRelated = json_decode($rel, true);
            //$insertArray[] = $thisRelated;
            if (is_array($thisRelated) && isset($thisRelated["related_object_key"])) {
                $inserted_keys[] = $thisRelated["related_object_key"];
                $insertArray[] = $thisRelated;
                $this->db->insert("registry_object_relationships", $thisRelated);
            }
        }
        // why doesn't insert_batch just work??
        //$this->db->insert_batch("registry_object_relationships", $insertArray);
        //print_r($insertArray);
        return $inserted_keys;
    }

    /**
     * Remove a set of relationships
     *
     * @param $removed_relationships
     * @return array
     */
    private function removeNonRenewedRelationships($removed_relationships)
    {
        $deleted_keys = array();
        foreach ($removed_relationships as $rel) {
            $deletedArray = json_decode($rel, true);
            if (is_array($deletedArray) && isset($deletedArray["related_object_key"])) {
                $deleted_keys[] = $deletedArray["related_object_key"];
                $this->db->where(array(
                        "registry_object_id" => $deletedArray["registry_object_id"],
                        "related_object_key" => $deletedArray["related_object_key"],
                        "related_object_class" => $deletedArray["related_object_class"],
                        "relation_type" => $deletedArray["relation_type"],
                        "relation_description" => $deletedArray["relation_description"],
                        "relation_url" => $deletedArray["relation_url"],
                        "origin" => $deletedArray["origin"]
                    )
                );
                $this->db->delete("registry_object_relationships");
            }
        }
        return $deleted_keys;
    }

    /**
     * Similar to getRelatedKeys
     * Returns a list of related object keys of the type of identifier relationship
     *
     * @return array
     */
    private function getRelatedKeysByIdentifier()
    {
        /* Step 1 - Straightforward link relationships */
        $related_keys = array();
        $this->db->select('r.key, ri.identifier')
            ->from('registry_object_identifier_relationships rir')
            ->join('registry_object_identifiers ri',
                'rir.related_object_identifier = ri.identifier and rir.related_object_identifier_type = ri.identifier_type',
                'left')
            ->join('registry_objects r', 'r.registry_object_id = ri.registry_object_id', 'left')
            ->where('rir.registry_object_id', $this->id);
        $query = $this->db->get();

        foreach ($query->result_array() AS $row) {
            if ($row['key'] != null) {
                $related_keys[] = $row['key'];
            }
        }
        return $related_keys;
    }


    /**
     * Returns the DB response of existing relationships
     *
     * @return array
     */
    private function getExistingRelationships()
    {
        $relationships = array();
        $this->db->select("registry_object_id, related_object_key, related_object_class, relation_type, relation_description, relation_url, origin");
        $this->db->where('registry_object_id', (string)$this->ro->id);
        $result = $this->db->get('registry_object_relationships');

        foreach ($result->result_array() AS $row) {
            $relationships[] = json_encode($row);
        }
        return $relationships;
    }

    /**
     * Returns a list of related objects with the related identifier type
     * Possibly a duplicate of getRelatedKeysByIdentifier()
     *
     * todo check with team and remove accordingly
     *
     * @return array
     */
    public function getRelatedObjectsByIdentifier()
    {
        $my_connections = array();
        $this->db->select('r.title, r.registry_object_id as related_id, r.class as class, rir.*')
            ->from('registry_object_identifier_relationships rir')
            ->join('registry_object_identifiers ri',
                'ri.identifier = rir.related_object_identifier and ri.identifier_type = rir.related_object_identifier_type',
                'left')
            ->join('registry_objects r', 'r.registry_object_id = ri.registry_object_id', 'left')
            ->where('rir.registry_object_id', (string)$this->ro->id)
            ->where('r.status', 'PUBLISHED');
        $query = $this->db->get();
        foreach ($query->result_array() AS $row) {
            $my_connections[] = $row;
        }

        return $my_connections;
    }

    /**
     * Returns a list of currently related object, with all of their detail
     * for PUBLISHED records
     * Possibly a duplicate of getRelatedKeys()
     *
     * todo check with team and remove accordingly
     *
     * @return array
     */
    public function getRelatedObjects()
    {
        $my_connections = array();
        $this->db->select('r.title, r.registry_object_id as related_id, r.class as class, rr.*')
            ->from('registry_object_relationships rr')
            ->join('registry_objects r', 'rr.related_object_key = r.key', 'left')
            ->where('rr.registry_object_id', (string)$this->ro->id)
            ->where('r.status', 'PUBLISHED');
        $query = $this->db->get();
        foreach ($query->result_array() AS $row) {
            $my_connections[] = $row;
        }

        return $my_connections;
    }

    /**
     * Returns a list of related classes,
     * calls getConnections which is a bad way of doing this
     * Couldn't find a reference usage outside this file that is useful
     *
     * todo check with team and remove accordingly
     *
     * @return array
     */
    public function getRelatedClasses()
    {
        /* Holy crap! Use getConnections to infer relationships to drafts and reverse links :-))) */
        $classes = array();
        $connections = $this->ro->getConnections(false);
        $connections = array_pop($connections);
        if (isset($connections['activity'])) {
            $classes[] = "Activity";
        }
        if (isset($connections['collection'])) {
            $classes[] = "Collection";
        }
        if (isset($connections['party']) || isset($connections['party_one']) || isset($connections['party_multi']) || isset($connections['contributor'])) {
            $classes[] = "Party";
        }
        if (isset($connections['service'])) {
            $classes[] = "Service";
        }

        return $classes;
    }

    /**
     * This function uses a single SQL query to identify the classes of linked records
     * that would be relevant to the quality string of any given record. This is significantly
     * more performant than using the getConnections() function (particularly as this is used
     * for every enriched record in a harvest).
     *
     * A replacement of getRelatedClasses called by getRelatedClassesString()
     *
     * todo think about removing SQL syntax and replace them with CI DB object ref
     *
     * @return array
     */
    function getRelatedClassesLite()
    {
        $classes = array();
        // Check for the distinct classes from the
        $explicit_and_reverse_links_query = 'SELECT DISTINCT rr.related_object_class AS `class` FROM registry_object_relationships rr WHERE rr.registry_object_id=' . (int)$this->ro->id . '
		UNION
		SELECT DISTINCT ro.class AS `class` FROM registry_object_relationships rr1 JOIN registry_objects ro ON rr1.related_object_key=ro.`key`  WHERE  rr1.related_object_key="' . $this->db->escape($this->ro->key) . '"
		UNION
		SELECT DISTINCT rir.related_info_type AS `class` FROM registry_object_identifier_relationships rir WHERE rir.registry_object_id=' . (int)$this->ro->id . ';';
        $class_relationships = $this->db->query($explicit_and_reverse_links_query);
        foreach ($class_relationships->result_array() as $class) {
            $classes[$class['class']] = ucfirst($class['class']);
        }

        // If we haven't found a party record yet, lets check for institutional pages (which we assume to always be parties)
        if (!isset($classes['party'])) {
            $institutional_pages_query = 'SELECT "party" AS `class` FROM institutional_pages WHERE `group` = "' . $this->db->escape($this->ro->group) . '"';
            $result = $this->db->query($institutional_pages_query);
            if ($result->num_rows() > 0) {
                $classes['party'] = 'Party';
            }
        }

        // That should be it!
        return $classes;
    }

    /**
     * Get a list of related class
     * Contains references to previous functions
     * Optimised should always be true
     * References found in transform.php and quality level checking
     *
     * todo check with team and modify accordingly
     *
     * @param bool|false $optimised
     * @return string
     */
    public function getRelatedClassesString($optimised = false)
    {
        if ($optimised) {
            $list = $this->getRelatedClassesLite();
        } else {
            $list = $this->getRelatedClasses();
        }
        return implode($list);
    }

    /**
     * Resolve Identifier to a link
     * References found in links.php
     *
     * todo check viability with team
     *
     * @param $type
     * @param $value
     * @return string
     */
    function getResolvedLinkForIdentifier($type, $value)
    {

        $urlValue = $value;
        switch ($type) {
            case 'handle':
                if (strpos($value, 'http://hdl.handle.net/') === false) {
                    $urlValue = 'http://hdl.handle.net/' . $value;
                }
                return 'Handle : <a class="identifier" href="' . $urlValue . '" title="Resolve this handle">' . $value . '<img class="identifier_logo" src="' . asset_url('assets/core/images/icons/handle_icon.png',
                    'base_path') . '" alt="Handle icon"></a><br/>';
                break;
            case 'purl':
                if (strpos($value, 'http://purl.org/') === false) {
                    $urlValue = 'http://purl.org/' . $value;
                }
                return 'PURL : <a class="identifier" href="' . $urlValue . '" title="Resolve this purl identifier">' . $value . '<img class="identifier_logo" src="' . asset_url('assets/core/images/icons/external_link.png',
                    'base_path') . '" alt="PURL icon"></a><br/>';
                break;
            case 'doi':
                if (strpos($value, 'http://dx.doi.org/') === false) {
                    $urlValue = 'http://dx.doi.org/' . $value;
                }
                return 'DOI: <a class="identifier" href="' . $urlValue . '" title="Resolve this DOI">' . $value . '<img class="identifier_logo" src="' . asset_url('assets/core/images/icons/doi_icon.png',
                    'base_path') . '" alt="DOI icon"></a><br/>';
                break;
            case 'uri':
                if (strpos($value, 'http://') === false && strpos($value, 'https://') === false) {
                    $urlValue = 'http://' . $value;
                }
                return 'URI : <a class="identifier" href="' . $urlValue . '" title="Resolve this URI">' . $value . '<img class="identifier_logo" src="' . asset_url('assets/core/images/icons/external_link.png',
                    'base_path') . '" alt="URI icon"></a><br/>';
                break;
            case 'urn':
                return 'URN : <a class="identifier" href="' . $value . '" title="Resolve this URN">' . $value . '<img class="identifier_logo" src="' . asset_url('assets/core/images/icons/external_link.png',
                    'base_path') . '" alt="URI icon"></a><br/>';
                break;
            case 'orcid':
                if (strpos($value, 'http://orcid.org/') === false) {
                    $urlValue = 'http://orcid.org/' . $value;
                }
                return 'ORCID: <a class="identifier" href="' . $urlValue . '" title="Resolve this ORCID">' . $value . '<img class="identifier_logo" src="' . asset_url('assets/core/images/icons/orcid_icon.png',
                    'base_path') . '" alt="ORCID icon"></a><br/>';
                break;
            case 'AU-ANL:PEAU':
                if (strpos($value, 'http://nla.gov.au/') === false) {
                    $urlValue = 'http://nla.gov.au/' . $value;
                }
                return 'NLA: <a class="identifier" href="' . $urlValue . '" title="View the record for this party in Trove">' . $value . '<img class="identifier_logo" src="' . asset_url('assets/core/images/icons/nla_icon.png',
                    'base_path') . '" alt="NLA icon"></a><br/>';
                break;
            case 'local':
                return "Local: " . $value . "<br/>";
                break;
            default:
                return strtoupper($type) . ": " . $value . "<br/>";
        }


    }
}
