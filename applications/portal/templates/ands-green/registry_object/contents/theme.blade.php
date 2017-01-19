
@if(isset($ro->core['theme_page']) && isset($theme_page))
    <?php
    $displayTheme = false;
    for($i=0;$i<count($theme_page);$i++) {
        if($theme_page[$i] !== false)   $displayTheme = true;
    }

    if($displayTheme ) { ?>
<div class="panel swatch-white">
    <div class="panel-heading"></div>
        <?php
        for($i=0;$i<count($theme_page);$i++) {
            if($theme_page[$i] !== false) { ?>
            <div class="panel-body">
             <a href="{{portal_url('theme/'.$ro->core['theme_page'][$i])}}"  style="margin-right:5px;"><img src="{{$theme_page[$i]['img_src']}}"/>See more {{$theme_page[$i]['title']}} data</a>
            </div>
        <?php }
         } ?>
</div>
    <?php } ?>
@endif
