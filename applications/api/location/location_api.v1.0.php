<?php
namespace ANDS\API;

use \Exception as Exception;

class Location_api
{

    private $client = null;

    public function handle($method = array())
    {
        $this->ci = &get_instance();
        // Some defaults
        $jsonData['status'] = 'ERROR';
        $jsonData['message'] = 'searchText must be defined';
        $searchText = '';
        $limit = 100;
        $recCount = 0;
        $feature = '';
        $callback = "function";
        // CC-2874 GA's new service
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
        // CC-2874 GA's new service
        if ($feature) {
            $filterText = '<?xml version="1.0" encoding="UTF-8"?>'
                .'<GetCapabilities xmlns="http://www.opengis.net/wfs" service="WFS" version="1.1.0" outputFormat="text/xml; subtype=gml/3.1.1"/>';

        }


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
            $gazetteerDoc = new \DOMDocument();
            $result = $gazetteerDoc -> loadXML($data);
            if($result === false)
            {
                $jsonData['status'] = 'ERROR';
                $jsonData['exception'] = 'not well formed xml';
                $jsonData = json_encode($jsonData);
                echo $callback . "(" . $jsonData . ");";
                exit();
            }
            $gXPath = new \DOMXpath($gazetteerDoc);
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
           // TODO: might add new sorting rules based on new Feature_codes
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
            // instead of getting features we just test if the services is available to enable or disable the widget accordingly
            $operations = $gXPath -> evaluate('//ows:Operation');
            if(sizeof($operations) === 0){
                $jsonData['status'] = 'ERROR';
                $jsonData['exception'] = "Australian Gazetteer services at ".$wfsg_host." is unavailable";
            }
        }

        // Send the response as JSONP
        $jsonData = json_encode($jsonData);
        return  $jsonData ;

    }

    public function __construct()
    {
        $this->ci = &get_instance();
        require_once BASE . 'vendor/autoload.php';
    }
}
