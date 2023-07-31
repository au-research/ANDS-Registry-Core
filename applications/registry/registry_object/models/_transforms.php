<?php

/* use static definitions to only load the transform 
 * XSLT once
 */
class Transforms {

	static $rif_to_html_transformer = NULL;
    static $rif_to_edit_form_transformer = NULL;
	static $form_to_cleanrif_transformer = NULL;
	static $clean_ns_transformer = NULL;
	static $rif_to_iso19115_transformer = NULL;

    static $qa_transformer = NULL;
    static $qa_level_transformer = NULL;


    static function get_qa_transformer()
    {
        if (is_null(self::$qa_transformer))
        {
            $rmdQualityTest = new DomDocument();
            $rmdQualityTest->load(REGISTRY_APP_PATH.'registry_object/transforms/quality_report.xsl');
            $qualityTestproc = new XSLTProcessor();
            $qualityTestproc->importStyleSheet($rmdQualityTest);
            self::$qa_transformer =	$qualityTestproc;
        }

        return self::$qa_transformer;
    }

    static function get_qa_level_transformer()
    {
        if (is_null(self::$qa_level_transformer))
        {
            $rmdQualityTest = new DomDocument();
            $rmdQualityTest->load(REGISTRY_APP_PATH.'registry_object/transforms/level_report.xsl');
            $qualityTestproc = new XSLTProcessor();
            $qualityTestproc->importStyleSheet($rmdQualityTest);
            self::$qa_level_transformer =	$qualityTestproc;
        }

        return self::$qa_level_transformer;
    }



	
	static function get_rif_to_html_transformer()
	{
		if (is_null(self::$rif_to_html_transformer))
		{
			$rifToHtml = new DomDocument();
			$rifToHtml->load(REGISTRY_APP_PATH.'registry_object/transforms/rif_to_html.xsl');
			$rifToHtmlproc = new XSLTProcessor();
			$rifToHtmlproc->importStyleSheet($rifToHtml);
			self::$rif_to_html_transformer =	$rifToHtmlproc;
		}
		return self::$rif_to_html_transformer;
	}
	

	static function get_rif_to_edit_form_transformer()
	{
		if (is_null(self::$rif_to_edit_form_transformer))
		{
			$rifToForm = new DomDocument();
			$rifToForm->load(REGISTRY_APP_PATH.'registry_object/transforms/rif_to_edit_form.xsl');
			$rifToFormProc = new XSLTProcessor();
			$rifToFormProc->importStyleSheet($rifToForm);
			self::$rif_to_edit_form_transformer =	$rifToFormProc;
		}

		return self::$rif_to_edit_form_transformer;
	}


	static function get_form_to_cleanrif_transformer()
	{
		if (is_null(self::$form_to_cleanrif_transformer))
		{
			$cleanEmtyTags = new DomDocument();
			$cleanEmtyTags->load(REGISTRY_APP_PATH.'registry_object/transforms/clean_empty_tags.xsl');
			$cleanEmtyTagsproc = new XSLTProcessor();
			$cleanEmtyTagsproc->importStyleSheet($cleanEmtyTags);
			self::$form_to_cleanrif_transformer =	$cleanEmtyTagsproc;
		}

		return self::$form_to_cleanrif_transformer;
	}

	static function get_clean_ns_transformer()
	{
		if (is_null(self::$clean_ns_transformer))
		{
			$cleanNS = new DomDocument();
			$cleanNS->load(REGISTRY_APP_PATH.'registry_object/transforms/clean_ns.xsl');
			$cleanNSproc = new XSLTProcessor();
			$cleanNSproc->importStyleSheet($cleanNS);
			self::$clean_ns_transformer =	$cleanNSproc;
		}
		return self::$clean_ns_transformer;
	}

    static function get_rif_to_iso19115_3_transformer()
    {
        if (is_null(self::$rif_to_iso19115_transformer))
        {
            $iso_xsl = new DomDocument();
            $iso_xsl->load(REGISTRY_APP_PATH.'registry_object/transforms/RIFCS-to-ISO19115-3.xsl');
            $isoProc = new XSLTProcessor();
            $isoProc->importStyleSheet($iso_xsl);
            self::$rif_to_iso19115_transformer = $isoProc;
        }

        return self::$rif_to_iso19115_transformer;
    }
	
}		