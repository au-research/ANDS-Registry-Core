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
            }else{
                $coords = $this->getLocationAsLonLats();
                foreach($coords as $polygon) {
                    $extent = $this->calcExtent($polygon);
                    $result[] = array(
                        'extent' => $extent['extent'],
                        'polygon' => $polygon,
                        'center' => $extent['center'],
                    );
                }
            }

        return $result;

	}

    function getLocationAsLonLats()
    {
        $coords = array();
        $sxml = $this->xml;
        $sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
        foreach ($sxml->xpath('//ro:' . $this->ro_class . '/ro:coverage/ro:spatial') AS $spatial) {
            $type = $spatial["type"];
            $value = preg_replace('!\s+!', ' ', (string)$spatial);

            if ($this->isValidKmlPolyCoords($value) && ($type == 'kmlPolyCoords' || $type == 'gmlKmlPolyCoords')) {
                $coords[] = $value;
            } elseif ($type == 'iso19139dcmiBox') {
                $tok = strtok($value, ";");
                $north = null;
                $south = null;
                $west = null;
                $east = null;
                while ($tok !== false) {
                    $keyValue = explode("=", $tok);
                    if (strtolower(trim($keyValue[0])) == 'northlimit' && is_numeric($keyValue[1])) {
                        $north = floatval($keyValue[1]);
                    }
                    if (strtolower(trim($keyValue[0])) == 'southlimit' && is_numeric($keyValue[1])) {
                        $south = floatval($keyValue[1]);
                    }
                    if (strtolower(trim($keyValue[0])) == 'westlimit' && is_numeric($keyValue[1])) {
                        $west = floatval($keyValue[1]);
                    }
                    if (strtolower(trim($keyValue[0])) == 'eastlimit' && is_numeric($keyValue[1])) {
                        $east = floatval($keyValue[1]);
                    }
                    $tok = strtok(";");
                }

                if ($this->isValidWSEN($west, $south, $east, $north)) {
                    if ($north == $south && $east == $west) {
                        $coords[] = $east . "," . $north;
                    } else {
                        $coords[] = $east . "," . $north . " " . $east . "," . $south . " " . $west . "," . $south . " " . $west . "," . $north . " " . $east . "," . $north;
                    }
                }
            } elseif ($type == 'iso19139dcmiPoint' || $type == 'dcmiPoint') //"name=Tasman Sea, AU; east=160.0; north=-40.0"
            {
                $tok = strtok($value, ";");
                $north = null;
                $south = null;
                $west = null;
                $east = null;
                while ($tok !== false) {
                    $keyValue = explode("=", $tok);
                    if (strtolower(trim($keyValue[0])) == 'north' && is_numeric($keyValue[1])) {
                        $north = floatval($keyValue[1]);
                    }
                    if (strtolower(trim($keyValue[0])) == 'east' && is_numeric($keyValue[1])) {
                        $east = floatval($keyValue[1]);
                    }
                    $tok = strtok(";");
                }
                if ($this->isValidWSEN($east, $north, $east, $north)) {
                    $coords[] = $east . "," . $north;
                }
            } elseif ($type == 'iso31661' || $type == 'iso31662' || $type == 'iso3166') //"name=Tasman Sea, AU; east=160.0; north=-40.0"
            {

                $north = 90;
                $south = -90;
                $west = 180;
                $east = -180;

                $gCoords = $this->getExtentFromGoogle(trim($value), $type);

                if ($gCoords) {
                    $north = floatval($gCoords['north']);
                    $south = floatval($gCoords['south']);
                    $west = floatval($gCoords['west']);
                    $east = floatval($gCoords['east']);

                    if ($north == $south && $east == $west) {
                        $coords[] = $east . "," . $north;
                    } else {
                        $coords[] = $east . "," . $north . " " . $east . "," . $south . " " . $west . "," . $south . " " . $west . "," . $north . " " . $east . "," . $north;
                    }
                }
            }
        }

        return $coords;
    }


    function calcExtent($coords)
    {
        $extents = Array();
        $north = -90;
        $south = 90;
        $west = 180;
        $east = -180;
        $tok = strtok($coords, " ");
        while ($tok !== false) {
            $keyValue = explode(",", $tok);
            if (is_numeric($keyValue[1]) && is_numeric($keyValue[0])) {

                $lng = floatval($keyValue[0]);
                $lat = floatval($keyValue[1]);
                //$msg = $msg.'<br/>lat ' .$lat. ' long '.$lng;
                if ($lat > $north) {
                    $north = $lat;
                }
                if ($lat < $south) {
                    $south = $lat;
                }
                if ($lng < $west) {
                    $west = $lng;
                }
                if ($lng > $east) {
                    $east = $lng;
                }
            }
            $tok = strtok(" ");
        }

        if ($east > 180) {
            $east = 180;
        }
        if ($north > 90) {
            $north = 90;
        }
        if ($south < -90) {
            $south = -90;
        }
        if ($west < -180) {
            $west = -180;
        }

        if ($north == $south && $east == $west) {
            $extents['area'] = 0;
            $extents['center'] = $west . "," . $south;
            $extents['extent'] = $west . " " . $south;
            $extents['west'] = $west;
            $extents['east'] = $east;
        } else {
            $extents['area'] = ($east - $west) * ($north - $south);
            $extents['center'] = (($east + $west) / 2) . "," . (($north + $south) / 2);
            $extents['extent'] = $west . " " . $south . " " . $east . " " . $north . " ";
            $extents['west'] = $west;
            $extents['east'] = $east;
        }
        return $extents;
    }

    function insertZeroBypassCoords($coords, $west, $east)
    {
        $newCoords = "";
        $tok = strtok($coords, " ");
        $prevLat = null;
        $prevLng = null;
        $space = "";
        while ($tok !== false) {
            $keyValue = explode(",", $tok);
            if (is_numeric($keyValue[1]) && is_numeric($keyValue[0])) {
                $lng = floatval($keyValue[1]);
                $lat = floatval($keyValue[0]);
                //insert a coordinate at lat=0 to force drawing tool to go around the globe.
                if ($prevLat && (($prevLat == $west && $lat == $east) || ($prevLat == $east && $lat == $west))) {
                    $newCoords .= $space . "0," . $prevLng;
                }
                $newCoords .= $space . $tok;
                $space = " ";
                $prevLat = $lat;
                $prevLng = $lng;
            }
            $tok = strtok(" ");
        }
        return $newCoords;
    }

    function isValidKmlPolyCoords($coords)
    {
        $valid = false;
        $coordinates = preg_replace("/\s+/", " ", trim($coords));
        if (preg_match('/^(\-?\d+(\.\d+)?),(\-?\d+(\.\d+)?)( (\-?\d+(\.\d+)?),(\-?\d+(\.\d+)?))*$/', $coordinates)) {
            $valid = true;
        }
        return $valid;
    }

    function isValidWSEN($west = null, $south = null, $east = null, $north = null)
    {
        if ($west === null || $west < -180 || $west > 180) {
            return false;
        } else {
            if ($east === null || $east < -180 || $east > 180) {
                return false;
            } else {
                if ($north === null || $north < -90 || $north > 90) {
                    return false;
                } else {
                    if ($south === null || $south < -90 || $south > 90) {
                        return false;
                    } else {
                        return true;
                    }
                }
            }
        }
    }

    function getExtentFromGoogle($value, $type)
    {

        if ($type == 'iso31662' && strpos($value, '-') !== false) {
            $hypenPos = strpos($value, '-');
            $countryCode = substr($value, 0, $hypenPos);
            $administrativeArea = substr($value, $hypenPos + 1);
            $url = "http://maps.google.com/maps/api/geocode/json?components=country:" . urlencode($countryCode) . "|administrative_area:" . urlencode($administrativeArea);
        } else {
            $url = "http://maps.google.com/maps/api/geocode/json?components=country:" . urlencode($value);
        }
        $resp_json = curl_file_get_contents($url);
        $resp = json_decode($resp_json, true);
        $coords = array();
        if ($resp['status'] == 'OK') {
            if ($resp['results'][0]['geometry']['viewport']) {
                $coords['north'] = floatval($resp['results'][0]['geometry']['viewport']['northeast']['lat']);
                $coords['south'] = floatval($resp['results'][0]['geometry']['viewport']['southwest']['lat']);
                $coords['east'] = floatval($resp['results'][0]['geometry']['viewport']['northeast']['lng']);
                $coords['west'] = floatval($resp['results'][0]['geometry']['viewport']['southwest']['lng']);
            } elseif ($resp['results'][0]['geometry']['location']) {
                $coords['north'] = floatval($resp['results'][0]['geometry']['location']['lat']);
                $coords['south'] = floatval($resp['results'][0]['geometry']['location']['lat']);
                $coords['east'] = floatval($resp['results'][0]['geometry']['location']['lng']);
                $coords['west'] = floatval($resp['results'][0]['geometry']['location']['lng']);
            }
        } else {
            //print ("ERROR:    ".$resp['status']."<br/>");
            return false;
        }
        return $coords;
    }
}