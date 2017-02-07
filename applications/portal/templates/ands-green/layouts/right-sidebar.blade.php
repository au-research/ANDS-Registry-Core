<!DOCTYPE html>
<html lang="en" ng-app="app">
    @include('includes/header')
    <body>
        @include('includes/top-menu')
        <div id="content" ng-controller="searchCtrl">
        	<article>
        		@include('includes/search-section')
    		    <section class="section swatch-gray" style="z-index:1">
    		    	<div class="container">
                        @yield('header')
    		    		<div class="row">
    		    			<div class="col-md-9">
    		    				@yield('content')
    		    			</div>
    		    			<div class="col-md-3">
    		    				@yield('sidebar')
    		    			</div>
    		    		</div>
    		    	</div>
    		    </section>
        	</article>
            @include('includes/advanced_search')
        </div>
        @include('includes/footer')
    </body>
</html>