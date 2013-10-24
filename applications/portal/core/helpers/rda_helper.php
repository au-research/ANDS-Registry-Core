<?php
function rda_exception_handler( $e ) {

	if ($e instanceof ErrorException)
	{
		notifySiteAdmin("-1", $e->getMessage(), "...", "");
	}

    $_ci =& get_instance(); // CI super object to access load etc.
    
	$data['js_lib'] = array('core');
	$data['scripts'] = array();
	$data['title'] = 'An error occurred!';

    echo $_ci->load->view( 'rda_header' , $data , true); 
    
   	echo $_ci->load->view( 'rda_exception' , array("message" => $e->getMessage()) , true );
   
    echo $_ci->load->view( 'rda_footer' , $data , true);
}
set_exception_handler('rda_exception_handler');