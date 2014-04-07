<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends MX_Controller {

	function index(){
		ini_set('xdebug.profiler_enable',1);
		$data['title']='Research Data Australia';

		check_services();

		//solr for counts
		$this->load->library('solr');
		$this->solr->setOpt('q', '*:*');
		//$this->solr->setOpt('fq', 'status:PUBLISHED');
		$this->solr->setOpt('rows','0');
		$this->solr->setFacetOpt('field', 'class');
		$this->solr->executeSearch();

		//classes
		$classes = $this->solr->getFacetResult('class');
		$data = array('collection'=>0,'service'=>0,'activity'=>0,'party'=>0);
		foreach($classes as $class=>$num){
			$data[$class] = $num;
		}

		$this->solr->init();
		$this->solr->setOpt('q', 'class:("collection")');
		//$this->solr->setOpt('fq', 'status:PUBLISHED');
		$this->solr->setOpt('rows','0');
		$this->solr->setFacetOpt('field', 'group');
		$this->solr->setFacetOpt('limit', '200');
		$this->solr->executeSearch();
		//groups
		$groups = $this->solr->getFacetResult('group');
		$data['groups'] = array();
		foreach($groups as $group=>$num){
			if ($num > 0)
			{
				$data['groups'][$group] = $num;
			}
		}

		$this->load->library('stats');
		$this->stats->registerPageView();
		//spotlights
		
		$data['scripts'] = array('home_page');
		$data['js_lib'] = array('qtip');
		$this->load->view('home', $data);
	}


	function contributors(){
		//solr for counts
		$this->load->library('solr');
		$this->solr->setOpt('q', 'class:("collection")');
		//$this->solr->setOpt('fq', 'status:PUBLISHED');
		$this->solr->setOpt('rows','0');
		$this->solr->setFacetOpt('field', 'class');
		$this->solr->setFacetOpt('field', 'group');
		$this->solr->setFacetOpt('sort', 'group asc');
		$this->solr->setFacetOpt('limit', '200');
		$this->solr->executeSearch();

		//groups
		$groups = $this->solr->getFacetResult('group');

		$data['groups'] = array();
		foreach($groups as $group=>$num){
			if ($num > 0)
			{
				$data['groups'][$group] = $num;
			}
		}
		ksort($data['groups'], SORT_FLAG_CASE | SORT_NATURAL);

		//contributors
		$this->load->model('view/registry_fetch','registry');
		$data['contributors'] = $this->registry->fetchInstitutionalPages();


		$links = array();
		foreach($data['groups'] as $g=>$count){
			$l = '';
			if(sizeof($data['contributors']['contents'])>0){
				foreach($data['contributors']['contents'] as $c){
					if($c['title']==$g){
						$l = anchor($c['slug'], $g.' ('.$count.')');
						break;
					}else{
						$l = anchor('search#!/group='.rawurlencode($g), $g.' ('.$count.')');
					}
				}
			}else{
				$l = anchor('search#!/group='.rawurlencode($g), $g.' ('.$count.')');
			}
			array_push($links, $l);
		}
		$data['links'] = $links;
		$this->load->library('stats');
		$this->stats->registerPageView();
		$data['title'] = 'Contributors - Research Data Australia';
		$this->load->view('who_contributes', $data);
	}

	function about(){
		$data['title'] = 'About - Research Data Australia';
		$this->load->view('about', $data);
	}

	function disclaimer(){
		$data['title'] = 'Disclaimer - Research Data Australia';
		$this->load->view('disclaimer', $data);
	}

	function privacy_policy() {
		$data['title'] = 'Privacy Policy - Research Data Australia';
		$this->load->view('privacy_policy', $data);
	}

	function contact(){
		$data['title'] = 'Contact Us - Research Data Australia';
		$data['message'] = '';
		$site_admin_email = $this->config->item('site_admin_email');

		/*
			Obscure text email address from contact us page to help avoid email scrapers
		*/
		$data['contact_email'] = $this->myobfiscate($site_admin_email);
		if($this->input->get('sent')!=''){
			$this->load->library('user_agent');
			$data['user_agent']=$this->agent->browser();

			/* 
				check if fields hidden from actual users but avaiable to bots have been filled - if so let's not email 
			*/
			$title = $this->input->post('title');
			$last_name = $this->input->post('last_name');
			$bogus_email = $this->input->post('email');		

			/*
				also check that all three fields have been filled out - if not let's not email
			*/
			$name = $this->input->post('first_name');
			$email = $this->input->post('contact_email');
			$content = $this->input->post('content');

			if($title || $last_name || $bogus_email || !$name || !$email || !$content)
			{
				$data['sent'] = false;
				$data['message'] = "Please fill in all required fields.";
			//	$this->load->view('contact', $data);
			}
			else
			{
				$this->load->library('email');
				$this->email->from($email, $name);
				$this->email->to($site_admin_email);
				$this->email->subject('RDA Contact Us');
				$this->email->message($content);
				$this->email->send();
				$data['sent'] = true;
			}

		}else 
		{
			$data['sent'] = false;
		}
		
		$this->load->view('contact', $data);
	}

	function sitemap(){

    	parse_str($_SERVER['QUERY_STRING'], $_GET);
    	$solr_url = $this->config->item('solr_url');
    	$ds = '';
    	if(isset($_GET['ds'])) $ds=$_GET['ds'];


    	if($ds==''){
			$fields = array(
				'q'=>'*:*','version'=>'2.2','start'=>0,'rows'=>0, 'wt'=>'json',
				'fl'=>'key'
			);
					/*prep*/
			$fields_string='';
	    	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }//build the string
	    	$fields_string .= '&facet=true&facet.field=data_source_key&facet.limit=1000&facet.mincount=1';
	    	rtrim($fields_string,'&');

			$ch = curl_init();
	    	//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL,$solr_url.'select');//post to SOLR
			curl_setopt($ch,CURLOPT_POST,count($fields));//number of POST var
			curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);//post the field strings
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
	    	$content = curl_exec($ch);//execute the curl

	    	//echo 'json received+<pre>'.$content.'</pre>';

	    	$res = json_decode($content);
	    	$dsfacet = $res->{'facet_counts'}->{'facet_fields'}->{'data_source_key'};

			header("Content-Type: text/xml");
			// $this->output->set_content_type('text/xml');
			echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

			for($i=0;$i<sizeof($dsfacet);$i+=2){
				echo '<sitemap>';
				echo '<loc>'.base_url().'home/sitemap/?ds='.urlencode($dsfacet[$i]).'</loc>';
				echo '<lastmod>'.date('Y-m-d').'</lastmod>';
				echo '</sitemap>';
			}

			echo '</sitemapindex>';
		}elseif($ds!=''){
			$q = '*:* +data_source_key:("'.$ds.'")';
			$q = urlencode($q);
			$fields = array(
				'q'=>$q,'version'=>'2.2','start'=>0,'rows'=>50000, 'wt'=>'json',
				'fl'=>'key, slug, update_timestamp'
			);
					/*prep*/
			$fields_string='';
	    	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }//build the string
	    	rtrim($fields_string,'&');

			//echo $fields_string;

			$ch = curl_init();
	    	//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL,$solr_url.'select');//post to SOLR
			curl_setopt($ch,CURLOPT_POST,count($fields));//number of POST var
			curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);//post the field strings
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
	    	$content = curl_exec($ch);//execute the curl

	    	//echo 'json received+<pre>'.$content.'</pre>';

	    	$res = json_decode($content);
	    	$keys = $res->{'response'}->{'docs'};
	    	// var_dump($keys);

			header("Content-Type: text/xml");
			// $this->output->set_content_type('text/xml');
			echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

			foreach($keys as $k){
				//var_dump($k);
				echo '<url>';
				if ($k->{'slug'})
				{
					echo '<loc>'.base_url().$k->{'slug'}.'</loc>';
				}
				else
				{
					echo '<loc>'.base_url().'view/?key='.urlencode($k->{'key'}).'</loc>';
				}
				echo '<changefreq>weekly</changefreq>';
				echo '<lastmod>'.date('Y-m-d', strtotime($k->{'update_timestamp'})).'</lastmod>';
				echo '</url>';
			}

			echo '</urlset>';
		}
	}

	public function send(){
		$this->load->library('user_agent');
		$data['user_agent']=$this->agent->browser();
		$name = $this->input->post('name');
		$email = $this->input->post('email');
		$content = $this->input->post('content');

		$this->load->library('email');

		$this->email->from($email, $name);
		$this->email->to($this->config->item('site_admin_email'));
		$this->email->subject('RDA Contact Us');
		$this->email->message($content);

		$this->email->send();

		echo '<p> </p><p>Thank you for your response. Your message has been delivered successfully</p><p> </p><p> </p><p> </p><p> </p><p> </p><p> </p><p> </p>';
	}

	public function requestGrantEmail(){
		$this->load->library('user_agent');
		$data['user_agent']=$this->agent->browser();
		$name = $this->input->post('contact-name');
		$email = $this->input->post('contact-email');
		$content = 'Grant ID: '.$this->input->post('grant-id').NL;
		$content .= 'Grant Title: '.$this->input->post('grant-title').NL;	
		$content .= 'Institution: '.$this->input->post('institution').NL;
		$content .= 'purl: ('.$this->input->post('purl').')'.NL.NL;
		$content .= 'Reported by: '.$this->input->post('contact-name').NL;
		$content .= 'From: '.$this->input->post('contact-company').NL;
		$content .= 'Contact email: '.$this->input->post('contact-email').NL;
		
		$this->load->library('email');

		$this->email->from($email, $name);
		$this->email->to($this->config->item('site_admin_email'));
		$this->email->subject('Missing RDA Grant Record '.$this->input->post('grant-id'));
		$this->email->message($content);

		$this->email->send();

		echo '<p> </p><p>Thank you for your enquiry into grant `'.$this->input->post('grant-id').'`. A ticket has been logged with the ANDS Services Team. You will be notified when the grant becomes available in Research Data Australia. </p>';
	}

	public function myobfiscate($emailaddress){
 		$email= $emailaddress; 
 		$obfuscatedEmail = '';               
 		$length = strlen($email);                         
 		for ($i = 0; $i < $length; $i++){                
			$obfuscatedEmail .= "&#" . ord($email[$i]).";";
 		}
 		return $obfuscatedEmail;
	}
}