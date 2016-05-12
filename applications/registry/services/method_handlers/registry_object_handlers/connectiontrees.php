<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
* Conectiontree handler
* @author Liz Woods <liz.woods@ands.org.au>
* @return array
*/
class Connectiontrees extends ROHandler {
	function handle() {
        $ci =& get_instance();
        $ci->load->model('registry/registry_object/registry_objects','thisro');
        $ci->load->model('services/connectiontree','connectiontree');
        $ro = $ci->thisro->getByID($this->index['id']);

        $trees = array();

        // CC-1417 Increase the max to 100
        // Refer to applications/registry/services/models/connectiontree.php@getChildren() for detail
        $ci->connectiontree->max_width = 100;

        if ($ro->class == 'collection') {
            $ancestors = $ci->connectiontree->getImmediateAncestors($ro, true);
            $depth = 5;
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