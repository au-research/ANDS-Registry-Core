<!DOCTYPE html>
<html lang="en">
@include('includes/header')
<body>
@include('includes/top-menu')
<div id="content" >
    <article ng-controller="viewController">
        <section class="section swatch-gray" style="z-index:1">
            <div class="container">
                <div class="row element-short-top">
                    <div class="col-md-9 view-content" style="padding-right:0">
                        <div class="panel panel-primary swatch-gray panel-content">
                            <div class="panel-body swatch-grey">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-xs-12 col-md-12 swatch-gray">
                                            <h1 class="hairline bordered-normal" style="line-height:1.1em"><span itemprop="name">{{ $vocab->title }} </span></h1>
                                            <small>lets put the peeps in here</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="clear"></div>
                            </div>

                            <div>
                                <div class="pull-left swatch-gray" style="position:relative;z-index:9999;margin:35px 15px 15px 15px;width:350px;">
                                    @include('wrap-getvocabaccess')
                                </div>
                                @yield('content')
                                <div class="clear"></div>
                            </div>
                        </div>
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