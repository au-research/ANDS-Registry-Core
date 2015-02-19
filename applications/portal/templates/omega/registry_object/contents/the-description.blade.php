<?php
	$order = array('brief', 'full');
	$omit = array('logo');

	$the_description = false;
	if ($ro->descriptions) {
		foreach($ro->descriptions as $desc) {
			if($desc['type']=='brief' && !$the_description) {
				$the_description = $desc['description'];
			}
		}
		foreach($ro->descriptions as $desc) {
			if($desc['type']=='full' && !$the_description) {
				$the_description = $desc['description'];
			}
		}
		if(!$the_description) $the_description = $ro->descriptions[0]['description'];
	}
?>
@if($the_description)
	{{$the_description}}
@endif
