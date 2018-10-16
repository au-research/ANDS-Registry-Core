<?php

namespace ANDS\OAI;

use ANDS\Commands\Script\ProcessScholix;
use ANDS\OAI\Exception\CannotDisseminateFormat;
use ANDS\OAI\Exception\OAIException;
use Carbon\Carbon;
use DOMDocument;
use ANDS\OAI\Exception\BadArgumentException;
use ANDS\OAI\Exception\BadResumptionToken;
use ANDS\OAI\Exception\BadVerbException;
use ANDS\OAI\Exception\NoRecordsMatch;
use ANDS\OAI\Interfaces\OAIRepository;

class ServiceProvider
{
    protected static $validVerbs = [
        "Identify" => [],
        "ListMetadataFormats" => ['identifier'],
        "ListSets" => ['resumptionToken'],
        "GetRecord" => ['identifier', 'metadataPrefix'],
        "ListIdentifiers" => ['from', 'until', 'metadataPrefix', 'set', 'resumptionToken'],
        "ListRecords" => ['from', 'until', 'metadataPrefix', 'set', 'resumptionToken']
    ];

    private $options = [];
    private $repository;
    private $limit = 100;
    private $baseUrl = null;

    /**
     * OAIServiceProvider constructor.
     * @param OAIRepository $repository
     * @param null $baseUrl
     */
    public function __construct(OAIRepository $repository, $baseUrl = null)
    {
        $this->repository = $repository;
        $this->baseUrl = $baseUrl;
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;
        $this->sanitizeOptions();
        return $this;
    }

    /**
     * Sanitize the options provided
     * Remove all unneeded request param
     */
    private function sanitizeOptions()
    {
        // get the verb
        $verb = null;
        if (array_key_exists('verb', $this->options)) {
            $verb = $this->options['verb'];
        }

        // continue if verb is not found
        // probably throw exception here
        $validVerbs = array_keys(self::$validVerbs);
        if (!in_array($verb, $validVerbs)) {
            return;
        }

        // unset all other values other than the needed
        $valid = self::$validVerbs[$verb];
        foreach ($this->options as $key => $value) {
            if ($key == "verb" || in_array($key, $valid)) {
                continue;
            }
            unset($this->options[$key]);
        }
    }

    /**
     *
     */
    public function get()
    {

        $verb = null;
        if (array_key_exists('verb', $this->options)) {
            $verb = $this->options['verb'];
        }

        try {
            switch ($verb) {
                case "Identify":
                    return $this->identify();
                    break;
                case "ListMetadataFormats":
                    return $this->listMetadataFormats();
                    break;
                case "ListSets":
                    return $this->listSets();
                    break;
                case "ListRecords":
                    return $this->listRecords();
                    break;
                case "ListIdentifiers":
                    return $this->listIdentifiers();
                    break;
                case "GetRecord":
                    return $this->getRecord();
                    break;
                default:
                    throw new BadVerbException("Bad Verb");
                    break;
            }
        } catch (OAIException $e) {
            return $this->getExceptionResponse($e);
        }

    }

    /**
     * @param OAIException $exception
     * @return Response
     */
    public function getExceptionResponse(OAIException $exception)
    {
        // don't include request attributs when badVerb or badArgument
        $includeRequestAttribute = true;
        $exceptionClass = get_class($exception);
        if (in_array($exceptionClass, [BadVerbException::class, BadArgumentException::class])) {
            $includeRequestAttribute = false;
        }

        $response = $this->getCommonResponse($includeRequestAttribute);
        $error = $response->addElement('error', $exception->getMessage());
        $error->setAttribute('code', $exception->getErrorName());
        return $response;
    }

    private function getCommonResponse($includeRequestAttributes = true)
    {
        $response = new Response;

        $format = $this->repository->getDateFormat();
        $response->addElement('responseDate', Carbon::now()->format($format));

        $requestElement = $response->addElement('request', $this->repository->getBaseUrl());

        if ($includeRequestAttributes === false) {
            return $response;
        }

        foreach ($this->options as $key => $value) {
            $requestElement->setAttribute($key, $value);
        }

        // set the xmlns based on the metadataPrefix
        // OAI-PMH elements will have the oai: prefix
        $options = $this->collectOptions();
        if (array_key_exists('metadataPrefix', $options)) {
            $formats = $this->repository->getFormats();
            $xmlns = $formats[$options['metadataPrefix']]['metadataNamespace'];
            $response->getContent()->documentElement->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:default', $xmlns);
        }

        return $response;
    }

    private function addResumptionToken(Response $response, $token, $offset = 0, $total = 0)
    {
        $node = $response->addElement('resumptionToken', $token);
        if ($total > 0) {
            $node->setAttribute('cursor', $offset);
            $node->setAttribute('completeListSize', $total);
        }
        return $response;
    }

    private function identify()
    {
        $response = $this->getCommonResponse();
        $identity = $this->repository->identify();

        $identityElement = $response->addElement('Identify');
        foreach ($identity as $key => $value) {
            $node = $response->createElement($key, $value);
            $identityElement->appendChild($node);
        }
        return $response;
    }

    private function listMetadataFormats()
    {
        $response = $this->getCommonResponse();

        if ($this->requestHas("identifier")) {
            $metadataFormats = $this->repository->listMetadataFormats($this->requestValue("identifier"));
        } else {
            $metadataFormats = $this->repository->listMetadataFormats();
        }

        $element = $response->addElement("ListMetadataFormats");
        foreach ($metadataFormats as $key => $value) {
            $node = $response->createElement('metadataFormat');
            foreach ($value as $k => $v) {
                $node->appendChild(
                    $response->createElement($k, $v)
                );
            }
            $element->appendChild($node);
        }

        return $response;
    }

    private function requestHas($key)
    {
        return array_key_exists($key, $this->options);
    }

    private function requestValue($key)
    {
        return $this->options[$key];
    }

    private function listSets()
    {
        $response = $this->getCommonResponse();

        $offset = 0;

        if (array_key_exists('resumptionToken', $this->options)) {
            $data = $this->decodeToken($this->options['resumptionToken']);
            $offset = $data['offset'];
        }

        $sets = $this->repository->listSets($this->limit, $offset);

        $element = $response->addElement('ListSets');
        foreach ($sets['sets'] as $set) {
            $node = $response->createElement('set');
            foreach ($set->toArray() as $k => $v) {
                $node->appendChild(
                    $response->createElement($k, $v)
                );
            }
            $element->appendChild($node);
        }

        // check if there should be more
        // assign resumption token if true
        if (($sets['offset'] + $sets['limit']) < $sets['total']) {
            $resumptionToken = $this->encodeToken(
                [ 'offset' => $sets['offset'] + $sets['limit'] ]
            );
            $response = $this->addResumptionToken($response, $resumptionToken, $sets['offset'], $sets['total']);
        }

        return $response;
    }

    private function listIdentifiers()
    {
        $response = $this->getCommonResponse();

        $options = $this->collectOptions();

        if (!array_key_exists("metadataPrefix", $options)) {
            throw new BadArgumentException("Missing required metadataPrefix argument");
        }

        $records = $this->repository->listIdentifiers($options);
        if (count($records['records']) == 0) {
            throw new NoRecordsMatch();
        }

        $element = $response->addElement('ListRecords');
        foreach($records['records'] as $record) {
            $recordNode = $element->appendChild(
                $response->createElement('record')
            );
            $recordNode = $this->addOaiRecordResponse($recordNode, $record, $response);
        }

        // resumptionToken
        $cursor = $records['offset'] + $records['limit'];
        if ( $cursor <= $records['total']) {
            $options['offset'] = $records['offset'] + $records['limit'];
            $resumptionToken = $this->encodeToken(
                array_merge($options)
            );
            $response = $this->addResumptionToken($response, $resumptionToken, $cursor, $records['total']);
        }

        return $response;
    }

    private function getRecord()
    {
        // TODO: metadataPrefix & identifier

        $response = $this->getCommonResponse();

        $metadataPrefix = $this->options['metadataPrefix'];
        $identifier = $this->options['identifier'];

        $record = $this->repository->getRecord($metadataPrefix, $identifier);
        if ($record === null) {
            throw new NoRecordsMatch("No matching records found: record not found");
        }

        $element = $response->addElement('GetRecord');
        $element = $this->addOaiRecordResponse($element, $record, $response);

        return $response;
    }

    private function addOaiRecordResponse(\DOMNode $element, $record, Response $response)
    {
        $data = $record->toArray();
        $recordNode = $element->appendChild(
            $response->createElement('record')
        );

        $headerNode = $recordNode->appendChild($response->createElement('header'));
        $headerNode
            ->appendChild(
                $response->createElement('identifier', $data['identifier'])
            );
        $headerNode
            ->appendChild(
                $response->createElement('datestamp', $data['datestamp'])
            );
        foreach ($data['specs'] as $spec) {
            $headerNode
                ->appendChild(
                    $response->createElement('setSpec', $spec->getSetSpec())
                );
        }

        if ($data['metadata']) {
            $metadataNode = $recordNode->appendChild($response->createElement('metadata'));
            $fragment = $response->getContent()->createDocumentFragment();
            $fragment->appendXML($data['metadata']);
            $metadataNode->appendChild($fragment);
        }

        $element->appendChild($recordNode);

        return $element;
    }

    /**
     * Useful function for collecting options
     * Decode resumptionToken if presented
     *
     * @return array|mixed
     * @throws BadResumptionToken
     */
    private function collectOptions()
    {
        $options = array_merge([
            'limit' => $this->limit,
            'set' => null,
            'offset' => 0,
            'from' => null,
            'to' => null
        ], $this->options);


        if ($this->requestHas("resumptionToken")) {
            $data = $this->decodeToken($this->requestValue('resumptionToken'));
            if ($data === null) {
                // corrupted resumptionToken
                throw new BadResumptionToken();
            }
            $options = $data;
        }

        return $options;
    }

    /**
     * Response for ListRecords verb
     *
     * @return Response
     * @throws OAIException
     */
    private function listRecords()
    {
        $response = $this->getCommonResponse();
        $set = null;

        $options = $this->collectOptions();

        if (!array_key_exists('metadataPrefix', $options) && !array_key_exists('resumptionToken', $this->options)) {
            throw new BadArgumentException("bad argument: Missing required argument 'metadataPrefix'");
        }

        $validPrefixes = array_keys($this->repository->getFormats());
        if (!in_array($options['metadataPrefix'], $validPrefixes)) {
            throw new CannotDisseminateFormat();
        }

        $records = $this->repository->listRecords($options);
        if (count($records['records']) == 0) {
            throw new NoRecordsMatch();
        }

        $element = $response->addElement('ListRecords');
        foreach ($records['records'] as $record) {
            $data = $record->toArray();

            $recordNode = $element->appendChild(
                $response->createElement('record')
            );

            $headerNode = $recordNode->appendChild($response->createElement('header'));
            $headerNode
                ->appendChild(
                    $response->createElement('identifier', $data['identifier'])
                );
            $headerNode
                ->appendChild(
                    $response->createElement('datestamp', $data['datestamp'])
                );
            foreach ($data['specs'] as $spec) {
                $headerNode
                    ->appendChild(
                        $response->createElement('setSpec', $spec->getSetSpec())
                    );
            }

            $el = $response->createElement('metadata');
            $doc = new DOMDocument();
            $doc->loadXml($data['metadata'], LIBXML_NSCLEAN);
            $el->appendChild(
                $response->getContent()->importNode($doc->documentElement, true)
            );

            $recordNode->appendChild($el);


            $element->appendChild($recordNode);
        }

        // resumptionToken
        $cursor = $records['offset'] + $records['limit'];
        if ( $cursor <= $records['total']) {
            $options['offset'] = $records['offset'] + $records['limit'];

            $resumptionToken = $this->encodeToken(
                array_merge($options)
            );
            $response = $this->addResumptionToken($response, $resumptionToken, $cursor, $records['total']);
        }

        return $response;
    }

    private function encodeToken($data)
    {
        return base64_encode(json_encode($data, true));
    }

    private function decodeToken($data)
    {
        return json_decode(base64_decode($data), true);
    }

    /**
     * @param null $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

}