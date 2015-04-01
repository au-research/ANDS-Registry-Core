<?php
	$order = array('brief', 'full', 'note','significanceStatement','researchAreas','researchDataProfile','researchSupport','lineage','deliverymethod','local');
	$omit = array('logo');
    $currentHeading = '';
?>
<div class="swatch-white">
    <div class="panel panel-primary element-no-top element-short-bottom panel-content">

@if($ro->descriptions)
        <!-- <div class="panel-heading"> Descriptions </div> -->
		<div class="panel-body swatch-white">
			@foreach($order as $o)

				@foreach($ro->descriptions as $desc)
                    <?php
                        $showHeading = false;
                    ?>
					@if($desc['type']==$o && $desc['description']!='')
						<div class="description">
                            <?php
                            if($currentHeading != $desc['type']){
                                $showHeading = true;
                                $currentHeading = $desc['type'];
                            }
                            ?>
                            @if($showHeading)
							<small>{{readable($currentHeading)}}</small>
                            @endif
							<span itemprop="description">{{nl2br(html_entity_decode($desc['description']))}}</span>
						</div>
						
					@endif
				@endforeach
			@endforeach
			@foreach($ro->descriptions as $desc)
                <?php
                $showHeading = false;
                ?>
				@if(!in_array($desc['type'], $order) && !in_array($desc['type'], $omit) && $desc['description']!='')
					<div class="description">
                        <?php
                        if($currentHeading != $desc['type']){
                            $showHeading = true;
                            $currentHeading = $desc['type'];
                        }
                        ?>
                        @if($showHeading)
                        <small>{{readable($currentHeading)}}</small>
                        @endif
						<span itemprop="description">{{nl2br(html_entity_decode($desc['description']))}}</span>
					</div>
					
				@endif
			@endforeach
        </div>
@elseif($ro->core['class']=='collection')
        <div class="panel-heading">
            <a href="">Information</a>
        </div>
        <div class="panel-body swatch-white">
        </div>
@endif

    </div>
</div>