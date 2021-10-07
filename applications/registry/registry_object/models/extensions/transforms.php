<?php

class Transforms_Extension extends ExtensionBase
{

	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}

    function transformForQA($xml, $data_source_key = null, $output = "script")
    {
        try{
            $xslt_processor = Transforms::get_qa_transformer();
            $dom = new DOMDocument();
            $dom->loadXML(str_replace('&', '&amp;' , $xml), LIBXML_NOENT);
            $xslt_processor->setParameter('','dataSource', $data_source_key ?: $this->ro->data_source_key );
            $xslt_processor->setParameter('','relatedObjectClassesStr',$this->ro->getRelatedClassesString());
            $xslt_processor->setParameter("","output", $output);
            return $xslt_processor->transformToXML($dom);
        }catch (Exception $e)
        {
            echo "UNABLE TO TRANSFORM" . BR;
            echo "<pre>" . nl2br($e->getMessage()) . "</pre>" . BR;
        }
    }

	function transformForHtml($revision='', $data_source_key = null)
	{
		try{
			$xslt_processor = Transforms::get_rif_to_html_transformer();
			$dom = new DOMDocument();
			$dataSource = $this->ro->data_source_key;
			if($revision=='') {
				$dom->loadXML(wrapRegistryObjects($this->ro->getRif()));
			}else $dom->loadXML(wrapRegistryObjects($this->ro->getRif($revision)));
			$xslt_processor->setParameter('','dataSource', $data_source_key ?: $this->ro->data_source_key );
			return html_entity_decode($xslt_processor->transformToXML($dom));
		}catch (Exception $e)
		{
			echo "UNABLE TO TRANSFORM" . BR;
			echo "<pre>" . nl2br($e->getMessage()) . "</pre>" . BR;
		}
	}


	function cleanRIFCSofEmptyTags($rifcs, $removeFormAttributes='true', $throwExceptions = false){
		try{
			$xslt_processor = Transforms::get_form_to_cleanrif_transformer();
			$dom = new DOMDocument();
			//$dom->loadXML($this->ro->getXML());
			$dom->loadXML(str_replace('&', '&amp;' , $rifcs), LIBXML_NOENT);
			//$dom->loadXML($rifcs);
			$xslt_processor->setParameter('','removeFormAttributes',$removeFormAttributes);
			return $xslt_processor->transformToXML($dom);
		}catch (Exception $e)
		{

			if($throwExceptions)
			{
				throw new Exception("UNABLE TO TRANSFORM" . nl2br($e->getMessage()));
			}
			else{
				echo "UNABLE TO TRANSFORM" . BR;
				echo "<pre>" . nl2br($e->getMessage()) . "</pre>" . BR;
			}
		}
	}

    function substringAfter($string, $substring) {
        $pos = strpos($string, $substring);
        if ($pos === false)
            return $string;
        else
            return(substr($string, $pos+strlen($substring)));
    }

}
	
