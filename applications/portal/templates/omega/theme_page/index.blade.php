@extends('layouts/single')
@section('content')
<article>
	<section class="section swatch-white element-normal-bottom">
	   <div class="container">
			<div class="row">
				<div class="col-md-12">
				   <header class="text-center element-normal-top element-medium-bottom not-condensed os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
					   <h1 class="bigger hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;">Themed Collections</h1>
					</header>
				</div>
				<div class="col-md-12">
					<div class="portfolio-container element-medium-top element-medium-bottom">
                        <div class="portfolio masonry isotope" data-padding="15" data-col-xs="2" data-col-sm="2" data-col-md="3" data-col-lg="3" data-layout="fitRows">
                        	@foreach($theme_pages['items'] as $item)
                        	<?php 
                        		if(!$item['img_src']){
                        			$img_src = 'http://placehold.it/350x150&text=No+Cover+Image';
                        		} else {
                        			$img_src = $item['img_src'];
                        		}
                        	?>
                            <div class="masonry-item portfolio-item isotope-item" data-menu-order="1" data-title="Coffee and Biscuits">
                                <div class="figure portfolio-os-animation element-no-top element-no-bottom text-center figcaption-middle normalwidth image-filter-sepia fade-in image-filter-onhover animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s">
            	                   	
                                    <div class="figure-caption text-center">
                                        <h3 class="figure-caption-title bordered bordered-small bordered-link">
                                        	<a href="{{portal_url('theme/'.$item['slug'])}}" target="_self">{{$item['title']}}</a>
                                        </h3>
                                    </div>
                                    <div class="element-no-top element-no-bottom text-center figcaption-middle normalwidth image-filter-sepia fade-in image-filter-onhover" data-os-animation="fadeIn">
            	                       <a href="{{portal_url('theme/'.$item['slug'])}}" class="figure-image magnific-vimeo" data-links="" target="_self">
            	                           <img src="{{$img_src}}" alt="" class="normalwidth">
            	                    	</a>
            	                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
				</div>
		   </div>
		   <div class="row ">
		   </div>
		</div>
	</section>
</article>
@stop