<?php (defined('BASEPATH')) OR exit('No direct script access allowed');


class MY_Exceptions extends CI_Exceptions {

    function __construct()
    {
        parent::__construct();
    }

	// --------------------------------------------------------------------

	/**
	 * General Error Page
	 *
	 * This function takes an error message as input
	 * (either as a string or an array) and displays
	 * it using the specified template.
	 *
	 * @access	private
	 * @param	string	the heading
	 * @param	string	the message
	 * @param	string	the template name
	 * @param 	int		the status code
	 * @return	string
	 */
	function show_error($heading, $message, $template = 'error_general', $status_code = 500)
	{
		if (defined('IS_CLI_SCRIPT'))
		{
			// CLI error
			echo NL.NL.$heading . NL;
			echo implode($message, NL) . NL . NL;
			return;
		}


		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();
		}
		ob_start();
		include(APPPATH.'errors/'.$template.'.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

}