<!DOCTYPE html>
<html lang="en" ng-app="app" ng-controller="mainController">
    @include('includes/header')
    <body ng-controller="searchController">
        @include('includes/top-menu')
        <div id="content">
        	<article>
        		@include('includes/search-section')
                <section class="section swatch-white section-text-shadow section-innder-shadow element-short-top element-short-bottom">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12">
                                <header>
                                    @if($ro->logo)
                                    <img src="{{$ro->logo[0]}}" alt="logo" class="header-logo animated fadeInDown">
                                    @endif
                                    <h1 class="hairline bordered-normal">{{$ro->core['title']}}</h1>
                                    <small>{{$ro->core['group']}}</small>
                                </header>
                                @include('registry_object/contents/related-parties')
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
                                @include('registry_object/contents/standard-getdatalicence')
                                @include('registry_object/contents/standard-metafunc')
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