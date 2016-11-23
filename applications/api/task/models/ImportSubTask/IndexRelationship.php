<?php


namespace ANDS\API\Task\ImportSubTask;


use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class IndexRelationship
 * Index Relationship data for importedRecords
 *
 * @package ANDS\API\Task\ImportSubTask
 */
class IndexRelationship extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "INDEXING RELATIONSHIP";

    public function run_task()
    {
        $targetStatus = $this->parent()->getTaskData('targetStatus');
        if (!RegistryObjectsRepository::isPublishedStatus($targetStatus)) {
            $this->log("Target status is ". $targetStatus.' No indexing required');
            return;
        }

        $this->parent()->getCI()->load->library('solr');

        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);

        $this->parent()->updateHarvest(
            ["importer_message" => "Indexing $total importedRecords"]
        );

        return;

        // TODO: MAJORLY REFACTOR THIS
        foreach ($importedRecords as $index => $roID) {
            $record = RegistryObjectsRepository::getRecordByID($roID);

            $directRelationship = RelationshipProvider::getDirectRelationship($record);
            $reverseRelationship = RelationshipProvider::getReverseRelationship($record);
            $grantRelationship = RelationshipProvider::getGrantsRelationship($record);

            // update portal index
            $this->updatePortalIndex($record, [
                'directRelationship' => $directRelationship,
                'grantRelationship' => $grantRelationship
            ]);

            // update relation index
            $this->updateRelationIndex($record, [
                'directRelationship' => $directRelationship,
                'grantRelationship' => $grantRelationship,
                'reverseRelationship' => $reverseRelationship
            ]);

            $this->updateProgress(
                $index, $total, "Processed ($index/$total) $record->title($roID)"
            );
        }

        $this->parent()->getCI()->solr->init()->setCore('portal')->commit();
        $this->parent()->getCI()->solr->init()->setCore('relations')->commit();
    }

    /**
     * @param $record
     * @param $relationships
     */
    public function updatePortalIndex($record, $relationships)
    {
        $directRelationship = $relationships['directRelationship'];
        $grantRelationship = $relationships['grantRelationship'];

        // update portal index
        $updateDoc = [
            'id' => $record->registry_object_id
        ];

        foreach ($directRelationship as $relation) {
            $rel = $relation->format();
            $class = $rel['to_class'];
            if ($rel['to_class'] == 'party') {
                if ($rel['to_type'] == "group") {
                    $class = "party_multi";
                } else {
                    $class = "party_one";
                }
            }
            $relationType = $rel['relation_type'];
            $updateDoc["related_".$class."_id"][] = $rel['to_id'];
            $updateDoc["related_".$class."_title"][] = $rel['to_title'];
            $updateDoc["relationType_".$relationType."_id"][] = $rel['to_id'];
        }

        // funder
        $funder = $grantRelationship['funder'];
        if ($funder) {
            $updateDoc["related_party_multi_id"][] = $funder->registry_object_id;
            $updateDoc["relation_grants_isFundedBy"][] = $funder->registry_object_id;
            $updateDoc["related_party_multi_title"][] = $funder->title;
            $updateDoc["relationType_isFundedBy_id"][] = $funder->registry_object_id;
        }

        //parents_activities
        $isOutputOf = $grantRelationship['parents_activities'];
        if (count($isOutputOf) > 0) {
            foreach ($isOutputOf as $grant) {
                $updateDoc["related_activity_id"][] = $grant['registry_object_id'];
                $updateDoc["relation_grants_isOutputOf"][] = $grant['registry_object_id'];
                $updateDoc["relationType_isOutputOf_id"][] = $grant['registry_object_id'];
                $updateDoc["related_activity_title"][] = $grant['title'];
            }
        }

        //parents collection
        $isPartof = $grantRelationship['parents_collections'];
        if (count($isPartof) > 0) {
            foreach ($isPartof as $parent) {
                $updateDoc["related_collection_id"][] = $parent['registry_object_id'];
                $updateDoc["relation_grants_isPartOf"][] = $parent['registry_object_id'];
                $updateDoc["relationType_isPartOf_id"][] = $parent['registry_object_id'];
                $updateDoc["related_collection_title"][] = $parent['title'];
            }
        }

        foreach ($updateDoc as $key => &$value) {
            if (is_array($value)) {
                $value = array_unique($value);
            }
            if ($key != "id") {
                $value = ["set" => $value];
            }
        }

        $this->parent()->getCI()->solr->add_json(json_encode([$updateDoc]));
    }

    /**
     * @param $record
     * @param $relationships
     */
    public function updateRelationIndex($record, $relationships)
    {
        $directRelationship = $relationships['directRelationship'];
        $reverseRelationship = $relationships['reverseRelationship'];
        $grantRelationship = $relationships['grantRelationship'];


    }
}