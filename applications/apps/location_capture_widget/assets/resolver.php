<?php
/*
Copyright 2009 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*******************************************************************************/ 
/*
 * ANDS Location Capture Widget - resolution service v0.1
 * 
 * Code Example ONLY - JSONP service
 * 
 * This code snippet emulates the behaviour of the ANDS
 * location resolution service powering the Location Capture
 * Widget. It will resolve place names against the Gazetteer 
 * (including partial matches) and return them in a JSON array
 * for use with the map widget.
 * 
 * Please DO NOT use this resolver service for general location 
 * resolution services - you should be using the Gazetteer directly.
 * Any unintended usage will result in your application being blocked. 
 * 
 * Requires libxml and cURL PHP extensions (and PHP5+)
 */
 
// Setup HTTP headers so jQuery/browser interprets as JSON
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');

// Some defaults
$jsonData['status'] = 'ERROR';
$jsonData['message'] = 'searchText must be defined';
$searchText = '';
$limit = 100;
$recCount = 0;
$feature = '';
$callback = "function";
$wfsg_host = 'https://services.ga.gov.au/gis/services/Australian_Gazetteer/MapServer/WfsServer';
// Parse parameters
if (isset($_GET['searchText'])) {
	$searchText = $_GET['searchText'];
	$jsonData['message'] = 'searchText' . $searchText;
}
// jQuery will interpolate the callback function as a random integer
if (isset($_GET['callback'])) {
	$callback = $_GET['callback'];
}
if (isset($_GET['limit'])) {
	//$limit = $_GET['limit']; // ignore this, for now...
	$jsonData['limit'] = $limit;
}
if (isset($_GET['feature'])) {
	$feature = $_GET['feature'];
	$jsonData['feature'] = $feature;
}

$debug = false;
if(isset($_GET['debug']))
{
	$debug = true;
}

// Design the XML query
if ($searchText) {
    $filterText = '<?xml version="1.0" encoding="UTF-8"?>'
        .'<GetFeature xmlns="http://www.opengis.net/wfs"'
        .' xmlns:ogc="http://www.opengis.net/ogc" service="WFS"'
        .' version="1.1.0" outputFormat="text/xml; subtype=gml/3.1.1" maxFeatures="'.$limit.'">'
        .' <Query typeName="Gazetteer_of_Australia" srsName="EPSG:4326">'
        .' <ogc:Filter>'
        .'<ogc:PropertyIsLike wildCard="*" singleChar="#" escapeChar="\\" matchCase="false">'
        .' <ogc:PropertyName>Australian_Gazetteer:Name</ogc:PropertyName>'
        .' <ogc:Literal>'.$searchText.'</ogc:Literal>'
        .'</ogc:PropertyIsLike>'
        .'</ogc:Filter>'
        .'</Query>'
        .'</GetFeature>';
}
if ($feature) {
    $filterText = '<?xml version="1.0" encoding="UTF-8"?>'
        .'<GetCapabilities xmlns="http://www.opengis.net/wfs" service="WFS" version="1.1.0" outputFormat="text/xml; subtype=gml/3.1.1"/>';

}

// Send the query (curl)
$jsonData = array();

try{
    $ch = curl_init($wfsg_host);
    if(!$ch)
    {
        $jsonData['status'] = 'ERROR';
        $jsonData['exception'] = curl_error($ch);
        $jsonData = json_encode($jsonData);
        echo $callback . "(" . $jsonData . ");";
        exit();
    }
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent:ARDC-LOCATION-CAPTURE-WIDGET', 'Content-Type: application/xml'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $filterText);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    if(!$data)
    {
        $jsonData['status'] = 'ERROR';
        $jsonData['exception'] = "NO DATA";
        $jsonData = json_encode($jsonData);
        echo $callback . "(" . $jsonData . ");";
        exit();
    }
    $gazetteerDoc = new DOMDocument();
    $result = $gazetteerDoc -> loadXML($data);
    if($result === false)
    {
        $jsonData['status'] = 'ERROR';
        $jsonData['exception'] = 'not well formed xml';
        $jsonData = json_encode($jsonData);
        echo $callback . "(" . $jsonData . ");";
        exit();
    }
    $gXPath = new DOMXpath($gazetteerDoc);
    $jsonData['status'] = 'OK';
}
catch (Exception $e)
{
    $jsonData['status'] = 'ERROR';
    $jsonData['exception'] = $e->getMessage();
    $jsonData = json_encode($jsonData);
    echo $callback . "(" . $jsonData . ");";
    exit();
}

if ($searchText) {

    // Resolve and order the results (we only want a few of the feature types to avoid a massive list)
    $featureMemberList = $gXPath -> evaluate('//Australian_Gazetteer:Gazetteer_of_Australia');
    // dd( $featureMemberList);
    $jsonData['items_count'] = ($featureMemberList -> length);
    $items = array();
    for ($i = 0; $i < $featureMemberList -> length; $i++) {
        $item = array();
        $featureMember = $featureMemberList -> item($i);
        $item['title'] = $gXPath -> evaluate('Australian_Gazetteer:Name', $featureMember) -> item(0) -> nodeValue;
        $item['coords'] = $gXPath -> evaluate('.//gml:pos', $featureMember) -> item(0) -> nodeValue;
        $item['lat'] = $gXPath -> evaluate('Australian_Gazetteer:Latitude', $featureMember) -> item(0) -> nodeValue;
        $item['lng'] = $gXPath -> evaluate('Australian_Gazetteer:Longitude', $featureMember) -> item(0) -> nodeValue;
        $item['types'][] = $gXPath -> evaluate('Australian_Gazetteer:Feature_code', $featureMember) -> item(0) -> nodeValue;
        $item['types'][] = $gXPath -> evaluate('Australian_Gazetteer:Classification', $featureMember) -> item(0) -> nodeValue;
        array_push($items, $item);
    }
    $jsonData['items'] = $items;

}

if ($feature) {
    $operations = $gXPath -> evaluate('//ows:Operation');
    if(sizeof($operations) === 0){
        $jsonData['status'] = 'ERROR';
        $jsonData['exception'] = "Australian Gazetteer services at ".$wfsg_host." is unavailable";
    }
}

// Send the response as JSONP
$jsonData = json_encode($jsonData);
echo $callback . "(" . $jsonData . ");";
?>