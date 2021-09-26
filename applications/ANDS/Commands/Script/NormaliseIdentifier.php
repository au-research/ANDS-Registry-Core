<?php


namespace ANDS\Commands\Script;


use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
use ANDS\Registry\Providers\RIFCS\IdentifierProvider;
use ANDS\Registry\Providers\RelationshipProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class NormaliseIdentifier
 * @package ANDS\Commands\Script
 * This script is used to update records that are Identified in a relatedInfo Relationship by an Identifier that is
 * not th exact representation that the registry object is Identified by
 * eg DOI is using full resolvable vs DOI only or http vs https resolving
 * those Identifiers are normalised in this release, but to get them reflect the changes those records and their related infos need to be updated
 * This script will do them all, update and reindex the records
 * to run the script after the release
 * run the sql query registry/etc/db/mysql/identifier_temp_tables.sql from the etc dir
 * run the command in the root folder of the registry : "php ands.php run normaliseIdentifier"
 */

class NormaliseIdentifier extends GenericScript implements GenericScriptRunnable
{

    private $conn = null;
    private $pageSize = 100;

    public function run()
    {
        $this->info("Running");
        $db_conf = Config::get("database.default.hostname");
        $servername = $db_conf["hostname"];
        $username = $db_conf["username"];
        $password = $db_conf["password"];
        $port = $db_conf["port"];
        $database = $db_conf["database"];

        $this->conn = mysqli_connect($servername, $username, $password, $database, $port);
        $this->process_identifiers();
        $this->process_identifier_relationships();
        $idexableIDs = $this->getids_to_index();
        $this->info("id size: ". sizeof($idexableIDs));
        $this->processRecords($idexableIDs);
        $this->info("id size: ". sizeof($idexableIDs));

        $this->conn->close();
    }


    private function process_identifiers()
    {
        try {
            // var_dump($conn);
            $identifiers = mysqli_query($this->conn, "select * from `registry_object_identifiers`");
            $identifierCount = mysqli_num_rows($identifiers);
            mysqli_free_result($identifiers);
            $page = 0;
            while ($this->pageSize * $page < $identifierCount) {
                $result = mysqli_query($this->conn, "select * from `registry_object_identifiers` order by `id` asc limit " . $this->pageSize . " offset " . $page * $this->pageSize);
                //$entries = mysqli_fetch_assoc($result);

                while ($entry = mysqli_fetch_assoc($result)) {
                    $identifier = IdentifierProvider::getNormalisedIdentifier($entry["identifier"], $entry["identifier_type"]);
                    if ($identifier["value"] != $entry["identifier"] || $identifier["type"] != $entry["identifier_type"]) {
                        //var_dump($identifier);
                        $this->info($identifier["value"] . " << " . $entry["identifier"] . "   " . $identifier["type"] . " << " . $entry["identifier_type"] . "\n");
                    }
                    $id = $entry['id'];
                    $registry_object_id = $entry['registry_object_id'];
                    $identifier_value = mysqli_real_escape_string($this->conn, $identifier["value"]);
                    $identifier_type = mysqli_real_escape_string($this->conn, $identifier["type"]);
                    $sql = "INSERT INTO `registry_object_identifiers_normalised`(`id`,`registry_object_id`,`identifier`,`identifier_type`)
                                    VALUES ( $id , $registry_object_id ,'$identifier_value' , '$identifier_type' );";

                    $success = mysqli_query($this->conn, $sql);
                    if (!$success) {
                        $this->info($sql . "\n");
                        $this->info(mysqli_error($this->conn));
                        exit();
                    }
                }
                mysqli_free_result($result);
                $this->conn->commit();
                $page += 1;
            }
        } catch (Exception $e) {
            var_dump($e);
        }

    }


    private function process_identifier_relationships()
    {
        try {

            $identifiers = mysqli_query($this->conn, "select * from `registry_object_identifier_relationships`");
            $identifierCount = mysqli_num_rows($identifiers);
            mysqli_free_result($identifiers);
            print("DOING relationships\n");
            $page = 0;
            while ($this->pageSize * $page < $identifierCount) {
                $result = mysqli_query($this->conn, "select * from `registry_object_identifier_relationships` order by `id` asc limit " . $this->pageSize . " offset " . $page * $this->pageSize);
                //$entries = mysqli_fetch_assoc($result);

                while ($entry = mysqli_fetch_assoc($result)) {
                    $identifier = IdentifierProvider::getNormalisedIdentifier($entry["related_object_identifier"], $entry["related_object_identifier_type"]);
                    if ($identifier["value"] != $entry["related_object_identifier"] || $identifier["type"] != $entry["related_object_identifier_type"]) {
                        $this->info($identifier["value"] . " << " . $entry["related_object_identifier"] . "   " . $identifier["type"] . " << " . $entry["related_object_identifier_type"] . "\n");
                    }

                    $id = $entry['id'];
                    $registry_object_id = $entry['registry_object_id'];
                    $related_object_identifier = mysqli_real_escape_string($this->conn, $identifier["value"]);
                    $related_info_type = mysqli_real_escape_string($this->conn, $entry['related_info_type']);
                    $related_object_identifier_type = mysqli_real_escape_string($this->conn, $identifier["type"]);
                    $relation_type = mysqli_real_escape_string($this->conn, $entry['relation_type']);
                    $related_title = mysqli_real_escape_string($this->conn, $entry['related_title']);
                    $related_url = "";//$entry['related_url'];
                    $related_description = "";//$entry['related_description'];
                    $connections_preview_div = "";// $entry['connections_preview_div'];
                    $notes = "";//$entry['notes'];
                    $sql = "INSERT INTO `registry_object_identifier_relationships_normalised`
                            (`id`,
                            `registry_object_id`,
                            `related_object_identifier`,
                            `related_info_type`,
                            `related_object_identifier_type`,
                            `relation_type`,
                            `related_title`,
                            `related_url`,
                            `related_description`,
                            `connections_preview_div`,
                            `notes`)
                            VALUES
                            ($id,
                            $registry_object_id,
                            '$related_object_identifier',
                            '$related_info_type',
                            '$related_object_identifier_type',
                            '$relation_type',
                            '$related_title',
                            '$related_url',
                            '$related_description',
                            '$connections_preview_div',
                            '$notes');";
                    $success = mysqli_query($this->conn, $sql);
                    if (!$success) {
                        $this->info($sql . "\n");
                        $this->info(mysqli_error($this->conn));
                        exit();
                    }
                }
                mysqli_free_result($result);
                $this->conn->commit();
                $page += 1;
            }


        } catch (Exception $e) {
            var_dump($e);
        }

    }

    private function getids_to_index()
    {
        $sql = "SELECT irn.to_id , irn.from_id FROM identifier_relationships_normalised irn, identifier_relationships ir 
            where irn.relation_identifier_id = ir.relation_identifier_id and irn.to_id is not null and ir.to_id is null;";
        $id_array = [];
        $result = mysqli_query($this->conn, $sql);
        //$entries = mysqli_fetch_assoc($result);

        while ($entry = mysqli_fetch_assoc($result)) {
            $fromId = $entry['from_id'];
            $toId = $entry['to_id'];
            if(!(in_array($fromId, $id_array))){
                array_push($id_array, $fromId);
            }
            if(!(in_array($toId, $id_array))){
                array_push($id_array, $toId);
            }
        }
        mysqli_free_result($result);
        return $id_array;
    }


    private function processRecords($id_array){
        foreach($id_array as $id){
            $this->info($id);
            $record = RegistryObjectsRepository::getRecordByID($id);
            // no need to process them here the sync API will do it
            //IdentifierProvider::process($record);
            //RelationshipProvider::process($record);
            $this->syncRecord($record);
        }
    }

    private function syncRecord(RegistryObject $record)
    {

        // TODO: Workaround CI limitation, have to call internal API
        $client = new Client([
            'base_uri' => baseUrl("api/registry/object/"),
            'timeout'  => 360,
        ]);

        try {
            // mark record
            $record->setRegistryObjectAttribute('processing_by', uniqid());
            $url = baseUrl("api/registry/object/$record->id/sync");
            $response = $client->get($url);
            // unmark record
            $record->deleteRegistryObjectAttribute('processing_by');

            $body = (string) $response->getBody();
            $response_msg = json_decode($body);
            $this->info($response_msg->status);
            return true;
        } catch (RequestException $e) {
            $this->error($e->getMessage());
            return false;
        }
    }
}