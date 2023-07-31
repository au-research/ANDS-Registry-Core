<?php
/**
  * This class is used to control the sending of Tweets about registry
  * activity to your nominated and configured twitter account.
  */

class Tweet extends MX_Controller
{
	private $consumer_key;
	private $consumer_secret;
	private $oauth_access_token;
	private $oauth_access_secret;
	private $num_days_history = 7; // number of days worth of history to include

//http://devl.ands.org.au/ben/registry/services/rda/getLatestActivityBySubject/10/
	public function index()
	{
		$this->activityUpdatesBySubject(true);
	}

	public function activityUpdatesBySubject($dryrun = false)
	{
		echo "<h3>Running Activity Tweet Update</h3><i>Date Started: " . date('r') . "</i>" . BR . BR;
		if($dryrun == "true")
		{
			echo "<pre>RUNNING IN DRY-RUN MODE - NO ACTUAL TWEETS WILL BE SENT!!! Remove /true in the URL to run properly!</pre>";
		}
		// Setup our API access
		$this->load->library('twitter');
		$twitter = new Twitter(array('consumerKey'=>$this->consumer_key,'consumerSecret'=>$this->consumer_secret));
		$twitter->setOAuthToken($this->oauth_access_token);
		$twitter->setOAuthTokenSecret($this->oauth_access_secret);

		// Go and get our activity information
		$service_url = registry_url('services/rda/getLatestActivityBySubject/' . $this->num_days_history);
		$data = @json_decode(@file_get_contents($service_url),true);

		if (!isset($data['results']) || count($data['results']) == 0)
		{
			echo "No activity information to be displayed. No updates matched the query at " . $service_url;
			return;
		}
		else
		{
			echo "<h4>Found " . count($data['results']) . " updates for the past " . $this->num_days_history . " days...</h4>";
			// Reverse the sort order so largest update counts come last (i.e. highest on the Twitter feed)

			krsort($data['results']);
			foreach($data['results'] AS $idx => $update)
			{
				try
				{
					// Format our tweet message
					$tweet = sprintf("%d %s added with the subject '%s' #ANZSRC%s",
												$update['num_records'],
												pluralise("collection", $update['num_records']),
												ellipsis($update['value']), $update['notation']);

					echo "Sending Tweet: <i>" . $tweet . "</i>..."; flush();
					if (!$dryrun)
					{
						$twitter->statusesUpdate($tweet);
						echo "sent " . BR; flush();
					}
					sleep(0.5);

					// Pause between big chunks of tweets
					if ($idx % 5 == 0)
					{
						sleep(5);
					}
			}
			catch (TwitterException $e)
			{
			    echo BR . BR . "Unable to send Tweet to Twitter API: " . $e->getMessage() . BR . BR;
			}
			catch (Exception $e)
			{
			    echo BR . BR . "Unknown Exception: " . $e->getMessage() . BR . BR;
			}
			}
		}

	return;
	}



	public function __construct()
	{
		parent::__construct();

		if (!mod_enabled('twitter')) {
			throw new Exception("Twitter module not enabled in your global config.");
		}

		$this->consumer_key = $this->config->item('twitter_consumer_key');
		$this->consumer_secret = $this->config->item('twitter_consumer_secret');
		$this->oauth_access_token = $this->config->item('twitter_oauth_access_token');
		$this->oauth_access_secret = $this->config->item('twitter_oauth_access_secret');
		if (!$this->consumer_key || !$this->oauth_access_token)
		{
			throw new Exception("You must first configure your global config variables for the Twitter API!");
		}

		if (!$this->input->is_cli_request() && !$this->user->hasFunction('REGISTRY_SUPERUSER'))
		{
			throw new Exception("Access denied");
		}
	}
}