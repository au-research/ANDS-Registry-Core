<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * OAI Provider: OAI "Bad verb" Exception
 *
 * @author Steven McPhillips <steven.mcphillips@gmail.com>
 * @package ands/services/oai
 *
 */
class Oai_BadVerb_Exceptions extends Oai_Exceptions
{
	protected $msg_prefix = "Bad verb";
	protected $error_code = "badVerb";
}

?>
