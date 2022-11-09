<?php
namespace ANDS\Registry\ContentProvider\ANZCTR;
use ANDS\Util\ANZCTRUtil;
use DOMDocument;

class ContentProvider{

    private $content = null;
    private $indexableArray = [];

    public function get($identifier){
        $this->content = ANZCTRUtil::retrieveMetadata($identifier);
    }

    /**
     * @return
     */
    public function getIndexableArray(){

        $elements = ['publictitle','briefsummary','healthcondition','conditioncode1','conditioncode2','inclusivecriteria'];
        $dom = new DOMDocument;
        $dom->loadXML($this->content);
        foreach ($elements as $el){
            $element = $dom->getElementsByTagName($el);
            foreach ($element as $e) {
                $this->indexableArray[] = $e->nodeValue;
            }
        }
        return $this->indexableArray;
    }

    /**
     * @return
     */
    public function getContent(){
        return $this->content;
    }
}