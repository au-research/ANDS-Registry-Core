<!DOCTYPE html>
<html lang="en" ng-app="app" id="ng-app">
    @include('includes/header')
    <body>
        @include('includes/top-menu')
        <div id="content" ng-controller="searchCtrl">
        	<article>
                <section class="section swatch-white" style="overflow:visible">
                    <div class="swatch-white scroll-fixed element-shorter-top element-shorter-bottom" ui-scrollfix="+224" style="overflow:visible">
                        @include('includes/search-header')
                    </div>
                </section>
    		    <section class="section swatch-white" style="z-index:1;background:#e9e9e9">
    		    	<div class="container-fluid">
    		    		<div class="row element-short-top">
    		    			<div class="col-xs-12 col-md-4 col-lg-4 sidebar">
    		    				@yield('sidebar')
    		    			</div>
                            <div class="col-xs-12 col-md-8 col-lg-8 content">
                                @yield('content')
                            </div>
    		    		</div>
    		    	</div>
    		    </section>
        	</article>
            @include('includes/my-rda')
        </div>
        @include('includes/footer')
    </body>
</html>