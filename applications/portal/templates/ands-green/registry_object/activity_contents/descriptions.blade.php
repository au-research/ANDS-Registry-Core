<?php
	$order = array('researchers','brief', 'fundingAmount','fundingScheme','full');
	$omit = array('logo');
    $researchersfound='no';
    $prev_type = '';
?>
@if($ro->descriptions)
<div class="swatch-white">
	<div class="panel panel-primary element-no-top element-short-bottom panel-content">
		<!-- <div class="panel-heading"> Descriptions </div> -->
		<div class="panel-body swatch-white">
            @if($ro->core['type']=='grant')
                <h2 style="display:inline;">Research Grant</h2>

                @if(is_array($ro->identifiers))
                    @foreach($ro->identifiers as $col)
                        @if($col['type']=='purl' && isset($col['identifier']['href']) && $col['identifier']['href'] != '')
                        <?php echo '<span style="display:inline;padding-left:10px;">[Cite as <a href="' . $col['identifier']['href'] . '" title="'.$col['identifier']['href'].'">' . $col['value'] . '</a>]</span><br/><br/>';?>
                        @endif
                    @endforeach
                @endif

            @endif
            @if($ro->core['type']=='project')
            <h2>Research Project</h2>
            @endif

            @include('registry_object/activity_contents/activity-people')
			@foreach($order as $o)
				@foreach($ro->descriptions as $desc)
					@if($desc['type']==$o)
                        <?php
                            $type = readable($desc['type']);
                        ?>
                        <?php if($prev_type!=''&& $prev_type!=$type){ echo "</p>";}
                        if ($prev_type!=$type) { ?>
						<p><strong>{{$type}}</strong> <?php } else { echo ", ";} ?>{{html_entity_decode($desc['description'])}}

                        <?php
                            $prev_type=$type;
                        ?>
					@endif
				@endforeach
			@endforeach


			@foreach($ro->descriptions as $desc)
				@if(!in_array($desc['type'], $order) && !in_array($desc['type'], $omit))
                    <p><strong>{{readable($desc['type'])}} </strong>
					{{html_entity_decode($desc['description'])}}</p>
				@endif
			@endforeach
			@if(isset($ro->core['landingPage']) && strpos($ro->core['landingPage'], "dataportal.arc.gov.au")  !== false)
					<p><a href="{{ $ro->core['landingPage'] }}">View this grant in the ARC Data Portal</a></p>
			@endif
		</div>
	</div>
</div>
@endif

