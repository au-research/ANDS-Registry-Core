<?php


namespace ANDS\Registry\Providers\ISO19115;

use ANDS\Registry\Providers\RegistryContentProvider;
use ANDS\RegistryObject;
use ANDS\Registry\Transforms;
use \DOMDocument as DOMDocument;
use \Exception;
use ANDS\RegistryObject\AltSchemaVersion;

class ISO19115_3Provider implements RegistryContentProvider
{
    private static $schema = 'iso19115-3';

    public static function process(RegistryObject $record)
    {

        if(!static::isValid($record))
            return false;

        $iso = static::generateISO($record->getCurrentData()->data);

        if ($existing = AltSchemaVersion::where('registry_object_id', $record->id)->where('schema', static::$schema)->first()) {
            $existing->data = $iso;
            $existing->hash = md5($iso);
            $existing->updated_at = date("Y-m-d G:i:s");
            $existing->save();
            return true;
        }

        $new = new AltSchemaVersion;
        $new->setRawAttributes([
            'data' => $iso,
            'hash' => md5($iso),
            'schema' => static::$schema,
            'registry_object_id' => $record->id,
            'registry_object_group' => $record->group,
            'registry_object_key' => $record->key,
            'registry_object_data_source_id' => $record->data_source_id,
            'updated_at' => date("Y-m-d G:i:s")
        ]);
        $new->save();

        return true;

    }


    public static function get(RegistryObject $record)
    {
        $existing = AltSchemaVersion::where('registry_object_id', $record->id)->where('schema', static::$schema)->first();

        if ($existing) {
            if ($record->modified_at > $existing->updated_at) {
                static::process($record);
                $existing = AltSchemaVersion::where('registry_object_id', $record->id)->where('schema', static::$schema)->first();
            }
            return $existing->data;
        }

        if(static::process($record)){
            $existing = AltSchemaVersion::where('registry_object_id', $record->id)->where('schema', static::$schema)->first();
            return $existing->data;
        }

        return null;

    }

    private static function isValid(RegistryObject $record){
        if($record->class != 'service')
            return false;
        if(strpos($record->type , 'OGC:') !== 0)
            return false;
        return true;
    }

    private static function generateISO($recordData){

        try {
            $xslt_processor = Transforms::get_extrif_to_iso19115_3_transformer();
            $dom = new DOMDocument();
            $dom->loadXML($recordData, LIBXML_NOENT);
            return trim($xslt_processor->transformToXML($dom));


        } catch (Exception $e) {
            echo "UNABLE TO TRANSFORM" . BR;
            echo "<pre>" . nl2br($e->getMessage()) . "</pre>" . BR;
        }
    }


    public static function validateContent($xml, $schemaPath = "/etc/schema/19115/-3/mdb/1.0/mdb.xsd")
    {

        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->loadXML($xml);
        if (!$doc) {
            throw new Exception("Unable to parse XML. Perhaps your XML file is not well-formed?");
        }

        // TODO: Does this cache in-memory?
        libxml_use_internal_errors(true);
        $validation_status = $doc->schemaValidate(
            BASE . $schemaPath
        );
        if ($validation_status === true) {
            libxml_use_internal_errors(false);
            return true;
        } else {
            $errors = libxml_get_errors();
            $error_string = '';
            foreach ($errors as $error) {
                $error_string .= TAB . "Line " . $error->line . ": " . $error->message;
            }
            libxml_clear_errors();
            libxml_use_internal_errors(false);
            throw new Exception("Unable to validate XML document against schema: " . NL . $error_string);
        }
    }

}
