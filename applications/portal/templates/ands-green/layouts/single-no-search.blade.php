<!DOCTYPE html>
<html lang="en" ng-app="app">
    @include('includes/header')
    <body>
        @include('includes/top-menu')
        <div id="content">
            @yield('content')
        </div>
        @include('includes/footer')
    </body>
</html>