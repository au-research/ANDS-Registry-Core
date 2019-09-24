<?php


namespace ANDS\API\Task\ImportSubTask;


use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Repository\RegistryObjectsRepository;
use Carbon\Carbon;

/**
 * Class IndexRelationship
 * Index Relationship data for importedRecords
 *
 * @package ANDS\API\Task\ImportSubTask
 */
class IndexRelationship extends ImportSubTask
{
    protected $title = "INDEXING RELATIONSHIP";

    public function run_task()
    {
        $targetStatus = $this->parent()->getTaskData('targetStatus');
        if (!RegistryObjectsRepository::isPublishedStatus($targetStatus)) {
            $this->log("Target status is ". $targetStatus.' No indexing required');
            return;
        }

        $this->parent()->getCI()->load->library('solr');

        $importedRecords = $this->parent()->getTaskData("importedRecords") ? $this->parent()->getTaskData("importedRecords") : [];

        $affectedRecords = $this->parent()->getTaskData("affectedRecords") ? $this->parent()->getTaskData("affectedRecords") : [];

        $totalRecords = array_merge($importedRecords, $affectedRecords);
        $totalRecords = array_values(array_unique($totalRecords));

        $total = count($totalRecords);

        if ($total == 0) {
            $this->log("No records needed to be reindexed");
            return;
        }

        $this->parent()->updateHarvest(
            ["importer_message" => "Indexing $total importedRecords"]
        );

        $this->log("Indexing $total records");

        // TODO: MAJORLY REFACTOR THIS
        foreach ($totalRecords as $index => $roID) {
            $record = RegistryObjectsRepository::getRecordByID($roID);
            if (!$record) {
                $this->addError("No Record with id $roID found to be indexed");
                continue;
            }

            $allRelationships = RelationshipProvider::getMergedRelationships($record);


            // save relations_count
            $record->setRegistryObjectAttribute('relations_count', count($allRelationships));

            // update portal index
            try {
                $this->updatePortalIndex($record, $allRelationships);
            } catch (\Exception $e) {
                $message = $e->getMessage();
                if ($message == "") {
                    $trace = $e->getTrace();
                    $first = array_shift($trace);
                    $message = implode(' ', $first['args']);
                    $this->addError($message);
                } else {
                    $this->addError($message);
                }
                continue;
            }


            // update relation index
            try {
                $this->updateRelationIndex($record, $allRelationships);
            } catch (\Exception $e) {
                $message = $e->getMessage();
                if ($message == "") {
                    $trace = $e->getTrace();
                    $first = array_shift($trace);
                    $message = implode(' ', $first['args']);
                    $this->addError($message);
                } else {
                    $this->addError($message);
                }
                continue;
            }

            // save last_sync_relations
            $record->setRegistryObjectAttribute('indexed_relations_at', Carbon::now()->timestamp);

            $this->updateProgress(
                $index, $total, "Processed ($index/$total) $record->title($roID)"
            );

        }

//        $this->parent()->getCI()->solr->init()->setCore('portal')->commit();
//        $this->parent()->getCI()->solr->init()->setCore('relations')->commit();
    }

    /**
     * @param $record
     * @param $relationships
     */
    public function updatePortalIndex($record, $relationships)
    {
        // if there's no relationship, quit
        if (count($relationships) == 0) {
            return;
        }

        $this->parent()->getCI()->solr->init()->setCore('portal');
        // update portal index
        $updateDoc = [];

        foreach ($relationships as $relation) {
            $rel = $relation->format();
            if (!$rel['to_id']) {
                continue;
            }
            $class = $rel['to_class'];
            if ($rel['to_class'] == 'party') {
                if ($rel['to_type'] == "group") {
                    $class = "party_multi";
                } else {
                    $class = "party_one";
                }
            }

            $relationType = is_array($rel['relation_type']) ? $rel['relation_type'] : [$rel['relation_type']];
            $relationType = collect($relationType)->flatten()->toArray();
            $updateDoc["related_".$class."_id"][] = $rel['to_id'];
            $updateDoc["related_".$class."_title"][] = $rel['to_title'];
            foreach ($relationType as $type) {
                if ($type) {
                    $updateDoc["relationType_".$type."_id"][] = $rel['to_id'];
                }
            }
        }

        // if there's no valid relationship, then quit
        if (count($updateDoc) == 0) {
            return;
        }

        $updateDoc['id'] = $record->registry_object_id;

        // relation_grants_isFundedBy
        // relation_grants_isOutputOf
        // relation_grants_isPartOf

        foreach ($updateDoc as $key => &$value) {
            if (is_array($value)) {
                $value = collect($value)->flatten()->values()->toArray();
            }
            if ($key != "id") {
                $value = ["set" => $value];
            }
        }

        $result = $this->parent()->getCI()->solr->add_json(json_encode([$updateDoc]));
        $result = json_decode($result, true);
        if (array_key_exists('error', $result)) {
            $this->addError("portal for $record->registry_object_id: ". $result['error']['msg']);
        }
    }

    /**
     * @param $record
     * @param $relationships
     */
    public function updateRelationIndex($record, $relationships)
    {
        if (count($relationships) == 0) {
            return;
        }


        // delete all from_id
        $this->parent()->getCI()->solr->init()->setCore('relations');
        $this->parent()->getCI()->solr->deleteByQueryCondition('+from_id:'.$record->registry_object_id);

        // add
        $docs = $this->getRelationshipIndex($relationships);

        $result = $this->parent()->getCI()->solr->add_json(json_encode($docs));
        $result = json_decode($result, true);
        if (array_key_exists('error', $result)) {
            $this->addError("relations for $record->registry_object_id: ". $result['error']['msg']);
        }
    }
    
    

    /**
     * @param $relationships
     * @return array
     */
    public function getRelationshipIndex($relationships)
    {
        $docs = [];
        foreach ($relationships as $key => $relation) {
            $doc = $relation->format([
                'to_identifier' => 'relation_identifier_identifier',
                'to_identifier_type' => 'relation_identifier_type'
            ]);
            unset($doc['related_description']);
            $doc['id'] = $key;
            unset($doc['from_data_source_id']);
            unset($doc['to_data_source_id']);
            $doc['relation'] = [$doc['relation_type']];
            unset($doc['relation_type']);
            if (!is_array($doc['relation'])) {
                $doc['relation'] = [$doc['relation']];
            }
            if (isset($doc['relation_origin']) && !is_array($doc['relation_origin'])) {
                $doc['relation_origin'] = [$doc['relation_origin']];
            }

            // to_finder is the title
            if ((in_array('funds', $doc['relation']) ||
                    in_array('isFundedBy', $doc['relation']))
                && in_array($doc['to_class'], ['activity', 'collection'])
            ) {
                $doc['to_funder'] = $doc['from_title'];
            }

            // identifier_relationship
            if (array_key_exists('relation_identifier_identifier', $doc)) {
                $doc['to_class'] = $doc['to_related_info_type'];
                $doc['to_type'] = $doc['to_related_info_type'];
                if(trim($doc['to_title']) == ''){
                    $doc['to_title'] = $doc['relation_to_title'] != "" ? $doc['relation_to_title'] : $doc['relation_identifier_identifier'];
                }
                $doc['relation_identifier_url'] = getIdentifierURL($doc['relation_identifier_type'], $doc['relation_identifier_identifier']);
                if (is_array($doc['relation_identifier_id'])) {
                    $doc['relation_identifier_id'] = array_first($doc['relation_identifier_id']);
                }
            }

            // set certain things to false when they not exist
            foreach ($doc as &$field) {
                if ($field === null) {
                    $field = false;
                }
            }

            $docs[] = $doc;
        }

        return $docs;
    }
}