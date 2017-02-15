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

    public function migrate_tags_to_r11()
    {
        acl_enforce('REGISTRY_STAFF');
        $this->load->model('registry_object/registry_objects', 'ro');
        $filters = array(
            'filter' => array('tag' => '!=')
        );
        $ros = $this->ro->filter_by($filters, 100);

        $affected_ros = array();
        foreach ($ros as $ro) {
            if ($ro->tag !== '1' && $ro->tag !== '0') array_push($affected_ros, $ro);
        }
        if (sizeof($affected_ros) == 0) {
            echo 'No legacy tags found!';
        } else {
            foreach ($affected_ros as $ro) {
                echo '<b>ID</b>: ' . $ro->id . ' <b>title</b>: ' . $ro->title . ' <b>Tag</b>: ' . $ro->tag . '<br/>';
                $tags = explode(';;', $ro->tag);
                foreach ($tags as $tag) {
                    $ro->addTag($tag);
                }
                $ro->sync();
                echo 'Tags added correctly!';
                echo '<hr/>';
            }
        }
    }

    public function migrate_themes_to_r12()
    {
        acl_enforce('REGISTRY_STAFF');
        $directory = './assets/shared/theme_pages/';
        $index_file = 'theme_cms_index.json';
        $root = scandir($directory, 1);
        $this->load->helper('file');
//        $result = array();
        $this->db->empty_table('theme_pages');
        foreach ($root as $value) {
            if ($value === '.' || $value === '..') {
                continue;
            }
            $pieces = explode(".", $value);
            if (is_file("$directory/$value")) {
                if ($pieces[0] . '.json' != $index_file) {
                    $file = json_decode(read_file($directory . $pieces[0] . '.json'), true);
                    $theme_page = array(
                        'title' => (isset($file['title']) ? $file['title'] : 'No Title'),
                        'slug' => (isset($file['slug']) ? $file['slug'] : $pieces[0]),
                        'img_src' => (isset($file['img_src']) ? $file['img_src'] : ''),
                        'description' => (isset($file['desc']) ? $file['desc'] : ''),
                        'visible' => (isset($file['visible']) ? $file['visible'] : false),
                        'content' => json_encode($file)
                    );
                    if (isset($file['visible']) && $file['visible'] === 'true') {
                        $theme_page['visible'] = 1;
                    } else $theme_page['visible'] = 0;
                    $this->db->insert('theme_pages', $theme_page);
                }
            }
        }
        echo 'Done';
    }

    public function migrate_tags_to_r12()
    {
        acl_enforce('REGISTRY_STAFF');
        $this->db->select('distinct(tag), type')->from('registry_object_tags');
        $tags = $this->db->get();
        $tags = $tags->result_array();
        foreach ($tags as $t) {
            $tag = array(
                'name' => $t['tag'],
                'type' => $t['type']
            );
            $this->db->insert('tags', $tag);
        }
        echo 'Done';
    }

    public function migrate_slugs_to_r13($commit = false)
    {
        acl_enforce('REGISTRY_STAFF');

        if (!$commit) {
            $result = $this->db->query('SELECT slug,registry_object_id FROM dbs_registry.registry_objects WHERE CHAR_LENGTH(SLUG) > 60;');
            echo 'There are ' . $result->num_rows() . ' slugs that are longer than 60 characters <br/>';
            $result = $this->db->query('select slug,registry_object_id from dbs_registry.url_mappings where registry_object_id IS NULL and slug in(select slug from registry_objects);');
            echo 'There are ' . $result->num_rows() . ' orphaned slugs <br/>';
            $result = $this->db->select('slug, registry_object_id')->from('url_mappings')->where('registry_object_id', NULL)->get();
            echo 'There are ' . $result->num_rows() . ' bad slugs <br/>';
            echo 'Run migrate_slugs_to_r13/true to commit fixing orphaned slugs and delete bad slugs';
        } else {
            ob_start();
            ob_implicit_flush(1);

            $this->load->model('registry_object/registry_objects', 'ro');

            //fix orphaned slugs, giving them a registry object id
            $result = $this->db->query('select slug,registry_object_id from dbs_registry.url_mappings where registry_object_id IS NULL and slug in(select slug from registry_objects);');
//            $result_array = $result->result_array();
            if ($result->num_rows() == 0) {
                echo 'There are no orphaned slug. <br/>';
            } else echo 'There are ' . $result->num_rows() . ' orphaned slug. Fixing. <br/>';
            foreach ($result->result_array as $r) {
                $ro = $this->ro->getBySlug($r['slug']);
                if ($ro) {
                    $result = $this->db->update('url_mappings', array(
                        'registry_object_id' => $ro->id
                    ), array('slug' => $r['slug']));
                    if ($result) {
                        echo 'success: ' . $r['slug'] . ' updated to ' . $ro->id . '<br/>';
                    } else {
                        echo 'failed: (cant update):' . $r['slug'] . '<br/>';
                    }
                } else {
                    echo 'failed (no record): ' . $r['slug'] . '<br/>';
                }
                unset($ro);
                ob_flush();
                flush();
            }

            //delete bad slug
            $result = $this->db->select('slug, registry_object_id')->from('url_mappings')->where('registry_object_id', NULL)->get();
            if ($result->num_rows() == 0) {
                echo 'There are no bad slug. <br/>';
            } else {
                echo 'There are ' . $result->num_rows() . ' bad slug. Removing. <br/>';
                $result = $this->db->delete('url_mappings', array('registry_object_id' => NULL));
                if ($result) {
                    echo 'success<br/>';
                } else {
                    echo 'failed<br/>';
                }
            }

            //generating new slugs
            $result = $this->db->query('SELECT slug,registry_object_id FROM dbs_registry.registry_objects WHERE CHAR_LENGTH(SLUG) > 60;');
            echo 'There are ' . $result->num_rows() . ' slugs that are longer than 60 characters <br/>';
            $i = 1;
            foreach ($result->result_array() as $r) {
                echo $i . ' ';
                $ro = $this->ro->getByID($r['registry_object_id']);
                if ($ro) {
//                    $oldSlug = $ro->slug;
                    $newSlug = $ro->generateSlug();
                    if ($newSlug) {
                        echo 'success:' . $r['slug'] . ' -> ' . $newSlug . '<br/>';
                    }
                } else {
                    echo 'failed (no record): ' . $r['slug'] . '<br/>';
                }
                $i++;
                unset($ro);
                ob_flush();
                flush();
            }
            ob_end_flush();

        }

        // $result = $this->db->select('slug, registry_object_id')->from('url_mappings')->where('registry_object_id', NULL)->get();

    }

    public function migrate_ds_to_r13()
    {
        acl_enforce('REGISTRY_STAFF');
        set_exception_handler('json_exception_handler');
        $this->load->model('data_source/data_sources', 'ds');
        $all_ds = $this->ds->getAll(0, 0);
        foreach ($all_ds as $ds) {
            try {
                //fix Title
                $ds->_initAttribute('title', $ds->title, TRUE);
                $ds->_initAttribute('record_owner', $ds->record_owner, TRUE);

                if ($ds->harvest_method == 'GET') $ds->harvest_method = 'GETHarvester';
                if ($ds->harvest_method == 'PMH' || $ds->harvest_method == 'RIF') $ds->harvest_method = 'PMHHarvester';

                $ds->save();

                $this->db->delete('data_source_attributes', array('data_source_id' => $ds->id, 'attribute' => 'title'));
                $this->db->delete('data_source_attributes', array('data_source_id' => $ds->id, 'attribute' => 'record_owner'));

            } catch (Exception $e) {
                throw new Exception($e);
            }
        }
        echo 'done';
    }

    public function migrate_harvest_reqs_to_r13()
    {
        acl_enforce('REGISTRY_STAFF');
        set_exception_handler('json_exception_handler');

        $old_harvest_requests = $this->db->get('harvest_requests');
        if ($old_harvest_requests->num_rows() > 0) {
            foreach ($old_harvest_requests->result() as $orq) {
                $row = array(
                    'data_source_id' => $orq->data_source_id,
                    'status' => 'SCHEDULED',
                    'next_run' => date('Y-m-d H:i:s', strtotime($orq->next_harvest)),
                    'mode' => 'HARVEST',
                    'batch_number' => strtoupper(sha1(strtotime($orq->next_harvest)))
                );
                $harvest = $this->db->insert('harvests', $row);
                if (!$harvest) {
                    echo $this->db->_error_message();
                    die();
                }
            }
        }
        echo 'done';
    }

    public function migrate_content_path_to_r13()
    {
        acl_enforce('REGISTRY_STAFF');
        set_exception_handler('json_exception_handler');
        if ($this->input->get('val')) {
            set_config_item('harvested_contents_path', 'string', $this->input->get('val'));
            echo 'done';
        } else {
            throw new Exception('val required');
        }
    }

    public function migrate_ds_attr_to_r13()
    {
        acl_enforce('REGISTRY_STAFF');
        set_exception_handler('json_exception_handler');
        $this->db->where('value', 't');
        $query = $this->db->update('data_source_attributes', array('value' => DB_TRUE));
        if ($query) echo 'Query updated. Rows affected: ' . $this->db->affected_rows() . '<br/>';

        $this->db->where('value', 'f');
        $query = $this->db->update('data_source_attributes', array('value' => DB_FALSE));
        if ($query) echo 'Query updated. Rows affected: ' . $this->db->affected_rows() . '<br/>';
    }

    public function migrate_roles_to_r131()
    {
        $cosi_db = $this->load->database('roles', TRUE);
        $query = $cosi_db->where('enabled', 't')->update('roles', array('enabled' => DB_TRUE));
        if ($query) echo 'Query updated. Rows affected: ' . $cosi_db->affected_rows() . '<br/>';
        $query = $cosi_db->where('enabled', 'f')->update('roles', array('enabled' => DB_FALSE));
        if ($query) echo 'Query updated. Rows affected: ' . $cosi_db->affected_rows() . '<br/>';

        $query = $cosi_db->get_where('roles', array('authentication_service_id' => 'AUTHENTICATION_SHIBBOLETH', 'shared_token' => null));

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $q) {
                echo $q->name . ' set shared_token to' . $q->role_id . '<br/>';
                $cosi_db->where('role_id', $q->role_id)->update('roles', array('shared_token' => $q->role_id));
            }
        }
    }

    public function index_missing_byfield($field = 'quality_level')
    {
        set_exception_handler('json_exception_handler');
        $this->load->model('registry_object/registry_objects', 'ro');
        $this->load->library('solr');
        $this->solr->setOpt('rows', 0)->setOpt('fl', 'id')->setOpt('fq', '-' . $field . ':*');
        $result = $this->solr->executeSearch(true);
        $chunk = 1000;
        if (ob_get_level() == 0) ob_start();

        ob_flush();
        flush();
        $remain = $result['response']['numFound'];
        while ($remain > 0) {
            echo 'Remaining: ' . $remain . "\n";
            ob_flush();
            flush();

            //do stuff
            $this->solr->init()->setOpt('rows', $chunk)->setOpt('fl', 'id')->setOpt('fq', '-' . $field . ':*');
            $rr = $this->solr->executeSearch(true);
            $docs = array();
            foreach ($rr['response']['docs'] as $doc) {
                $document = array();
                $document['id'] = $doc['id'];
                if ($field == 'tr_cited') {
                    $document[$field] = array('set' => $this->ro->getPortalStat($doc['id'], 'cited'));
                } else {
                    $document[$field] = array('set' => $this->ro->getAttribute($doc['id'], $field));
                }
                $docs[] = $document;
            }

            $this->solr->add_json(json_encode($docs));
            $this->solr->commit();
            echo "Indexed $chunk \n";
            ob_flush();
            flush();

            //update remain
            $this->solr->init()->setOpt('rows', $chunk)->setOpt('fl', 'id')->setOpt('fq', '-' . $field . ':*');
            $rr = $this->solr->executeSearch(true);
            $remain = $rr['response']['numFound'];
        }

        echo 'Done';
        ob_end_flush();
    }

    public function fix_trim_roles_type_id()
    {
        $cosi_db = $this->load->database('roles', TRUE);
        $query = $cosi_db->get('roles');
        foreach ($query->result() as $q) {
            // echo $q->role_id.' >'.$q->role_type_id.'<br/>';
            $cosi_db->where('role_id', $q->role_id)->update('roles', array('role_type_id' => trim($q->role_type_id)));
        }
        echo 'Query updated. Rows affected:' . $cosi_db->affected_rows();
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

    public function init()
    {
        acl_enforce('REGISTRY_STAFF');
        $this->load->library('importer');
        $slogTitle = 'Import from URL completed successfully' . NL;
        $elogTitle = 'An error occurred whilst importing from the specified URL' . NL;
        $log = 'IMPORT LOG' . NL;
        //$log .= 'URI: ' . $this->input->post('url') . NL;
        $log .= 'Harvest Method: Direct import from URL' . NL;
        $this->load->model('data_source/data_sources', 'ds');

        $data_source = $this->ds->getByKey($this->config->item('example_ds_key'));

        if (!$this->config->item('example_ds_key')) {
            echo "Example DataSource Key is required to complete the task" . NL;
            return;
        }

        if (!$data_source) {
            $data_source = $this->ds->create($this->config->item('example_ds_key'), url_title($this->config->item('example_ds_title')));
            $data_source->setAttribute('title', $this->config->item('example_ds_title'));
            $data_source->setAttribute('record_owner', 'superuser');
            $data_source->save();
            $data_source->updateStats();
            $data_source = $this->ds->getByKey($this->config->item('example_ds_key'));
        }

        $sampleRecordUrls = array('http://services.ands.org.au/documentation/rifcs/1.6.1/examples/eg-collection-1.xml',
            'http://services.ands.org.au/documentation/rifcs/1.6.1/examples/eg-party-1.xml',
            'http://services.ands.org.au/documentation/rifcs/1.6.1/examples/eg-service-1.xml',
            'http://services.ands.org.au/documentation/rifcs/1.6.1/examples/eg-activity-1.xml');

        $xml = '';
        foreach ($sampleRecordUrls as $recUrl) {
            $xml .= unWrapRegistryObjects(file_get_contents($recUrl));
        }

        $this->importer->setXML(wrapRegistryObjects($xml));
        $this->importer->setDatasource($data_source);
        $this->importer->commit(false);
        $this->importer->finishImportTasks();
        $data_source->updateStats();

        if ($error_log = $this->importer->getErrors()) {
            $log .= $elogTitle . $log . $error_log;
            $data_source->append_log($log, HARVEST_ERROR, "HARVEST_ERROR");
        }
        //else{
        $log .= $slogTitle . $log . $this->importer->getMessages();
        $data_source->append_log($log, HARVEST_INFO, "HARVEST_INFO");

        header('Location: ' . registry_url('data_source/manage_records/' . $data_source->id));
        exit();
    }

    function status()
    {
        set_exception_handler('json_exception_handler');
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        $data = array();

        $data['solr'] = array(
            'url' => get_config_item('solr_url')
        );

        $data['deployment'] = array(
            'state' => get_config_item('deployment_state')
        );

        $data['admin'] = array(
            'name' => get_config_item('site_admin'),
            'email' => get_config_item('site_admin_email')
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

    function sync($roID) {
        $this->benchmark->mark('start');
        set_exception_handler('json_exception_handler');
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        $this->load->model('registry_object/registry_objects', 'ro');
        $this->load->library('solr');
        $ro = $this->ro->getByID($roID);
        try {
            $result = $ro->sync();
            var_dump($result);
        } catch (Exception $e) {
            dd($e->getMessage());
        }
        $this->benchmark->mark('end');
        var_dump($this->benchmark->elapsed_time('start', 'end'));
    }

    function indexSolr($roID) {
        set_exception_handler('json_exception_handler');
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        $this->load->model('registry_object/registry_objects', 'ro');
        $this->load->library('solr');
        $ro = $this->ro->getByID($roID);
        try {
            $result = $ro->index_solr();
            var_dump($result);
            $result = $ro->indexRelationship();
            var_dump($result);
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    function fixRelationships($id)
    {
        set_exception_handler('json_exception_handler');
        $this->load->model('registry_object/registry_objects', 'ro');
        $ro = $this->ro->getByID($id);
        $ro->sync();
        $relationships = $ro->getAllRelatedObjects();
        $relationships = array_merge($relationships, $ro->_getGrantsNetworkConnections($relationships));
        $already_sync = array();
        foreach ($relationships as $r) {
            if (!in_array($r['registry_object_id'], $already_sync)) {
                $rr = $this->ro->getByID($r['registry_object_id']);
                echo $rr->id . ' > ' . $rr->class . ' > ' . $rr->title . "\n";
                $rr->sync();
                $already_sync[] = $rr->id;
                unset($rr);
            }
        }
        echo 'done';
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