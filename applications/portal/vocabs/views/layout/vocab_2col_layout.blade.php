<!DOCTYPE html>
<html lang="en">
@include('includes/header')
<body>
@include('includes/top-menu')
<?php
$publisher = array();
if(isset($vocab['related_entity'])){
    foreach($vocab['related_entity'] as $related){
        if($related['type']=='publisher'){
            $publisher[]=$related;
        }
    }
}

$url = base_url().$vocab['slug'];
$title = $vocab['title'] ;

?>
<div id="content" >
    <article ng-controller="viewController">
        <section class="section swatch-gray" style="z-index:1">
            <div class="container">
                <div class="row element-short-top">
                    <div class="col-md-9 view-content" style="padding-right:0">

                        <div class="panel panel-primary swatch-white panel-content">
                            <div class="panel-body">
                                <h1 class="hairline bordered-normal" style="line-height:1.1em"><span itemprop="name">{{ $vocab['title'] }} </span></h1>

                                @foreach($publisher as $apub)
                                   <a class="re_preview" re_id="{{$apub['id']}}"> {{$apub['title']}} </a><small>({{readable($apub['relationship'])}})</small>
                                @endforeach
                                <div class="pull-right">{{$vocab['creation_date']}}
                                    <a href="http://www.facebook.com/sharer.php?u={{$url}}"><i class="fa fa-facebook" style="padding-right:4px"></i></a>
                                    <a href="https://twitter.com/share?url={{$url}}&text={{$title}}&hashtags=andsdata"><i class="fa fa-twitter" style="padding-right:4px"></i></a>
                                    <a href="https://plus.google.com/share?url={{$url}}"><i class="fa fa-google" style="padding-right:4px"></i></a></div>
                            </div>
                        </div>
                        @include('wrap-getvocabaccess')
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