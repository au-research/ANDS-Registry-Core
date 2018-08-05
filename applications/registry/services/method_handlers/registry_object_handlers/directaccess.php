<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');

/**
 * Download Direct Access handler
 * @author Liz Woods <liz.woods@ands.org.au>
 * @return array
 */
class Directaccess extends ROHandler
{
    function handle()
    {
        $download = array();
        if ($this->ro->class != 'collection' && $this->ro->class != 'service') {
            return array();
        }
        $query = '';
        $relationshipTypeArray = ['isPresentedBy', 'supports'];
        $classArray = [];

        // @todo don't get viaService if the record is a service
        $services = $this->ro->getRelatedObjectsIndex($classArray, $relationshipTypeArray);

        foreach ($services as &$service) {
            if (isset($service['relation_url'])
                && sizeof($service['relation_url']) > 0
            ) {
                $relationUrl = $service['relation_url'][0];
                $relationDescription = $service['to_title'];
                if (isset($service['relation_description'])
                    && sizeof($service['relation_description']) > 0
                    && $service['relation_description'][0] != '') {
                    $relationDescription = $service['relation_description'][0];
                }
                $download[] = Array(
                    'access_type' => 'viaService',
                    'contact_type' => 'url',
                    'access_value' => $relationUrl,
                    'title' => $relationDescription,
                    'mediaType' => '',
                    'byteSize' => '',
                    'notes' => 'Visit Service'
                );
            }
        }

        if ($this->xml && $this->gXPath->evaluate("count(//ro:location/ro:address/ro:electronic)") > 0) {
            if ($this->gXPath->evaluate("count(//ro:location/ro:address/ro:electronic[@type='url' or @type='uri'])") > 0) {
                $query = "//ro:location/ro:address/ro:electronic[@type='url' or @type='uri']";
            }

            if ($query != '') {
                $locations = $this->gXPath->query($query);

                foreach ($locations as $directaccess) {
                    $target = $directaccess->getAttribute('target');
                    $url_link = '';
                    foreach ($directaccess->getElementsByTagName('value') as $url) {
                        $url_link = trim($url->nodeValue);
                    }
                    if (strpos($url_link, 'http') === false && strpos($url_link, 'ftp://') === false) {
                        $url_link = 'http://' . $url_link;
                    }
                    if ($target != 'directDownload' && $target != 'landingPage') $target = 'url';
                    $title = '';
                    foreach ($directaccess->getElementsByTagName('title') as $title) {
                        $title = $title->nodeValue;
                    }
                    if ($title == '') $title = $directaccess->nodeValue;
                    $mediaTypeStr = '';
                    foreach ($directaccess->getElementsByTagName('mediaType') as $mediaType) {
                        $mediaTypeStr .= $mediaType->nodeValue . ",";
                    }
                    $byteSizeStr = '';
                    foreach ($directaccess->getElementsByTagName('byteSize') as $byteSize) {
                        $byteSizeStr .= $byteSize->nodeValue . ",";
                    }
                    $notes = '';
                    foreach ($directaccess->getElementsByTagName('notes') as $notes) {
                        $notes = $notes->nodeValue;
                    }
                    $download[] = Array(
                        'access_type' => $target,
                        'contact_type' => 'url',
                        'access_value' => $url_link,
                        'title' => $title,
                        'mediaType' => trim($mediaTypeStr, ","),
                        'byteSize' => trim($byteSizeStr, ","),
                        'notes' => $notes
                    );

                }
            }
        }

        //if the record is a service, remove all access_type viaService
        // @todo don't get it to begin with
        if ($this->ro->class == "service") {
            $download = array_values(array_filter($download, function($d) {
                return $d['access_type'] != 'viaService';
            }));
        }
        
        return $download;
    }
}