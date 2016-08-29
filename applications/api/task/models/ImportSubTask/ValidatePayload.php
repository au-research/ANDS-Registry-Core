<?php

namespace ANDS\API\Task\ImportSubTask;

include_once("applications/registry/registry_object/models/_transforms.php");
use \Transforms as Transforms;
use \DOMDocument as DOMDocument;
use \Exception as Exception;

class ValidatePayload extends ImportSubTask
{
    public function run_task()
    {
        $this->loadPayload();

        if (count($this->parent()->getPayloads()) === 0) {
            $this->stoppedWithError("No payload found");
            return;
        }

        foreach ($this->parent()->getPayloads() as $path => &$xml) {
            $this->log("Validation started for $path");

            // validate RIFCS schema
            try {
                $xml = $this->escapeXML($xml);
                $xml = $this->cleanNameSpace($xml);
                $xml = $this->validatePayloadSchema($xml);
            } catch (Exception $e) {
                $this->addError("Validation error found: ". $e->getMessage());
                $xml = $this->attemptIndividualValidation($xml);
            }

            // xml is processed individually and there's none that pass validation
            if ($xml === false) {
                $this->stoppedWithError("XML does not pass validation");
                return;
            }

            // @todo validate key attributes
            // key
            // group
            // originatingSource

            // update parent payload to the already validated one
            $this->parent()->setPayload($path, $xml);

            // @todo write path_validated.xml

            $this->log("Validation completed for $path");
        }
    }

    /**
     * Load the payload specified in the parent task
     * to the parent payloads array
     * @todo need a better file accessor than file_get_contents
     */
    public function loadPayload()
    {
        $harvestedContentDir = get_config_item('harvested_contents_path');
        $path = $harvestedContentDir . '/' . $this->parent()->dataSourceID . '/' . $this->parent()->batchID;

        if (!is_dir($path)) {
            $path = $path . '.xml';
            if (is_file($path)) {
                $this->parent()->setPayload(
                    $path, file_get_contents($path)
                );
            }
        } else {
            $directory = scandir($path);
            $files = array();
            foreach ($directory as $f) {
                if (endsWith($f, '.xml')) {
                    $files[] = $f;
                }
            }
            foreach ($files as $index => $f) {
                $this->parent()->setPayload(
                    $f, file_get_contents($path . '/' . $f)
                );
            }
        }

        return $this;
    }

    /**
     * return if an xml pass validation
     * writes to log if not
     *
     * @param $xml
     * @return bool
     */
    public function validatePayloadSchema($xml)
    {
        // @todo once validateRIFCS can be done by an XMLValidator, change this
        $result = $this->validateRIFCS($xml);

        // @todo if not validated, try to form new xml by removing the invalidated one and log them
        return $xml;
    }

    /**
     * Attempt to validate the payload for individual RIFCS registryObject
     * Should return the XML string of all of the validated RIFCS objects
     *
     * @param $xml
     * @return string
     */
    public function attemptIndividualValidation($xml)
    {
        $validated = [];
        $sxml = $this->getSimpleXMLFromString($xml);
        $sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);

        $attempt = 0;
        foreach($sxml->xpath('//ro:registryObject') AS $registryObject) {
            try {
                $attempt++;
                $this->validateRIFCS(
                    $this->wrapRegistryObject(
                        $registryObject->asXML()
                    )
                );
                $validated[] = $registryObject->asXML();
            } catch (Exception $e) {
                $key = (string) $registryObject->key;
                $this->addError("Error validating record (#$attempt) with key:" . ($key!="" ? $key : "(unknown key)") . " :". $e->getMessage());
            }
        }

        if (sizeof($validated) > 0) {
            //put them together as xml
            return $this->wrapRegistryObject(implode("", $validated));
        }

        return false;
    }

    /**
     * cleaning the namespace off an xml
     *
     * @param $xml
     * @return string
     * @throws Exception
     */
    public function cleanNameSpace($xml)
    {
        try {
            $xslt_processor = Transforms::get_clean_ns_transformer();
            $dom = new DOMDocument();
            $dom->loadXML($xml);
            return $xslt_processor->transformToXML($dom);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Escape Ampercent before validation
     *
     * @param $xml
     * @return mixed|string
     */
    public function escapeXML($xml)
    {
        $xml = mb_convert_encoding($xml, "UTF-8");
        // unescape (some entities are double escaped) first
        while (strpos($xml, '&amp;') !== false) {
            $xml = str_replace("&amp;", "&", $xml);
        }
        $xml = str_replace("&", "&amp;", $xml);
        return $xml;
    }

    /**
     * @todo move to own class ANDS\Registry\XMLValidator
     * @param $xml
     * @return bool
     * @throws Exception
     */
    public function validateRIFCS($xml)
    {
        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->loadXML($xml);
        if (!$doc) {
            throw new Exception("Unable to parse XML. Perhaps your XML file is not well-formed?");
        }

        // TODO: Does this cache in-memory?
        libxml_use_internal_errors(true);
        $validation_status = $doc->schemaValidate(REGISTRY_APP_PATH . "registry_object/schema/registryObjects.xsd");
        if ($validation_status === true) {
            libxml_use_internal_errors(false);
            return true;
        } else {
            $errors = libxml_get_errors();
            $error_string = '';
            foreach ($errors as $error) {
                $error_string .= TAB . "Line " . $error->line . ": " . $error->message;
            }
            libxml_clear_errors();
            libxml_use_internal_errors(false);
            throw new Exception("Unable to validate XML document against schema: " . NL . $error_string);
        }
    }

    public function countElement($xml, $element, $namespace = RIFCS_NAMESPACE)
    {
        $sxml = $this->getSimpleXMLFromString($xml);
        $sxml->registerXPathNamespace("ro", $namespace);
        return count($sxml->xpath('//ro:'.$element));
    }

    /**
     * @todo move to XMLValidator
     * @param $xml
     * @return \SimpleXMLElement
     * @throws Exception
     */
    public function getSimpleXMLFromString($xml)
    {
        libxml_use_internal_errors(true);

        if (!defined('LIBXML_PARSEHUGE')) {
            $xml = simplexml_load_string($xml, 'SimpleXMLElement');
        } else {
            $xml = simplexml_load_string($xml, 'SimpleXMLElement',
                LIBXML_PARSEHUGE);
        }

        if ($xml === false) {
            $exception_message = "Could not parse Registry Object XML" . NL;
            foreach (libxml_get_errors() as $error) {
                $exception_message .= "    " . $error->message;
            }
            libxml_use_internal_errors(false);
            throw new Exception($exception_message);
        }
        return $xml;
    }

    /**
     * @todo move to XMLValidator
     * @todo make constants
     * @param $xml
     * @return string
     */
    private function wrapRegistryObject($xml)
    {
        $return = $xml;
        if (strpos($xml, '<registryObjects') === false) {
            $return = '<?xml version="1.0" encoding="UTF-8"?>' . NL . '<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects http://services.ands.org.au/documentation/rifcs/schema/registryObjects.xsd">' . NL;
            $return .= $xml;
            $return .= '</registryObjects>';
        }
        return $return;
    }
}