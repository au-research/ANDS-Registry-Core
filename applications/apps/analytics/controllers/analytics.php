<?php

/*
 * Analytics Module
 * for Data Source Report functionality
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

class Analytics extends MX_Controller
{

    /**
     * Analytics Index Function
     * Requires various exclusive library and acts as a front to the
     * Analytics AngularJS app
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @return view
     */
    public function index()
    {
        // acl_enforce('REGISTRY_STAFF');
        $data = array(
            'title' => 'Analytics',
        );

        $data['scripts'] = array(
            'analytics_app',
            'main_ctrl',
            'report_ctrl',
            'doi_ctrl',
            'analytics_chart_directive',
            'ro_directive',
            'analytics_filter_service',
            'analytics_factory',
            'analytics_modal_detail_controller',
        );

        $data['app_js_lib'] = array(
            'angular/angular.min.js',
            'angular-route/angular-route.min.js',
            'Chart.js/Chart.min.js',
            'angular-chart.js/angular-chart.js',
            'moment/moment.js',
            'angular-bootstrap/ui-bootstrap.min.js',
            'angular-bootstrap/ui-bootstrap-tpls.min.js',
            'bootstrap-daterangepicker/daterangepicker.js',
            'angular-daterangepicker/js/angular-daterangepicker.js',
        );

        $data['app_css_lib'] = array(
            'angular-chart.js/dist/angular-chart.css',
            'bootstrap-daterangepicker/daterangepicker-bs2.css',
        );

        $data['js_lib'] = array('core');
        $this->load->view('analytics_app', $data);
    }

    /**
     * Summary
     * Returns the summary statistic for a given period
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @param POST $filters
     * @return JSON
     */
    public function summary()
    {
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');

        //capturing filters from AngularJS POST field
        $filters = false;
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata, true);
        $filters = isset($request['filters']) ? $request['filters'] : $filters;

        $this->load->model('summary');
        $summary_result = $this->summary->get($filters);
        $result = array(
            'dates' => $summary_result['dates'],
            'group_event' => $summary_result['group_event'],
            'aggs' => $summary_result['aggs'],
            'filters' => $filters,
        );
        echo json_encode($result);
    }

    public function getRO($id)
    {
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');

        //try get it from the index, faster
        $result = $this->elasticsearch->init()->setPath('/rda/production/'.$id)->get();
        if ($result && $result['found']) {
            echo json_encode($result['_source']);
        } else {
            //try and get it from the registry, slower
            $this->load->model('registry/registry_object/registry_objects', 'ro');
            $ro = $this->ro->getByID($id);
            if ($ro) {
                echo json_encode([
                    'roid' => $ro->id,
                    'key' => $ro->id,
                    'title' => $ro->title,
                    'slug' => $ro->slug,
                    'class' => $ro->class,
                    'group' => $ro->group,
                    'record_owner' => $ro->record_owner
                ]);
            } else {
                echo json_encode('notfound');
            }
        }
    }

    public function getEvents()
    {
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata, true);
        $filters = isset($request['filters']) ? $request['filters'] : false;

        $this->elasticsearch->init()->setPath('/logs/production/_search');
        $this->elasticsearch
            ->setOpt('from', 0)->setOpt('size', 20)
            ->mustf('term', 'is_bot', false)
            ->mustf('range', 'date',
                array(
                    'from' => $filters['period']['startDate'],
                    'to' => $filters['period']['endDate']
                )
            );

        //groups
        if (isset($filters['groups'])) {
            foreach ($filters['groups'] as $group) {
                $this->elasticsearch->shouldf('term', 'group', $group);
            }
        }


        $this->elasticsearch
            ->setAggs(
                'rostat', array('terms' => array('field' => 'roid'))
            )
            ->setAggs(
                'qstat', array('terms' => array('field' => 'q'))
            );

        $result = array();
        // echo json_encode($this->elasticsearch->getOptions());die();
        $search_result = $this->elasticsearch->search();
        echo json_encode($search_result);
    }

    public function getDOIStat()
    {
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');
        $this->load->model('dois');
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata, true);
        $filters = isset($request['filters']) ? $request['filters'] : $filters;
        $stats = $this->dois->getStat($filters);
        echo json_encode($stats);
    }

    public function getStat($stat = 'doi')
    {
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');
        $this->load->model('summary');
        $this->load->model('dois');
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata, true);
        $filters = isset($request['filters']) ? $request['filters'] : $filters;

        // $filters['class'] = 'collection';
        $filters['ctype'] = $stat;

        $result = array();
        $search_result = $this->summary->getStat('/rda/production/', $filters);
        switch ($stat) {
            case 'doi':
                $result = array(
                    'total' => $search_result['hits']['total'],
                    'missing_doi' => $search_result['aggregations']['missing_doi']['doc_count']
                );
                $result['has_doi'] = $result['total'] - $result['missing_doi'];
                break;
            case 'tr':
                $result = $search_result['aggregations']['portal_cited']['buckets'];
                break;
            case 'doi_activity':
                $result = $this->dois->getDOIActivityStat($filters);
                break;
            case 'doi_client':
                $result = $this->dois->getClientStat($filters);
                break;
            case 'ro_ql':
                $result = $search_result['aggregations']['quality_level']['buckets'];
                break;
            case 'ro_class':
                $result = $search_result['aggregations']['class']['buckets'];
                break;
            case 'ro_group':
                $result = $search_result['aggregations']['group']['buckets'];
                break;
            default :
                break;
        }

        echo json_encode($result);
    }



    /**
     * Returns a list of organisational roles with groups and doi app id
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @return json
     */
    public function getOrg() {
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');

        // check in the cache
        $this->load->driver('cache');
        $cache_id = 'org-role-analytics';
        if(! $result = $this->cache->file->get($cache_id)) {
            //not in the cache, get it and save it
            $this->load->model('summary');
            $result = $this->summary->getOrgs();
            //save for 30 minutes
            $this->cache->file->save($cache_id, $result, 30);
        }

        $role_id = $this->input->get('role_id') ? $this->input->get('role_id') : false;
        if ($role_id) {
            foreach ($result as $r) {
                if ($r['role_id']==$role_id) {
                    echo json_encode($r);
                }
            }
        } else {
            echo json_encode($result);
        }
    }

    public function indexLog($date = '2015-06-01')
    {
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');
        $this->load->model('summary');
        $this->load->model('registry/registry_object/registry_objects', 'ro');
        $this->load->library('ElasticSearch');

        $date = $this->input->get('date') ? $this->input->get('date') : $date;

        //construct the posting array
        $post = array();
        $filters = ['log' => 'portal'];
        $lines = $this->summary->getStatFromInternalLog($date, $filters);


        //delete all data for this date
        $result = $this->elasticsearch
            ->init()
            ->setPath('/logs/production/_query/?q=date:"' . $date . '"')
            ->delete();

        //let's do the chunking here instead of in ElasticSearch
        $chunkSize = 2000;
        if (sizeof($lines) > $chunkSize) {
            $chunks = array_chunk($lines, $chunkSize);
            foreach ($chunks as $key => $chunk) {
                $post = [];
                foreach ($chunk as $line) {
                    $content = readString($line);
                    if ($content && is_array($content) && sizeof($content) > 0) {
                        if (isset($content['user_agent'])) {
                            $content['is_bot'] = isbot($content['user_agent']) ? true : false;
                        } else $content['is_bot'] = false;
                        if (isset($content['roid'])) {

                            //fill it up with group, dsid, slug, path
                            //TAKES TOO LONG
                            /*$fields = ['group', 'slug', 'data_source_id', 'group'];
                            foreach ($fields as $field) {
                                if (!isset($content[$field])) {
                                    $value = $this->ro->getAttribute($content['roid'], $field);
                                    if ($value) {
                                        $content[$field] = $value;
                                    }
                                }
                            }*/

                        }

                        //fill ip with geolocation TAKES TOO LONG
                        /*if (isset($content['ip'])) {
                            if ($geo = getIPLocation($content['ip'])) {
                                $content['city'] = isset($geo['city']) ? $geo['city'] : false;
                                $content['country'] = isset($geo['country']) ? $geo['country'] : false;
                                $content['regionName'] = isset($geo['regionName']) ? $geo['regionName'] : false;
                            }
                        }*/

                        $post[] = $content;
                    }
                    unset($line);
                    unset($content);
                }

                //add data for this chunk
                $result = $this->elasticsearch
                    ->init()
                    ->setPath('/logs/production/_bulk')
                    ->bulk('index', $post);

                if ($result) {
                    echo 'Done ' . $date . ' chunk ' . $key . " out of " . sizeof($chunks) . "\n";
                }
            }
        }

        if ($result) {
            //handle success
            echo "Done " . $date . "\n";
        }

    }

    public function indexLogs($date_from = '2014-01-01')
    {
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');

        //index Portal logs
        $dates = readDirectory('engine/logs/portal');
        foreach ($dates as &$date) {
            $date = str_replace("log-portal-", "", $date);
            $date = str_replace(".php", "", $date);
        }
        natsort($dates);

        $dates = date_range(reset($dates), end($dates), '+1day', 'Y-m-d');

        // $date_from = "2015-07-01";
        if ($date_from) {
            $key = array_search($date_from, $dates);
            $dates = array_slice($dates, $key);
        }

        if (ob_get_level() == 0) ob_start();
        // $date_range = date_range('2015-04-23', '2015-07-20', '+1day', 'Y-m-d');
        echo 'Initial: ' . number_format(memory_get_usage(), 0, '.', ',') . " bytes\n";
        foreach ($dates as $date) {
            $this->indexLog($date);
            ob_flush();
            flush();
        }
        echo "\n";
        echo 'Peak: ' . number_format(memory_get_peak_usage(), 0, '.', ',') . " bytes\n";
        echo 'End: ' . number_format(memory_get_usage(), 0, '.', ',') . " bytes\n";
        ob_end_flush();
    }

    public function indexDois()
    {
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');
        if (ob_get_level() == 0) ob_start();

        $this->load->model('dois');


        $chunkSize = 5000;
        $offset = 0;
        $total = $this->dois->getTotalActivityLogs();

        while ($offset < $total) {
            $activities = $this->dois->getActivity($offset, $chunkSize);

            $post = array();
            foreach ($activities as $log) {
                $p = $log;
                $p['publisher'] = $this->dois->getGroupForDOI($p['doi_id']);
                $post[] = $p;
            }

            $result = $this->elasticsearch
                ->init()
                ->setPath('/logs/dois/_bulk')
                ->bulk('index', $post);

            if ($result) {
                //handle success
                echo "Done " . $offset . "\n";
            }

            $offset += $chunkSize;
            ob_flush();
            flush();
        }

        ob_end_flush();
    }

    public function indexROs($offset = 0, $upper_limit = false)
    {
        ini_set('max_execution_time', 3600);
        $this->benchmark->mark('code_start');
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');
        $this->load->model('registry/registry_object/registry_objects', 'ro');

        $total = $upper_limit ? $upper_limit : $this->db->count_all('registry_objects');
        $chunkSize = 500;
        // $offset = 0;
        //

        //delete all ro here
        // $this->setUp($core='rda');

        if (ob_get_level() == 0) ob_start();
        while ($offset < $total) {
            $this->benchmark->mark('batch_start');

            $ros = $this->ro->getAll($chunkSize, $offset);
            $post = array();
            foreach ($ros as $ro) {
                if ($ro) {
                    $data = array(
                        '_id' => $ro->id,
                        'roid' => $ro->id,
                        'key' => $ro->key,
                        'class' => $ro->class,
                        'title' => $ro->title,
                        'status' => $ro->status,
                        'slug' => $ro->slug,
                        'record_owner' => $ro->record_owner,
                        'group' => $ro->group,
                        'quality_level' => $ro->quality_level,
                        'created' => date('Y-m-d H:i:s',$ro->created),
                        'error_count' => $ro->error_count,
                        'warning_count' => $ro->warning_count,
                        'dsid' => $ro->data_source_id
                    );

                    //has doi
                    if ($identifiers = $ro->getIdentifiers()) {
                        foreach ($identifiers as $id) {
                            if ($id['identifier_type'] == 'doi') {
                                $data['doi'] = $id['identifier'];
                            }
                        }
                    }

                    //portal stats
                    $stat = $ro->getAllPortalStat();
                    $data['portal_accessed'] = $stat['accessed'];
                    $data['portal_cited'] = $stat['cited'];

                    // var_dump($data['_id']);ob_flush();flush();
                    $post[] = $data;
                    unset($ro);
                }
            }
            // dd($post);

            $result = $this->elasticsearch
                ->init()
                ->setPath('/rda/production/_bulk')
                ->bulk('index', $post);

            $this->benchmark->mark('batch_end');
            $elapsed = $this->benchmark->elapsed_time('batch_start', 'batch_end');
            echo "Done " . $offset . " Took: " . $elapsed . "\n";
            $offset += $chunkSize;
            ob_flush();
            flush();
        }
        $this->benchmark->mark('code_end');
        echo "Took total: " . $this->benchmark->elapsed_time('code_start', 'code_end') . "\n";
        ob_end_flush();

    }

    public function test4()
    {
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');
        $filters = array(
            'log' => 'portal',
            'period' => ['startDate' => '2015-06-01', 'endDate' => '2015-06-04'],
            'groups' => ['PARADISEC', 'AuScope', 'Griffith Univesrity'],
            'dimensions' => ['portal_view', 'portal_search'],
        );
        $this->load->model('summary');
        echo json_encode($this->summary->get($filters));
    }

    /**
     * Useful function for automated testing
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @return JSON
     */
    public function test()
    {
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');

        $filters = array(
            'log' => 'portal',
            'period' => ['startDate' => '2015-03-01', 'endDate' => '2015-03-05'],
            'group' => [
                'type' => 'group',
                'value' => 'University of South Australia',
            ],
            'dimensions' => ['portal_view', 'portal_search'],
        );
        $this->load->model('summary');
        echo json_encode($this->summary->get($filters));
    }

    public function indexDOIClient()
    {
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');

        if (ob_get_level() == 0) ob_start();
        $this->load->model('dois');

        $result = array();
        foreach ($this->dois->getClients() as $client) {

            //delete all data for this client
            $result = $this->elasticsearch
                ->init()
                ->setPath('/report/doi/' . $client['client_id'])
                ->delete();

            //save
            $client_result = $client;

            //run report
            $command = escapeshellcmd('python3 etc/misc/python/linkchecker/linkchecker.py -i etc/misc/python/linkchecker/linkchecker.ini --no_emails -m DOI -c' . $client['client_id']);
            $output = shell_exec($command);
            $client_result['linkchecker_report'] = $output;

            $data = [];
            foreach (explode("\n", $output) as $line) {
                $bin = explode(":", $line, 2);
                if (isset($bin[0]) && isset($bin[1])) {
                    $bin[1] = trim($bin[1]);
                    $data[$bin[0]] = $bin[1];
                }
            }

            $client_result['url_num'] = isset($data['Number of URLs to be tested']) ? $data['Number of URLs to be tested'] : false;
            $client_result['url_broken_num'] = isset($data['Broken Links Discovered']) ? $data['Broken Links Discovered'] : false;
            $client_result['error'] = isset($data['ERROR']) ? $data['ERROR'] : false;

            //index client_result
            $result = $this->elasticsearch
                ->init()
                ->setPath('/report/doi/' . $client['client_id'])
                ->put($client_result);

            //save
            $result[] = $client_result;

            echo 'Done ' . $client['client_id'] . ": " . $client['client_name'] . "\n";
            ob_flush();
            flush();
        }
        ob_end_flush();
    }

    public function setUpCore($core='rda') {
        if ($core=='rda') {
            $result = $this->elasticsearch
                ->init()
                ->setPath('/rda/production/')
                ->delete();
            $result = $this->elasticsearch
                ->init()
                ->setPath('/rda/production')
                ->put(array());
            // var_dump($result);
            $result = $this->elasticsearch
                ->init()
                ->setPath('/rda/production/_mapping/')
                ->setOpt('production',
                    array(
                        'properties' => array(
                            'group' => array(
                                'type' => 'string',
                                'index' => 'not_analyzed'
                            ),
                            'created' => array(
                                'type' => 'date',
                                'store' => true,
                                'format' => 'yyyy-MM-dd HH:mm:ss || yyyy-MM-dd || yyyy || yyyy-MM'
                            ),
                        )
                    )
                )
                ->put($this->elasticsearch->getOptions());
            // var_dump($result);
        } elseif ($core=='logs') {
            $result = $this->elasticsearch
                ->init()
                ->setPath('/logs/')
                ->delete();
            // var_dump($result);

            $result = $this->elasticsearch
                ->init()
                ->setPath('/logs/')
                ->put(array());
            // var_dump($result);

            $result = $this->elasticsearch
                ->init()
                ->setPath('/logs/_mapping/production')
                ->setOpt('production',
                    array(
                        'properties' => array(
                            'date' => array(
                                'type' => 'date',
                                'store' => true,
                                'format' => 'yyyy-MM-dd HH:mm:ss || yyyy-MM-dd || yyyy || yyyy-MM'
                            ),
                            'group' => array(
                                'type' => 'string',
                                'index' => 'not_analyzed'
                            ),
                            'q' => array(
                                'type' => 'string',
                                'index' => 'not_analyzed'
                            )
                        )
                    )
                )
                ->put($this->elasticsearch->getOptions());
            // var_dump($result);
        }
    }

    public function setUp()
    {
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');

        $this->setUpCore('logs');
        $this->setUpCore('rda');
    }


    //boring construct
    public function __construct()
    {
        $this->load->library('ElasticSearch');
        parent::__construct();
    }
}
