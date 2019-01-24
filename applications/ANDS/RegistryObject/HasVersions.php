<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 23/1/19
 * Time: 12:16 PM
 */


namespace ANDS\RegistryObject;

use ANDS\Registry\Schema;
use ANDS\Registry\Versions;

trait HasVersions
{
    public function addVersion($data, $schemaURI, $origin='REGISTRY')
    {

        $schema = Schema::where('uri', $schemaURI)->first();

        if($schema == null){

            $schema = new Schema();
            $schema->setRawAttributes([
                'prefix' => Schema::getPrefix($schemaURI),
                'uri' => $schemaURI
            ]);
            $schema->save();
        }

        $existingVersion = $this->getVersionBySchemaURI($schema->uri);

        if($existingVersion){

            echo "UPDATING";
            return;
        }

        $newVersion = new \ANDS\Registry\Versions();
        $newVersion->setRawAttributes([
                    'data' => $data,
                    'hash' => md5($data),
                    'origin' => $origin,
                    'schema_id' => $schema->id,
                    'updated_at' => date("Y-m-d G:i:s")
                ]);
        $newVersion->save();
        // check if the schema exists, if not, create it
        $registry_object_version = new RegistryObjectVersion(['version_id'=>$newVersion->id, 'registry_object_id'=>$this->id]);
        $registry_object_version->save();
        // create the relation based on the class versionRelationModel
        return;
    }

    public function versions()
    {
        return $this->hasMany($this->versionRelationModel,  $this->versionRelationForeignKey);
    }


    public function getVersionBySchemaURI($schemaURI)
    {
        $schema = Schema::where('uri', $schemaURI)->first();
        $versionIDs = $this->versions()->get()->pluck('version_id');
        if(sizeof($versionIDs) > 0){
            $version = \ANDS\Registry\Versions::where('schema_id', $schema->id)->whereIn('id', $versionIDs)->first();
            if($version)
                return $version;
        }

        return null;
    }
}