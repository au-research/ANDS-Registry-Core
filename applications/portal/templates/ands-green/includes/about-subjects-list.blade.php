<?php $count=0; ?>
<div class="portfolio-container element-medium-top element-medium-bottom">
    <div class="portfolio masonry isotope" data-padding="10" data-col-xs="2" data-col-sm="2" data-col-md="2" data-col-lg="2" data-layout="fitRows">
        @foreach($highlevel as $item)
        <?php $count++;
        if($count<5){ ?>
        <div class="masonry-item portfolio-item isotope-item" data-menu-order="1" data-title="{{$item['display']}}">
            <div class="figure portfolio-os-animation element-no-top element-no-bottom image-filter-sepia fade-in image-filter-onhover animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s">
                <div class="element-no-top element-no-bottom image-filter-sepia image-filter-onhover">
                   <a href="{{base_url('search').'#!'.$item['query']}}" class="figure-image magnific-vimeo" data-links="" target="_self">
                       <img src="{{asset_url('images/subjects/'.$item['img'], 'core')}}" alt="" style="width:160px">
                    </a>
                </div>
            </div>
        </div>
        <?php } ?>
        @endforeach
    </div>
</div>