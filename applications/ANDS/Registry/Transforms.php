<?php

namespace ANDS\Registry;
use \DOMDocument as DomDocument;
use \XSLTProcessor as XSLTProcessor;

class Transforms
{
    static $extrif_to_iso_19115_tranformer = NULL;


    static function get_extrif_to_iso19115_3_transformer()
    {
        if (is_null(self::$extrif_to_iso_19115_tranformer))
        {
            $iso_xsl = new DomDocument();
            $iso_xsl->load(REGISTRY_APP_PATH.'registry_object/transforms/RIFCS-to-ISO19115-3.xsl');
            $isoProc = new XSLTProcessor();
            $isoProc->importStyleSheet($iso_xsl);
            self::$extrif_to_iso_19115_tranformer = $isoProc;
        }

        return self::$extrif_to_iso_19115_tranformer;
    }

}