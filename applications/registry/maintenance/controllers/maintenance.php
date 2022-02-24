<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Core Maintenance Dashboard
 *
 *
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 *
 */

use ANDS\Util\Config as ConfigUtil;


class Maintenance extends MX_Controller
{


    public function index()
    {
        acl_enforce('REGISTRY_STAFF');
        $data['title'] = 'Registry Status';
        $data['scripts'] = array('status_app');
        $data['js_lib'] = array('core', 'angular');
        $this->load->view("maintenance_dashboard", $data);
    }

    public function syncmenu()
    {
//		acl_enforce('REGISTRY_STAFF');
        redirect(apps_url('sync_manager'));
    }

    public function harvester()
    {
        acl_enforce('REGISTRY_STAFF');
        $data['title'] = 'ARMS Harvester Management';
        $data['scripts'] = array('harvester_app');
        $data['js_lib'] = array('core', 'angular');
        $this->load->view("harvester_app", $data);
    }

    function status()
    {
        set_exception_handler('json_exception_handler');
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        $data = array();

        $data['solr'] = array(
            'url' => ConfigUtil::get('app.solr_url')
        );

        $data['deployment'] = array(
            'state' => ConfigUtil::get('app.deployment_state')
        );

        $data['admin'] = array(
            'name' => ConfigUtil::get('app.site_admin'),
            'email' => ConfigUtil::get('app.site_admin_email')
        );

        echo json_encode($data);
    }

    function findMissingRecords($dataSourceID, $spawnTask = false) {
        $this->load->model('data_source/data_sources', 'ds');
        $this->load->model('registry_object/registry_objects', 'ro');
        $dataSource = $this->ds->getByID($dataSourceID);
        $publishedRecords = $this->ro->getIDsByDataSourceID($dataSourceID);

        $this->load->library('solr');
        $this->solr
            ->setOpt('fq', '+data_source_id:'.$dataSourceID)
            ->setOpt('fl', 'id')
            ->setOpt('rows', sizeof($publishedRecords));
        $result = $this->solr->executeSearch(true);

        $indexedRecords = [];
        foreach ($result['response']['docs'] as $doc) {
            $indexedRecords[] = $doc['id'];
        }

        $difference = array_diff($publishedRecords, $indexedRecords);

        // spawn task to deal with the difference records

        echo "published: ". count($publishedRecords)."\n";
        echo "indexed: ".count($indexedRecords). "\n";
        echo "difference: ".count($difference). "\n";

        if ($spawnTask === FALSE) return;

        echo "---spawning task---";

        require_once BASE . 'vendor/autoload.php';

        $task = [
            'name' => "Sync Missing Records for Data Source: ". $dataSourceID,
            'type' => 'POKE',
            'frequency' => 'ONCE',
            'priority' => 5,
            'params' => http_build_query([
                'class' => 'sync',
                'type' => 'ro',
                'id' => implode(',',$difference)
            ])
        ];

        $taskManager = new \ANDS\API\Task\TaskManager($this->db, $this);
        $taskAdded = $taskManager->addTask($task);
        echo "task added: ".$taskAdded['id'];
    }

    function fixRecordsWithNoIdentifiers()
    {
        initEloquent();
        $ids = \ANDS\RegistryObject\Identifier::where('identifier', '')->get()->pluck('registry_object_id')->toArray();

        $importTask = new \ANDS\API\Task\ImportTask();
        $importTask->init([
            'name' => "Fix records with identifiers is null",
            'params' => http_build_query([
                'ds_id' => 147,
                'targetStatus' => 'PUBLISHED',
                'pipeline' => 'UpdateRelationshipWorkflow'
            ]),
            'type' => 'DONTRUNME'
        ]);
        $importTask->setDb($this->db)->setCI($this);
        $importTask
            ->skipLoadingPayload()
            ->enableRunAllSubTask()
            ->setTaskData("importedRecords", $ids);
        $importTask->initialiseTask();
        $importTask->sendToBackground()->run();

        return $importTask;

    }

    function flush_buffers()
    {
        ob_flush();
        flush();
    }

    /**
     * Clean the Index of records that doesn't exist
     */
    function cleanNotExist()
    {
        acl_enforce('REGISTRY_STAFF');
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        $this->load->model('maintenance_stat', 'mm');
        $this->load->model('registry_object/registry_objects', 'ro');

        $solr_ids = $this->mm->getAllIDs('solr');
        $data['logs'] = '';

        //collect the unset array
        $unset = array();
        foreach ($solr_ids as $id) {
            try {
                $ro = $this->ro->getByID($id);
                if (!$ro || !$ro->getRif() || $ro->status != 'PUBLISHED') {
                    array_push($unset, $id);
                }
                unset($ro);
            } catch (Exception $e) {
                echo "<pre>error in: $e" . nl2br($e->getMessage()) . "</pre>" . BR;
            }
        }

        //actually delete them from the index
        $this->load->library('solr');
        foreach ($unset as $id) {
            $this->solr->deleteByID($id);
            $data['logs'] .= $id . ' deleted from index | ';
        }

        echo json_encode($data);
    }

    /**
     * @ignore
     */
    public function __construct()
    {
        parent::__construct();
    }
}