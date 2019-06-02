<!DOCTYPE html>
<html lang="en">
    @include('includes/header')
    <body  ng-app="app">
        @include('includes/top-menu')
        <div id="content" ng-controller="searchCtrl">
            @yield('content')
            @include('includes/advanced_search')
            @include('includes/my-rda')
        </div>
        @include('includes/footer')
    </body>
</html>