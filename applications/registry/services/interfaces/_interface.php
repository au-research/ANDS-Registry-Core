<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

abstract class FormatHandler
{
	abstract protected function display($payload);
    abstract protected function error($message);
	abstract protected function output_mimetype();
}
