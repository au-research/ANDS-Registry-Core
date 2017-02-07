<!DOCTYPE html>
<html lang="en" ng-app="app">
    @include('includes/header')
    <body>
        @include('includes/top-menu')
        <div id="content" ng-controller="searchCtrl">
        	@include('includes/search-section')
            @yield('content')
            @include('includes/advanced_search')
            @include('includes/my-rda')
        </div>
        @include('includes/footer')
    </body>
</html>