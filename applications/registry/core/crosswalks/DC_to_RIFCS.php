<?php
/**
 * very basic example XSLT Crosswalk to transform records from Dublin Core RIFCS.
 *
 * @author u4187959
 * @created 10/04/2014
 */

class DC_to_RIFCS extends Crosswalk
{
	/**
	 * Identify this crosswalk (give a user-friendly name)
	 */
	public function identify()
	{
		return "Dublin Core to RIFCS";
	}

    /**
     * Internal name for this metadataFormat
     */
    public function metadataFormat()
    {
        return "oai_dc";
    }

    /**
     * Do the transformation of the payload to RIFCS.
     *
     * @return string A valid RIFCS XML string (including wrapper)
     */
    public function payloadToRIFCS($payload, &$log = array())
    {
        $dom = new DOMDocument();
        $dom->loadXML($payload);
        unset($payload);
        $log[] = "[CROSSWALK] Attempting to execute crosswalk " . $this->identify();
        $xsl = new DomDocument();
        $xsl->load(REGISTRY_APP_PATH.'core/crosswalks/_xsl/dc_to_rifcs.xsl');
        $transformer = new XSLTProcessor();
        $transformer->importStyleSheet($xsl);

        return $transformer->transformToXML($dom);

    }


    /**
     * Validate that this payload is valid CSV
     * TODO: define validaton rule until then say it's true
     */
    public function validate($payload)
    {
        return true;
    }

}
