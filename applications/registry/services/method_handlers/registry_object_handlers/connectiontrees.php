<?php
use ANDS\Registry\Providers\NestedConnectionsProvider;
use ANDS\Repository\EloquentConnectionsRepository;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
* Conectiontree handler
* @author Liz Woods <liz.woods@ands.org.au>
* @return array
*/
class Connectiontrees extends ROHandler {

    public function handle()
    {
        if ($this->ro->status == "DELETED")
            return [];

        $conn = new NestedConnectionsProvider(new EloquentConnectionsRepository);
        $links = $conn->getNestedCollectionsFromChild($this->ro->key, 5);

        $links = $links->format([
            'from_id' => 'registry_object_id',
            'from_title' => 'title',
            'from_class' => 'class',
            'from_slug' => 'slug',
            'relation_type' => 'relation_type',
            'from_status' => 'status',
            'children' => 'children'
        ], true);

        $links = [$links];

        return $links;
    }

	function handle_slow_and_memory_hungry() {

        if ($this->ro->status == "DELETED")
            return [];

        $ci =& get_instance();
        $ci->load->model('registry_object/registry_objects','thisro');
        $ci->load->model('services/connectiontree','connectiontree');
        $ro = $ci->thisro->getByID($this->ro->id);

        $trees = array();

        // CC-1417 Increase the max to 100
        // Refer to applications/registry/services/models/connectiontree.php@getChildren() for detail
        $ci->connectiontree->max_width = 100;

        if ($ro->class == 'collection') {
            $ancestors = $ci->connectiontree->getImmediateAncestors($ro, true);
            $depth = 4;
            if ($ancestors) {
                foreach ($ancestors AS $ancestor_element) {
                    if($ro->id != $ancestor_element['registry_object_id']){
                        $root_element_id = $ci->connectiontree->getRootAncestor($ci->thisro->getByID($ancestor_element['registry_object_id']), true);
                        $root_registry_object = $ci->thisro->getByID($root_element_id->id);

                        // Only generate the tree if this is a unique ancestor
                        if (!isset($ci->connectiontree->recursed_children[$root_registry_object->id])) {
                            $trees[] = $ci->connectiontree->get($root_registry_object, $depth, true, $ro->id);
                        }
                    }
                }
            } else {
                $trees[] = $ci->connectiontree->get($ro, $depth, true);
            }
        }
        return $trees;
	}
}