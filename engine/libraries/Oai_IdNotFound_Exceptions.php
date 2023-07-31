<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * OAI Provider: OAI "ID does not exist" Exception
 *
 * @author Steven McPhillips <steven.mcphillips@gmail.com>
 * @package ands/services/oai
 *
 */
class Oai_IdNotFound_Exceptions extends Oai_Exceptions
{
  protected $msg_prefix = "invalid identifier";
  protected $error_code = "idDoesNotExist";
}

?>
