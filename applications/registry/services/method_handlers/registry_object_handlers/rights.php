<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');

/**
 * Rights handler
 * @author Liz Woods <liz.woods@ands.org.au>
 * @return array
 */
class Rights extends ROHandler
{
    function handle()
    {
        $rights = $this->processLicence();

        if (!$rights) $rights = array();

        $skip = false;
        if ($rights && sizeof($rights) > 0) {
            foreach ($rights as $right) {
                if ($right['type'] == 'accessRights' && isset($right['accessRights_type'])) $skip = true;
            }
        }


        //if there's a secret tag of SECRET_TAG_ACCESS_OPEN (defined in constants), add a right of accessRights_type open
        if (!$skip) {
            if ($this->hasTag(SECRET_TAG_ACCESS_OPEN)) {
                $rights[] = array(
                    'value' => '',
                    'type' => 'accessRights',
                    'accessRights_type' => 'open'
                );
            } elseif ($this->hasTag(SECRET_TAG_ACCESS_CONDITIONAL)) {
                $rights[] = array(
                    'value' => '',
                    'type' => 'accessRights',
                    'accessRights_type' => 'conditional'
                );
            } elseif ($this->hasTag(SECRET_TAG_ACCESS_RESTRICTED)) {
                $rights[] = array(
                    'value' => '',
                    'type' => 'accessRights',
                    'accessRights_type' => 'restricted'
                );
            }
        }

        //if there's a direct downloads, assign access_rights to open
        require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/directaccess.php');
        $handler = new Directaccess(array(
            'xml' => $this->xml,
            'ro' => $this->ro,
            'gXPath' => $this->gXPath
        ));
        $downloads = $handler->handle();
        foreach ($downloads as $download) {
            if ($download['access_type'] == 'directDownload') {
                $rights[] = array(
                    'value' => '',
                    'type' => 'accessRights',
                    'accessRights_type' => 'open'
                );
            }
        }

        return $rights;
    }

    private function processLicence()
    {
        $rights = array();
        $sxml = $this->xml;
        $sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
        foreach ($sxml->xpath('//ro:' . $this->index['class'] . '/ro:rights') AS $theRights) {
            $right = array();
            foreach ($theRights as $key => $theRight) {
                $right['value'] = (string)$theRight;
                if ((string)$theRight['rightsUri'] != '')
                    $right['rightsUri'] = (string)$theRight['rightsUri'];
                $right['type'] = (string)$key;
                if ($right['type'] == 'licence') {
                    if ((string)$theRight['type'] != '') {
                        $right['licence_type'] = (string)$theRight['type'];
                    } else {
                        $right['licence_type'] = 'Unknown';
                    }

                    $right['licence_group'] = $this->getLicenceGroup($right['licence_type']);
                    if ($right['licence_group'] == '') $right['licence_group'] = 'Unknown';
                }
                if ($right['type'] == 'accessRights') {
                    if (trim((string)$theRight['type']) != '') {
                        $right['accessRights_type'] = (string)$theRight['type'];
                    }
                }
                $rights[] = $right;
                unset($right);
            }

        }
        $sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
        foreach ($sxml->xpath('//ro:' . $this->index['class'] . '/ro:description') AS $theRightsDescription) {

            if ($theRightsDescription['type'] == 'rights' || $theRightsDescription['type'] == 'accessRights') {

                $right = array();
                $right['value'] = html_entity_decode((string)$theRightsDescription);

                $right['type'] = (string)$theRightsDescription['type'];

                if ($this->checkRightsText($right['value'])) {
                    $right['licence_group'] = $this->checkRightsText($right['value']);
                }

                $rights[] = $right;
            }

        }

        return $rights;

    }


// Temporary workaround for storing "groupings" of licence identifiers
// XXX: Long term solution should use a vocabulary service (such as ANDS's)
    private static $licence_groups = array(
        "GPL" => "Open Licence",
        "CC-BY-SA" => "Open Licence",
        "CC-BY-ND" => "Non-Derivative Licence",
        "CC-BY-NC-SA" => "Non-Commercial Licence",
        "CC-BY-NC-ND" => "Non-Derivative Licence",
        "CC-BY-NC" => "Non-Commercial Licence",
        "CC-BY" => "Open Licence",
        "CSIRO Data Licence" => "Non-Commercial Licence",
        "AusGoalRestrictive" => "Restrictive Licence",
        "NoLicence" => "No Licence"

    );

    private function getLicenceGroup($licence_type)
    {
        if (isset(self::$licence_groups[(string)$licence_type])) {
            return self::$licence_groups[(string)$licence_type];
        } else {
            return '';
        }

    }

    private function checkRightsText($value)
    {

        if ((str_replace("http://creativecommons.org/licenses/by/", "", $value) != $value) || (str_replace("http://creativecommons.org/licenses/by-sa/", "", $value) != $value)) {
            return "Open Licence";
        } elseif ((str_replace("http://creativecommons.org/licenses/by-nc/", "", $value) != $value) || (str_replace("http://creativecommons.org/licenses/by-nc-sa/", "", $value) != $value)) {
            return "Non-Commercial Licence";
        } elseif ((str_replace("http://creativecommons.org/licenses/by-nd/", "", $value) != $value) || (str_replace("http://creativecommons.org/licenses/by-nc-nd/", "", $value) != $value)) {
            return "Non-Derivative Licence";
        } else {
            return false;
        }
    }


    function hasTag($tag){
        $ci = &get_instance();
        $db = $ci->load->database('registry', true);
        $tags = $db->select('*')->from('registry_object_tags')->where('key', $this->ro_key)->where('tag', $tag)->get()->result_array();
        if(sizeof($tags) > 0){
            return true;
        }else return false;
    }

}