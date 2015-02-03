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
        $suggestors = array('subjects');

        //populate the pool with different suggestor
        $ci =& get_instance();

        foreach ($suggestors as $suggestor) {
            $ci->load->model('registry_object/suggestors/'.$suggestor.'_suggestor', 'ss');
            $ci->ss->set_ro($this->ro);
            $result[$suggestor] = $ci->ss->suggest();
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