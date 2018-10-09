<?php


namespace ANDS\Util;

include_once(dirname(__FILE__)."./../../registry/registry_object/models/_transforms.php");
use \Transforms as Transforms;
use \DOMDocument as DOMDocument;
use \Exception as Exception;

if (!defined("RIFCS_NAMESPACE")) {
    define('EXTRIF_NAMESPACE', "http://ands.org.au/standards/rif-cs/extendedRegistryObjects");
    define('RIFCS_NAMESPACE', "http://ands.org.au/standards/rif-cs/registryObjects");
}

if (!defined("NL")) {
    define('NL',"\n");
}

if (!defined("REGISTRY_APP_PATH")) {
    define('REGISTRY_APP_PATH', "applications/registry/");
}

class XMLUtil
{

    private $validationMessage;

    /**
     * @param $xml
     * @param $xpath
     * @param string $namespace
     * @return \SimpleXMLElement[]
     * @throws Exception
     */
    public static function getElementsByXPath(
        $xml,
        $xpath,
        $namespace = RIFCS_NAMESPACE
    ) {
        $sxml = self::getSimpleXMLFromString($xml);
        $sxml->registerXPathNamespace("ro", $namespace);
        return $sxml->xpath($xpath);
    }

    /**
     * @param $sxml
     * @param $xpath
     * @return mixed
     */
    public static function getElementsByXPathFromSXML(\SimpleXMLElement $sxml, $xpath)
    {
        return $sxml->xpath($xpath);
    }

    /**
     * @param $xml
     * @param $element
     * @param string $namespace
     * @return \SimpleXMLElement[]
     * @throws Exception
     */
    public static function getElementsByName($xml, $element, $namespace = RIFCS_NAMESPACE)
    {
        return self::getElementsByXPath($xml, "//ro:".$element, $namespace);
    }


    /**
     * @param $xml
     * @param $element
     * @param string $namespace
     * @return int
     * @throws Exception
     */
    public static function countElementsByName($xml, $element, $namespace = RIFCS_NAMESPACE)
    {
        return count(self::getElementsByName($xml, $element, $namespace));
    }

    /**
     * @param $xml
     * @return \SimpleXMLElement
     * @throws Exception
     */
    public static function getSimpleXMLFromString($xml)
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

        $xml->registerXPathNamespace("ro", RIFCS_NAMESPACE);

        return $xml;
    }

    /**
     * @param $xml
     * @param bool $includeXMLDeclaration
     * @return string
     */
    public static function wrapRegistryObject($xml, $includeXMLDeclaration = true)
    {
        $return = $xml;

        if (strpos($xml, '<registryObjects') === false) {
            if ($includeXMLDeclaration) {
                $return = '<?xml version="1.0" encoding="UTF-8"?><registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects http://services.ands.org.au/documentation/rifcs/schema/registryObjects.xsd">';
            } else {
                $return = '<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects http://services.ands.org.au/documentation/rifcs/schema/registryObjects.xsd">' . NL;
            }
            $return .= $xml;
            $return .= '</registryObjects>';
        }
        return $return;
    }

    /**
     * @param $xml
     * @return mixed
     * @throws Exception
     */
    public static function unwrapRegistryObject($xml)
    {
        if (strpos($xml, '<registryObjects') === false) {
            return $xml;
        }
        $simpleXML = static::getSimpleXMLFromString(static::cleanNameSpace($xml));
        return $simpleXML->registryObject->asXML();
    }

    /**
     * @param $xml
     * @return mixed|string
     * @throws Exception
     */
    public static function ensureWrappingRegistryObjects($xml)
    {
        $xml = static::unwrapRegistryObject($xml);
        $xml = static::wrapRegistryObject($xml);
        return $xml;
    }

    /**
     * Escape Ampercent and return the escaped xml
     *
     * @param $xml
     * @return mixed|string
     */
    public static function escapeXML($xml)
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
     * cleaning the namespace off an xml
     *
     * @param $xml
     * @return string
     * @throws Exception
     */
    public static function cleanNameSpace($xml)
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
     * @param $xml
     * @param array $params
     * @return string
     * @throws Exception
     */
    public static function getHTMLForm($xml, $params = [])
    {
        try{
            $xslt_processor = Transforms::get_rif_to_edit_form_transformer();
            $dom = new DOMDocument();
            $dom->loadXML($xml, LIBXML_NOENT);
            foreach($params as $key=>$val){
                $xslt_processor->setParameter('', $key, $val);
            }
            return html_entity_decode($xslt_processor->transformToXML($dom));
        } catch (Exception $e) {
            throw new Exception(get_exception_msg($e));
        }
    }

    /**
     * TODO: Refactor
     * @return null|\XSLTProcessor
     */
    public static function getORCIDTransformer()
    {
        return Transforms::get_extrif_to_orcid_transformer();
    }

    /**
     * validates datacite xml against required schema version
     *
     * @param $schema
     * @param $payload
     * @return string
     * @internal param $xml
     */
    public function validateSchema($schema, $payload)
    {
        libxml_use_internal_errors(true);

        try {
            $xml = new \DOMDocument();
            $xml->loadXML($payload);
        } Catch (\Exception $e) {
            $this->validationMessage = $e->getMessage();
            return false;
        }

        $schemaPath = dirname(__DIR__) ."/../../etc/schema/$schema/$schema.xsd";
        $result = $xml->schemaValidate($schemaPath);
        foreach (libxml_get_errors() as $error) {
            $this->validationMessage = $error->message;
        }
        return $result;
    }

    /**
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

    public function validateRemoteSchema($schema, $payload)
    {
        libxml_use_internal_errors(true);

        try {
            $xml = new \DOMDocument();
            $xml->loadXML($payload);
        } Catch (\Exception $e) {
            $this->validationMessage = $e->getMessage();
            return false;
        }

        $result = $xml->schemaValidate($schema);
        foreach (libxml_get_errors() as $error) {
            $this->validationMessage = $error->message;
        }
        return $result;
    }

    /**
     * Returns a new instance of the class, to be able to use validationMessage
     * Mainly use when call validateSchemaVersion statically
     *
     * @usage XMLValidator::create()->validateSchemaVersion($xml)
     * @return static
     */
    public static function create()
    {
        return new static;
    }

    /**
     * @return mixed
     */
    public function getValidationMessage()
    {
        return $this->validationMessage;
    }

    public static function stripXMLHeader($xml)
    {
        return preg_replace("/<\?xml (.*)\?>/s", "", $xml);
    }

    /**
     * @param $xml
     * @param null $simpleXML
     * @return string
     * @throws Exception
     */
    public static function getRegistryObjectClass($xml, $simpleXML = null)
    {
        $xml = XMLUtil::unwrapRegistryObject($xml);
        $xml = XMLUtil::wrapRegistryObject($xml);
        $simpleXML = $simpleXML ?: XMLUtil::getSimpleXMLFromString($xml);

        if (count($simpleXML->xpath("//ro:collection"))) {
            return "collection";
        } elseif (count($simpleXML->xpath("//ro:party"))) {
            return "party";
        } elseif (count($simpleXML->xpath("//ro:activity"))) {
            return "activity";
        } elseif (count($simpleXML->xpath("//ro:service"))) {
            return "service";
        } else {
            throw new \InvalidArgumentException("Unable to discern class from xml");
        }
    }

}