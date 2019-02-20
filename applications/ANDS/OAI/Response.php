<?php


namespace ANDS\OAI;
use \Exception as Exception;

class Response
{
    private $status = 200;
    private $headers = ['Content-Type' => 'application/xml'];
    private $content = null;
    private $pretty = true;
    private $errors = [];

    /**
     * OAIResponse constructor.
     */
    public function __construct()
    {
        $this->content = new \DOMDocument('1.0', 'UTF-8');
        $this->content->formatOutput = true;
        $documentElement = $this->content->createElementNS('http://www.openarchives.org/OAI/2.0/', "OAI-PMH");
        $documentElement->setAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
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

    /**
     * @param $nameSpace
     * @param $name
     * @param null $value
     * @return \DOMElement
     */
    public function createElementNS($nameSpace, $name, $value = null)
    {
        $element = $this->content->createElementNS($nameSpace, $name, htmlspecialchars($value, ENT_XML1));
        return $element;
    }

    /**
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getResponse()
    {
        $xml = $this->content->saveXML();

        if ($this->pretty) {
            $dom = new \DOMDocument("1.0");
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xml);
            $xml = $dom->saveXML();
        }

        $xml = str_replace("<default:", "<", $xml);
        $xml = str_replace("</default:", "</", $xml);

        return new \GuzzleHttp\Psr7\Response(
            $this->status,
            $this->headers,
            $xml
        );
    }

    /**
     * @return \DOMDocument|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param $error
     * @return Response
     */
    public function setError($error)
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function errored()
    {
        return count($this->errors) > 0;
    }
}