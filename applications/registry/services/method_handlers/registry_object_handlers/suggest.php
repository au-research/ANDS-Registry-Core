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
        $result['message'] = 'No Suggested Collection was found';
        //pools
        $suggestors = array(
           'subjects'=>array('boost'=>0.3,'handler'=>'subjects'),
           'shared_text'=>array('boost'=>0.3,'handler'=>'shared_text'),
           'related_object'=>array('boost'=>0.1,'handler'=>'related_object'),
           'temporal_coverage'=>array('boost'=>0.1,'handler'=>'temporal_coverage'),
           'spatial_coverage'=>array('boost'=>0.1,'handler'=>'spatial_coverage'),
           'tags'=>array('boost'=>0.1,'handler'=>'tags')
        );
        
        //populate the pool with the different suggestors
        $ci =& get_instance();

        // Merged results of the various suggestors
        $allSet = array();

        // Get results from each suggestor
        foreach ($suggestors as $key=>$val) {
            // Can't load a model on top of an existing field, so
            // create a different field name for each suggestor.
            $suggestor_field = 'ss_'.$key;
            $ci->load->model(
                'registry_object/suggestors/'.$key.'_suggestor',
                $suggestor_field
            );
            $ci->$suggestor_field->set_ro($this->ro);
            // Override boost parameters from request URL.
            $boost = $ci->input->get($key);
            if ($boost){
                $suggestors[$key]['boost'] = floatval($boost);
            }
            $result[$key] = $ci->$suggestor_field->suggest();

            if(is_array($result[$key])){
                $allSet = array_merge($result[$key], $allSet);
            }

        }
        
        // Normalize rankings and apply boosting

        $fullSet = array();
        foreach ($suggestors as $key=>$val) {
            if(count($result[$key]) > 0){
                foreach($result[$key] as $suggestedRo){
                    if(array_key_exists($suggestedRo['id'],$fullSet)){
                        $fullSet[$suggestedRo['id']] +=
                            ($suggestors[$key]['boost'] * floatval($suggestedRo['score']));
                    }else{
                        $fullSet[$suggestedRo['id']] =
                            ($suggestors[$key]['boost'] * $suggestedRo['score']);
                    }
                }
            }
        }

        // (Descending) sort by value: the score of each record
        arsort($fullSet,SORT_NUMERIC);

        // Get the top five
        $limit = $ci->input->get('limit');
        if (!$limit)
            $limit = 5;
        $subSet = array_slice($fullSet, 0, $limit, true);

        // We have only the ID and score, so now get the records
        $result['final'] = array();
        foreach($subSet as $id=>$score)
        {
            $result['final'][] = $this->getRecord($id, $score, $allSet);
        }
        $result['message'] = sizeof($fullSet). " Suggested Collections were found";
        return $result;
	}


    // Get an individual record out of the merged suggestor results
    private function getRecord($id, $score, $sourceArray){
        foreach($sourceArray as $record){
            if($record['id'] == $id){
                $record['score'] = $score;
                return $record;
            }

        }
        return null;
    }
}