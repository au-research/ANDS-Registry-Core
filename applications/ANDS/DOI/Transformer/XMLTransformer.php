<?php


namespace ANDS\DOI\Transformer;

use DOMDocument;
use XSLTProcessor;

class XMLTransformer
{
    /**
     * Migrate different kernel to kernel 4
     * Uses to_kernel-4_migration.xsl file
     *
     * @param $xmlString
     * @return string
     */
    public static function migrateToKernel4($xmlString)
    {
        $xml = new DOMDocument();
        $xml->loadXML($xmlString);

        return static::transform("to_kernel-4_migration", $xml);
    }

    /**
     * Common transform functionality
     *
     * @param $xslFileName
     * @param DOMDocument $xml
     * @return string
     * @throws \Exception
     */
    public static function transform($xslFileName, DOMDocument $xml)
    {
        $xsl = new DOMDocument;
        $xsl->load(__DIR__.'/../xslt/'.$xslFileName.'.xsl');

        $proc = new XSLTProcessor;
        $proc->importStyleSheet($xsl);

        libxml_use_internal_errors(true);

        $result = $proc->transformToXML($xml);

        foreach (libxml_get_errors() as $error) {
            //throw new \Exception($error->message);
        }

        return $result;
    }
}