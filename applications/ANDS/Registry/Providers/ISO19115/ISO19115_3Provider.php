<?php


namespace ANDS\Registry\Providers\ISO19115;

use ANDS\Registry\Providers\RegistryContentProvider;
use ANDS\RegistryObject;
use ANDS\Registry\Transforms;
use \DOMDocument as DOMDocument;
use \Exception;
use \ANDS\Registry\Schema;
use ANDS\RegistryObject\AltSchemaVersion;

class ISO19115_3Provider implements RegistryContentProvider
{
    private static $schema_uri = 'http://standards.iso.org/iso/19115/-3/mdb/1.0';
    private static $origin = "REGISTRY";
    public static function process(RegistryObject $record)
    {

        if(!static::isValid($record))
            return false;

        $existing = AltSchemaVersion::where('prefix', Schema::getPrefix(static::$schema_uri))
            ->where('registry_object_id', $record->id)->first();
// don't generate one if we have an other instance from different origin eg HARVESTER
        if($existing && $existing->origin != static::$origin)
            return false;


        $iso = static::generateISO($record->getCurrentData()->data);
        $schema = Schema::where('uri', static::$schema_uri)->first();

        if($schema == null){

            $schema = new Schema();
            $schema->setRawAttributes([
                'prefix' => Schema::getPrefix(static::$schema_uri),
                'uri' => static::$schema_uri
            ]);
            $schema->save();
        }

        $record->addVersion($iso, static::$schema_uri, static::$origin);

        return true;

    }


    public static function get(RegistryObject $record)
    {
        $existingVersion = AltSchemaVersion::where('prefix', Schema::getPrefix(static::$schema_uri))
            ->where('registry_object_id', $record->id)->first();
        if ($existingVersion) {
            if ($record->modified_at > $existingVersion->version->updated_at) {
                static::process($record);
                $existingVersion = AltSchemaVersion::where('prefix', Schema::getPrefix(static::$schema_uri))
                    ->where('registry_object_id', $record->id)->first();
            }
            return $existingVersion->version->data;
        }

        if(static::process($record)){
            $existingVersion = AltSchemaVersion::where('prefix', Schema::getPrefix(static::$schema_uri))
                ->where('registry_object_id', $record->id)->first();
            return $existingVersion->version->data;
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
