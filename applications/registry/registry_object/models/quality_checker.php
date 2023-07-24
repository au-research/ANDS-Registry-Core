<?php


class Quality_checker extends CI_Model {
			
			
	function get_quality_test_result($registry_object, $relatedClassStr, $output_mode = 'xml')
	{
		$xslt_processor = Transforms::get_qa_transformer();
		
		$dom = new DOMDocument();
		$dom->loadXML(str_replace('&', '&amp;' , $registry_object->getRif()), LIBXML_NOENT);
		$xslt_processor->setParameter('', 'dataSource', $registry_object->data_source_id);
		$xslt_processor->setParameter('', 'output', $output_mode);
		$xslt_processor->setParameter('', 'relatedObjectClassesStr', $relatedClassStr); // XXX: TODO!!!
		return $xslt_processor->transformToXML($dom);
	}
	
	function get_qa_level_test_result($registry_object, $relatedClassStr)
	{
		$xslt_processor = Transforms::get_qa_level_transformer();
		
		$dom = new DOMDocument();
		$dom->loadXML(str_replace('&', '&amp;' , $registry_object->getRif()), LIBXML_NOENT);
		$xslt_processor->setParameter('', 'relatedObjectClassesStr', $relatedClassStr); // XXX: TODO!!!
		return $xslt_processor->transformToXML($dom);
	}
	
	
	
	/*function runQualityCheck($rifcs, $objectClass, $dataSource, $output, $relatedObjectClassesStr='')
{
	global $qualityTestproc;
	$relRifcs = getRelatedXml($dataSource,$rifcs,$objectClass);
	$registryObjects = new DomDocument();
	$registryObjects->loadXML($relRifcs);
	$qualityTestproc->setParameter('', 'dataSource', $dataSource);
	$qualityTestproc->setParameter('', 'output', $output);
	$qualityTestproc->setParameter('', 'relatedObjectClassesStr', $relatedObjectClassesStr);
	$result = $qualityTestproc->transformToXML($registryObjects);
	return $result;
}
	 * */
			
			
	
	/**
	 * @ignore
	 */
	function __construct()
	{
		parent::__construct();
		include_once("_registry_object.php");
		include_once("_transforms.php");
	}	
		
}
