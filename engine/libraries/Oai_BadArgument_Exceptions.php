<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * OAI Provider: OAI "Bad Argument" Exception
 *
 * @author Steven McPhillips <steven.mcphillips@gmail.com>
 * @package ands/services/oai
 *
 */
class Oai_BadArgument_Exceptions extends Oai_Exceptions
{
	protected $msg_prefix = "bad argument";
	protected $error_code = "badArgument";
}

?>
