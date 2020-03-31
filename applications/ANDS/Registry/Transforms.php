<?php

namespace ANDS\Registry;
use \DOMDocument as DomDocument;
use \XSLTProcessor as XSLTProcessor;

class Transforms
{
    static $rif_to_iso19115_3_transformer = NULL;

    /**
     * @return XSLTProcessor
     * to Transform rifcs xml to ISO19115-5
     */
    static function get_rif_to_iso19115_3_transformer()
    {
        if (is_null(self::$rif_to_iso19115_3_transformer))
        {
            $iso_xsl = new DomDocument();
            $iso_xsl->load(REGISTRY_APP_PATH.'registry_object/transforms/RIFCS-to-ISO19115-3.xsl');
            $isoProc = new XSLTProcessor();
            $isoProc->importStyleSheet($iso_xsl);
            self::$rif_to_iso19115_3_transformer = $isoProc;
        }

        return self::$rif_to_iso19115_3_transformer;
    }

}