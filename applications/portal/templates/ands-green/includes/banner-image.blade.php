<?php 
	$banner = isset($banner) ? $banner : asset_url('images/collection_banner.jpg', 'core');
?>
<div id="banner-image" class="background-media" style="background-image: url({{$banner}}); background-size: cover; background-position: 50% 53%; background-repeat: no-repeat;  box-shadow: none;">
</div>
<div class="background-overlay grid-overlay-30" style="background-color: rgba(0,0,0,0.1); box-shadow: none;"></div>
