<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Registry Widget Controller
 * Acts as proxy and documentation
 *
 * @author  Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 */
class Registry_widget extends MX_Controller{

	/**
	 * Display Documentation
	 * @return HTML
	 */
	function index(){
		$data['title'] = 'Registry Widget - ANDS';
        $data['scripts'] = array('registry_widget_loader');
        $data['js_lib'] = array('core', 'registry_widget', 'prettyprint');
        $this->load->view('documentation', $data);
	}

	function proxy($action=''){
		set_exception_handler('json_exception_handler');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$callback = (isset($_GET['callback'])? $_GET['callback']: '?');

        //log the usage of this widget with a public api_key

        require_once (API_APP_PATH.'core/helpers/api_helper.php');
        $terms = array(
            'event' => 'api_hit',
            'api_key' => 'public',
            'api_version' => 'legacy',
            'path' => 'registry_widget'
        );
        api_log_terms($terms);

		if($action=='lookup'){
			if(isset($_GET['q'])){
				$r = $this->lookup($_GET['q']);
				$this->JSONP($callback, $r);
			}else{
				$this->JSONP($callback, array('status'=>1, 'message'=>'q must be specified for lookup'));
			}
		}elseif($action=='search'){
			$q = (isset($_GET['q'])? $_GET['q']: false);
			$custom_q = (isset($_GET['custom_q'])? $_GET['custom_q']: false);
			if($q){
				$r = $this->search($q);
				$this->JSONP($callback, $r);
			}elseif($custom_q){
				$r = $this->search($custom_q, true);
				$this->JSONP($callback, $r);
			}else{
				$this->JSONP($callback, array('status'=>1, 'message'=>'q or custom_q must be specified for search'));
			}
		}
	}

	private function JSONP($callback, $r){
		echo ($callback) . '(' . json_encode($r) . ')';
	}

	private function lookup($query){
		$query = urldecode($query);
		$r = array();
		$this->load->model('registry/registry_object/registry_objects', 'ro');
		$ro = $this->ro->getByID($query);
		if(!$ro) $ro = $this->ro->getBySlug($query);
		if(!$ro) $ro = $this->ro->getPublishedByKey($query);
		if($ro){
			$r['status'] = 0;
			$r['result'] = array(
				'id'=>$ro->id,
				'rda_link'=>portal_url($ro->slug),
				'key'=>$ro->key,
				'slug'=>$ro->slug,
				'title'=>$ro->title,
				'class'=>$ro->class,
				'type'=>$ro->type,
				'group'=>$ro->group,
			);
			if($ro->getMetadata('the_description')) {
				$r['result']['description']=$ro->getMetadata('the_description');
			}else $r['result']['description'] = '';
		}else{
			$r['status'] = 1;
			$r['message'] = 'No Registry Object Found';
		}
		return $r;
	}

    private function search($query, $custom = false){
        $q = urldecode($query);
        $r = array();
        $this->load->library('solr');
        if(!$custom){
            $filters = array('q'=>$q);
            $this->solr->setFilters($filters);
        }else{
            $this->solr->setCustomQuery($q);
        }

        $this->solr->executeSearch();

        $r['numFound'] = $this->solr->getNumFound();

        if($r['numFound'] > 0){
            $r['status']=0;
            $solrResult = $this->solr->getResult();
            if($custom){
                $r['result'] = $this->addExtraContent($solrResult);
            }else{
                $r['result'] = $solrResult;
            }

        }else{
            $r['status']=1;
            $r['message'] = 'No Result Found!';
        }
        $r['solr_header'] = $this->solr->getHeader();
        $r['timeTaken'] = $r['solr_header']->{'QTime'} / 1000;
        return $r;
    }


    private function addExtraContent($solrResult)
    {

        foreach($solrResult->docs as $doc)
        {
            //var_dump($doc);
            $id = $doc->id;

            $this->db->select('data')
                ->from('record_data')
                ->where('registry_object_id',$id)
                ->where('scheme','rif')
                ->where('current',true)
                ->limit(1);
            $query = $this->db->get();
            foreach ($query->result_array() AS $row)
            {
                $doc->rif = simplexml_load_string($row['data']);
            }

        }
        return $solrResult;
    }

	function download($min=''){
		$this->load->library('zip');
		if($min=='minified'){
			$this->zip->read_file('./applications/apps/registry_widget/assets/dist/registry_widget_v2.min.css');
			$this->zip->read_file('./applications/apps/registry_widget/assets/dist/registry_widget_v2.min.js');
		}elseif($min=='full'){
			$this->zip->read_dir('./applications/apps/registry_widget/assets/css/', false);
			$this->zip->read_dir('./applications/apps/registry_widget/assets/js/', false);
			$this->zip->read_dir('./applications/apps/registry_widget/assets/dist/', false);
		}else{
			$this->zip->read_file('./applications/apps/registry_widget/assets/css/registry_widget_v2.css');
			$this->zip->read_file('./applications/apps/registry_widget/assets/js/registry_widget_v2.js');
		}
		$this->zip->download('registry_widget.zip');
	}
}