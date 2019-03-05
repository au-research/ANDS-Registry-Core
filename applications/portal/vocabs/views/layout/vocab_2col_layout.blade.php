<!DOCTYPE html>
<html lang="en">
@include('includes/header')
<body ng-app="app" ng-controller="searchCtrl">
@include('includes/top-menu')
<?php
$publisher = array();
if(isset($vocab['related_entity'])){
    foreach($vocab['related_entity'] as $related){
        if($related['type']=='party'){
            if(is_array($related['relationship'])){
                foreach($related['relationship'] as $relationship){
                    if($relationship=='publishedBy'){
                        $publisher[]=$related;
                    }
                }
            }elseif($related['relationship']=='publishedBy'){
                $publisher[]=$related;
            }
        }
    }
}

$url = base_url().$vocab['slug'];
$title = rawurlencode(substr($vocab['title'], 0, 200)) ;

?>
<div id="content">
    <article>
        <section class="section swatch-gray" style="z-index:1">
            <div class="container">
                <div class="row element-short-top">
                    <div class="col-md-9 view-content" style="padding-right:0">

                        <div class="panel panel-primary swatch-white panel-content">
                            <div class="panel-body">
                                @if($vocab['status']=='deprecated')
                                <span class="label label-default pull-right" style="margin-left:5px">{{ $vocab['status'] }}</span>
                                @endif
                                <a id="widget-link" class="pull-right" href="javascript:showWidget()" tip="<b>Widgetable</b><br/>This vocabulary can be readily used for resource description or discovery in your system using our vocabulary widget.<br/><a id='widget-link2' href='javascript:showWidget()'>Learn more</a>">
                                    <span class="label label-default pull-right"><img class="widget-icon" height="16" width="16"src="{{asset_url('images/cogwheels_white.png', 'core')}}"/> widgetable</span>
                                </a>
                                <h1 class="hairline bordered-normal break" style="line-height:1.1em"><span itemprop="name" ng-non-bindable>{{ htmlspecialchars($vocab['title']) }} </span></h1>
                                @if (isset($vocab['acronym']))
                                <small>Acronym: {{ $vocab['acronym'] }}</small><br>
                                @endif
                                @if(isset($publisher))
                                @foreach($publisher as $apub)
                                <small>Publisher </small>  <a class="re_preview" related='{{json_encode($apub)}}' v_id="{{ $vocab['id'] }}" sub_type="publisher"> {{$apub['title']}} </a>
                                @endforeach
                                @endif
                                <div class="pull-right">
                                    {{ isset($vocab['creation_date']) ? "Created: ".display_release_date($vocab['creation_date']) : ''}}
                                    <a href="http://www.facebook.com/sharer.php?u={{$url}}"><i class="fa fa-facebook" style="padding-right:4px"></i></a>
                                    <a href="https://twitter.com/share?url={{$url}}&text={{$title}}&hashtags=andsdata"><i class="fa fa-twitter" style="padding-right:4px"></i></a>
                                </div>
                            </div>
                        </div>
                        @yield('content')
                    </div>

                    <div class="col-md-3">
                        @yield('sidebar')
                    </div>
                </div>
            </div>
        </section>
    </article>

</div>
@include('includes/footer')
</body>
</html>