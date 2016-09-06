<?php

namespace ANDS\API\Task\ImportSubTask;

include_once("applications/registry/registry_object/models/_transforms.php");
use ANDS\Util\XMLUtil;
use \Transforms as Transforms;
use \DOMDocument as DOMDocument;
use \Exception as Exception;

class ValidatePayload extends ImportSubTask
{
    protected $requirePayload = true;

    public function run_task()
    {
        foreach ($this->parent()->getPayloads() as $path => &$xml) {
            $this->log("Validation started for $path");

            // validate RIFCS schema
            try {
                $xml = XMLUtil::escapeXML($xml);
                $xml = XMLUtil::cleanNameSpace($xml);
                $xml = $this->validatePayloadSchema($xml);
            } catch (Exception $e) {
                $this->addError("Validation error found: ". $e->getMessage());
                $xml = $this->attemptIndividualValidation($xml);
            }

            // xml is processed individually and there's none that pass validation
            if ($xml === false) {
                $this->addError("XML does not pass validation");
                return;
            }
            // update parent payload to the already validated one
            $this->parent()->setPayload($path, $xml);

            // @todo write path_validated.xml

            $this->log("Validation completed for $path");
        }
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
        $result = $this->validateRIFCS($xml);
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
        $sxml = XMLUtil::getSimpleXMLFromString($xml);
        $sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);

        $attempt = 0;
        foreach($sxml->xpath('//ro:registryObject') AS $registryObject) {
            try {
                $attempt++;
                $this->validateRIFCS(
                    XMLUtil::wrapRegistryObject(
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
            return XMLUtil::wrapRegistryObject(implode("", $validated));
        }

        return false;
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

}