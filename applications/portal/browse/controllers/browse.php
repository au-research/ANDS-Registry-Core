<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Browse extends MX_Controller {

	var $image_base_url;
	public function index(){
		$data['title']='Research Data Australia - Browse by Subjects';
		$data['scripts'] = array('rdabrowse');
		$data['js_lib'] = array('vocab_widget');
		// $data['vocabularies'] = $this->getVocabTree();
		// $data['image_base_url'] = $this->image_base_url;
		$this->load->view('browse_index', $data);
	}

	public function loadVocab(){
		$url = $this->input->post('url');
		$this->load->library('vocab');

		// Some sanity checking for cases where user clicks before the tree has fully rendered
		if (!$url)
		{
			echo "No details could be retrieved for null vocabulary URL";
			return;
		}

		$data['r'] = json_decode($this->vocab->getConceptDetail('anzsrc-for', $url));
		$data['notation'] = $data['r']->{'result'}->{'primaryTopic'}->{'notation'};
		$data['vocab'] = 'anzsrc-for';
		$data['prefLabel'] = $data['r']->{'result'}->{'primaryTopic'}->{'prefLabel'}->{'_value'};
		$data['uri']=$data['r']->{'result'}->{'primaryTopic'}->{'_about'};

		$this->load->view('conceptdetail', $data);
	}

	public function search(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$url = $this->input->post('url');
		if($this->input->post('start')){
			$start = $this->input->post('start');
		}else $start = 0;

	//	var_dump('+subject_vocab_uri:("'.$url.'")');
		$this->load->library('solr');
		//$this->solr->setOpt('defType', 'edismax');
		//$this->solr->setOpt('mm', '3');
		$this->solr->setOpt('start', $start);
		$this->solr->setOpt('q', '+subject_vocab_uri:("'.$url.'")');
		$data['solr_result'] = $this->solr->executeSearch();
		$data['solr_header'] = $this->solr->getHeader();
		$data['result'] = $this->solr->getResult();
		$data['numFound'] = $this->solr->getNumFound();
		$data['timeTaken'] = $data['solr_header']->{'QTime'} / 1000;

		$items = array();
		foreach($data['solr_result']->{'response'}->{'docs'} as $d){
			$item = array(
				'title'=>$d->{'list_title'},
				'class'=>$d->{'class'},
				'description'=>html_entity_decode($d->{'description'}),
				'url'=> base_url($d->{'slug'}),
				'display_footer' => true
			);
			$items[] = $item;
		}
		$result['links'] = $items;

		$rows=10;
		$pagination = array();
		if($start==0){
			$currentPage = 1;
		}else{
			$currentPage = ceil($start/$rows)+1;
		}
		$totalPage = ceil($data['numFound'] / (int) $rows);

		if ($currentPage==$totalPage)
		{
			$prev = $start-$rows;
			$next = false;
		}
		elseif ($currentPage != 1)
		{
			$prev = $start-$rows;
			$next = $start+$rows;
		}
		else
		{
			$prev = false;
			$next = $start+$rows;
		}
		$pagination = array("currentPage"=>$currentPage,"totalPage"=>$totalPage);
		if($prev!==false) $pagination['prev']=(string)$prev;
		if($next!==false) $pagination['next']=(string)$next;
		$result['pagination'] = $pagination;
		$result['count'] = $data['numFound'];

		echo json_encode($result);
	}




	private function renderRdaBrowsePage()
	{	
		$data['title']='Research Data Australia';
		$data['js_lib'] = array('vocab_widget');
		$data['scripts'] = array('rdabrowse');

		$data['vocabs'] = $this->getVocabTree();

		if ($this->input->get('subject'))
		{
			$data['result_list'] = $this->registry->fetchResultsBySubject($this->input->get('subject'));

		}
				

		$data['resultsDiv'] = $this->load->view('list_results', $data, true);	
		$this->load->view('list_vocabs', $data);

	}


	private function getVocabTree()
	{
		$vocabs = array();
	//	$data_file = json_decode(@file_get_contents($this->config->item('topics_datafile')), true);
	//	if ($data_file && isset($data_file['topics']))
	//	{
	//		$topics = $data_file['topics'];
	//		$this->image_base_url = $data_file['image_url'];
	//	}
	//	else
	//	{
	//		throw new Exception("No topics could be loaded from the topics datafile.");
	//	}

		return $vocabs;
	}

}