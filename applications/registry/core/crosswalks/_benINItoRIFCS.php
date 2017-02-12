<?php

class benINItoRIFCS extends Crosswalk
{

    private $parsed_array = null;

	/**
	 * Identify this crosswalk (give a user-friendly name)
	 */
	public function identify()
	{
		return "Ben's Custom INI to RIFCS";
	}

    public function metadataFormat()
    {
        return "benINI";
    }

 	public function validate($payload)
    {
    	$valid = true; 
    	$this->parsed_array = parse_ini_string($payload);
        if ($this->parsed_array === FALSE)
        {
            $valid = false;
        }
        return $valid;
    }

    public function payloadToRIFCS($payload)
    {
        $this->parsed_array = parse_ini_string($payload, TRUE);
        $rifcs_elts = array();

    	foreach($this->parsed_array AS $key => $attrs)
        {
            $registryObject = '<registryObject group="'.$attrs['group'].'">' . NL;
            $registryObject .='<key>' . $key . '</key>' . NL;
            $registryObject .='<originatingSource>Ben\'s INI Crosswalk Demo</originatingSource>' . NL;
            $registryObject .='<'.$attrs['class'].' type="'.$attrs['type'].'">' . NL;

            $registryObject .='<name type="primary"><namePart>'.$attrs['name'].'</namePart></name>' . NL;
            $registryObject .='<description type="full">'.$attrs['description'].'</description>' . NL;

            $registryObject .='<relatedInfo type="'.NATIVE_HARVEST_FORMAT_TYPE.'">' . NL;
            $registryObject .='<identifier type="internal">'.$this->metadataFormat().'</identifier>' . NL;
            $registryObject .='<notes><![CDATA[' . NL;
            $registryObject .= $this->wrapNativeFormat(php2ini(array($key => $attrs))) . NL;
            $registryObject .=']]></notes>' . NL;
            $registryObject .='</relatedInfo>' . NL;
            $registryObject .='</'.$attrs['class'].'>' . NL;
            $registryObject .='</registryObject>' . NL;

            $rifcs_elts[] = $registryObject;
        }

    	return trim(wrapRegistryObjects(implode("",$rifcs_elts)));
    }


    public function wrapNativeFormat($payload)
    {
        return $payload;
    }

}