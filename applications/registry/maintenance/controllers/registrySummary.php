<?php


class registrySummary extends MX_Controller
{
	private $input; // pointer to the shell input
	private $start_time; // time script run (microtime float)
	private $exec_time; // time execution started
	private $_CI; 	// an internal reference to the CodeIgniter Engine 


	function index()
	{
		
		acl_enforce('REGISTRY_STAFF');
		$data['title'] = 'Whole of Registry Quality Summary';
		$data['small_title'] = '';
		$data['scripts'] = array('registry_summary');
		$data['js_lib'] = array('core',  'dataTables', 'data_source', 'googleapi');

		$data['dataSources'] = $this->getDataSources();

		$this->load->view("registry_summary", $data);
	}

function getDataSources(){
		acl_enforce('REGISTRY_STAFF');

		$this->load->model("data_source/data_sources","ds");

		$dataSources = $this->ds->getAll(0,0);//get everything

		$items = array();
		foreach($dataSources as $ds){
			$item = array();
			$item['title'] = $ds->title;
			$item['id'] = $ds->id;
			$item['chart_html'] = '<h4>'.$ds->title.'</h4>';
			$item['chart_html'] .= "<div class='drawGraph' id='".$ds->id."'>					<div id='overall_chart_div_".$ds->id."' style='width:80%; margin:auto; min-height:250px;''>
						<i>Loading data source quality information...</i>
					</div></div><hr />";
		
			array_push($items, $item);
		}

		return $items;

	}
}