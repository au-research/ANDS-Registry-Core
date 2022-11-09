<?php

class xml_builder extends CI_Controller {

    function index() {

        $data['title'] = 'XML Builder';
        $data['js_lib'] = array('core', 'angular129', 'prettyprint',  'APIService', 'APIRoleService', 'APIDOIService',  'xmlToJson');
        $data['scripts'] = array('doi_cms_app1', 'doi_cms_mainCtrl1', 'angular_datacite_xml_builder');
        $this->load->view('xml_builder',$data);
    }

}