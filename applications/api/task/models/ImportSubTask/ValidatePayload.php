<?php

namespace ANDS\API\Task\ImportSubTask;

include_once("applications/registry/registry_object/models/_transforms.php");
use \Transforms as Transforms;
use \DOMDocument as DOMDocument;

class ValidatePayload extends ImportSubTask
{
    public function run_task()
    {
        $this->loadPayload();

        if (count($this->parent()->getPayloads()) === 0) {
            $this->stoppedWithError("No payload found");
            return;
        }

        foreach ($this->parent()->getPayloads() as $path=>&$xml) {
            $xml = $this->cleanNameSpace($xml);
            $result = $this->validatePayloadSchema($xml);

            // update parent payload to the validated one
            $this->parent()->setPayload($path, $xml);

            // @todo write path.$xml_validated.xml
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
        $path = $harvestedContentDir.'/'.$this->parent()->dataSourceID.'/'.$this->parent()->batchID;

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
        return $this->validateRIFCS($xml);
    }

    /**
     * cleaning the namespace off an xml
     *
     * @param $xml
     * @return string
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
     * @todo move to own class ANDS\Registry\XMLValidator
     * @param $xml
     * @return bool
     * @throws \Exception
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
        if ($validation_status === TRUE) {
            libxml_use_internal_errors(false);
            return TRUE;
        } else {
            $errors = libxml_get_errors();
            $error_string = '';
            foreach ($errors as $error) {
                $error_string .= TAB . "Line " . $error->line . ": " . $error->message;
            }
            libxml_clear_errors();
            libxml_use_internal_errors(false);
            throw new \Exception("Unable to validate XML document against schema: " . NL . $error_string);
        }
    }
}