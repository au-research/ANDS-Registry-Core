<!DOCTYPE html>
<html lang="en" ng-app="app" ng-controller="mainController">
    @include('includes/header')
    <body ng-controller="searchController">
        @include('includes/top-menu')
        <div id="content">
            @yield('content')
        </div>
        @include('includes/footer')
    </body>
</html>