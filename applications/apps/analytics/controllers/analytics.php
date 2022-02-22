<?php

/*
 * Analytics Module
 * for Data Source Report functionality
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

use ANDS\Cache\Cache;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

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
//        acl_enforce('REGISTRY_USER');
        $data = array(
            'title' => 'ARDC Services Analytics',
        );


        $data['app_js_dist'] = array(
            'analytics_js_combined.js'
        );


        $data['app_css_dist'] = array(
            'daterangepicker-bs2.css',
            'angular-chart.css'
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
        header('Content-type: application/json');
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
            'query' => json_encode($this->elasticsearch->getOptions())
        );
        echo json_encode($result);
    }

    public function getUser(){
        if($this->user->isSuperAdmin()){
            $superUser=true;
        }else{
            $superUser=false;
        }
        echo $superUser;
    }

    public function getRO($id)
    {
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');

        //try get it from the index, faster
        $result = $this->elasticsearch->init()->setPath('/rda/production/'.$id)->get();
        if ($result && array_key_exists('found', $result) && $result['found']) {
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

        $log = isset($filters['log']) ? $filters['log'] : 'rdalogs';
        $this->elasticsearch->init();

        if ($log == 'rdalogs') {
            $this->elasticsearch->setPath('/portal-*/_search');
        } elseif ($log == 'rda') {
            $this->elasticsearch->setPath('/rda/production/_search');
        }

        $this->elasticsearch->setOpt('from', 0)->setOpt('size', 20);

        if (isset($filters['type'])) {
            switch ($filters['type']) {
                case 'has_doi':
                    $this->elasticsearch->mustf('exists', 'field', 'identifier_doi');
                    break;
                case 'missing_doi':
                    $this->elasticsearch->mustf('missing', 'field', 'identifier_doi');
                    break;
                default: break;
            }
        }


        $this->elasticsearch->setFilters($filters);

        $this->elasticsearch
            ->setAggs(
                'rostat', array('terms' => array('field' => 'roid'))
            )
            ->setAggs(
                'qstat', array('terms' => array('field' => 'q'))
            )
            ->setAggs(
                'accessedstat',
                array(
                    'filter' => array('term' => array('event'=>'accessed')),
                    'aggs'=>array("key"=>array("terms"=>array('field'=>'roid'))))

            );

        $result = array();
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
           // case 'doi':
            //    $result = $this->summary->getSolrDOIStat($filters);
            //    break;
            case 'tr':
                $result = $this->summary->getSolrStat('tr_cited', $filters);
                break;
           // case 'doi_activity':
             //   $result = $this->dois->getDOIActivityStat($filters);
             //   break;
           // case 'doi_client':
            //    $result = $this->dois->getClientStat($filters);
            //    break;
            case 'ro_ql':
                $result = $this->summary->getSolrStat('quality_level', $filters);
                break;
			case 'ro_ar':
                $result = $this->summary->getSolrStat('access_rights', $filters);
                break;
            case 'ro_class':
                $result = $this->summary->getSolrStat('class', $filters);
                break;
            case 'ro_group':
                $result = $this->summary->getSolrStat('group', $filters);
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
    public function getOrg($format = 'json') {

        set_exception_handler('json_exception_handler');
        // check in the cache
        $cache_id = 'org-role-analytics';
           if(!$result = Cache::driver('file')->get($cache_id)) {
            //not in the cache, get it and save it
            $this->load->model('summary');
            $result = $this->summary->getOrgs();
            Cache::driver('file')->put($cache_id, $result, 3000);
          }
        $role_id = $this->input->get('role_id') ? $this->input->get('role_id') : false;
        if ($role_id) {
            foreach ($result as $r) {
                if ($r['role_id']==$role_id&&(in_array($role_id,$this->user->affiliations())||$this->user->isSuperAdmin())) {
                    echo json_encode($r);
                }
            }
        } else {
            if ($format=='csv-download') {

                header( 'Content-Type: text/csv' );
                header( 'Content-Disposition: attachment;filename=organisations.csv');
                ob_end_clean();
                $out = fopen('php://output', 'w');
                $header = array('id', 'name', 'data_sources', 'groups', 'doi_app_id');
                fputcsv($out, $header);

                foreach ($result as $row) {
                    $datasources = array();
                    if (isset($row['data_sources'])) {
                        foreach ($row['data_sources'] as $ds) {
                            $datasources[] = $ds['title'] . '('.$ds['data_source_id'].')';
                        }
                    }

                    $groups = isset($row['groups']) ? $row['groups'] : array();
                    $doi_app_id = isset($row['doi_app_id']) ? $row['doi_app_id'] : array();


                    fputcsv($out, array(
                        $row['id'],
                        $row['name'],
                        implode(';;', $datasources),
                        implode(';;', $groups),
                        implode(';;', $doi_app_id)
                    ));
                }

                fclose($out);
            } elseif ($format=='json') {
               $this->output->set_header('Content-type: application/json');
               $filtered_orgs = array();
               if($this->user->loggedIn()){
                    for($i=0;$i<count($result);$i++){
                        if(in_array($result[$i]['role_id'],$this->user->affiliations())||$this->user->isSuperAdmin()){
                            $filtered_orgs[]=$result[$i];
                        }
                    }
               }
               echo json_encode($filtered_orgs);
            }
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
        }
        else{
            $chunks[] = $lines;
        }

            foreach ($chunks as $key => $chunk) {
                $post = [];
                foreach ($chunk as $line) {
                    $content = readString($line);
                    if ($content && is_array($content) && sizeof($content) > 0) {

                        if (isset($content['date'])) {
                            $content['day'] = date("Y-m-d", strtotime($content['date']));
                        }

                        if (isset($content['q'])) {
                            $content['q_lowercase'] = strtolower($content['q']);
                        }

                        if (isset($content['user_agent'])) {
                            $content['is_bot'] = isbot($content['user_agent']) ? true : false;
                        } else $content['is_bot'] = false;
                        if (isset($content['roid'])) {

                            if (isset($content['roclass']) && !isset($content['class'])) {
                                $content['class'] = $content['roclass'];
                            }

                            if (isset($content['dsid']) && !isset($content['data_source_id'])) {
                                $content['data_source_id'] = $content['dsid'];
                            }

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


        if ($result) {
            //handle success
            echo "Done " . $date . "\n";
        }

    }

    public function processLegacyLog($date_from = '2014-01-01')
    {
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');
        $this->load->model('summary');
        $this->load->model('registry/registry_object/registry_objects', 'ro');
        $this->load->library('ElasticSearch');

        $CrawlerDetect = new CrawlerDetect();

        $legacyLogs = '/Users/mnguyen/dev/elk/logs/';

        $dates = readDirectory($legacyLogs. 'portal');
        foreach ($dates as &$date) {
            $date = str_replace("log-portal-", "", $date);
            $date = str_replace(".php", "", $date);
        }
        $dates = array_values($dates);

        natsort($dates);

        $dates = date_range(reset($dates), end($dates), '+1day', 'Y-m-d');
        $dates = array_reverse($dates);

        if ($date_from) {
            $key = array_search($date_from, $dates);
            $dates = array_slice($dates, $key);
        }

        foreach ($dates as $date) {
            $filters = ['log' => 'portal'];

            $file_path = $legacyLogs . $filters['log'] . '/log-' . $filters['log'] . '-' . $date . '.php';
            $lines = readFileToLine($file_path);

            //create the date file in the processed
            $processed_file_path = $legacyLogs . 'processed_'. $filters['log'] . '/'.$date.'.log';

            foreach ($lines as $line) {
                $content = readString($line);

                if ($content && count($content) > 0 && array_key_exists('date', $content)) {

                    $timestamp = date("c", strtotime($content['date']));
                    $event = array_key_exists('event', $content) ? $content['event'] : 'unknown_event';

                    $content['user_agent'] = array_key_exists('user_agent', $content) ? $content['user_agent'] : 'dude';

                    if($CrawlerDetect->isCrawler($content['user_agent'])) {
                        $content['is_bot'] = true;
                    } else {
                        $content['is_bot'] = false;
                    }

                    if (!$content['is_bot']) {
                        $parsed = [
                            '@timestamp' => $timestamp, //todo
                            '@source' => 'localhost',
                            '@message' => $event,
                            '@tags' => ['portal', $event],
                            '@type' => 'portal'
                        ];

                        if (array_key_exists('roid', $content)) {
                            if (isset($content['roclass']) && !isset($content['class'])) {
                                $content['class'] = $content['roclass'];
                            }

                            if (isset($content['dsid']) && !isset($content['data_source_id'])) {
                                $content['data_source_id'] = $content['dsid'];
                            }

                            //fill the record up with group, dsid, slug, path
                            $fields = ['group', 'slug', 'data_source_id', 'group', 'key'];
                            foreach ($fields as $field) {
                                if (!isset($content[$field])) {
                                    $value = $this->ro->getAttribute($content['roid'], $field);
                                    if ($value) {
                                        $content[$field] = $value;
                                    } else {
                                        $content['unknown_'.$field] = true;
                                    }
                                }
                            }
                        }

                        // split
                        $splittableFields = ['result_roid', 'result_group', 'result_dsid'];
                        foreach ($splittableFields as $field) {
                            if (array_key_exists($field, $content)) {
                                $content[$field] = explode(',,', $content[$field]);
                            }
                        }

                        $parsed['@fields'] = [
                            'channel' => 'portal',
                            'level' => 200
                        ];

                        foreach ($content as $key=>$value) {
                            $parsed['@fields']['ctxt_'.$key] = $value;
                        }

                        $message = json_encode($parsed);

                        if (file_exists($processed_file_path)) {
                          $fh = fopen($processed_file_path, 'a');
                          fwrite($fh, $message."\n");
                        } else {
                          $fh = fopen($processed_file_path, 'w');
                          fwrite($fh, $message."\n");
                        }
                        fclose($fh);
                    }
                    unset($content);
                    unset($parsed);
                }
            }
            echo "Done $date\n";
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

        $dates = array_reverse($dates);
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

    public function test4()
    {
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');
        $filters = array(
            'log' => 'portal',
            'period' => ['startDate' => '2016-07-01', 'endDate' => '2016-07-28'],
            'record_owner' => 'ANDS',
//            'groups' => ['PARADISEC', 'AuScope', 'Griffith Univesrity'],
            'dimensions' => ['portal_view', 'portal_search','accessed'],
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
            'record_owner' => 'ANDS',
            'dimensions' => ['portal_view', 'portal_search', 'accessed'],
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
                            'day' => array(
                                'type' => 'date',
                                'store' => true,
                                'format' => 'yyyy-MM-dd'
                            ),
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
                            ),
                            'q_lowercase' => array(
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
