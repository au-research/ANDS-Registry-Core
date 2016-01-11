<?php
/**
 * Class:  SyncTask
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Task;

use \Exception as Exception;

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
                        } elseif($this->mode == 'clearIndex') {
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
            default:
                throw new Exception("No valid target found for TaskID: " . $this->getId() . " check parameters: " . $this->getParams());
                break;
        }
    }

    /**
     * Analyze a Data Source and spawn Sync task per chunk
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

            $params = array();

            if ($this->indexOnly) {
                $params['indexOnly'] = 'true';
            }

            if ($this->missingOnly) {
                $offset = ($i - 1) * $this->chunkSize;
                $end = $i * $this->chunkSize;
                if ($end > sizeof($ids)) $end = sizeof($ids) - 1;
                $chunkArray = array_slice($ids, $offset, $end);
                $params['type'] = 'ro';
                $params['id'] = implode(',',$chunkArray);
            } else {
                $params['type'] = 'ds';
                $params['id'] = $dsID;
                $params['chunkPos'] = $i;
            }


            $task = array(
                'name' => 'sync',
                'priority' => $this->getPriority(),
                'frequency' => 'ONCE',
                'type' => 'POKE',
                'params' => http_build_query($params),
            );

            $this->taskManager->addTask($task);
        }

        $this->log('Analyzed Data Source ' . $dsID . " spawned " . $data['numChunk'] . " sync tasks for " . $data['total'] . ' records');
    }

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

    private function clearIndexDS($dsID){
        $this->log('deleting the index of data source '.$dsID);
        $queryCondition = 'data_source_id:"'.$dsID.'"';
        $deleteResult = json_decode($this->ci->solr->deleteByQueryCondition($queryCondition),true);
        if (isset($deleteResult['responseHeader']) && $deleteResult['responseHeader']['status'] === 0) {
            $this->log("Delete Index Successful")->save();
        } else {
            throw new Exception(json_encode($deleteResult));
        }
    }

    /**
     * Sync a Data Source based on the Chunk Position
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

        $this->log('Syncing chunk ' . $this->chunkPos . ' of Data Source ' . $dsID . ' for ' . sizeof($ids) . ' records');
        try {
            $this->syncRO($ids);
        } catch (Exception $e) {
            throw new Exception('Error DSID:' . $dsID . ' Message: ' . $e->getMessage());
        }

    }

    /**
     * Sync a list of registry objects
     * Used by syncDS as well
     * @param $ids
     * @throws Exception
     */
    private function syncRO($ids)
    {
        $solr_docs = array();
        if (sizeof($ids) > 0) {
            foreach ($ids as $ro_id) {
                try {
                    $ro = false;
                    if ($ro_id) $ro = $this->ci->ro->getByID($ro_id);
                    if ($ro) {
                        if (!$this->indexOnly) {
                            $ro->processIdentifiers();
                            $ro->addRelationships();
                            $ro->update_quality_metadata();
                            $ro->enrich();
                        }
                        $solr_docs[] = $ro->indexable_json();
                        unset($ro);
                    } else {
                        $this->log('Error: roid:' . $ro_id . ' not found');
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
        if (sizeof($solr_docs) > 0) {
            try {
                $add_result = json_decode($this->ci->solr->add_json(json_encode($solr_docs)), true);
                if (isset($add_result['responseHeader']) && $add_result['responseHeader']['status'] === 0) {
                    $this->log("Adding to SOLR successful")->save();
                } else {
                    throw new Exception(json_encode($add_result));
                }
                $commit_result = json_decode($this->ci->solr->commit(), true);
                if (isset($commit_result['responseHeader']) && $commit_result['responseHeader']['status'] === 0) {
                    $this->log("Commit to Indexed successful")->save();
                } else {
                    throw new Exception(json_encode($commit_result));
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
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
        $this->missingOnly = isset($params['missingOnly']) ? $params['missingOnly'] : false;

        if ($this->indexOnly === 'true') $this->indexOnly = true;
        if ($this->missingOnly === 'true') $this->missingOnly = true;

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