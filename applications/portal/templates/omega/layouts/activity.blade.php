<!DOCTYPE html>
<html lang="en" ng-app="app" ng-controller="mainController">
    @include('includes/header')
    <body ng-controller="searchController">
        @include('includes/top-menu')
        <div id="content">
        	<article>
        		@include('includes/search-section')
                <section class="section swatch-gray">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12">
                                  <div class="panel panel-primary element-no-top element-short-bottom panel-content">
                                        <div class="panel-body {{$ro->core['type']}}" style="background-color: #30CB8B">
                                    @if($ro->logo)
                                    <img src="{{$ro->logo[0]}}" alt="logo" class="header-logo animated fadeInDown">
                                    @endif
                                    <h1 class="hairline bordered-normal">{{$ro->core['title']}} </h1>
                                    <small>{{$ro->core['group']}}</small>

                                @include('registry_object/contents/related-parties')
                            </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
    		    <section class="section swatch-gray" style="z-index:1">
    		    	<div class="container">
    		    		<div class="row element-short-top">
    		    			<div class="col-md-8">
    		    				@yield('content')
    		    			</div>
    		    			<div class="col-md-4 sidebar animated slideInRight">
                                @include('registry_object/activity_contents/activity-metafunc')
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