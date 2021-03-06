<?php
/*
Copyright 2009 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*******************************************************************************/
/*
 * ANDS SISSVOC Vocab Widget - search service v0.1
 *
 * JSONP service
 *
 *
 * Requires cURL PHP extensions. PHP5.2+, PHP5.4 recommended
 */
// some constants
if (isset($solr_base) && !empty($solr_base)) {
    define("SOLR_URL", $solr_base . "select?wt=phps&rows=0&q=subject_vocab_uri%%3A(%%22%s%%22)+%s+%s");
}
else {
	throw new Exception('SOLR must be configured and installed correctly');
    // define("SOLR_URL", "http://researchdata.ands.org.au:8080/solr/select?wt=phps&rows=0&q=subject_vocab_uri%%3A(%%22%s%%22)+%s+%s");
}
if (isset($sissvoc_base) && !empty($sissvoc_base)) {
    define("BASE_URL", $sissvoc_base);
}
else {
	throw new Exception('Vocab Server must be configured and installed correctly');
    // define("BASE_URL", "http://researchdata.ands.org.au:8080/vocab/api/");
}

define("SEARCH_URL", "/concept.json?labelcontains=");
define("NARROW_URL", "/concept/narrower.json?uri=");
define("INCOLLECTION_URL", "/concept/inCollection.json?uri=");
define("ALLNARROW_URL", "/concept/allNarrower.json?uri=");
define("BROAD_URL", "/concept/broader.json?uri="); #future use
define("TOP_URL", "/concept/topConcepts.json");
define("MAX_RESULTS", 200); #sisvoc only returns 200 items

// logging of calls to legacy version of our services and widgets to flat file logging - added 09/02/2016

require_once (API_APP_PATH.'core/helpers/api_helper.php');
$terms = array(
    'event' => 'api_hit',
    'api_key' => 'public',
    'api_version' => 'legacy',
    'path' => 'apps/vocab_widget',
);
api_log_terms($terms);

class VocabProxy
{

	/*
	 * @var actions this proxy can execute; processor callbacks are
	 * initialised by the constructor
	 */
	private $valid_actions = array(
		"search" => array(
			'url' => SEARCH_URL,
			'queryprocessor' => false,
			'itemprocessor' => false,
			'sortprocessor' => false),
		"allnarrow" => array(
			'url' => ALLNARROW_URL,
			'queryprocessor' => false,
			'itemprocessor' => false,
			'sortprocessor' => false),
		"narrow" => array(
			'url' => NARROW_URL,
			'queryprocessor' => false,
			'itemprocessor' => false,
			'sortprocessor' => false),
		"collection" => array(
			'url' => INCOLLECTION_URL,
			'queryprocessor' => false,
			'itemprocessor' => false,
			'sortprocessor' => false),
		"top" => array(
			'url' => TOP_URL,
			'queryprocessor' => false,
			'itemprocessor' => false,
			'sortprocessor' => false));

	//what we send back
	private $jsonData = array('status' => 'ERROR',
				  'message' => 'action must be defined');

	// Some defaults
	private $limit = 100; #a mildly sane limit
	private $callback = "function";
	private $action = false;
	private $lookfor = false;
	private $repository = false;
	private $debug = false;

	/**
	 * these have to be public because my dodgy count injector code uses (a
	 * reference to) $this in a closure
	 */
	public $solr_query_callback = false;
	public $solr_query_callback_op = false;

	/**
	 * Proxy is an atomic object: it gets instantiated, runs, and prints
	 * output all in one fell swoop.
	 */
	public function __construct() {
		// Setup HTTP headers so jQuery/browser interprets as JSON
		header('Cache-Control: private, must-revalidate');
		header('Content-type: application/json');

		/**
		 * Set up the query processors:
		 *  - for 'search', we want to urlencode the query.
		 *  - for 'narrow', we have a URI, so we don't want to mess with that
		 *  - for 'top', we don't need (or want) a query: silently drop it
		 */
		$this->valid_actions['search']['queryprocessor'] = function($e) { return urlencode($e); };
		$this->valid_actions['top']['queryprocessor'] = function($e) { return ""; };

		/**
		 * Set up sort processors:
		 *  - for 'top', 'narrow', and 'allnarrow' we want terms sorted by notation, or label
		 *  - for 'collection' sort by description
		 */
		foreach (array('top', 'allnarrow') as $action) {
		    $this->valid_actions[$action]['sortprocessor'] = function($e1, $e2) {
			if (array_key_exists('notation', $e1) && array_key_exists('notation', $e2))
			{
			    $l1 = (int)$e1['notation'];
			    $l2 = (int)$e2['notation'];
			    return $l1 <= $l2 ? -1 : 1;
			}
			else
			{
			    return;
			}
		    };
		}

		  $this->valid_actions['collection']['sortprocessor'] = function($e1, $e2) {
			if (array_key_exists('definition', $e1) && array_key_exists('definition', $e2))
			{
			    $l1 = $e1['definition'];
			    $l2 = $e2['definition'];
			    return $l1 <= $l2 ? -1 : 1;
			}
			else
			{
			    return;
			}
		    };



		/**
		 * Set up item processors; this is used to inject solr term counts based on the
		 * vocab's 'about' URL. Note: this mightn't be the best way to go... awfully hard coded
		 */
		foreach($this->valid_actions as &$action)
		{
			$handler = $this;
			$action['itemprocessor'] = function($items) use($handler) {
				return array_map(function($e) use($handler) {
						if (array_key_exists('about', $e) &&
						    substr($e['about'], 0, 7) === 'http://')
						{
							if ($handler->solr_query_callback !== false) {
								$count_url = sprintf(SOLR_URL,$e['about'],
										     $handler->solr_query_callback_op,
										     $handler->solr_query_callback);
							}
							else {
								$count_url = sprintf(SOLR_URL,$e['about'], "", "");
							}
							try
							{
								$solr_response = unserialize(file_get_contents($count_url));
								$e['count'] = $solr_response['response']['numFound'];
							}
							catch (Exception $ee)
							{
								$e['count'] = 0;
							}
						}
						else
						{
							$e['count'] = 0;
						}
						return $e;
					},
					$items);
			};
		}



		if ($this->setup()) {
			$this->handle();
		}
		$this->display();
	}

	/**
	 * Handle the request by querying the action url
	 * with specified parameters
	 */
	private function handle() {
		$data = false;
		$url = $this->urlFor($this->action);
		if ($this->debug)
		{
			$this->jsonData['message'] .= " [$url]";
		}

		if ($url) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);
			$data = json_decode(curl_exec($ch), true) or die(curl_error($ch));
			if ($data != null) {
				$this->jsonData['status'] = 'OK';
			}
		}
		if ($data && $this->jsonData['status'] == "OK") {
			$items = (array)$data['result']['items'];

			if (is_callable($this->valid_actions[$this->action]['sortprocessor']))
			{
				usort($items,
				      $this->valid_actions[$this->action]['sortprocessor']);
			}

			$this->jsonData['items'] = array_map(function($i) {
					if (is_string($i)) {
						return false;
					}
					$i['label'] = $i['prefLabel']['_value'];

					$i['about'] = $i['_about'];
					if (array_key_exists('broader', $i) &&
					    is_array($i['broader']))

					{
					    $i['broader'] = $i['_about'];
					}


					if (!array_key_exists('narrower', $i))
					{
					    $i['narrower'] = false;
					}
					else
					{
					    $i['narrower'] = (array)$i['narrower'];
					}

					unset($i['_about'],
					      $i['prefLabel']);
					return $i;
				},
				array_slice($items,
					    0,
					    $this->limit));
			$this->jsonData['items'] = array_values(array_filter($this->jsonData['items'],
									     function($e) {
										     return $e !== false;
									     }));
			$this->jsonData['count'] = count($this->jsonData['items']);
		}
	}

	/**
	 * Is the supplied action valid?
	 * @param the action
	 * @return true if valid, false otherwise
	 */
	private function isValidAction($action) {
		return array_key_exists($action, $this->valid_actions);
	}

	/**
	 * Get the endpoint url for the specified action
	 * @param the action
	 * @return the URL to query, or false if the action isn't valid
	 */
	private function urlFor($action) {
		if ($this->isValidAction($action)) {
			$validAction = $this->valid_actions[$action];
			$processor = $validAction['queryprocessor'];
			$querystring = is_callable($processor) ?
				call_user_func($processor, $this->lookfor) :
				$this->lookfor;
            if(strpos($this->repository, 'http') === 0){
                // using full url for sissvoc webapp
                return sprintf("%s%s%s",
                    $this->repository,
                    $validAction['url'],
                    $querystring);
            } else {
			    return sprintf("%s%s%s%s",
				       BASE_URL,
				       $this->repository,
				       $validAction['url'],
				       $querystring);
            }
		}
		else {
			return false;
		}
	}

	/**
	 * A "kitchen sink" setup routine:
	 *  - parses $_REQUEST params for expected data:
	 *     - repsitory
	 *     - action
	 *     - lookfor
	 *     - limit
	 *     - callback
	 *     - sqc
	 *     - sqc_op
	 *  - initialises internal variables based on request data
	 *  - sets message response accordingly
	 * @return true if preconditions met, false otherwise
	 */
	private function setup() {
		$this->debug = isset($_REQUEST['debug']);
      //  $action = $this->input->get('action');
		if (isset($_REQUEST['sqc']) &&
		    !empty($_REQUEST['sqc'])) {
			$this->solr_query_callback = rawurlencode($_REQUEST['sqc']);

			if (isset($_REQUEST['sqc_op']) &&
			    !empty($_REQUEST['sqc_op'])) {
				$this->solr_query_callback_op = $_REQUEST['sqc_op'];
			}
			else {
				$this->solr_query_callback_op = "AND";
			}
		}

		if (isset($_REQUEST['repository'])) {
			//strip leading "../", and normalise "/"
            if(strpos($_REQUEST['repository'], 'http') === 0){
                $this->repository = $_REQUEST['repository'];
            }
            else{
			$this->repository = preg_replace(
				"/\/+/",
				"/",
				preg_replace(
					"/^(\.\.\/)+/",
					"",
					$_REQUEST['repository']));
            }
		}
		if (isset($_REQUEST['action'])) {
			if ($this->isValidAction($_REQUEST['action'])) {
				$this->action = $_REQUEST['action'];
				$this->jsonData['message'] = 'action: ' .
					$this->action;
			}
			else {
				$this->jsonData['message'] .= " and valid: " .
					"one of " .
					implode(", ", array_keys($this->valid_actions));
			}
		}

		if ($this->action) {
			if (isset($_REQUEST['lookfor'])) {
				// This was rawurlencode(...), but there's a call
				// to urlencode() in the queryprocessor for search.
				// So no longer double-encode the search term.
				$this->lookfor = $_REQUEST['lookfor'];
				$this->jsonData['message'] .= " (" . $_REQUEST['lookfor'] . ")";
			}

			if (isset($_REQUEST['limit'])) {
				if (is_numeric($_REQUEST['limit'])) {
					$this->limit = $_REQUEST['limit'];
					if ($this->limit > MAX_RESULTS) {
						$this->jsonData['warning'] = "only retrieving first " .
							MAX_RESULTS . " matches";
						$this->limit = MAX_RESULTS;
					}
				}
				else {
					$this->jsonData['warning'] = "limit must be numeric: " .
						"falling back to default limit of " . $this->limit;
				}
				$this->jsonData['limit'] = $this->limit;
			}

			if (isset($_REQUEST['callback'])) {
				$this->callback = $_REQUEST['callback'];
			}
			return true;
		}
		else {
			return false;
		}

	}

	/**
	 * Dump a whole bunch of JSON to STDOUT
	 */
	private function display() {
		$this->jsonData = (defined(PHP_VERSION_ID) && PHP_VERSION_ID >= 50400) ?
			json_encode($this->jsonData, JSON_UNESCAPED_SLASHES) :
			str_replace('\/','/', json_encode($this->jsonData));
		echo $this->callback . "(" . $this->jsonData . ");";
	}
}

function definition_sort ($e1, $e2) {
	if (array_key_exists('definition', $e1) && array_key_exists('definition', $e2))
	{
	    $l1 = $e1['definition'];
	    $l2 = $e2['definition'];
	    return $l1 <= $l2 ? -1 : 1;
	}
	else
	{
	    return;
	}
};

$proxy = new VocabProxy();
?>
