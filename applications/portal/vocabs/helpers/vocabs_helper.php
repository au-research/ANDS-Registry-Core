<?php
function get_vocab_config($item) {
	$vocab_configs = get_config_item('vocab_config');
	if (isset($vocab_configs[$item])) {
		return $vocab_configs[$item];
	} else return false;
}