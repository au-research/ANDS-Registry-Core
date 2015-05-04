<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
 * Spatial handler
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @return array list of spatial polygons, extents and center from SOLR index, provide an area sum
 */
class Spatial extends ROHandler {
	function handle() {
		$result = array();
        if($this->ro->status == 'PUBLISHED' || $this->ro->status == 'DRAFT' )
        {
            if($this->index && isset($this->index['spatial_coverage_extents'])) {
                //spatial_coverage_extents, spatial_coverage_polygons, spatial_coverage_centres, spatial_coverage_area_sum
                foreach($this->index['spatial_coverage_extents'] as $key=>$sub) {
                    $result[] = array(
                        'extent' => $sub,
                        'polygon' => $this->index['spatial_coverage_polygons'][$key],
                        'center' => $this->index['spatial_coverage_centres'][$key],
                    );
                    if(isset($this->index['spatial_coverage_area_sum'])) $result['area_sum'] = $this->index['spatial_coverage_area_sum'];
                }
            }

            if ($this->gXPath->evaluate("count(//ro:coverage/ro:spatial)")>0) {
                $query = "//ro:coverage/ro:spatial";
                $coverages = $this->gXPath->query($query);
                foreach($coverages as $spatial){
                    $type = $spatial->getAttribute('type');
                    if($type!='kmlPolyCoords' && $type !='gmlKmlPolyCoords' && $type !='iso19139dcmiBox'){
                        $result[] = array (
                            'type' => $type,
                            'value' => $spatial->nodeValue
                        );
                    }
                    elseif($type=='kmlPolyCoords' || $type =='gmlKmlPolyCoords'){
                        if(!$this->ro->isValidKmlPolyCoords($spatial->nodeValue)){
                            $result[] = array (
                                'type' => $type,
                                'value' => $spatial->nodeValue
                            );
                        }

                    }
                    elseif($type=='iso19139dcmiBox'){
                        $north = null;
                        $south = null;
                        $west  = null;
                        $east  = null;
                        $tok = strtok($spatial->nodeValue, ";");
                        while ($tok !== FALSE)
                        {
                            $keyValue = explode("=",$tok);
                            if(strtolower(trim($keyValue[0])) == 'northlimit' && is_numeric($keyValue[1]))
                            {
                                $north = floatval($keyValue[1]);
                            }
                            if(strtolower(trim($keyValue[0])) == 'southlimit' && is_numeric($keyValue[1]))
                            {
                                $south = floatval($keyValue[1]);
                            }
                            if(strtolower(trim($keyValue[0])) == 'westlimit' && is_numeric($keyValue[1]))
                            {
                                $west = floatval($keyValue[1]);
                            }
                            if(strtolower(trim($keyValue[0])) == 'eastlimit' && is_numeric($keyValue[1]))
                            {
                                $east = floatval($keyValue[1]);
                            }
                            $tok = strtok(";");
                        }
                        if(!$north&&!$south&&!$east&&!$west)
                        $result[] = array (
                            'type' => $type,
                            'value' => $spatial->nodeValue
                        );

                    }

                }
            }
            if ($this->gXPath->evaluate("count(//ro:location/ro:spatial)")>0) {
                $query = "//ro:location/ro:spatial";
                $coverages = $this->gXPath->query($query);
                foreach($coverages as $spatial){
                    $type = $spatial->getAttribute('type');
                    if($type!='kmlPolyCoords' && $type !='gmlKmlPolyCoords' && $type !='iso19139dcmiBox'){
                        $result[] = array (
                            'type' => $type,
                            'value' => $spatial->nodeValue
                        );
                    }
                    elseif($type=='kmlPolyCoords' || $type =='gmlKmlPolyCoords'){
                        if(!$this->ro->isValidKmlPolyCoords($spatial->nodeValue)){
                            $result[] = array (
                                'type' => $type,
                                'value' => $spatial->nodeValue
                            );
                        }
                    }
                    elseif($type=='iso19139dcmiBox'){
                        $north = null;
                        $south = null;
                        $west  = null;
                        $east  = null;
                        $tok = strtok($spatial->nodeValue, ";");
                        while ($tok !== FALSE)
                        {
                            $keyValue = explode("=",$tok);
                            if(strtolower(trim($keyValue[0])) == 'northlimit' && is_numeric($keyValue[1]))
                            {
                                $north = floatval($keyValue[1]);
                            }
                            if(strtolower(trim($keyValue[0])) == 'southlimit' && is_numeric($keyValue[1]))
                            {
                                $south = floatval($keyValue[1]);
                            }
                            if(strtolower(trim($keyValue[0])) == 'westlimit' && is_numeric($keyValue[1]))
                            {
                                $west = floatval($keyValue[1]);
                            }
                            if(strtolower(trim($keyValue[0])) == 'eastlimit' && is_numeric($keyValue[1]))
                            {
                                $east = floatval($keyValue[1]);
                            }
                            $tok = strtok(";");
                        }
                        if(!$north&&!$south&&!$east&&!$west)
                            $result[] = array (
                                'type' => $type,
                                'value' => $spatial->nodeValue
                            );
                    }
                }
            }

            
        }
        else{
            if ($this->gXPath->evaluate("count(//ro:location/ro:spatial)")>0) {
                $query = "//ro:location/ro:spatial";
                $coverages = $this->gXPath->query($query);
                foreach($coverages as $spatial){
                    $type = $spatial->getAttribute('type');
                    if($type!='kmlPolyCoords' && $type !='gmlKmlPolyCoords' && $type !='iso19139dcmiBox'){
                        $result[] = array (
                            'type' => $type,
                            'value' => $spatial->nodeValue
                        );
                    }
                }
            }
            if ($this->gXPath->evaluate("count(//ro:coverage/ro:spatial)")>0) {
                $query = "//ro:coverage/ro:spatial";
                $coverages = $this->gXPath->query($query);
                foreach($coverages as $spatial){
                    $type = $spatial->getAttribute('type');
                    if($type!='kmlPolyCoords' && $type !='gmlKmlPolyCoords' && $type !='iso19139dcmiBox'){
                        $result[] = array (
                            'type' => $type,
                            'value' => $spatial->nodeValue
                        );
                    }
                }
            }
            $coords = $this->ro->getLocationAsLonLats();
            foreach($coords as $polygon) {
                $extent = $this->ro->calcExtent($polygon);
                $result[] = array(
                    'extent' => $extent['extent'],
                    'polygon' => $polygon,
                    'center' => $extent['center'],
                );
            }
        }

        return $result;
	}
}