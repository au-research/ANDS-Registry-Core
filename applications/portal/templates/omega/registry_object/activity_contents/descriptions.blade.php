<?php
	$order = array('researchers','fundingAmount','fundingScheme','brief', 'full');
	$omit = array('logo');
    $researchersfound='no';
?>
@if($ro->descriptions)
<div class="swatch-white">
	<div class="panel panel-primary element-no-top element-short-bottom panel-content">
		<div class="panel-heading">
	        <a href="">Descriptions</a>
	    </div>
		<div class="panel-body swatch-white">
            @if($ro->core['type']=='grant')
            <h2>Research Grant</h2>
            @endif
            @if($ro->core['type']=='project')
            <h2>Research Project</h2>
            @endif

			@foreach($order as $o)
				@foreach($ro->descriptions as $desc)
					@if($desc['type']==$o)
                        <?php   $type = readable($desc['type']);
                                if($desc['type']=='researchers'){
                                    $researchersfound='yes';
                                }
                                if($o == 'brief' && $researchersfound=='no'){
                                    ?>
                                   <strong>Researchers </strong>
                        <?php
                                }
                        ?>
                        @include('registry_object/activity_contents/activity-people')
						<p><strong>{{$type}}</strong> {{html_entity_decode($desc['description'])}}

                        </p>
					@endif
				@endforeach
			@endforeach

			
			@foreach($ro->descriptions as $desc)
				@if(!in_array($desc['type'], $order) && !in_array($desc['type'], $omit))
					<small>{{$desc['type']}}</small>
					<p>{{html_entity_decode($desc['description'])}}</p>
				@endif
			@endforeach
		</div>
	</div>
</div>
@endif
