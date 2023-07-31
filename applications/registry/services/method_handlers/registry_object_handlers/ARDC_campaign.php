<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
 * Subjects handler
 * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 * @return array list of subjects from SOLR index
 */
class ARDC_campaign extends ROHandler {
    function handle() {
        $result = array();
        $HASS = ["image" => portal_url('assets/core/images/HASS-435pxAdSpace.gif'),
            "link"=> 'https://ardc.edu.au/campaign/accelerate-your-hass-and-indigenous-research/?utm_source=RDA&utm_medium=referral-HASS&utm_id=TRDC&utm_term=HASS&utm_content=rda-view',
            "anz_for" =>[13,16,17,19,20,21,22],
            "anz_for_2020" => [36,43,47,45]
        ];
        $People = ["image" => portal_url('assets/core/images/People-435pxAdSpace.gif'),
            "link"=> 'https://ardc.edu.au/campaign/accelerate-your-health-and-medical-research/?utm_source=RDA&utm_medium=referral-HM&utm_id=TRDC&utm_term=people&utm_content=rda-view',
            "anz_for" =>[11],
            "anz_for_2020" => [42,32]
        ];
        $Planet = ["image" => portal_url('assets/core/images/Planet-435pxAdSpace.gif'),
            "link"=> 'https://ardc.edu.au/campaign/accelerate-your-earth-and-environmental-sciences-research/?utm_source=RDA&utm_medium=referral-EE&utm_id=TRDC&utm_term=planet&utm_content=rda-view',
            "anz_for" =>['05','04'],
            "anz_for_2020" => [37,41]
        ];
        $GEN =  ["image" => portal_url('assets/core/images/GEN-435pxAdSpace.gif'),
            "link"=> 'https://ardc.edu.au/researcher/?utm_source=RDA&utm_medium=referral-G&utm_id=TRDC&utm_term=generic&utm_content=rda-view',
            "anz_for" =>[0],
            "anz_for_2020" => [0]
        ];
        if($this->ro->status == 'PUBLISHED')
        {
            if($this->index && isset($this->index['subject_value_resolved'])) {
                //subject_value_unresolved, subject_value_resolved, subject_type, subject_vocab_uri
                foreach($this->index['subject_value_unresolved'] as $key=>$sub) {
                    $sub = substr($sub, 0, 2);
                    if(($this->index['subject_type'][$key]=='anzsrc-for-2020' && in_array($sub, $HASS["anz_for_2020"]))
                        ||($this->index['subject_type'][$key]=='anzsrc-for' && in_array($sub, $HASS["anz_for"])) )
                        return $HASS;
                    if(($this->index['subject_type'][$key]=='anzsrc-for-2020' && in_array($sub, $People["anz_for_2020"]))
                        ||($this->index['subject_type'][$key]=='anzsrc-for' && in_array($sub, $People["anz_for"])) )
                        return $People;
                    if(($this->index['subject_type'][$key]=='anzsrc-for-2020' && in_array($sub, $Planet["anz_for_2020"]))
                        ||($this->index['subject_type'][$key]=='anzsrc-for' && in_array($sub, $Planet["anz_for"])) )
                        return $Planet;
                }
            }
        }
        else{
            $subjects = $this->ro->processSubjects();
            foreach($subjects as $subject) {
                $sub = substr($subject['value'], 0, 2);
                if(($subject['type']=='anzsrc-for-2020' && in_array($sub, $HASS["anz_for_2020"]))
                    ||($subject['type']=='anzsrc-for' && in_array($sub, $HASS["anz_for"])) )
                    return $HASS;
                if(($subject['type']=='anzsrc-for-2020' && in_array($sub, $People["anz_for_2020"]))
                    ||($subject['type']=='anzsrc-for' && in_array($sub, $People["anz_for"])) )
                    return $People;
                if(($subject['type']=='anzsrc-for-2020' && in_array($sub, $Planet["anz_for_2020"]))
                    ||($subject['type']=='anzsrc-for' && in_array($sub, $Planet["anz_for"])) )
                    return $Planet;
            }
        }
        return $GEN;
    }

}