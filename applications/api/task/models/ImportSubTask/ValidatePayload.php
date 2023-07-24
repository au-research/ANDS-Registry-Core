<?php

namespace ANDS\API\Task\ImportSubTask;

include_once("applications/registry/registry_object/models/_transforms.php");
use ANDS\Util\XMLUtil;
use ANDS\Repository\DataSourceRepository;
use ANDS\DataSource\Harvest as Harvest;
use \DOMDocument as DOMDocument;
use \Exception as Exception;

/**
 * Class ValidatePayload
 * @package ANDS\API\Task\ImportSubTask
 */
class ValidatePayload extends ImportSubTask
{
    protected $requirePayload = true;
    protected $payloadSource = "unvalidated";
    protected $payloadOutput = "validated";
    protected $registryObjectReceived = 0;
    protected $title = "VALIDATING PAYLOADS";

    public function run_task()
    {
        $counter = 0;

        $total = count($this->parent()->getPayloads());
        $this->log("Validating $total payload(s)");
        foreach ($this->parent()->getPayloads() as $index => &$payload) {
            $counter++;
            // this task requires unvalidated payload
            $path = $payload->getPath();
            $xml = $payload->getContentByStatus($this->payloadSource);
            // validate RIFCS schema
            try {
                // $this->log("Validation started for $path");
                $this->parent()->updateHarvest([
                        'message' => json_encode([
                            'progress' => [
                                'total' => count($this->parent()->getPayloads()),
                                'current' => $counter
                            ]
                        ], true),
                        'importer_message'=>"Validating $path"]
                );
                $this->registryObjectReceived += XMLUtil::countElementsByName($xml, "registryObject");
                $xml = XMLUtil::escapeXML($xml);
                $xml = XMLUtil::cleanNameSpace($xml);
                $xml = $this->validatePayloadSchema($xml);
            } catch (Exception $e) {
                try {
                    $xml = $this->attemptIndividualValidation($xml);
                } catch (Exception $e) {
                    $this->addError("Validation error found: ". $e->getMessage());
                    $xml = false;
                }
            }

            // xml is processed individually and there's none that pass validation
            if ($xml === false) {
                $this->parent()->stoppedWithError("XML does not pass validation". NL . implode(NL, $this->parent()->getError()));
                continue;
            }

            $payload->writeContentByStatus(
                $this->payloadOutput, XMLUtil::wrapRegistryObject($xml)
            );

            $payload->init();
            //$this->log("Validation completed for $path");
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
        $this->validateRIFCS($xml);
        return $xml;
    }

    /**
     * Attempt to validate the payload for individual RIFCS registryObject
     * Should return the XML string of all of the validated RIFCS objects
     *
     * @param $xml
     * @return string
     * @throws Exception
     */
    public function attemptIndividualValidation($xml)
    {
        $validated = [];
        try {
            $sxml = XMLUtil::getSimpleXMLFromString($xml);
            $sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
            return;
        }

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
                $this->parent()->incrementTaskData("invalidRegistryObjectsCount");
                $this->parent()->updateHarvest([
                    "importer_message" => "Failed to Validate:".$this->parent()->getTaskData("invalidRegistryObjectsCount")
                ]);
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
        $doc->loadXML($xml, LIBXML_PARSEHUGE);
        if (!$doc) {
            throw new Exception("Unable to parse XML. Perhaps your XML file is not well-formed?");
        }

        // TODO: Does this cache in-memory?
        libxml_use_internal_errors(true);
        $validation_status = $doc->schemaValidate(
            REGISTRY_APP_PATH . "registry_object/schema/registryObjects.xsd"
        );
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