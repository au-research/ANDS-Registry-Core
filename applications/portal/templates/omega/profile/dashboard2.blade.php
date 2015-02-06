<!DOCTYPE html>
<html lang="en" ng-app="app" ng-controller="mainController">
    @include('includes/header')
    <body ng-controller="searchController">
        @include('includes/top-menu')
        <div id="content">
            <article>
                @include('includes/search-section')
                @yield('header')
                <section class="section swatch-gray" style="z-index:1">
                    <div ng-view></div>
                    
                </section>
            </article>
        </div>
        @include('includes/footer')
    </body>
</html>