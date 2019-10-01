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
            $schema = Schema::create([
                'prefix' => Schema::getPrefix($schemaURI),
                'uri' => $schemaURI,
                'exportable' => 1]);
        }

        // quicker way of obtaining existing Versions
        $altVersionsIDs = RegistryObjectVersion::where('registry_object_id', $this->id)->get()->pluck('version_id')->toArray();
        $existing = null;
        $existing = Versions::wherein('id', $altVersionsIDs)->where("schema_id", $schema->id)->first();

        $hash = md5($data);

        if (!$existing) {
            $version = Versions::create([
                'data' => $data,
                'hash' => $hash,
                'origin' => $origin,
                'schema_id' => $schema->id,
            ]);
            RegistryObjectVersion::create([
                'version_id' => $version->id,
                'registry_object_id' => $this->id
            ]);
        } elseif ($hash != $existing->hash) {
            $existing->update([
                'data' => $data,
                'origin' => $origin,
                'hash' => $hash
            ]);
        } else {
            // update the timestamps so that the truth test in JsonLDPRovider passes
            $existing->touch();
        }

        return;
    }

    public function versions()
    {
        return $this->hasMany($this->versionRelationModel,  $this->versionRelationForeignKey);
    }

}