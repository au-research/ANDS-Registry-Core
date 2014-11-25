<?php
	$order = array('brief', 'full');
?>
<div id="descriptions">
@if($ro->descriptions)

	@foreach($order as $o)
		@foreach($ro->descriptions as $desc)
			@if($desc['type']==$o)
				<h3>{{$desc['type']}}</h3>
				<p>{{$desc['description']}}</p>
			@endif
		@endforeach
	@endforeach
	
	@foreach($ro->descriptions as $desc)
		@if(!in_array($desc['type'], $order))
			<h3>{{$desc['type']}}</h3>
			<p>{{$desc['description']}}</p>
		@endif
	@endforeach

@endif
</div>