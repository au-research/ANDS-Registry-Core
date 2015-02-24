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
                    <div ng-view></div>
                </section>
            </article>
        </div>
        @include('includes/advanced_search')
        @include('includes/footer')
    </body>
</html>