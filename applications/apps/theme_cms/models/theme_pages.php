<?php

class Theme_pages extends CI_Model {

	private $table = 'theme_pages';

	function get($slug='') {
		if($slug==''){
			$result = $this->db->get($this->table);
			if($result){
				return $result->result_array();
			}else return array();
		}else{
			$result = $this->db->get_where($this->table, array('slug'=>$slug), 1, 0);
			if($result) {
				return $result->result_array();
			}else return array();
		}
	}

	function add($data){
		$json = json_decode($data);
		$new = array(
			'title' => $json->title,
			'slug' => $json->slug,
			'img_src' => (isset($json->img_src) ? $json->img_src : ''),
			'description' => (isset($json->desc) ? $json->desc : ''),
			'visible' => 0,
			'content' => $data
		);
		$this->db->insert($this->table, $new);
		echo 1;
	}

	function save($data){
		$json = json_decode($data);
		$update = array(
			'title' => $json->title,
			'img_src' => (isset($json->img_src) ? $json->img_src : ''),
			'description' => (isset($json->desc) ? $json->desc : ''),
			'visible' => (isset($json->visible) ? $json->visible : 0),
			'secret_tag' => (isset($json->secret_tag) ? $json->secret_tag : ''),
			'content' => $data
		);

		$this->db->where('slug', $json->slug);

		$this->db->update($this->table, $update);
		if($update['visible']==1) $this->index($data);

		if($json->secret_tag){
			$secret_tag = $this->db->get_where('tags', array('name'=>$json->secret_tag));
			if($secret_tag->num_rows() > 0){
				$this->db->where('theme', $json->slug);
				$this->db->update('tags', array('theme'=>''));
				$this->db->where('name', $json->secret_tag);
				$this->db->update('tags', array('theme'=>$json->slug));
			}else{
				$this->db->where('theme', $json->slug);
				$this->db->update('tags', array('theme'=>''));
				$this->db->insert('tags', array(
					'name' => $json->secret_tag,
					'type' => 'secret',
					'theme' => $json->slug
				));
			}
		}
		echo 1;
	}

	function delete($data){
		$json = json_decode($data);
		$this->db->delete($this->table, array('slug'=>$json->slug));
		echo 1;
	}

	function index($data){
		$data = json_decode($data);
		$xml = '<doc>';
		$xml .=	"<field name='id'>topic_" . $data->slug ."</field>" . NL;
		$xml .=	"<field name='data_source_id'>topic</field>" . NL;
		$xml .=	"<field name='key'>topic</field>" . NL;
		$xml .=	"<field name='display_title'>".$data->title." Theme Page</field>" . NL;
		$xml .=	"<field name='list_title'>".$data->title." Theme Page</field>" . NL;
		$xml .=	"<field name='simplified_title'>".$data->title." Theme Page</field>" . NL;
		$xml .=	"<field name='class'>topic</field>" . NL;
		$xml .=	"<field name='slug'>theme/".$data->slug."</field>" . NL;
		$xml .=	"<field name='status'>PUBLISHED</field>" . NL;
		if(isset($data->desc)) $xml .= "<field name='description'>".htmlentities($data->desc)."</field>" . NL;
		if(isset($data->desc)) $xml .= "<field name='description_value'>".htmlentities($data->desc)."</field>" . NL;
		$xml .='</doc>';
		$this->load->library('solr');
		$this->solr->deleteByQueryCondition("id:(topic_".$data->slug.")");
		echo 'indexed';
	}

}