<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * OAI Provider: OAI "cannotDisseminateFormat" Exception
 *
 * @author Steven McPhillips <steven.mcphillips@gmail.com>
 * @package ands/services/oai
 *
 */
class Oai_BadFormat_Exceptions extends Oai_Exceptions
{
	protected $msg_prefix = "Unknown format";
	protected $error_code = "cannotDisseminateFormat";
}

?>
