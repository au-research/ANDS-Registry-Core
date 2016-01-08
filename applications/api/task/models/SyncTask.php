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
    private $mode = 'sync';

    private $taskManager;

    /**
     * todo sync Registry Objects
     * todo sync by SOLR query
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
                        if ($this->mode == 'analyze') {
                            $this->analyzeDS($dsID);
                        } else if ($this->mode == 'sync') {
                            $this->syncDS($dsID);
                        }
                    } else {
                        throw new Exception("No valid Data Source ID found");
                    }
                }
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
        $ids = $this->ci->ro->getIDsByDataSourceID($dsID, false, 'PUBLISHED');
        $data['total'] = sizeof($ids);
        $data['chunkSize'] = $this->chunkSize;
        $data['numChunk'] = ceil(($this->chunkSize < $data['total'] ? ($data['total'] / $this->chunkSize) : 1));

        $this->log('Analyzing Data Source ' . $dsID);
        //spawn new tasks
        for ($i = 1; $i <= $data['numChunk']; $i++) {
            $task = array(
                'name' => 'sync',
                'params' => 'type=ds&id=' . $dsID . '&chunkPos=' . $i,
            );
            $this->taskManager->addTask($task);
        }

        $this->log('Analyzed Data Source ' . $dsID . " spawned " . $data['numChunk'] . " sync tasks for " . $data['total'] . ' records');
    }

    /**
     * Sync a Data Source based on the Chunk Position
     * @param $dsID
     * @throws Exception
     */
    private function syncDS($dsID)
    {
        if (!$this->chunkSize) throw new Exception("No chunk defined for this sync task");
        if (!$dsID) throw new Exception("Data Source ID required");
        $offset = ($this->chunkPos - 1) * $this->chunkSize;
        $limit = $this->chunkSize;
        $ids = $this->ci->ro->getIDsByDataSourceID($dsID, false, 'PUBLISHED', $offset, $limit);

        $this->log('Syncing chunk ' . $this->chunkPos . ' of Data Source ' . $dsID . ' for ' . sizeof($ids) .' records');

        $solr_docs = array();

        // sync and getting indexable_json for SOLR indexing
        if (sizeof($ids) > 0) {
            foreach ($ids as $ro_id) {
                try {
                    $ro = $this->ci->ro->getByID($ro_id);
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
                        $this->log('Sync error DSID:' . $dsID . ' ROID:' . $ro_id . ' Message: RO not found');
                    }
                } catch (Exception $e) {
                    throw new Exception('Sync error DSID:' . $dsID . ' ROID:' . $ro_id . ' Message: ' . $e->getMessage());
                }
            }
        } else {
            throw new Exception("No records found");
        }

        //indexing in SOLR
        if (sizeof($solr_docs) > 0) {
            try {
                $add_result = json_decode($this->ci->solr->add_json(json_encode($solr_docs)), true);
                if (isset($add_result['responseHeader']) && $add_result['responseHeader']['status'] === 0) {
                    $this->log("Adding to SOLR successful")
                        ->log('Task finishes successfully')
                        ->save();
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

        $this->indexOnly = true;

        $this->log('Task parameters: '. http_build_query($params))->save();

        //analyze if there is no chunkPosition
        if (isset($params['chunkPos'])) {
            $this->chunkPos = $params['chunkPos'];
        } else {
            $this->mode = 'analyze';
        }
    }
}