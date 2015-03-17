<?php
	$order = array('brief', 'full');
	$omit = array('logo');

	$the_description = false;
	if (isset($ro->descriptions[0])) {
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
        $the_description = trim(strip_tags(html_entity_decode(html_entity_decode(html_entity_decode($the_description)))));
        if (strlen($the_description) > 500)
            $the_description = substr($the_description, 0, 500) . '...';
	}

?>
@if($the_description)
	{{$the_description}}
@endif
