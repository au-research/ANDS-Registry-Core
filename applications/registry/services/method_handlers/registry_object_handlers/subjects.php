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
        if($this->ro['status'] == 'PUBLISHED')
        {// already resolved and stored in solr index
            if($this->index && isset($this->index['subject_value_resolved'])) {
                //subject_value_unresolved, subject_value_resolved, subject_type, subject_vocab_uri
                foreach($this->index['subject_value_unresolved'] as $key=>$sub) {
                    $result[] = array(
                        'subject' => $sub,
                        'resolved' => $this->index['subject_value_resolved'][$key],
                        'type' => $this->index['subject_type'][$key],
                        'vocab_uri' => isset($this->index['subject_vocab_uri'][$key]) ? $this->index['subject_vocab_uri'][$key] : ''
                    );
                }
            }
        }
        else{
            $subjects = $this->processSubjects();
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


    function processSubjects()
    {
        $subjectsResolved = array();
        $ci =& get_instance();
        $ci->load->library('vocab');
        $sxml = $this->xml;
        $sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
        $subjects = $sxml->xpath('//ro:subject');
        foreach ($subjects AS $subject)
        {
            $type = (string)$subject["type"];
            $value = (string)$subject;
            if(!array_key_exists($value, $subjectsResolved))
            {
                $resolvedValue = $ci->vocab->resolveSubject($value, $type);
                $subjectsResolved[$value] = array('type'=>$type, 'value'=>$value, 'resolved'=>$resolvedValue['value'], 'uri'=>$resolvedValue['about']);
                if($resolvedValue['uriprefix'] != 'non-resolvable')
                {
                    $broaderSubjects = $ci->vocab->getBroaderSubjects($resolvedValue['uriprefix'],$value);
                    foreach($broaderSubjects as $broaderSubject)
                    {
                        $subjectsResolved[$broaderSubject['notation']] = array('type'=>$type, 'value'=>$broaderSubject['notation'], 'resolved'=>$broaderSubject['value'], 'uri'=>$broaderSubject['about']);
                    }
                }
            }
        }
        return $subjectsResolved;
    }

}