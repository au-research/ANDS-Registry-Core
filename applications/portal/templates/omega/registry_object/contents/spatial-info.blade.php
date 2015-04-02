
@if($ro->spatial)
<?php
$needmap = false;
foreach($ro->spatial as $a_coverage){
    if(isset($a_coverage['polygon'])){
        $needmap = true;
    }
}
?>


<div class="swatch-white">
    <div class="panel panel-primary element-no-top element-short-bottom panel-content">
        <!-- <div class="panel-heading"> Spatial Coverage And Location </div> -->
        <div class="panel-body swatch-white">
            @if($needmap)
			<div id="spatial_coverage_map" class="map-canvas angular-google-map-container"></div>
            @else
            <h4>Spatial Coverage And Location</h4>
            @endif
			<!-- @include('registry_object/facet/map') -->

			@foreach($ro->spatial as $a_coverage)
            @if(isset($a_coverage['polygon']))
			    <p class="coverage hide" itemprop="spatial">{{$a_coverage['polygon']}}</p>
            @endif
            @if(isset($a_coverage['center']))
			    <p class="spatial_coverage_center hide">{{$a_coverage['center']}}</p>
            @endif
			@endforeach

            @foreach($ro->spatial as $a_coverage)
                @if(isset($a_coverage['type']))
                    <p>{{$a_coverage['type']}}: {{$a_coverage['value']}}</p>
                @endif
            @endforeach
        </div>
    </div>
    </div>
@endif