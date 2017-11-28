<?php


namespace ANDS\Registry\Providers\ORCID;


use DOMDocument;

class ORCIDDocument
{
    private $document = [];
    private $dom = null;

    private $namespaces = [
        "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
        "xmlns:common" => "http://www.orcid.org/ns/common",
        "xmlns:work" => "http://www.orcid.org/ns/work",
//        "xsi:schemaLocation" => "http://www.orcid.org/ns/common
//            https://raw.githubusercontent.com/ORCID/ORCID-Source/master/orcid-model/src/main/resources/common_2.0/common-2.0.xsd
//            http://www.orcid.org/ns/work
//            https://raw.githubusercontent.com/ORCID/ORCID-Source/master/orcid-model/src/main/resources/record_2.0/work-2.0.xsd"
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
            'xsi:schemaLocation', implode(' ', $this->schemaLocations));

        if ($this->get('put_code')) {
            $root->setAttribute('put-code', $this->get('put_code'));
        }

        // fill
        $fillables = ['title', 'short-description', 'url'];
        foreach ($fillables as $fillable) {
            if ($this->get($fillable)) {
                $root->appendChild(
                    $this->dom->createElementNS(
                        'http://www.orcid.org/ns/work',
                        "work:$fillable",
                        $this->get($fillable)
                    )
                );
            }
        }

        // work:citation
        if ($citation = $this->get('citation')) {
            $citationDOM = $this->workElem('citation');
            $citationTypeDOM = $this->workElem('citation-type', $citation['type']);
            $citationValueDOM = $this->workElem('citation-value', htmlspecialchars($citation['value']));
            $citationDOM->appendChild($citationTypeDOM);
            $citationDOM->appendChild($citationValueDOM);
            $root->appendChild($citationDOM);
        }

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
                $externalIDsDOM
                    ->appendChild($this->commonElem('external-id-type', $id['type']))
                    ->appendChild($this->commonElem('external-id-value', $id['value']))
                    ->appendChild($this->commonElem('external-id-relationship', 'self'));
            }
            $root->appendChild($externalIDsDOM);
        }

        // contributors
        if ($contributors = $this->get('contributors')) {
            $contributorsDOM = $this->workElem('contributors');
            foreach ($contributors as $contributor) {
                $contributorDOM = $this->workElem('contributor');
                $contributorDOM->appendChild(
                    $this->workElem('credit-name', $contributor['credit-name'])
                );

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