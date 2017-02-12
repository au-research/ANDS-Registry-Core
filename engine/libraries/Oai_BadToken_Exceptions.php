<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * OAI Provider: OAI "Bad resumption token" Exception
 *
 * @author Steven McPhillips <steven.mcphillips@gmail.com>
 * @package ands/services/oai
 *
 */
class Oai_BadToken_Exceptions extends Oai_Exceptions
{
	protected $msg_prefix = "The resumption token is invalid or expired";
	protected $error_code = "badResumptionToken";
}

?>
