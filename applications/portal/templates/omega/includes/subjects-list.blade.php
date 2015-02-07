<div class="portfolio-container element-medium-top element-medium-bottom">
    <div class="portfolio masonry isotope" data-padding="0" data-col-xs="2" data-col-sm="2" data-col-md="4" data-col-lg="4" data-layout="fitRows">
    	@foreach($subjects as $item)
        <div class="masonry-item portfolio-item isotope-item" data-menu-order="1" data-title="{{$item['prefLabel']}}">
            <div class="figure portfolio-os-animation element-no-top element-no-bottom text-center figcaption-middle normalwidth image-filter-sepia fade-in image-filter-onhover animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s">
               	
                <div class="figure-caption text-center">
                    <h3 class="figure-caption-title bordered bordered-small bordered-link">
                    	<a href="{{base_url('search')}}#!/subject_vocab_uri={{rawurlencode($item['uri'])}}" target="_self">{{$item['prefLabel']}}</a>
                    </h3>
                </div>
                <div class="element-no-top element-no-bottom text-center figcaption-middle normalwidth image-filter-sepia fade-in image-filter-onhover" data-os-animation="fadeIn">
                   <a href="{{base_url('search')}}#!/subject_vocab_uri={{rawurlencode($item['uri'])}}" class="figure-image magnific-vimeo" data-links="" target="_self">
                       <img src="http://placehold.it/350x150&text=No+Cover+Image" alt="" class="normalwidth">
                	</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>