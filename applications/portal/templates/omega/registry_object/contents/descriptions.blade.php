<?php
	$order = array('brief', 'full');
	$omit = array('logo');
?>
<div class="swatch-white">
    <div class="panel panel-primary element-no-top element-short-bottom panel-content">

@if($ro->descriptions)
        <div class="panel-heading"> Descriptions </div>
		<div class="panel-body swatch-white">
			@foreach($order as $o)
				@foreach($ro->descriptions as $desc)
					@if($desc['type']==$o && $desc['description']!='')
						<div class="description">
							<small>{{readable($desc['type'])}}</small>
							<span itemprop="description">{{nl2br(html_entity_decode($desc['description']))}}</span>
						</div>
						
					@endif
				@endforeach
			@endforeach
			
			@foreach($ro->descriptions as $desc)
				@if(!in_array($desc['type'], $order) && !in_array($desc['type'], $omit) && $desc['description']!='')
					<div class="description">
						<small>{{readable($desc['type'])}}</small>
						<span itemprop="description">{{nl2br(html_entity_decode($desc['description']))}}</span>
					</div>
					
				@endif
			@endforeach
        </div>
@elseif($ro->rights||$ro->contact||($ro->directaccess && $ro->core['class']=='collection'))
        <div class="panel-heading">
            <a href="">Information</a>
        </div>
        <div class="panel-body swatch-white">
        </div>
@endif

    </div>
</div>