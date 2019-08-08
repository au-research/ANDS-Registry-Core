<?php


namespace ANDS\DOI\Validator;


use ANDS\DOI\SchemaProvider;

class XMLValidator
{

    private $validationMessage;

    /**
     * validates datacite xml against required schema version
     *
     * @param $xml
     * @return string
     */

    public function validateSchemaVersion($xml)
    {
        libxml_use_internal_errors(true);

        try {
            $theSchema = self::getSchemaVersion($xml);
            $doiXML = new \DOMDocument();
            $doiXML->loadXML($xml);
        } Catch (\Exception $e) {
            $this->validationMessage = $e->getMessage();
            return false;
        }

        $schemaPath = SchemaProvider::getSchema($theSchema);

        // if the schema is not stored locally, lookup on datacite
        if (!is_file($schemaPath)) {
            $schemaPath = 'http://schema.datacite.org/meta' . $theSchema;
        }

        $result = $doiXML->schemaValidate($schemaPath);

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
     * determines datacite xml schema version xsd
     *
     * @param $xml
     * @return string
     */

    public static function getSchemaVersion($xml)
    {
        $doiXML = new \DOMDocument();
        $doiXML->loadXML($xml);

        $resources = $doiXML->getElementsByTagName('resource');
        $theSchema = 'unknown';
        if ($resources->length > 0) {
            if (isset($resources->item(0)->attributes->item(0)->name)) {
                $theSchema = substr($resources->item(0)->attributes->item(0)->nodeValue,
                    strpos($resources->item(0)->attributes->item(0)->nodeValue,
                        "/meta/kernel") + 5);
            }
        }

        return $theSchema;
    }


    /**
     * Replaces the DOI Identifier value in the provided XML
     *
     * @param $doiValue
     * @param $xml
     * @return string
     */

    public static function replaceDOIValue($doiValue, $xml)
    {
        $doiXML = new \DOMDocument();
        $doiXML->loadXML($xml);

        // remove the current identifier
        $currentIdentifier = $doiXML->getElementsByTagName('identifier');
        for ($i = 0; $i < $currentIdentifier->length; $i++) {
            $doiXML
                ->getElementsByTagName('resource')
                ->item(0)
                ->removeChild($currentIdentifier->item($i));
        }

        // add new identifier to the DOM
        $newIdentifier = $doiXML->createElement('identifier', $doiValue);
        $newIdentifier->setAttribute('identifierType', "DOI");
        $doiXML
            ->getElementsByTagName('resource')
            ->item(0)
            ->insertBefore(
                $newIdentifier,
                $doiXML->getElementsByTagName('resource')->item(0)->firstChild
            );

        return $doiXML->saveXML();
    }


    /**
     * Gets the DOI Identifier value in the provided XML
     *
     * @param $xml
     * @return string
     */

    public static function getDOIValue($xml)
    {
        $doiXML = new \DOMDocument();
        $doiXML->loadXML($xml);

        // get the current identifier
        $currentIdentifier = $doiXML->getElementsByTagName('identifier');

        return $currentIdentifier->item(0)->nodeValue;
    }

    /**
     * @return mixed
     */
    public function getValidationMessage()
    {
        return $this->validationMessage;
    }
}