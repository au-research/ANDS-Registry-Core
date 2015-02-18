<div class="portfolio-container element-medium-top element-medium-bottom">
    <div class="portfolio masonry isotope" data-padding="10" data-col-xs="1" data-col-sm="1" data-col-md="4" data-col-lg="4" data-layout="fitRows">
        @foreach($highlevel as $item)
        <div class="masonry-item portfolio-item isotope-item" data-menu-order="1" data-title="{{$item['display']}}">
            <div class="figure portfolio-os-animation element-no-top element-no-bottom text-center figcaption-middle normalwidth image-filter-sepia fade-in image-filter-onhover animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s">
                <div class="element-no-top element-no-bottom text-center figcaption-middle normalwidth image-filter-sepia image-filter-onhover">
                   <a href="{{base_url('search').'#!'.$item['query']}}" class="figure-image magnific-vimeo" data-links="" target="_self">
                       <img src="{{asset_url('images/subjects/'.$item['img'], 'core')}}" alt="" class="normalwidth" style="height:190px">
                    </a>
                </div>
                <div class="figure-caption text-center">
                    <h3 class="figure-caption-title bordered bordered-small bordered-link">
                        <a href="{{base_url('search').'#!'.$item['query']}}" target="_self">{{$item['display']}}</a>
                    </h3>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>