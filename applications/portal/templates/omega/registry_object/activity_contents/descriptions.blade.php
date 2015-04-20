<?php
	$order = array('researchers','fundingAmount','fundingScheme','brief', 'full');
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
            <h2>Research Grant</h2>
            @endif
            @if($ro->core['type']=='project')
            <h2>Research Project</h2>
            @endif

			@foreach($order as $o)
				@foreach($ro->descriptions as $desc)
					@if($desc['type']==$o)
                        <?php   $type = readable($desc['type']);
                                if($desc['type']=='Researchers'){
                                    $researchersfound='yes';
                                }
                                if(($o == 'fundingAmount'||$o == 'fundingScheme'||$o == 'brief'||$o='full') && $researchersfound=='no'){


                                    if($ro->relationships){
                                        if(isset($ro->relationships['party_one']))
                                            $researchersfound='yes';
                                    }
                                    if($ro->relatedInfo){
                                        foreach($ro->relatedInfo as $relatedInfo){
                                            if($relatedInfo['type']=='party'){
                                                $researchersfound='yes';
                                            }
                                        }
                                    }
                                    if($researchersfound=='yes'){
                                    ?>
                                  <p><strong>Researchers </strong> @include('registry_object/activity_contents/activity-people')</p>
                                <?php
                                    }
                                }
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
		</div>
	</div>
</div>
@endif
