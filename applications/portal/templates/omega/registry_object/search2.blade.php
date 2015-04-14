<!DOCTYPE html>
<html lang="en" ng-app="app">
    @include('includes/header')
    <body ng-controller="searchCtrl">
        @include('includes/top-menu')
        <div id="content">
            <article>
                <section class="section swatch-black section-text-shadow section-inner-shadow" style="overflow:visible">
                   <div class="background-media skrollable skrollable-between" style="background-image: url(http://devl.ands.org.au/minh/assets/templates/omega/images/uploads/home-classic-1.jpg); background-attachment: fixed; background-size: cover; background-position: 50% 60%; background-repeat: no-repeat;" data-start="background-position:" data-70-top-bottom="background-position:">
                   </div>
                   <div class="background-overlay grid-overlay-30 "style="background-color: rgba(0,0,0,0.3);"></div>
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12 element-medium-top element-short-bottom os-animation animated fadeIn">
                                @include('includes/search-bar')
                            </div>
                        </div>
                    </div>
                </section>
                <section class="section swatch-white">
                    <div class="swatch-white scroll-fixed element-shorter-top element-shorter-bottom" ui-scrollfix="+224">
                        @include('includes/search-header')
                    </div>
                </section>
                <section class="section swatch-white" style="z-index:1;background:#e9e9e9">
                    <div class="container-fluid">
                        <div class="row element-short-top">
                            <div class="col-md-3 sidebar animated slideInLeft">
                                Sidebar
                            </div>
                            <div class="col-md-9">
                                Content
                            </div>
                        </div>
                    </div>
                </section>
                @include('includes/advanced_search')
                @include('includes/my-rda')
            </article>
        </div>
        @include('includes/footer')
    </body>
</html>