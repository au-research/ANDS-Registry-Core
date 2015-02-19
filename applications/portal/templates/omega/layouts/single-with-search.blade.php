<!DOCTYPE html>
<html lang="en" ng-app="app">
    @include('includes/header')
    <body ng-controller="searchCtrl">
        @include('includes/top-menu')
        <div id="content">
        	@include('includes/search-section')
            @yield('content')
        </div>
        @include('includes/footer')
    </body>
</html>