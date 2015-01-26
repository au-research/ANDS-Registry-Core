<?php
	$order = array('brief', 'full');
?>
@if($ro->descriptions)
<div class="swatch-white">
	<div class="panel panel-primary element-no-top element-short-bottom panel-content">
		<div class="panel-heading">
	        <a href="">Descriptions</a>
	    </div>
		<div class="panel-body swatch-white">
			@foreach($order as $o)
				@foreach($ro->descriptions as $desc)
					@if($desc['type']==$o)
						<small>{{$desc['type']}}</small>
						<p>{{$desc['description']}}</p>
					@endif
				@endforeach
			@endforeach
			
			@foreach($ro->descriptions as $desc)
				@if(!in_array($desc['type'], $order))
					<small>{{$desc['type']}}</small>
					<p>{{$desc['description']}}</p>
				@endif
			@endforeach
		</div>
	</div>
</div>
@endif
