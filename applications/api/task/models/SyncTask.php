<?php
/**
 * Class:  SyncTask
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Task;

use \Exception as Exception;

/**
 * Class SyncTask
 *
 * @package ANDS\API\Task
 */
class SyncTask extends Task
{
    private $target = false; //ds or ro
    private $target_id = false;
    private $chunkSize = 100;
    private $chunkPos = 0;
    private $indexOnly = false;
    private $missingOnly = false;
    private $mode = 'sync';

    private $taskManager;

    /**
     * todo sync by SOLR query (analyze)
     * Run the actual task
     * Loads CodeIgniter models and libraries as required
     *
     * @throws Exception
     */
    function run_task()
    {
        $this->loadParams();

        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');
        $this->ci->load->model('registry/data_source/data_sources', 'ds');
        $this->ci->load->library('solr');

        $this->taskManager = new TaskManager($this->getDb());

        switch ($this->target) {
            case 'ds' :
                $list = explode(',', $this->target_id);
                foreach ($list as $dsID) {
                    if ($dsID) {
                        if ($this->mode == 'analyze' && $this->target == 'ds') {
                            $this->analyzeDS($dsID);
                        } elseif ($this->mode == 'clearIndex') {
                            $this->clearIndexDS($dsID);
                        } else {
                            if ($this->mode == 'sync') {
                                $this->syncDS($dsID);
                            }
                        }
                    } else {
                        throw new Exception("No valid Data Source ID found");
                    }
                }
                break;
            case 'ro':
                $list = explode(',', $this->target_id);
                $this->syncRo($list);
                break;
            case 'all':
                $this->analyzeAll();
                break;
            default:
                throw new Exception("No valid target found for TaskID: " . $this->getId() . " check parameters: " . $this->getParams());
                break;
        }
    }

    private function analyzeAll()
    {
        $dataSourceIDs = $this->ci->ds->getAll(0, 0, true);
        foreach ($dataSourceIDs as $dsID) {

            $params = array(
                'class' => 'sync',
                'type' => 'ds',
                'id' => $dsID
            );

            if ($this->indexOnly) {
                $params['indexOnly'] = 'true';
            }

            if ($this->addRelationships) {
                $params['addRelationships'] = 'true';
            }

            $task = array(
                'name' => 'Analyze DataSource ' . $dsID,
                'priority' => $this->getPriority(),
                'frequency' => 'ONCE',
                'type' => 'POKE',
                'params' => http_build_query($params),
            );

            $this->taskManager->addTask($task);
        }
        $this->log('Analyzed All spawned ' . sizeof($dataSourceIDs) . ' tasks');
    }

    /**
     * Analyze a Data Source and spawn Sync task per chunk
     *
     * @param $dsID
     */
    private function analyzeDS($dsID)
    {
        $this->log('Analyzing Data Source ' . $dsID);

        if ($this->missingOnly) {
            $ids = $this->getMissingIDs($dsID);
        } else {
            $ids = $this->ci->ro->getIDsByDataSourceID($dsID, false, 'PUBLISHED');
        }

        $data['total'] = sizeof($ids);
        $data['chunkSize'] = $this->chunkSize;
        $data['numChunk'] = ceil(($this->chunkSize < $data['total'] ? ($data['total'] / $this->chunkSize) : 1));

        $this->log('Analyzing Data Source ' . $dsID);
        //spawn new tasks
        for ($i = 1; $i <= $data['numChunk']; $i++) {

            //determine the class to use is the SyncTask
            $params = array(
                'class' => 'sync'
            );

            if ($this->indexOnly) {
                $params['indexOnly'] = 'true';
            }

            if ($this->addRelationships) {
                $params['addRelationships'] = 'true';
            }

            //define chunking for ro only
            $chunkArray = array();
            if ($this->missingOnly) {
                $offset = ($i - 1) * $this->chunkSize;
                $end = $i * $this->chunkSize;
                if ($end > sizeof($ids)) {
                    $end = sizeof($ids) - 1;
                }
                $chunkArray = array_slice($ids, $offset, $end);
                $params['type'] = 'ro';
                $params['id'] = implode(',', $chunkArray);
            } else {
                $params['type'] = 'ds';
                $params['id'] = $dsID;
                $params['chunkPos'] = $i;
            }

            //construct a human readable name
            $name = $this->indexOnly ? "Index " : "Sync ";
            if ($this->missingOnly) {
                $name .= "Missing ";
            }

            if ($params['type'] == 'ds') {
                $name .= " Data Source " . $params['id'] . '(' . $i . '/' . $data['numChunk'] . ')';
            } elseif ($params['type'] == 'ro') {
                $name .= sizeof($chunkArray) . " Records";
            }

            //adding normal sync task
            $task = array(
                'name' => $name,
                'priority' => $this->getPriority(),
                'frequency' => 'ONCE',
                'type' => 'POKE',
                'params' => http_build_query($params),
            );
            $this->taskManager->addTask($task);

//            adding a graph generation task
//            Placeholder for future usage, do not remove for now
//            $params['class'] = 'graph';
//            $task = array(
//                'name' => $name,
//                'priority' => $this->getPriority(),
//                'frequency' => 'ONCE',
//                'type' => 'POKE',
//                'params' => http_build_query($params),
//            );
//            $this->taskManager->addTask($task);
        }

        $this->log('Analyzed Data Source ' . $dsID . " spawned " . $data['numChunk'] . " sync tasks for " . $data['total'] . ' records');
    }


    /**
     * Returns a list of un indexed registry object from a given data source ID
     *
     * @param $dsID
     * @return array
     */
    private function getMissingIDs($dsID)
    {
        //get all ids
        $databaseIDs = $this->ci->ro->getIDsByDataSourceID($dsID, false, 'PUBLISHED');
        $solrIDs = array();
        $solrQuery = $this->ci->solr
            ->init()
            ->setOpt('fq', '+data_source_id:' . $this->getID())
            ->setOpt('fl', 'id')
            ->setOpt('rows', '50000')
            ->executeSearch(true);
        foreach ($solrQuery['response']['docs'] as $doc) {
            $solrIDs[] = $doc['id'];
        }
        $result = array_diff($databaseIDs, $solrIDs);
        return $result;
    }

    /**
     * Clear the index of a particular data source
     *
     * @param $dsID
     * @throws Exception
     */
    private function clearIndexDS($dsID)
    {
        $this->log('deleting the index of data source ' . $dsID);
        $queryCondition = 'data_source_id:"' . $dsID . '"';
        $deleteResult = json_decode($this->ci->solr->deleteByQueryCondition($queryCondition), true);
        if (isset($deleteResult['responseHeader']) && $deleteResult['responseHeader']['status'] === 0) {
            $this->log("Delete Index Successful")->save();
        } else {
            throw new Exception(json_encode($deleteResult));
        }
    }

    /**
     * Sync a Data Source based on the Chunk Position
     *
     * @param $dsID
     * @throws Exception
     */
    private function syncDS($dsID)
    {
        if (!$this->chunkSize) {
            throw new Exception("No chunk defined for this sync task");
        }
        if (!$dsID) {
            throw new Exception("Data Source ID required");
        }
        $offset = ($this->chunkPos - 1) * $this->chunkSize;
        $limit = $this->chunkSize;
        $ids = $this->ci->ro->getIDsByDataSourceID($dsID, false, 'PUBLISHED', $offset, $limit);

        if (sizeof($ids) > 0) {
            $this->log('Syncing chunk ' . $this->chunkPos . ' of Data Source ' . $dsID . ' for ' . sizeof($ids) . ' records');
            try {
                $this->syncRO($ids);
            } catch (Exception $e) {
                throw new Exception('Error DSID:' . $dsID . ' Message: ' . $e->getMessage());
            }
        } else {
            $this->log('No records to sync for data source: ' . $dsID);
        }
    }

    /**
     * Sync a list of registry objects
     * Used by syncDS as well
     *
     * @param $ids
     * @throws Exception
     */
    private function syncRO($ids)
    {
        $solr_docs = array();
        $relation_docs = array();
        $remove_ids = array();

        if (sizeof($ids) > 0) {
            foreach ($ids as $ro_id) {
                try {
                    $ro = false;
                    if ($ro_id) {
                        $ro = $this->ci->ro->getByID($ro_id);
                    }
                    if ($ro && $ro->status != 'PUBLISHED') {
                        $this->log('roid:' . $ro_id . ' is a ' . $ro->status . ' record and should be removed from the index');
                        $remove_ids[] = $ro_id;
                    }
                    if ($ro && $ro->status == 'PUBLISHED') {

                        if (!$this->indexOnly) {
                            $ro->processIdentifiers();
                            $ro->addRelationships();
                            $ro->update_quality_metadata();
                            $ro->processLinks();
                        }

                        if ($this->addRelationships) {
                            $ro->addRelationships();
                        }

                        // index portal documents
                        $solr_doc = $ro->indexable_json();
                        if ($solr_doc && is_array($solr_doc) && sizeof($solr_doc) > 0) {
                            $solr_docs[] = $solr_doc;
                        } else {
                            $this->log('Empty doc found for ROID:' . $ro->id);
                        }

                        // index relation document
                        $relation_doc = $ro->getRelationshipIndex();
                        if ($relation_doc && is_array($relation_doc) && sizeof($relation_doc) > 0) {
                            $relation_docs = array_merge($relation_docs, $relation_doc);
                        }

                        unset($ro);
                    } else {
                        //doesn't exist in the database, queue it to be remove from SOLR
                        $this->log('Error: roid:' . $ro_id . ' not found');
                        if ($ro_id) {
                            $remove_ids[] = $ro_id;
                        }
                    }
                } catch (Exception $e) {
                    throw new Exception('Error: roid:' . $ro_id . ' Message: ' . $e->getMessage());
                }
            }
            $this->log('Importing Post Process and SOLR doc generation completed');
        } else {
            throw new Exception("No records found");
        }

        //indexing in SOLR
        $this->log('Indexing SOLR for Portal')->save();
        $this->indexSolr('portal', $solr_docs);
        $this->log('Indexing SOLR for Relations')->save();
        $this->indexSolr('relations', $relation_docs, true);

        //remove records
        if (sizeof($remove_ids) > 0) {
            try {
                $remove_result = json_decode($this->ci->solr->deleteByIDsCondition($remove_ids), true);
                if (isset($remove_result['responseHeader']) && $remove_result['responseHeader']['status'] === 0) {
                    $this->log("Remove IDS" . implode(', ', $remove_ids) . ' successful')->save();
                } else {
                    throw new Exception("Remove IDS: " . implode(', ',
                            $remove_ids) . ' FAILED: ' . json_encode($remove_result));
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * Index into a SOLR core a set of documents
     *
     * @param string $core
     * @param array  $solr_docs
     * @throws Exception
     */
    private function indexSolr($core = 'portal', $solr_docs = array(), $commit = false) {
        if (sizeof($solr_docs) > 0) {
            try {
                $this->ci->solr->setCore($core);

                $total = sizeof($solr_docs);
                $chunkSize = 500;
                $numChunk = ceil(($chunkSize < $total ? ($total / $chunkSize) : 1));

                for ($i = 1; $i <= $numChunk; $i++) {
                    $offset = ($i == 1) ? 0 : $i * $chunkSize;
                    $docs = array_slice($solr_docs, $offset, $chunkSize);
                    $add_result = json_decode($this->ci->solr->add_json(json_encode($docs)), true);
                    if (isset($add_result['responseHeader']) && $add_result['responseHeader']['status'] === 0) {
                        $this->log("Adding to SOLR successful $i/$numChunk")->save();
                    } else {
                        $this
                            ->log("Adding to SOLR failed: " . json_encode($add_result))
                            ->log("Attempting to POST each document separately")->save();
                        $this->separateSolrIndexing($docs);
                    }
                }

                /**
                 * Commenting out the following code because:
                 * If SOLR is set to auto soft commit, we don't need to do a hard commit here
                 */

                if ($commit) {
                    $commit_result = json_decode($this->ci->solr->commit(), true);
                    if (isset($commit_result['responseHeader']) && $commit_result['responseHeader']['status'] === 0) {
                        $this->log("Commit to Indexed successful")->save();
                    } else {
                        throw new Exception("Commit to SOLR failed: " . json_encode($commit_result));
                    }
                }

            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * Attempt to index into SOLR the documents separately
     * If a document failed, the entire task is recorded as failed
     * Records that aren't supposed to fail, shouldn't fail
     *
     * @param $docs
     */
    private function separateSolrIndexing($docs)
    {
        foreach ($docs as $doc) {
            $solr_docs = [$doc];
            $add_result = json_decode($this->ci->solr->add_json(json_encode($solr_docs)), true);
            if (isset($add_result['responseHeader']) && $add_result['responseHeader']['status'] === 0) {
                $this->log("Success for document : " . $doc['id'])->save();
            } else {
                $this->log("Error for document " . $doc['id'] . " Error: " . json_encode($add_result));
                //get error message and repost it without the troublesome field, namely spatial_coverage_extents_wkt
                if (isset($doc['spatial_coverage_extents_wkt'])) {
                    $message = $add_result['error']['msg'];
                    if (strpos($message, "Couldn't parse shape") >= 0) {
                        $this->indexRecordWithout($doc, 'spatial_coverage_extents_wkt');
                    }
                }
            }
        }
    }

    /**
     * Index a record in SOLR without a particular field
     * as a last resort
     *
     * @param $doc
     * @param $field
     */
    private function indexRecordWithout($doc, $field)
    {
        unset($doc[$field]);
        $solr_docs = [$doc];
        $add_result = json_decode($this->ci->solr->add_json(json_encode($solr_docs)), true);
        if (isset($add_result['responseHeader']) && $add_result['responseHeader']['status'] === 0) {
            $this->log("Success for document : " . $doc['id'] . ' without field: ' . $field)->save();
        } else {
            $this->log("Error for document " . $doc['id'] . " Error: " . json_encode($add_result));
            $this->errored = true;
        }
    }

    /**
     * Helper method
     * Analyze the parameters and load them into this object
     */
    private function loadParams()
    {
        //parsing parameters
        parse_str($this->params, $params);
        $this->target = isset($params['type']) ? $params['type'] : false;
        $this->target_id = isset($params['id']) ? $params['id'] : false;
        $this->indexOnly = isset($params['indexOnly']) ? $params['indexOnly'] : false;
        $this->addRelationships = isset($params['addRelationships']) ? $params['addRelationships'] : false;
        $this->missingOnly = isset($params['missingOnly']) ? $params['missingOnly'] : false;

        if ($this->indexOnly === 'true') {
            $this->indexOnly = true;
        }

        if ($this->addRelationships === 'true') {
            $this->addRelationships = true;
        }

        if ($this->missingOnly === 'true') {
            $this->missingOnly = true;
        }

        $this->log('Task parameters: ' . http_build_query($params))->save();

        //analyze if there is no chunkPosition
        if (isset($params['chunkPos'])) {
            $this->chunkPos = $params['chunkPos'];
            $this->mode = 'sync';
        } elseif (isset($params['clearIndex'])) {
            $this->mode = 'clearIndex';
        } else {
            $this->mode = 'analyze';
        }
    }
}