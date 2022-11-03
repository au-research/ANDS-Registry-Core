<?php

/*
 * Analytics Module
 * for Data Source Report functionality
 * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 */

class Sync_manager extends MX_Controller
{
    /**
     * Sync Manager AngularJS app
     */
    public function index()
    {
//        acl_enforce('REGISTRY_USER');
        $data = array(
            'title' => 'ANDS Services Sync Manager',
        );

        $data['scripts'] = array(
            'sync_app',
            'sync_index_controller',
            'task_status_controller',
            'task_detail_controller',
            'directives/taskList'
        );

        $data['app_js_lib'] = array(
            'angular/angular.min.js',
            'angular-route/angular-route.min.js',
            'angular-bootstrap/ui-bootstrap.min.js',
            'angular-bootstrap/ui-bootstrap-tpls.min.js',
        );

        $data['js_lib'] = array( 'core', 'APIService', 'APITaskService', 'APIDataSourceService', 'APIRegistryObjectService');
        $this->load->view('sync_manager_view', $data);
    }
}