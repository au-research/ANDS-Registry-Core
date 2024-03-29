<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
 * Spatial handler
 * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 * @return array list of spatial polygons, extents and center from SOLR index, provide an area sum
 */
class Spatial extends ROHandler {
	function handle() {
        $result = [];
            if($this->index && isset($this->index['spatial_coverage_polygons'])) {
                //spatial_coverage_extents, spatial_coverage_polygons, spatial_coverage_centres, spatial_coverage_area_sum
                foreach($this->index['spatial_coverage_polygons'] as $key=>$sub) {
                    $result[] = array(
                        'polygon' => $this->index['spatial_coverage_polygons'][$key],
                        'center' => $this->index['spatial_coverage_centres'][$key],
                    );
                }
            }else{
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
            if ($this->gXPath->evaluate("count(//ro:coverage/ro:spatial)")>0) {
                $query = "//ro:coverage/ro:spatial";
                $coverages = $this->gXPath->query($query);
                foreach ($coverages as $spatial) {
                    $type = $spatial->getAttribute('type');
                    if ($type != 'kmlPolyCoords' && $type != 'gmlKmlPolyCoords' && $type != 'iso19139dcmiBox' && $spatial->nodeValue != '') {
                        $result[] = array(
                            'type' => $type,
                            'value' => $spatial->nodeValue
                        );
                    }
                }
            }

        return $result;
	}
}