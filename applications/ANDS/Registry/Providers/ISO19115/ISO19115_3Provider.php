<?php


namespace ANDS\Registry\Providers\ISO19115;

use \ANDS\Registry\Providers\RegistryContentProvider;
use \ANDS\Registry\Providers\RIFCS\SubjectProvider;
use \ANDS\RegistryObject;
use \ANDS\Registry\Transforms;
use \DOMDocument as DOMDocument;
use \Exception;
use \ANDS\Registry\Schema;
use \ANDS\Registry\Versions;
use ANDS\RegistryObject\RegistryObjectVersion;


class ISO19115_3Provider implements RegistryContentProvider
{
    private static $schema_uri = 'http://standards.iso.org/iso/19115/-3/mdb/1.0';
    private static $origin = "REGISTRY";

    public static function process(RegistryObject $record)
    {

        if(!static::isValid($record))
            return false;


        $schema = Schema::get(static::$schema_uri);

        $altVersionsIDs = RegistryObjectVersion::where('registry_object_id', $record->id)->get()->pluck('version_id')->toArray();
        $existing = null;

        if (count($altVersionsIDs) > 0) {
            $existing = Versions::wherein('id', $altVersionsIDs)->where("schema_id", $schema->id)->first();
        }

        // don't generate one if we have an other instance from different origin eg HARVESTER

       // if($existing && $existing->origin != static::$origin)
       //     $existing->data;
        // use the resolved subjects for the iso
        $subjects = SubjectProvider::processSubjects($record);
        $subjectStr = "";
        $pf= ",";
        foreach ($subjects as $key=>$subject)
        {
            $subjectStr .= $pf.$subject['resolved'];
            if($pf == "") $pf = ",";
        }

        $iso = static::generateISO($record->getCurrentData()->data, $subjectStr);

        $record->addVersion($iso, static::$schema_uri, static::$origin);

        return $iso;

    }

    public static function get(RegistryObject $record)
    {
        $schema = Schema::get(static::$schema_uri);

        $altVersionsIDs = RegistryObjectVersion::where('registry_object_id', $record->id)->get()->pluck('version_id')->toArray();
        $existing = null;

        if (count($altVersionsIDs) > 0) {
            $existing = Versions::wherein('id', $altVersionsIDs)->where("schema_id", $schema->id)->first();
        }
        if ($existing) {
            if ($record->modified_at > $existing->updated_at) {
                return static::process($record);
            }
            return $existing->data;
        }

        return static::process($record);
    }

    private static function isValid(RegistryObject $record){
        if($record->class != 'service')
            return false;
        if(strpos($record->type , 'OGC:') !== 0)
            return false;
        return true;
    }

    private static function generateISO($recordData, $subjects = ""){
        try {
            $xslt_processor = Transforms::get_extrif_to_iso19115_3_transformer();
            $xslt_processor->setParameter ('','subjects' , $subjects);
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
