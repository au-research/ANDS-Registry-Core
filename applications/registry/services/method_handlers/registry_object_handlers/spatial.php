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
        if($this->ro->status == 'PUBLISHED')
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
        }
        else{
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