<?php


class Registry_summary extends MX_Controller {


    function index()
    {
        set_exception_handler('json_exception_handler');
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        $callback = (isset($_GET['callback'])? $_GET['callback']: '?');
        $this->load->library('solr');
        $this->solr->setOpt('q', '*:*');
        $this->solr->setOpt('fq', 'status:PUBLISHED');
        $this->solr->setOpt('rows','0');
        $this->solr->setFacetOpt('field', 'class');
        $this->solr->executeSearch();

        $classes = $this->solr->getFacetResult('class');

        $data = array('collection'=>0,'service'=>0,'activity'=>0,'party'=>0);
        foreach($classes as $class=>$num){
            $data[$class] = $num;

        }

        $retStr['theHtml'] =$data['collection'];
        /*'<span class="green_heading">What&#39;s in Research Data Australia:</span><br />
<span class="green_heading indent">Collections </span>(Research Datasets) = '.$data['collection'].'<br />
<span class="green_heading indent">Parties </span>(Researchers, Organisations) = '.$data['party'].'<br />
<span class="green_heading indent">Activities </span>(Projects, Research Grants, Programs) = '.$data['activity'].'<br />
<span class="green_heading indent">Services </span>(to create or use a collection) =  '.$data['service'].'<br />';*/
        return $this->JSONP($callback,$retStr);
    }

    private function JSONP($callback, $r){
        echo ($callback) . '(' . json_encode($r) . ')';
    }

    function demo()
    {
        $data['title'] = 'Registry summary widget';

        $this->load->view("demo", $data);
    }
}

    ?>
