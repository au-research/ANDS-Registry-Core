<?php


namespace ANDS\Registry\Providers\ORCID;


use ANDS\Util\XMLUtil;
use DOMDocument;

class ORCIDDocument
{
    private $document = [];
    private $dom = null;

    private $namespaces = [
        "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
        "xmlns:common" => "http://www.orcid.org/ns/common",
        "xmlns:work" => "http://www.orcid.org/ns/work",
    ];

    protected $schemaLocations = [
        'http://www.orcid.org/ns/common',
        'https://raw.githubusercontent.com/ORCID/ORCID-Source/master/orcid-model/src/main/resources/common_2.0/common-2.0.xsd',
        'http://www.orcid.org/ns/work',
        'https://raw.githubusercontent.com/ORCID/ORCID-Source/master/orcid-model/src/main/resources/record_2.0/work-2.0.xsd'
    ];

    public function set($key, $value)
    {
        $this->document[$key] = $value;
        return $this;
    }

    public function get($key)
    {
        return array_key_exists($key, $this->document) ? $this->document[$key] : null;
    }

    public function toArray()
    {
        return $this->document;
    }

    public function toXML()
    {
        $this->dom  = new DOMDocument('1.0', 'utf-8');
        $this->dom->formatOutput = true;
        $root = $this->dom->createElementNS('http://www.orcid.org/ns/work', 'work:work');
        $this->dom->appendChild($root);

        // namespaces
        foreach ($this->namespaces as $key => $value) {
            $root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,$key, $value);
        }

        // schema location
        $root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance',
            'xsi:schemaLocation', $this->schema());

        if ($this->get('put_code')) {
            $root->setAttribute('put-code', $this->get('put_code'));
        }

        $workTitle = $this->workElem('title');
        $workTitle ->appendChild(
            $this->commonElem('title', $this->get('title'))
        );
        $root->appendChild($workTitle);

        $root->appendChild(
            $this->dom->createElementNS(
                'http://www.orcid.org/ns/work',
                "work:short-description",
                $this->get('short-description')
            )
        );

        // work:citation
        if ($citation = $this->get('citation')) {
            $citationDOM = $this->workElem('citation');
            $citationTypeDOM = $this->workElem('citation-type', $citation['type']);
            $citationValueDOM = $this->workElem('citation-value', htmlspecialchars($citation['value']));
            $citationDOM->appendChild($citationTypeDOM);
            $citationDOM->appendChild($citationValueDOM);
            $root->appendChild($citationDOM);
        }

        $root->appendChild(
            $this->dom->createElementNS(
                'http://www.orcid.org/ns/work',
                "work:type",
                $this->get('work-type')
            )
        );

        // common:publication-date
        if ($publicationDate = $this->get('publication-date')) {
            $publicationDateDOM = $this->commonElem('publication-date');
            $publicationDateDOM->appendChild(
                $this->commonElem('year', $publicationDate['year'])
            );
            $publicationDateDOM->appendChild(
                $this->commonElem('month', $publicationDate['month'])
            );
            $publicationDateDOM->appendChild(
                $this->commonElem('day', $publicationDate['day'])
            );
            $root->appendChild($publicationDateDOM);
        }

        // external-ids
        if ($externalIDs = $this->get('external-ids')) {
            $externalIDsDOM = $this->commonElem('external-ids');
            foreach ($externalIDs as $id) {
                $externalIDDOM = $this->commonElem('external-id');
                $externalIDDOM->appendChild($this->commonElem('external-id-type', $id['type']));
                $externalIDDOM->appendChild($this->commonElem('external-id-value', $id['value']));
                $externalIDDOM->appendChild($this->commonElem('external-id-relationship', 'self'));
                $externalIDsDOM->appendChild($externalIDDOM);
            }
            $root->appendChild($externalIDsDOM);
        }

        // work:url
        $root->appendChild(
            $this->dom->createElementNS(
                'http://www.orcid.org/ns/work',
                "work:url",
                $this->get('url')
            )
        );

        // work:contributors
        if ($contributors = $this->get('contributors')) {
            $contributorsDOM = $this->workElem('contributors');
            foreach ($contributors as $contributor) {
                $contributorDOM = $this->workElem('contributor');

                // work:contributors/work:contributor/contributor-orcid
//                if ($contributor['contributor-orcid']) {
//                    $contributorORCIDDOM = $this->commonElem('contributor-orcid');
//                    $contributorORCIDDOM->appendChild(
//                        $this->commonElem('uri', $contributor['contributor-orcid']['uri'])
//                    );
//                    $contributorORCIDDOM->appendChild(
//                        $this->commonElem('path', $contributor['contributor-orcid']['path'])
//                    );
//                    $contributorORCIDDOM->appendChild(
//                        $this->commonElem('host', $contributor['contributor-orcid']['host'])
//                    );
//                    $contributorDOM->appendChild($contributorORCIDDOM);
//                }

                // work:contributors/work:contributor/work:credit-name
                $contributorDOM->appendChild(
                    $this->workElem('credit-name', htmlspecialchars($contributor['credit-name']))
                );

                // work:contributors/work:contributor/contributor-attributes
                $contributorAttributesDOM = $this->workElem('contributor-attributes');
                if ($seq = $contributor['contributor-attributes']['contributor-sequence']) {
                    $contributorAttributesDOM->appendChild(
                        $this->workElem('contributor-sequence', $seq)
                    );
                }
                if ($role = $contributor['contributor-attributes']['contributor-role']) {
                    $contributorAttributesDOM->appendChild(
                        $this->workElem('contributor-role', $role)
                    );
                }
                $contributorDOM->appendChild($contributorAttributesDOM);

                $contributorsDOM->appendChild($contributorDOM);
            }

            $root->appendChild($contributorsDOM);
        }

        return $this->dom->saveXML();
    }

    private function schema()
    {
        return implode(" ", $this->schemaLocations);
    }

    public function validate()
    {
        $util = new XMLUtil();
        $result = $util->validateRemoteSchema("https://raw.githubusercontent.com/ORCID/ORCID-Source/master/orcid-model/src/main/resources/record_2.0/work-2.0.xsd", $this->toXML());
        if (!$result) {
            return $util->getValidationMessage();
        }
        return $result;
    }

    /**
     * @param $name
     * @param string $value
     * @return \DOMElement
     */
    private function workElem($name, $value = "")
    {
        return $this->dom->createElementNS(
            'http://www.orcid.org/ns/work',
            "work:$name",
            $value);
    }

    /**
     * @param $name
     * @param string $value
     * @return \DOMElement
     */
    private function commonElem($name, $value = "")
    {
        return $this->dom->createElementNS(
            'http://www.orcid.org/ns/common',
            "common:$name",
            $value);
    }
}