<!DOCTYPE html>
<html lang="en" ng-app="app">
    @include('includes/header')
    <body ng-controller="searchCtrl">
        @include('includes/top-menu')
        <div id="content">
        	<article>
        		@include('includes/search-section')
                @yield('header')
    		    <section class="section swatch-gray" style="z-index:1">
    		    	<div class="container">
    		    		<div class="row element-short-top">
    		    			<div class="col-md-9">
    		    				@yield('content')
    		    			</div>
    		    			<div class="col-md-3 sidebar">
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