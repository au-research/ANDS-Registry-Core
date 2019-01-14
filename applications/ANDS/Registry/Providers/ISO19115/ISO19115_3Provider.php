<?php


namespace ANDS\Registry\Providers\ISO19115;


use ANDS\RegistryObject;
use \Transforms;

class ISO19115_3Provider implements RegistryContentProvider
{


    public function process(RegistryObject $record){

            try{
                $xslt_processor = Transforms::get_extrif_to_iso19115_3_transformer();
                $dom = new DOMDocument();
                $dom->loadXML($record->getCurrentData()->data, LIBXML_NOENT);

                return trim($xslt_processor->transformToXML($dom));
            }catch (Exception $e)
            {
                echo "UNABLE TO TRANSFORM" . BR;
                echo "<pre>" . nl2br($e->getMessage()) . "</pre>" . BR;
            }

    }


    public function get(RegistryObject $record){
        return $this->process($record);

    }

}
