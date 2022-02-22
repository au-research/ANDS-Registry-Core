<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Administration controller
 *
 * Base stub for administrative control of the registry
 *
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @package ands/services
 *
 */
class Administration extends MX_Controller {

	public function index()
	{
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$data['title'] = 'Registry Administration';
		$this->load->view('admin_panel', $data);
	}

	public function triggerNLAHarvest()
	{
	    $script = \ANDS\Commands\RunScriptCommand::getScript("nlaPullBack");
	    $script->run();
	    $logs = $script->getCommand()->getLogs();
	    echo implode("<br/>", $logs);
	}

	public function nla_pullback()
	{
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$data['title'] = 'Registry Administration - NLA Party Pullback';


		$config = \ANDS\Util\Config::get('nla');

		$dataSource = \ANDS\Repository\DataSourceRepository::getByKey($config['datasource']['key']);

		if (!$dataSource) {
		    throw new Exception("Not configured for NLA pull back, update configurations");
        }

        // TODO: add created_at and modified_at to registry_objects table
//        $pullBackEntries = \ANDS\RegistryObject::where('data_source_id', $dataSource->data_source_id)
//            ->latest()
//            ->take(100);

        $pullBackEntries = [];
		$this->db->distinct('registry_object_id')->select('roa.registry_object_id, key, title, roa.value AS "created"')
				->join('registry_object_attributes roa', 'roa.registry_object_id = ro.registry_object_id')
				->from('registry_objects ro')
				->where('data_source_id', $dataSource->data_source_id)
				->where('roa.attribute = "updated"')
				->order_by('roa.value', 'DESC')
				->limit(100);
		$query = $this->db->get();
		if ($query->num_rows()) {
		    foreach ($query->result_array() AS $result) $pullBackEntries[] = $result;
		}

		$this->load->view('nla_pullback', [
		    'js_lib' => ['core'],
            'scripts' => [],
            'title' => 'Registry Administration - NLA Party Pullback',
            'dataSource' => $dataSource,
            'data_source_url' => base_url('data_source/manage#!/view/' . $dataSource->id),
            'pullback_entries' => $pullBackEntries
        ]);
	}


	public function api_log()
	{
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$data['title'] = 'Registry Administration - API Log';

		$this->db->order_by('timestamp','DESC');
		$query = $this->db->get('api_requests', 100);
		$data['log_entries'] = $query;

		$this->load->view('api_log', $data);
	}

	public function api_keys()
	{
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$data['title'] = 'Registry Administration - API Log';

		$this->db->order_by('created','DESC');
		$query = $this->db->get('api_keys', 100);

		$api_keys = array();

		foreach ($query->result_array() AS $result)
		{
			$this->db->where(array('api_key'=>$result['api_key'], 'timestamp >=' => (time()-ONE_MONTH)));
			$this->db->from('api_requests');
			$queries_this_month = $this->db->count_all_results();

			$this->db->where(array('api_key'=>$result['api_key']));
			$this->db->from('api_requests');
			$queries_ever = $this->db->count_all_results();

			$api_keys[] = array_merge($result, array(
				"queries_ever"=>$queries_ever,
				"queries_this_month"=>$queries_this_month
			));
		}
		$data['api_keys'] = $api_keys;

		$this->load->view('api_keys', $data);
	}

	public function clean_rif()
	{
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$data['title'] = 'Registry Administration - Clean rif of unnesessary extrif:annotations element';

		$pattern = "/\<extrif:ann(?ism).*\/>/";
		$pattern2 = "/(?ism)(\<extrif:annotations)(.*)(\>)(.*)(annotations\>)/";
		$pattern3 = "/(?ism)(\<extRif:annotations)(.*)(\>)(.*)(annotations\>)/";

		$this->db->select('id, , registry_object_id, data')
			->from('record_data')
			->where('scheme = "rif"')
			->where('data LIKE "%if:annotat%"')
			->where('data NOT LIKE "%digitalAssets%"');

		$query = $this->db->get();

		if ($query->num_rows())
		{
			echo "We have found ".$query->num_rows()." records which have the extRif:annotations element in the rifcs <br />";
			$emptycount=0;
			$embeddedcount=0;

			foreach($query->result() as $result){
				$olddata = $result->data;

				// The majority of hits is expected to be the empty extrif:annotations element
				$newdata = preg_replace($pattern,"",$result->data);

				if($newdata==$olddata)
				{
					//the element  is not an empty element
  					$newdata = preg_replace($pattern2,"",$result->data);

  					if($newdata==$olddata){
  						//might be the case that it is an extRif element rather than extrif
  						$newdata = preg_replace($pattern3,"",$result->data);

  						if($newdata==$olddata)
  						{
  							//none of our extrif:annotaion elements have matched the xml so lets print it out so we can investigate it mannually
  							echo "We have an issue cleaning rifcs of extrif annotaion element in record_data id ".$result->id." with registry_object_id ".$result->registry_object_id."<br />";
  						}
  					}
  					else
  					{
  						$embeddedcount++;
  					}

				}
				else
				{
					$emptycount++;
				}

				$data = array(
					'data'=> $newdata
					);
				$this->db->where('id', $result->id);
				$this->db->update('record_data', $data);

			}
			//just a summary of what we did
			echo "We needed to clean ".$emptycount. "empty annotaion elements and ".$embeddedcount." embedded elements.<br />";
		}
	}

	public function contributor_page_list() {
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$data['title'] = 'Registry Administration - Clean rif of unnesessary extrif:annotations element';

		$db = $this->load->database('portal', true);
		$data['page_requested'] = $db->get_where('contributor_pages', array('status'=>'REQUESTED'));
		$data['page_all'] = $db->get('contributor_pages');
		$this->load->view('contributor_page_list', $data);
	}

	public function __construct()
	{
		parent::__construct();
		acl_enforce('REGISTRY_SUPERUSER');
	}
}