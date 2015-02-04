@if($ro->spatial)
<div class="swatch-white">
    <div class="panel panel-primary element-no-top element-short-bottom panel-content">
        <div class="panel-heading">
            <a href="">Spatial Coverage And Location</a>
        </div>
        <div class="panel-body swatch-white">
			<div id="spatial_coverage_map" class="map-canvas angular-google-map-container"></div>
			<!-- @include('registry_object/facet/map') -->
			@foreach($ro->spatial as $a_coverage)
			<p class="coverage hide">{{$a_coverage['polygon']}}</p>
			<p class="spatial_coverage_center hide">{{$a_coverage['center']}}</p>
			@endforeach
        </div>
    </div>
    </div>
@endif