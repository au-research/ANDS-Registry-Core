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
            @if($a_coverage['polygon'])
			<p class="coverage hide" itemprop="spatial">{{$a_coverage['polygon']}}</p>
            @else
            <p class="coverage hide">{{$a_coverage['polygon']}}</p>
            @endif
			<p class="spatial_coverage_center hide">{{$a_coverage['center']}}</p>
			@endforeach
        </div>
    </div>
    </div>
@endif