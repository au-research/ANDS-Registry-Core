<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * 
 */
class Stats {
	
	private $CI;
	private $db;

	/**
	 * Register a page view and essential information
	 * about the request for statistical purposes
	 *
	 * @param note string 	optionally specifies a note to 
	 *						be appended to the entry
	 */
	public function registerPageView($registry_object_id=null)
	{
		$values = array();

		// The server time of the request
		$values['timestamp'] = time();

		// The request URI (what URI/page was called)
		if(isset($_SERVER['REQUEST_URI']) && $request_uri = $_SERVER['REQUEST_URI'])
		{
			$values['request_uri'] = $request_uri;
		}

		// The page which referred us here (if any)
		if(isset($_SERVER['HTTP_REFERER']) && $referer = $_SERVER['HTTP_REFERER'])
		{
			$values['referer'] = $referer;
		}

		// Details about the user that browsed here
		$values['ip_address'] = $this->CI->input->ip_address();
		$values['user_agent'] = $this->CI->input->user_agent();

		// Including their login id if they are already logged in
		if($this->CI->user->loggedIn())
		{
			$values['login_identifier'] = $this->CI->user->identifier();
		}

		// Optionally, if this page is a view of a registry object
		if ($registry_object_id) { $values['registry_object_id'] = $registry_object_id; }

		$this->db->insert('page_views', $values);
	}

	/**
	 * Register a click from our website to an outgoing
	 * link.
	 *
	 * @param note string 	optionally specifies a note to 
	 *						be appended to the entry
	 */
	public function registerClick($source_url, $target_url, $note=null)
	{
		$values = array();

		// The server time of the request
		$values['timestamp'] = time();

		// The source URI (what page was the link originally on)
		$values['source_url'] = $source_url;
		$values['target_url'] = $target_url;

		// Details about the user that browsed here
		$values['ip_address'] = $this->CI->input->ip_address();
		$values['user_agent'] = $this->CI->input->user_agent();

		// Including their login id if they are already logged in
		if($this->CI->user->loggedIn())
		{
			$values['login_identifier'] = $this->CI->user->identifier();
		}

		// Optionally, a note for whatever use...
		if ($note) { $values['note'] = $note; }

		$this->db->insert('click_stats', $values);
	}


	/**
	 * Register a term that has been searched for
	 *
	 * @param search_term 	the term being searched for
	 * @param note			optionally specifies a note to 
	 *						be appended to the entry
	 */
	public function registerSearchTerm($search_term, $num_found, $note=null)
	{
		$values = array();

		// The server time of the request
		$values['timestamp'] = time();

		// The term which was searched for
		$values['term'] = $search_term;
		$values['num_found'] = $num_found;

		// Details about the user that browsed here
		$values['ip_address'] = $this->CI->input->ip_address();
		$values['user_agent'] = $this->CI->input->user_agent();

		// Including their login id if they are already logged in
		if($this->CI->user->loggedIn()){
			$values['login_identifier'] = $this->CI->user->identifier();
		}

		// Optionally, a note for whatever use...
		if ($note) { $values['note'] = $note; }

		$this->db->insert('search_terms', $values);

		//increment or update search_occurence and update the ranking
		
		$existing_terms = $this->db->select('*')->where('term', $search_term)->limit(1)->get('search_occurence');
		if($existing_terms->num_rows() == 0){
			//there is none, add a new one
			$new_term['term'] = $search_term;
			$new_term['occurence'] = 1;
			$new_term['num_found'] = $num_found;
			$new_term['ranking'] = $this->calculate_ranking(1, $num_found);
			$this->db->insert('search_occurence', $new_term);
		}else{
			//there is already 1
			foreach($existing_terms->result() as $term){
				$new_occurence = $term->occurence + 1;
				$data = array(
					'occurence' => $new_occurence,
					'num_found' => $num_found,
					'ranking' => $this->calculate_ranking($new_occurence, $num_found)
				);
				$this->db->where('term', $search_term);
				$this->db->update('search_occurence', $data);
			}
		}
	}

	/**
	 * magic that results in the ranking of which we used to suggest search terms
	 * @param  int $occurence 
	 * @param  int $num_found 
	 * @return int ranking
	 */
	private function calculate_ranking($occurence, $num_found){
		return $occurence * log($num_found);
	}

	/**
	 * return a list of top 5 ranked search suggestion, ordered by search occurence
	 * @param  string $like the term to match with
	 * @return array       
	 */
	public function getSearchSuggestion($like)
	{
		$result = array();
		if($like){
			$this->db->select('term')->order_by('ranking', 'desc')->limit(5)->like('term', $like)->where('ranking >', 0);
			$matches = $this->db->get('search_occurence');
			foreach($matches->result() as $match){
				array_push($result, $match->term);
			}
		}
		return $result;
	}

	/**
	 * Register a term that has been searched for with it's reulting hits
	 *
	 * @param search_term 	the term being searched for
	 * @param occurence		the number of registry objects returned
	 */
	public function registerSearchStats($search_term, $occurence)
	{
		$values = array();

		// The server time of the request
		$values['timestamp'] = time();

		// The term which was searched for
		$values['search_term'] = $search_term;

		// The number of objects returned from the search
		$values['occurrence'] = $occurence;
	

		$this->db->insert('search_result_counts', $values);
	}

	/**
	 * Updated the search_occurence to sync the number of terms
	 * searched for
	 *
	 * @param since int 	timestamp since the last sync or
	 *						null to resync all counts
	 */
	public function updateSearchTermOccurence($since = null)
	{
		if ($since)
		{
			// This mode is "additive" for all entries since time $since
			// Select matching terms
			$this->db->select('term, COUNT(*) AS occurence', FALSE)->where('timestamp >=', $since)->group_by('term');
			$matches = $this->db->get('search_terms');

			foreach($matches->result_array() AS $match)
			{
				$this->db->where('term', $match['term'])->select('occurence');
				$aggregate_count = $this->db->get('search_occurence');

				// This function will always return a result (count = 0  if term does not exist)
				$aggregate_count = array_pop($aggregate_count->result_array());

				if ((int)$aggregate_count['occurence'] > 0)
				{
					// There already exists an occurence within the aggregate table
					// so ADD to it
					$this->db->where('term',$match['term']);
					$this->db->update('search_occurence', array('occurence' => ((int)$aggregate_count['occurence'] + (int)$match['occurence'])));
				}
				else
				{
					// Otherwise just add it 
					$this->db->insert('search_occurence', array('term'=> $match['term'], 'occurence' => $match['occurence']));
				}
			}

		}
		else
		{
			// This mode is like a "Resync" (it replaces all existing aggregates)
			$this->db->truncate('search_occurence');

			$this->db->select('term, COUNT(*) AS occurence', FALSE)->group_by('term');
			$matches = $this->db->get('search_terms');

			foreach($matches->result_array() AS $match)
			{
				$this->db->insert('search_occurence', array('term'=>$match['term'], 'occurence'=>$match['occurence']));
			}
		}
	}


	public function Stats()
	{
		$this->CI =& get_instance();

		// setup the DB connection
		$this->db = $this->CI->load->database('portal', TRUE);
	}

		 
}

/* End of file Stats.php */