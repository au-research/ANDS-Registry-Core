<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * OAI Provider: OAI "No Records Match" Exception
 *
 * @author Steven McPhillips <steven.mcphillips@gmail.com>
 * @package ands/services/oai
 *
 */
class Oai_NoRecordsMatch_Exceptions extends Oai_Exceptions
{
	protected $msg_prefix = "No matching records found";
	protected $error_code = "noRecordsMatch";
}

?>
