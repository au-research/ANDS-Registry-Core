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
        acl_enforce('REGISTRY_STAFF');
        $data = array(
            'title' => 'Analytics',
        );

        $data['scripts'] = array(
            'analytics_app',
            'analytics_factory',
        );

        $data['app_js_lib'] = array(
            'angular/angular.js',
            'Chart.js/Chart.min.js',
            'angular-chart.js/angular-chart.js',
            'moment/moment.js',
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
        $result = array(
            'result' => $this->summary->get($filters),
            'filters' => $filters,
        );
        echo json_encode($result);
    }

    public function indexLog($date='2015-06-01') {
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');
        $this->load->model('summary');
        $this->load->library('ElasticSearch');

        $date = $this->input->get('date') ? $this->input->get('date') : $date;

        //construct the posting array
        $post = array();
        $filters = ['log'=>'portal'];
        $lines = $this->summary->getStatFromInternalLog($date, $filters);
        foreach ($lines as $line) {
            $content = readString($line);
            if ($content && is_array($content) && sizeof($content) > 0) {
                $post[] = $content;
            }
            unset($line);
            unset($content);
        }

        //delete all data for this date
        $result = $this->elasticsearch
                    ->init()
                    ->setPath('/logs/production/_query/?q=date:"'.$date.'"')
                    ->delete();

        //add data for this date
        $result = $this->elasticsearch
                    ->init()
                    ->setPath('/logs/production/_bulk')
                    ->bulk('index', $post);

        if ($result) {
            //handle success
            echo "Done ".$date."\n";
        }

    }

    public function indexLogs() {
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');

        //index Portal logs
        $dates = readDirectory('engine/logs/portal');
        foreach ($dates as &$date) {
            $date = str_replace("log-portal-", "", $date);
            $date = str_replace(".php", "", $date);
        }
        natsort($dates);

        if (ob_get_level() == 0) ob_start();
        // $date_range = date_range('2015-04-23', '2015-07-20', '+1day', 'Y-m-d');
        echo 'Initial: ' . number_format(memory_get_usage(), 0, '.', ',') . " bytes\n";
        foreach ($dates as $date) {
            $this->indexLog($date);
            ob_flush();flush();
        }
        echo "\n";
        echo 'Peak: ' . number_format(memory_get_peak_usage(), 0, '.', ',') . " bytes\n";
        echo 'End: ' . number_format(memory_get_usage(), 0, '.', ',') . " bytes\n";
        ob_end_flush();
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

    public function test2() {
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');

        $this->load->library('ElasticSearch');
        $result = $this->elasticsearch->init()
                    ->setPath('/logs/production/_search')
                    ->andf('term', 'event', 'portal_view')
                    ->andf('term', 'group', 'University of South Australia')
                    ->andf('range', 'date', ['from'=>'2015-06-01 00:00:00', 'to'=>'2015-06-02 00:00:00'])
                    ->setFacet('group', array(
                            'terms' => array('field'=>'group')
                        )
                    )
                    ->setFacet('event', array(
                            'terms' => array('field'=>'event')
                        )
                    )
                    ->search();

        dd($result);


    }

    public function setUp() {
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');

        $result = $this->elasticsearch
            ->init()
            ->setPath('/logs/')
            ->delete();
        var_dump($result);

        $result = $this->elasticsearch
            ->init()
            ->setPath('/logs/')
            ->put(array());
        var_dump($result);

        $result = $this->elasticsearch
            ->init()
            ->setPath('/logs/_mapping/production')
            ->setOpt('production',
                array(
                    'properties' => array(
                        'date' => array(
                            'type'=>'date',
                            'store'=>true,
                            'format'=> 'yyyy-MM-dd HH:mm:ss || yyyy-MM-dd || yyyy || yyyy-MM'
                        ),
                        'group' => array(
                            'type' => 'string',
                            'index' => 'not_analyzed'
                        )
                    )
                )
            )
            ->put($this->elasticsearch->getOptions());
        dd($result);
        echo json_encode($data);
    }

    public function curl_put($uri, $content) {
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        $response = curl_exec($ch);
        return $response;
    }

    //boring construct
    public function __construct()
    {
        $this->load->library('ElasticSearch');
        parent::__construct();
    }
}
