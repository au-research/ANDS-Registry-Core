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
        $suggestors = array(
            'subjects'=>array("boost=>5,'handler"=>'subjects'),
            'shared_text'=>array("boost=>5,'handler"=>'shared_text'),
            'related_object'=>array("boost=>5,'handler"=>'related_object')
        );

        //populate the pool with different suggestor
        $ci =& get_instance();
        $allSet = array();
        foreach ($suggestors as $key=>$val) {
            // Can't load a model on top of an existing field, so
            // create a different field name for each suggestor.
            $suggestor_field = 'ss_'.$key;
            $ci->load->model('registry_object/suggestors/'.$key.'_suggestor', $suggestor_field);
            $ci->$suggestor_field->set_ro($this->ro);
            if($boost = $ci->input->get($key))
                $suggestors[$key]['boost'] = floatval($boost);
            $result[$key] = $ci->$suggestor_field->suggest($val['boost']);
            $allSet = array_merge($result[$key], $allSet);
        }
        $fullSet = array();
        foreach ($suggestors as $key=>$val) {
            $count = count($result[$key]);
            if($count > 0){
                $step = floatval(100/$count);
                foreach($result[$key] as $suggestedRo){
                    if(array_key_exists($suggestedRo['id'],$fullSet)){
                        $fullSet[$suggestedRo['id']] +=  ($suggestors[$key]['boost'] * (100 - $step));
                    }else{

                        $fullSet[$suggestedRo['id']] =  ($suggestors[$key]['boost'] * (100 - $step));
                    }
                    $step += floatval(100/$count);
                }
            }
        }
        arsort($fullSet,SORT_NUMERIC);
        $subSet = array_slice($fullSet, 0, 5, true);

        foreach($subSet as $id=>$score)
        {
            $topFive[] = $this->getRecord($id, $allSet);
        }
        return  $topFive;
	}

    private function getRecord($id, $sourceArray){
        foreach($sourceArray as $record){
            if($record['id'] == $id)
                return $record;
        }
        return null;
    }
}