<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
 * Suggested Datasets handler
 * returns a list of suggested datasets based on different type of pools
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @return array
 */
class Suggest extends ROHandler {
	function handle() {
		$result = array();

        //pools
                $suggestors = array('subjects', 'shared_text', 'related_object');

        //populate the pool with different suggestor
        $ci =& get_instance();

        foreach ($suggestors as $suggestor) {
            // Can't load a model on top of an existing field, so
            // create a different field name for each suggestor.
            $suggestor_field = 'ss_'.$suggestor;
            $ci->load->model('registry_object/suggestors/'.$suggestor.'_suggestor', $suggestor_field);
            $ci->$suggestor_field->set_ro($this->ro);

            $result[$suggestor] = $ci->$suggestor_field->suggest();
        }

        //finalize the pool
        //@todo need to compare scores and stuff
        $result['final'] = array();
        foreach($result as $source=>$pool) {
            foreach($pool as $ro) {
                if (!in_array_r($ro, $result['final'])){
                    array_push($result['final'], $ro);
                }
            }
        }
        // var_dump($all);

        return $result;
	}
}