<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
 * Identifiers handler
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @return array list of identifier from SOLR index
 */
class Identifiers extends ROHandler {
    function handle() {
        $result = array();
        if($this->ro->status == 'PUBLISHED')
        {
            if($this->index && isset($this->index['identifier_type'])) {
                //identifier_type, identifier_value
                foreach($this->index['identifier_type'] as $key=>$type) {
                    $result[] = array(
                        'type' => $type,
                        'value' => $this->index['identifier_value'][$key],
                        'identifier' => identifierResolution($this->index['identifier_value'][$key],$type)
                    );
                }
            }
        }
        else{
            $identifiers = $this->ro->getIdentifiers();
            foreach($identifiers as $identifier) {
                $result[] = array(
                    'type' => $identifier['identifier_type'],
                    'value' => $identifier['identifier'],
                    'identifier' => identifierResolution($identifier['identifier'],$identifier['identifier_type'])
                );
            }
        }

        return $result;
    }

}

