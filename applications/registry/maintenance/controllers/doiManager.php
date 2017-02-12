<?php


class doiManager extends MX_Controller
{
	private $input; // pointer to the shell input
	private $start_time; // time script run (microtime float)
	private $exec_time; // time execution started
	private $_CI; 	// an internal reference to the CodeIgniter Engine 
	private $source;
	private $testDOIprefix;
	
	function index()
	{
		$this->exec_time = microtime(true);
		set_error_handler(array(&$this, 'cli_error_handler'));
		echo "Connected to DOI database..." . NL;
		$this->source->where("created_when <= NOW() - INTERVAL '4 WEEK'");
		$this->source->like('doi_id',$this->testDOIprefix); //10.422501 10.5072
		$query = $this->source->delete('doi_objects');
		$dois = $this->source->affected_rows();		
		echo $dois." deleted".NL;
	}

	

	function __construct()
    {
            parent::__construct();
            
            $this->input = fopen ("php://stdin","r");
            $this->start_time = microtime(true);
			$this->_CI =& get_instance();
            $this->source = $this->load->database('dois', true);
			$this->testDOIprefix = "10.5072/";
            define('IS_CLI_SCRIPT', true);

    }

    function __destruct() {
       print "Execution finished! Took " . sprintf ("%.3f", (float) (microtime(true) - $this->exec_time)) . "s" . NL;
   	}


   	private function getInput()
	{
		if (is_resource(($this->input)))
		{
			return trim(fgets($this->input));
		}
	}


	function cli_error_handler($number, $message, $file, $line, $vars)
	{
		echo NL.NL.str_repeat("=", 15);
     	echo NL .NL . "An error ($number) occurred on line $line in the file: $file:" . NL;
        echo $message . NL . NL;
        echo str_repeat("=", 15) . NL . NL;

       //"<pre>" . print_r($vars, 1) . "</pre>";

        // Make sure that you decide how to respond to errors (on the user's side)
        // Either echo an error message, or kill the entire project. Up to you...
        // The code below ensures that we only "die" if the error was more than
        // just a NOTICE.
        if ( ($number !== E_NOTICE) && ($number < 2048) ) {
          //  die("Exiting on error...");
        }

	}
}