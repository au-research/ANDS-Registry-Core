<?php
function get_vocab_config($item) {
	$vocab_configs = get_config_item('vocab_config');
	if (isset($vocab_configs[$item])) {
		return $vocab_configs[$item];
	} else return false;
}

function vocab_uploaded_url($name) {
	$path = get_vocab_config('upload_path').$name;
	return $path;
}

function vocab_log($message, $type='info') {
	$CI =& get_instance();

	//check if the logging class is loaded, if not, load it
	if (!class_exists('Logging')) {
		$CI->load->library('logging');
	} else {
		$CI->load->library('logging');
	}
	
	try {
		$CI->logging->add_logger(
			array(
				'type' => 'file',
				// 'type' => 'database',
				// 'database_group' => 'vocabs',
				// 'table' => 'log',
				'level' => 'INFO',
				'name' => 'vocab',
				'format' => '[date:{date}] {message}',
				'file_path' => 'vocab'
			)
		);
		$logger = $CI->logging->get_logger('vocab');
		switch($type) {
			case 'info' : $logger->info($message);break;
			case 'debug' : $logger->debug($message);break;
			case 'warning' : $logger->warning($message);break;
			case 'error' : $logger->error($message);break;
			case 'critical' : $logger->critical($message);break;
		}
	} catch (Exception $e) {
		// throw new Exception($e);
	} catch (LoggingException $e) {
		// throw new Exception($e);
	}
}

function vocab_log_terms($terms=array(), $type='info') {
	$CI =& get_instance();
	$msg = '';
	
	if (!isset($terms['ip'])) $terms['ip'] = $CI->input->ip_address();
	if (!isset($terms['user_agent'])) $terms['user_agent'] = $CI->input->user_agent();

	//check if user is logged in, then record the current user
	if ($CI->user->isLoggedIn()) {
		$terms['username'] = $CI->user->name();
		$terms['userid'] = $CI->user->localIdentifier();
	}

	foreach($terms as $key=>$term) {
		if(!is_array($key) && !is_array($term)) {
			$msg.='['.$key.':'.$term.']';
		}
	}

	vocab_log($msg,$type);
}