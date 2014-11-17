<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * ANDS Datepicker widget: Javascript Datetime picker with timezone support.
 * Parses and outputs dates in ISO8601 (Zulu time) format.
 *
 * @author Steven McPhillips <steven.mcphillips@gmail.com>
 *
 */
class Datepicker_tz_widget extends MX_Controller {


	function demo()
	{
		$data['title'] = 'ANDS Datepicker widget';
		$data['scripts'] = array('ands_datetimepicker_loader');
		$data['js_lib'] = array('core', 'ands_datetimepicker_widget', 'prettyprint');

		$this->load->view("demo", $data);
	}


	public function index() {
		header('Content-type: text/plain');
		//I suppose this could dump some usage documentation or something...
		echo "Nothing to see here";
	}

}
?>
