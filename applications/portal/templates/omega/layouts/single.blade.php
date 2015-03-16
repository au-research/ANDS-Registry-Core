<!DOCTYPE html>
<html lang="en" ng-app="app">
    @include('includes/header')
    <body>
        @include('includes/top-menu')
        <div id="content" ng-controller="searchCtrl">
            @yield('content')
        </div>
        @include('includes/advanced_search')
        @include('includes/my-rda')
        @include('includes/footer')
    </body>
</html>