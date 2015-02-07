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
                                    @if($ro->core['alt_title'])
                                        <small>Also known as: 
                                            {{implode(', ',$ro->core['alt_title'])}}
                                        </small><br/>
                                    @endif
                                    <small>{{$ro->core['group']}}</small><br/>

                                    <div class="clear"></div>
                                    
                                    <div class="container-fluid">
                                        <div class="row">
                                            <div class="col-md-7">
                                                @include('registry_object/contents/related-parties')
                                            </div>
                                            <div class="col-md-5">
                                                <div class="btn-group btn-group-justified" ng-if="ro.stat">
                                                    <a href="#" class="btn btn-sm btn-link btn-noaction"><small>Viewed: </small>[[ro.stat.viewed]]</a>
                                                    <a href="#" class="btn btn-sm btn-link btn-noaction"><small>Cited: </small>[[ro.stat.cited]]</a>
                                                    <a href="#" class="btn btn-sm btn-link btn-noaction"><small>Accessed: </small>[[ro.stat.accessed]]</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                                @yield('sidebar')
                            </div>

    		    		</div>
    		    	</div>
    		    </section>
        	</article>
        </div>
        @include('registry_object/contents/citation-modal')
        @include('includes/footer')
    </body>
</html>