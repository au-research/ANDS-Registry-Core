<?php

class Topics extends MX_Controller
{
	function update_index()
	{
		$this->load->model('topics_list');
		$this->topics_list->loadFromFile();

		$solr_doc_list = $this->topics_list->transformTopics();
		echo $this->topics_list->reindexTopics($solr_doc_list);
		echo "<br/><br/><b>Update competed</b>";
	}	

	/**
	 * @ignore
	 */
	function __construct()
	{
		parent::__construct();
	}	
}