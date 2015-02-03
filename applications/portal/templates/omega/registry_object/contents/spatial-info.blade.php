@if($ro->spatial)
<div class="swatch-white">
    <div class="panel panel-primary element-no-top element-short-bottom panel-content">
        <div class="panel-heading">
            <a href="">Related</a>
        </div>
        <div class="panel-body swatch-white">
<h3>Spatial Coverage And Location</h3>
<div id="spatial_coverage_map"></div>
<?php
foreach($ro->spatial as $a_coverage){
   echo '<p class="coverage hide">'.$a_coverage['polygon'].'</p>';

 }
foreach($ro->spatial as $a_coverage){
echo '<p class="spatial_coverage_center hide">'.$a_coverage['center'].'</p>';

}?>
        </div>
    </div>
    </div>
@endif