<div class="portfolio-container element-medium-top element-medium-bottom">
    <div class="portfolio masonry isotope" data-padding="10" data-col-xs="1" data-col-sm="1" data-col-md="4" data-col-lg="4" data-layout="fitRows">
        @foreach($contributors as $group)
        <div class="masonry-item portfolio-item isotope-item" data-menu-order="1" data-title="{{$group['title']}}">
            <div class="figure portfolio-os-animation element-no-top element-no-bottom text-center figcaption-middle normalwidth image-filter-sepia fade-in image-filter-onhover animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s">
                <div class="element-no-top element-no-bottom text-center figcaption-middle normalwidth image-filter-sepia image-filter-onhover">
                   <a href="{{portal_url('contributors/'.$group['slug'])}}" class="figure-image magnific-vimeo" data-links="" target="_self">
                        @if($group['logo'])
                        <img src="{{$group['logo']}}" alt="" class="normalwidth logo-homepage">
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
        </div>
        @endforeach
    </div>
</div>