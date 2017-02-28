<?php


namespace ANDS\Util;

include_once("applications/registry/registry_object/models/_transforms.php");
use \Transforms as Transforms;
use \DOMDocument as DOMDocument;
use \Exception as Exception;

class XMLUtil
{

    private $validationMessage;

    /**
     * @param $xml
     * @param $xpath
     * @param string $namespace
     * @return \SimpleXMLElement[]
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

    public static function getElementsByXPathFromSXML($sxml, $xpath)
    {
        return $sxml->xpath($xpath);
    }

    /**
     * @param $xml
     * @param $element
     * @param string $namespace
     * @return \SimpleXMLElement[]
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
     * @return string
     */
    public static function wrapRegistryObject($xml)
    {
        $return = $xml;
        if (strpos($xml, '<registryObjects') === false) {
            $return = '<?xml version="1.0" encoding="UTF-8"?>' . NL . '<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects http://services.ands.org.au/documentation/rifcs/schema/registryObjects.xsd">' . NL;
            $return .= $xml;
            $return .= '</registryObjects>';
        }
        return $return;
    }

    public static function unwrapRegistryObject($xml)
    {
        if (strpos($xml, '<registryObjects') === false) {
            return $xml;
        }
        $simpleXML = static::getSimpleXMLFromString(static::cleanNameSpace($xml));
        return $simpleXML->registryObject->asXML();
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
            throw new Exception($e->getMessage());
        }
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


}