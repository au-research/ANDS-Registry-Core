<?php
/**
 * Class:  SyncTask
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * Date: 7/01/2016
 * Time: 10:49 AM
 */

namespace ANDS\API\Task;


class SyncTask extends Task
{
    private $target = false; //ds or ro
    private $target_id = false;
    private $chunkSize = 100;
    private $chunkPos = 0;
    private $mode = 'sync';

    private $taskManager;

    /**
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

        $this->taskManager = new \ANDS\API\Task\TaskManager($this->getDb());

        switch ($this->target) {
            case 'ds' :
                $list = explode(',', $this->target_id);
                foreach ($list as $dsID) {
                    if ($this->mode == 'analyze') {
                        $this->analyzeDS($dsID);
                    } else if ($this->mode == 'sync') {
                        $this->syncDS($dsID);
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
        $ids = $this->ci->ro->getIDsByDataSourceID($dsID, false, 'PUBLISHED');
        $data['total'] = sizeof($ids);
        $data['chunkSize'] = $this->chunkSize;
        $data['numChunk'] = ceil(($this->chunkSize < $data['total'] ? ($data['total'] / $this->chunkSize) : 1));

        //spawn new tasks
        for ($i = 1; $i <= $data['numChunk']; $i++) {
            $task = array(
                'name' => 'sync',
                'params' => 'type=ds&id=' . $dsID . '&chunkPos=' . $i,
            );
            $this->taskManager->addTask($task);
        }

        $this->log('[success][task:queued][size:' . $data['total'] . ']');
    }

    /**
     * Sync a Data Source based on the Chunk Position
     * @param $dsID
     * @throws Exception
     */
    private function syncDS($dsID)
    {
        if (!$this->chunkSize) throw new Exception("No chunk defined for this sync task");
        $offset = ($this->chunkPos - 1) * $this->chunkSize;
        $limit = $this->chunkSize;
        $ids = $this->ci->ro->getIDsByDataSourceID($dsID, false, 'PUBLISHED', $offset, $limit);

        $solr_docs = array();

        foreach ($ids as $ro_id) {
            try {
                $ro = $this->ci->ro->getByID($ro_id);
                if ($ro) {
                    $ro->processIdentifiers();
                    $ro->addRelationships();
                    $ro->update_quality_metadata();
                    $ro->enrich();
                    $solr_docs[] = $ro->indexable_json();
                    unset($ro);
                } else {
                    $this->log('[error][notfound][ro_id:' . $ro_id . ']');
                }
            } catch (Exception $e) {
                $this->log('[error][sync][ds_id:' . $dsID . '][message:' . $e->getMessage() . '][ro_id:' . $ro_id . ']');
            }
        }

        try {
            $add_result = $this->ci->solr->add_json(json_encode($solr_docs));
            $this->log($add_result);
            $commit_result = $this->ci->solr->commit();
            $this->log($commit_result);
        } catch (Exception $e) {
            $this->log('[error][sync][ds_id:' . $dsID . '][index:' . $e->getMessage() . ']');
        }

        $this->log('[task:success]');
    }

    /**
     * Analyze the parameters and load them into this object
     */
    private function loadParams()
    {
        //parsing parameters
        parse_str($this->params, $params);
        $this->target = $params['type'] ? $params['type'] : false;
        $this->target_id = $params['id'] ? $params['id'] : false;

        //analyze if there is no chunkPosition
        if (isset($params['chunkPos'])) {
            $this->chunkPos = $params['chunkPos'];
        } else {
            $this->mode = 'analyze';
        }
    }
}