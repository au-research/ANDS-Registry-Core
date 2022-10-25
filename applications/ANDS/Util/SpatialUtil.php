<?php

namespace ANDS\Util;

class SpatialUtil
{
    /**
     * Check if the polygon is self intersecting
     *
     * @param $polygon
     * @return bool
     */
    public static function isSelfIntersectPolygon($polygon)
    {
        $NumPoints = count($polygon);

        //fix polygon array values
        $polygon = array_values($polygon);

        //polygon comes in as a list of string, convert to a list of array of vertices

        foreach ($polygon as &$v) {
            if(!is_array($v)){
                $pp = explode(' ', $v);
                $v = array($pp[0], $pp[1]);
            }
        }

        //remove the last vertices
        if ($polygon[$NumPoints - 1] == $polygon[0]) {
            unset($polygon[$NumPoints - 1]);
            $NumPoints--;
        }

        //fix polygon array values
        $polygon = array_values($polygon);

        for ($i = 0; $i < $NumPoints; ++$i) {
            if ($i < $NumPoints - 1) {
                for ($h = $i + 1; $h < $NumPoints; ++$h) {
                    // Do two vertices lie on top of one another?
                    if ($polygon[$i] == $polygon[$h]) {
                        return true;
                    }
                }
            }

            $j = ($i + 1) % $NumPoints;
            $iToj = vertice::sub($polygon[$j], $polygon[$i]);
            $iTojNormal = array($iToj[1], -$iToj[0]);
            // i is the first vertex and j is the second
            $startK = ($j + 1) % $NumPoints;
            $endK = ($i - 1 + $NumPoints) % $NumPoints;
            $endK += $startK < $endK ? 0 : $startK + 1;
            $k = $startK;
            $iTok = vertice::sub($polygon[$k], $polygon[$i]);
            $onLeftSide = (vertice::multiple($iTok, $iTojNormal) >= 0);
            $prevK = $polygon[$k];
            ++$k;
            for (; $k <= $endK; ++$k) {
                $modK = $k % $NumPoints;
                $iTok = vertice::sub($polygon[$modK], $polygon[$i]);
                if ($onLeftSide != vertice::multiple($iTok, $iTojNormal) >= 0) {
                    $prevKtoK = vertice::sub($polygon[$modK], $prevK);
                    $prevKtoKNormal = array($prevKtoK[1], -$prevKtoK[0]);
                    if ((vertice::multiple(vertice::sub($polygon[$i], $prevK),
                                $prevKtoKNormal) >= 0) != (vertice::multiple(vertice::sub($polygon[$j], $prevK),
                                $prevKtoKNormal) >= 0)
                    ) {
                        return true;
                    }
                }
                $onLeftSide = (vertice::multiple($iTok, $iTojNormal) > 0);
                $prevK = $polygon[$modK];
            }
        }
        return false;
    }

    /**
     * Calculate the spatial extents for a particular set of coordinates
     *
     * @param $coords
     * @return array
     */
    public static function calcExtent($coords)
    {
        $extents = array();
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
            $extents['extent'] = round($west, 5). " " . round($south, 5);
            $extents['west'] = $west;
            $extents['east'] = $east;
        } else {
            $extents['area'] = ($east - $west) * ($north - $south);
            $extents['center'] = (($east + $west) / 2) . "," . (($north + $south) / 2);
            $extents['extent'] = round($west, 5) . " " . round($south, 5) . " " . round($east, 5) . " " . round($north, 5) . " ";
            $extents['west'] = $west;
            $extents['east'] = $east;
        }
        return $extents;
    }

    /**
     * By-pass the zero coords line for polygon that passes the meridian
     * Primed for Google Map display
     *
     * @param $coords
     * @param $west
     * @param $east
     * @return string
     */
    public static function insertZeroBypassCoords($coords, $west, $east)
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

    /**
     * Obtain the Spatial Extent from Google Maps API
     *
     * @param $value
     * @return false
     */
    public static function getExtentFromGoogle($value)
    {
        // TODO Fix the Request Denied due to API Key
        // return false until this fix is in place
        return false;

        if (strpos($value, '-') !== false) {
            $url = "http://maps.google.com/maps/api/geocode/json?components=administrative_area:" . urlencode($value);
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

    public static function isValidWSEN($west = null, $south = null, $east = null, $north = null)
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

    public static function isValidKmlPolyCoords($coords)
    {
        $valid = false;
        $coordinates = preg_replace("/\s+/", " ", trim($coords));
        if (preg_match('/^(\-?\d+(\.\d+)?),(\-?\d+(\.\d+)?)( (\-?\d+(\.\d+)?),(\-?\d+(\.\d+)?))*$/', $coordinates)) {
            $valid = true;
        }
        return $valid;
    }

    public static function toGoogleMapStr($longLatString){
        $trans = array(" " => ",", ", " => " ");
        return strtr($longLatString, $trans);
    }
}

class vertice
{
    static function multiple($v1, $v2)
    {
        return $v1[0] * $v2[0] + $v1[1] * $v2[1];
    }

    static function add($v1, $v2)
    {
        return array($v1[0] + $v2[0], $v1[1] + $v2[1]);
    }

    static function sub($v1, $v2)
    {
        return array($v1[0] - $v2[0], $v1[1] - $v2[1]);
    }
}