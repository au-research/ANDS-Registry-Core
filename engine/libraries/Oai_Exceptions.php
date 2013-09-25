<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * OAI Provider: Generic Exception class. Subclasses need only supply a message
 * prefix via `$msg_prefix`, and the corresponding OAI error code via `$error_code`.
 *
 * @author Steven McPhillips <steven.mcphillips@gmail.com>
 * @package ands/services/oai
 *
 */
abstract class Oai_Exceptions extends Exception
{
	protected $msg_prefix;
	protected $error_code;

	/**
	 * Get the OAI error code
	 * @return the OAI error code for the implementing Exception
	 */
	public function oai_code()
	{
		return $this->error_code;
	}

	/**
	 * Standard Exception constructor, except our `$msg_prefix` gets prepended
	 * to the incoming `$message`.
	 */
	public function __construct($message = null, 
				    $code = 0, 
				    Exception $previous = null) 
	{
		if (empty($message) or is_null($message))
		{
			$message = $this->msg_prefix;
		}
		else
		{
			$message = sprintf("%s: %s", $this->msg_prefix, $message);
		}
		parent::__construct($message, $code, $previous);
	}

}

?>
