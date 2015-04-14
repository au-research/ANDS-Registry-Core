<?php uasort($contributors, 'alphasort_byattr_title'); ?>
<div class="flexslider" id="slider">
	<ul class="slides">
		@foreach($contributors as $group)
			@if($group['logo'])
			<li style="text-align:center">
				<div class="figure portfolio-os-animation element-no-top element-no-bottom text-center figcaption-middle normalwidth image-filter-sepia fade-in image-filter-onhover animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s">
					<div class="element-no-top element-no-bottom text-center figcaption-middle normalwidth image-filter-sepia image-filter-onhover">
						<a href="{{portal_url('contributors/'.$group['slug'])}}" class="figure-image magnific-vimeo" data-links="" target="_self">
							@if($group['logo'])
							<div class="logo-place">
								<img src="{{$group['logo']}}" alt="" class="" align="middle"/>
							</div>
							
							@else
							<div class="logo-placement"><p>{{$group['title']}}</p></div>
							@endif
						</a>
					</div>
					<div class="figure-caption text-center">
						<h3 class="figure-caption-title bordered bordered-small bordered-link">
							<a href="{{portal_url('contributors/'.$group['slug'])}}" target="_self">{{$group['title']}}</a>
						</h3>
					</div>
				</div>
			</li>
			@endif
		@endforeach
	</ul>
</div>