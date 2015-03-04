<?php
	$order = array('brief', 'full');
	$omit = array('logo');
?>
<div class="swatch-white">
    <div class="panel panel-primary element-no-top element-short-bottom panel-content">

@if($ro->descriptions)
        <div class="panel-heading">
            <a href="">Descriptions</a>
        </div>
		<div class="panel-body swatch-white">
			@foreach($order as $o)
				@foreach($ro->descriptions as $desc)
					@if($desc['type']==$o && $desc['description']!='')
						<small>{{readable($desc['type'])}}</small>
						<span itemprop="description">{{html_entity_decode($desc['description'])}}</span>
					@endif
				@endforeach
			@endforeach
			
			@foreach($ro->descriptions as $desc)
				@if(!in_array($desc['type'], $order) && !in_array($desc['type'], $omit) && $desc['description']!='')
					<small>{{readable($desc['type'])}}</small>
					<span itemprop="description">{{html_entity_decode($desc['description'])}}</span>
				@endif
			@endforeach
        </div>
@else
        <div class="panel-heading">
            <a href="">Information</a>
        </div>
        <div class="panel-body swatch-white">
        </div>
@endif

    </div>
</div>