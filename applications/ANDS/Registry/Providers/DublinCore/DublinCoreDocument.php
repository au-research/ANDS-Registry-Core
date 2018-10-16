<?php


namespace ANDS\Registry\Providers\DublinCore;


use ANDS\RegistryObject;

class DublinCoreDocument
{
    private $data = [];

    /** @var RegistryObject */
    private $record = null;

    public static $DCNamespace = 'http://purl.org/dc/elements/1.1/';

    /**
     * DublinCoreDocument constructor.
     * @param RegistryObject $record
     * @param array $data
     */
    public function __construct(RegistryObject $record, array $data = [])
    {
        $this->record = $record;
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param bool $pretty
     * @return string
     */
    public function toXML($pretty = true)
    {
        $simpleXML = $this->toSimpleXML();

        if (!$pretty) {
            return $simpleXML->saveXML();
        }

        $dom = dom_import_simplexml($simpleXML)->ownerDocument;
        $dom->formatOutput = true;

        return $dom->saveXML();
    }

    /**
     * Get the SimpleXML representation of the document
     *
     * @return \SimpleXMLElement
     */
    public function toSimpleXML()
    {
        $data = $this->getData();

        // dc namespace
        $ns = self::$DCNamespace;

        $root = new \SimpleXMLElement("<oai_dc:dc 
        xmlns:dc='{$ns}' xmlns:oai_dc='http://www.openarchives.org/OAI/2.0/oai_dc/' xmlns='http://www.openarchives.org/OAI/2.0/oai_dc/' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd'></oai_dc:dc>", LIBXML_NOERROR, false, 'http://purl.org/dc/elements/1.1/', true);

        $root->addChild("dc:title", $data['title'], $ns);
        $root->addChild("dc:publisher", $data['publisher'], $ns);
        $root->addChild('dc:source', $data['source'], $ns);
        $root->addChild('dc:type', $data['type'], $ns);

        foreach ($data['identifiers'] as $identifier) {
            $root->addChild("dc:identifier", htmlspecialchars($identifier), $ns);
        }

        foreach ($data['descriptions'] as $description) {
            $root->addChild("dc:description", htmlspecialchars($description), $ns);
        }

        foreach ($data['rights'] as $right) {
            $root->addChild("dc:rights", htmlspecialchars($right), $ns);
        }

        foreach ($data['coverages'] as $coverage) {
            $root->addChild("dc:coverage", htmlspecialchars($coverage), $ns);
        }

        foreach ($data['contributors'] as $contributor) {
            $root->addChild("dc:contributor", $contributor, $ns);
        }

        foreach ($data['subjects'] as $subject) {
            $root->addChild("dc:subject", htmlspecialchars($subject), $ns);
        }

        return $root;
    }

}