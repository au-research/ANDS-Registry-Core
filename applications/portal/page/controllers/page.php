<?php
/**
 * Page controller
 * This controller main purpose is to display static pages
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Page extends MX_Controller {

	/**
	 * Index / Home page
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return view 
	 */
	function index(){
		header('Content-Type: text/html; charset=utf-8');
		$this->load->library('vocab');
		
		//high level
		$highlevel = $this->config->item('subjects');
		foreach ($highlevel as &$item) {
			$query = '';
			foreach($item['codes'] as $code) {
				$query.='/anzsrc-for='.$code;
			}
			$item['query'] = $query;
		}

		//contributors
		$this->load->model('group/groups', 'groups');
		$contributors = $this->groups->getAll();

		$this->record_hit('home');
		$this->blade
			->set('scripts', array('home'))
			->set('highlevel', $highlevel)
			->set('contributors', $contributors)
			->render('home');
	}

	/**
	 * About page
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return view 
	 */
	function about() {

        $highlevel = $this->config->item('subjects');
        foreach ($highlevel as &$item) {
            $query = '';
            foreach($item['codes'] as $code) {
                $query.='/anzsrc-for='.$code;
            }
            $item['query'] = $query;
        }

        //contributors
        $this->load->model('group/groups', 'groups');
        $this->load->model('registry_object/registry_objects', 'registry_object');
        $contributors = $this->groups->getAll();
        $filters = Array('class'=>'collection','status'=>'PUBLISHED');
        $collections = $this->registry_object->checkRecordCount($filters);

        $this->record_hit('about');
        $this->blade
            ->set('scripts', array('home'))
            ->set('highlevel', $highlevel)
            ->set('contributors', $contributors)
            ->set('collections', $collections)
            ->render('about');

	}

	/**
	 * Privacy Policy
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return view 
	 */
	function privacy() {
		$this->record_hit('privacy');
		$this->blade->render('privacy_policy');
	}

	/**
	 * Disclaimer page
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return view 
	 */
	function disclaimer() {
		$this->record_hit('disclaimer');
		$this->blade->render('disclaimer');
	}

    /**
     * Help page
     * @author Liz Woods <liz.woods@ands.org.au>
     * @return view
     */
    function help() {
    	$this->record_hit('help');
        $this->blade->render('help');
    }

    public function requestGrantEmail(){
    	header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');

        $message['status'] = 'ERROR';
        $message['message'] = 'NOT ENOUGH INFORMATION OR SOMETHING IS STILL WRONG!';
        $data = json_decode(file_get_contents("php://input"), true);
        $data = $data['data'];
        $from_email = $data['contact_email'];
        $to_email = $this->config->item('site_admin_email');
        if($from_email && $to_email && $to_email != '<admin @ email>') {
            $name = $data['contact_name'];
            $content = 'Grant ID: '.$data['grant_id'].NL;
            $content .= 'Grant Title: '.$data['grant_title'].NL;
            $content .= 'Institution: '.$data['institution'].NL;
            $content .= 'purl: ('.$data['purl'].')'.NL.NL;
            $content .= 'Reported by: '.$data['contact_name'].NL;
            $content .= 'From: '.$data['contact_company'].NL;
            $content .= 'Contact email: '.$data['contact_email'].NL;
            $email = $this->load->library('email');
            $email->from($from_email, $name);
            $email->to($to_email);
            $email->subject('Missing RDA Grant Record '.$data['grant_id']);
            $email->message($content);
            $email->send();
            $message['status'] = 'OK';
            $message['message'] = 'Thank you for your enquiry into grant `'.$data['grant_id'].'`. A ticket has been logged with the ANDS Services Team. You will be notified when the grant becomes available in Research Data Australia.';
        }
        echo json_encode($message);
    }

    function grants() {

    	//high level
		$highlevel = $this->config->item('subjects');
		foreach ($highlevel as &$item) {
			$query = '';
			foreach($item['codes'] as $code) {
				$query.='/anzsrc-for='.$code;
			}
			$item['query'] = '/class=activity'.$query;
		}

		//contributors
		$this->load->model('group/groups', 'groups');
		$contributors = $this->groups->getFunders();

		$banner = asset_url('images/activity_banner.jpg','core');

    	$this->blade
    		->set('scripts', array('home'))
    		->set('highlevel', $highlevel)
    		->set('banner', $banner)
    		->set('contributors', $contributors)
    		->render('grants');
    }



	/**
	 * Display the sitemap
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return view 
	 */
	function sitemap($page='') {
    	parse_str($_SERVER['QUERY_STRING'], $_GET);
    	$solr_url = get_config_item('solr_url');
    	$ds = '';
    	if(isset($_GET['ds'])) $ds=$_GET['ds'];

    	$event = array(
			'event'=>'portal_page',
			'page' => 'sitemap',
			'ip' => $this->input->ip_address(),
			'user_agent' => $this->input->user_agent()
		);
		ulog_terms($event,'portal');

    	if ($page == 'main'){
    		$pages = array(
    			base_url(),
    			base_url('home/about'),
    			base_url('home/contact'),
    			base_url('home/privacy_policy'),
    			base_url('themes')
    		);

    		header("Content-Type: text/xml");
			echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
			foreach ($pages as $p) {
				echo '<url>';
				echo '<loc>'.$p.'</loc>';
				echo '<changefreq>weekly</changefreq>';
				echo '<lastmod>'.date('Y-m-d').'</lastmod>';
				echo '</url>';
			}
			echo '</urlset>';
    	} else {
	    	if($ds==''){

				$this->load->library('solr');
				$this->solr->setFacetOpt('field', 'data_source_key');
				$this->solr->setFacetOpt('limit', 1000);
				$this->solr->setFacetOpt('mincount', 0);

				$this->solr->executeSearch();
				$res = $this->solr->getFacet();

		    	$dsfacet = $res->{'facet_fields'}->{'data_source_key'};

				header("Content-Type: text/xml");
				echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
				echo '<sitemap><loc>'.base_url('home/sitemap/main').'</loc><lastmod>'.date('Y-m-d').'</lastmod></sitemap>';
				for($i=0;$i<sizeof($dsfacet);$i+=2){
					echo '<sitemap>';
					echo '<loc>'.base_url().'home/sitemap/?ds='.urlencode($dsfacet[$i]).'</loc>';
					echo '<lastmod>'.date('Y-m-d').'</lastmod>';
					echo '</sitemap>';
				}

				echo '</sitemapindex>';
			}elseif($ds!=''){

				$this->load->library('solr');
				$filters = array('data_source_key'=>$ds, 'rows'=>50000, 'fl'=>'key, id, update_timestamp, slug');
				$this->solr->setFilters($filters);
				$this->solr->executeSearch();
				$res = $this->solr->getResult();

		    	$keys = $res->{'docs'};
				$freq = 'weekly';
				if($this->is_active($ds)){
					$freq = 'daily';
				}

				header("Content-Type: text/xml");
				echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
				foreach($keys as $k) {
					//var_dump($k);
					echo '<url>';
					if ($k->{'slug'}){
						echo '<loc>'.base_url().$k->{'slug'}.'/'.$k->{'id'}.'</loc>';
					} else {
						echo '<loc>'.base_url().'view/?key='.urlencode($k->{'key'}).'</loc>';
					}
					echo '<changefreq>'.$freq.'</changefreq>';
					echo '<lastmod>'.date('Y-m-d', strtotime($k->{'update_timestamp'})).'</lastmod>';
					echo '</url>';
				}
				echo '</urlset>';
			}
    	}
	}

	public function is_active($ds_key){
		$this->load->library('solr');
		$filters = array('data_source_key'=>$ds_key);
		$this->solr->setFilters($filters);
		$this->solr->setFacetOpt('query', 'record_created_timestamp:[NOW-1MONTH/MONTH TO NOW]');
		$this->solr->executeSearch();
		$facet = $this->solr->getFacet();
		$result = $this->solr->getNumFound();
		if($facet){
			if($facet->{'facet_queries'}->{'record_created_timestamp:[NOW-1MONTH/MONTH TO NOW]'} > 0){
				return true;
			}else return false;
		}else return false;
	}

	function record_hit($page = 'home') {
		$event = array(
			'event'=>'portal_page',
			'page' => $page,
			'ip' => $this->input->ip_address(),
			'user_agent' => $this->input->user_agent()
		);
		ulog_terms($event,'portal');
	}


	public function __construct() {
		parent::__construct();
		$this->load->library('blade');
		$this->blade->set_template('omega');
	}
}