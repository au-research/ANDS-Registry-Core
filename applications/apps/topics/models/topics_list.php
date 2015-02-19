<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
*/
class Topics_list extends CI_Model
{
	// Settings
	private $topic_file_location = './assets/shared/topics/topics.json';
	private $canned_description_text = "This topic page provides an overview of a selection of relevant data collections within the Australian Research Data Commons.";

	public $topic_contents = null;



	function loadFromFile()
	{
		$this->topic_contents = json_decode(file_get_contents($this->topic_file_location), true);
		return $this->topic_contents;
	}

	function reindexTopics($solr_update_doc)
	{
		if (!$this->topic_contents) return null;

		$this->load->library('solr');
		$this->solr->deleteByQueryCondition("class:(topic)");
		echo $this->solr->addDoc('<add>' . $solr_update_doc . '</add>');
		echo $this->solr->commit();
		return true;
	}


	function transformTopics()
	{
		if (!$this->topic_contents) return null;
		
		$xml = "";

		foreach ($this->topic_contents['topics'] AS $key => $topic)
		{
			$description = explode("\n", $topic['html']);
			if (is_array($description)) 
			{
				$description = array_shift($description);
			}
			else
			{
				$description = $this->$canned_description_text;
			}

			$xml .= "<doc>" . NL;

			$xml .=	"<field name='id'>topic_" . $key ."</field>" . NL;
			$xml .=	"<field name='data_source_id'>topic</field>" . NL;
			$xml .=	"<field name='key'>topic</field>" . NL;

			$xml .=	"<field name='display_title'>".$topic['name']." Topic Page</field>" . NL;
			$xml .=	"<field name='list_title'>".$topic['name']." Topic Page</field>" . NL;
			$xml .=	"<field name='simplified_title'>".$topic['name']." Topic Page</field>" . NL;

			$xml .=	"<field name='class'>topic</field>" . NL;
			$xml .=	"<field name='slug'>topic/".$key."</field>" . NL;
			$xml .=	"<field name='status'>PUBLISHED</field>" . NL;
			//$xml .=	"<field name='logo'>".$topic."</field>" . NL;

			$xml .= "<field name='description'>".htmlentities($description)."</field>" . NL;
			$xml .= "<field name='description_value'>".htmlentities($topic['html'])."</field>" . NL;

			$xml .="</doc>" . NL;
		}
		return $xml;
	}



	function __construct()
	{
		parent::__construct();
	}
}