<!DOCTYPE html>
<html lang="en" ng-app="app" ng-controller="mainController">
    @include('includes/header')
    <body ng-controller="searchController">
        @include('includes/top-menu')
        <div id="content">
            @include('includes/hidden-metadata')
        	<article ng-controller="viewController">
        		@include('includes/search-section')
    		    <section class="section swatch-gray" style="z-index:1">
    		    	<div class="container">
    		    		<div class="row element-short-top">
                            <div class="col-md-9 view-content" style="padding-right:0">
                                <div class="panel panel-body swatch-white">
                                    @if($ro->logo)
                                    <img src="{{$ro->logo[0]}}" alt="logo" class="header-logo animated fadeInDown">
                                    @endif
                                    [[message]]
                                    <h1 class="hairline bordered-normal">{{$ro->core['title']}}</h1>
                                    <small>{{$ro->core['group']}}</small><br/>
                                    @include('registry_object/contents/related-parties')
                                </div>

                                <div>

                                    <div class="pull-left swatch-white" style="position:relative;z-index:9999;margin:35px 15px 15px 15px;width:350px;">
                                        @include('registry_object/contents/wrap-getdatalicence')
                                        <div class="center-block" style="text-align:center">
                                            <i class="fa fa-lg fa-facebook fa-border"></i>
                                            <i class="fa fa-lg fa-twitter fa-border"></i>
                                            <i class="fa fa-lg fa-google fa-border"></i>
                                        </div>
                                    </div>
                                    @yield('content')
                                </div>

                            </div>

                            <div class="col-md-3">
                                <div class="panel panel-primary swatch-white" ng-if="ro.stat" ng-cloak>
                                    <div class="panel-body">
                                        <div class="center-block" style="text-align:center">
                                            <span class="label label-default">[[ro.stat.viewed]] Viewed</span>
                                            <span class="label label-default">[[ro.stat.cited]] Cited</span>
                                            <span class="label label-default">[[ro.stat.accessed]] Accessed</span>
                                        </div>
                                    </div>
                                </div>
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