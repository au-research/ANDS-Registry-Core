@if(isset($ro->core['theme_page']) && isset($theme_page) && $theme_page !== false)
<div class="panel swatch-white">
    <div class="panel-heading"></div>
    <div class="panel-body">
        <a href="{{portal_url('theme/'.$ro->core['theme_page'])}}"  style="margin-right:5px;"><img src="{{$theme_page['img_src']}}"/>See more {{$theme_page['title']}} data</a>
    </div>
</div>
@endif
