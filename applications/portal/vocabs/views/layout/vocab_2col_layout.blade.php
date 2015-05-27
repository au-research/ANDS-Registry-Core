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
                                <p>{{$vocab['creation_date']}}</p>
                                @foreach($publisher as $apub)
                                    {{$apub['title']}} <small>({{readable($apub['relationship'])}})</small>
                                @endforeach
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