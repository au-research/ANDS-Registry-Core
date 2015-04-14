<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
 * Subjects handler
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @return array list of subjects from SOLR index
 */
class Subjects extends ROHandler {
    function handle() {
        $result = array();
        if($this->ro->status == 'PUBLISHED')
        {
            if($this->index && isset($this->index['subject_value_resolved'])) {
                //subject_value_unresolved, subject_value_resolved, subject_type, subject_vocab_uri
                foreach($this->index['subject_value_unresolved'] as $key=>$sub) {
                    $result[] = array(
                        'subject' => $sub,
                        'resolved' => titleCase($this->index['subject_value_resolved'][$key]),
                        'type' => $this->index['subject_type'][$key],
                        'vocab_uri' => $this->index['subject_vocab_uri'][$key],
                    );
                }
            }
        }
        else{
            $subjects = $this->ro->processSubjects();
            foreach($subjects as $subject) {
                $result[] = array(
                    'subject' => $subject['value'],
                    'resolved' => $subject['resolved'],
                    'type' => $subject['type'],
                    'vocab_uri' => $subject['uri'],
                );
            }
        }

        return $result;
    }

}