<!DOCTYPE html>
<html lang="en" ng-app="app">
    @include('includes/header')
    <body>
        @include('includes/top-menu')
        <div id="content" ng-controller="searchCtrl">
            <article>
                @include('includes/search-section')
                @yield('header')
                <section class="section swatch-gray" style="z-index:1">
                    <div ng-view></div>
                </section>
            </article>
            @include('includes/advanced_search')
            @include('includes/my-rda')
        </div>
        @include('includes/footer')
    </body>
</html>