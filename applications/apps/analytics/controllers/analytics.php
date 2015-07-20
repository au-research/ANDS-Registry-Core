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
            'period' => ['startDate' => '2015-06-01', 'endDate' => '2015-06-02'],
            'group' => [
                'type' => 'group',
                'value' => 'State Records Authority of New South Wales',
            ],
            'dimensions' => ['portal_view', 'portal_search'],
        );
        $this->load->model('summary');
        echo json_encode($this->summary->get($filters));
    }

    //boring construct
    public function __construct()
    {
        parent::__construct();
    }
}
