<?php


namespace ANDS\OAI;
use \Exception as Exception;

class Response
{
    private $status = 200;
    private $headers = ['Content-Type' => 'application/xml'];
    private $content = null;
    private $pretty = true;

    /**
     * OAIResponse constructor.
     */
    public function __construct()
    {
        $this->content = new \DOMDocument('1.0', 'UTF-8');
        $this->content->formatOutput = true;
        $documentElement = $this->content->createElementNS('http://www.openarchives.org/OAI/2.0/', "oai:OAI-PMH");
        $documentElement->setAttribute('xmlns:oai', 'http://www.openarchives.org/OAI/2.0/');
        $documentElement->setAttributeNS(
            "http://www.w3.org/2001/XMLSchema-instance",
            'xsi:schemaLocation',
            'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd'
        );
        $this->content->appendChild($documentElement);
    }

    /**
     * @param $name
     * @param string $value
     * @return \DOMElement
     */
    public function addElement($name, $value = null)
    {
        $element = $this->createElement($name, $value);
        $this->content->documentElement->appendChild($element);
        return $element;
    }

    /**
     * @param string $name
     * @param \DOMDocument|string $value
     * @return \DOMElement
     */
    public function createElement($name, $value = null)
    {
        $nameSpace = 'http://www.openarchives.org/OAI/2.0/';
        $element = $this->content->createElementNS($nameSpace, $name, htmlspecialchars($value, ENT_XML1));
        return $element;
    }

    public function createElementNS($nameSpace, $name, $value = null)
    {
        $element = $this->content->createElementNS($nameSpace, $name, htmlspecialchars($value, ENT_XML1));
        return $element;
    }

    public function getResponse()
    {
        $xml = $this->content->saveXML();

        if ($this->pretty) {
            $dom = new \DOMDocument("1.0");
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($this->content->saveXML());
            $xml = $dom->saveXML();
        }

        return new \GuzzleHttp\Psr7\Response(
            $this->status,
            $this->headers,
            $xml
        );
    }

    public function getContent()
    {
        return $this->content;
    }
}