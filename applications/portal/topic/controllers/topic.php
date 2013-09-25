<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Topic extends MX_Controller {

	var $image_base_url;
	public function index()
	{
		$data['title']='Research Data Australia';
		$data['topics'] = $this->getFromDataFile();
		$data['image_base_url'] = $this->image_base_url;
		$this->load->view('list_topics', $data);
	}


	public function view_topic($topic_name)
	{
		$data['title']='Research Data Australia';

		$topics = $this->getFromDataFile();
		if (isset($topics[$topic_name]))
		{
			// The selected topic is in our data file
			$data['topic'] = $topics[$topic_name];
		}
		else
		{
			// Else change the status code; the view will handle the soft error display
			$this->output->set_header("HTTP/1.1 404 Not Found");
		}

		$this->load->view('view_topic', $data);
	}


	private function getFromDataFile()
	{
		$topics = array();
		$data_file = json_decode(@file_get_contents($this->config->item('topics_datafile')), true);
		if ($data_file && isset($data_file['topics']))
		{
			$topics = $data_file['topics'];
			$this->image_base_url = $data_file['image_url'];
		}
		else
		{
			throw new Exception("No topics could be loaded from the topics datafile.");
		}

		return $topics;
	}

}