<!DOCTYPE html>
<html lang="en" ng-app="app">
    @include('includes/header')
    <body ng-controller="searchCtrl">
        @include('includes/top-menu')
        <div id="content">
        	<article>
        		@include('includes/search-section')
                <section class="section swatch-white" style="overflow:visible">
                    <div class="swatch-white scroll-fixed element-shorter-top element-shorter-bottom" ui-scrollfix="+224" style="overflow:visible">
                        @include('includes/search-header')
                    </div>
                </section>
    		    <section class="section swatch-white" style="z-index:1;background:#e9e9e9">
    		    	<div class="container-fluid">
    		    		<div class="row element-short-top">
    		    			<div class="col-md-3 sidebar animated slideInLeft">
    		    				@yield('sidebar')
    		    			</div>
                            <div class="col-md-9">
                                @yield('content')
                            </div>
    		    		</div>
    		    	</div>
    		    </section>
                @include('includes/advanced_search')
                @include('includes/my-rda')
        	</article>
        </div>
        @include('includes/footer')
    </body>
</html>