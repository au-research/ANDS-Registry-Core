<?php
	$chosen = false;
	foreach($ro->descriptions as $desc) {
		if ($desc['type']=='brief') $chosen = $desc;
	}

?>

@if($chosen)
	<small>{{$chosen['description']}}</small>
@endif