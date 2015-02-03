<!DOCTYPE html>
<html lang="en" ng-app="app" ng-controller="mainController">
    @include('includes/header')
    <body>
        @include('includes/top-menu')
        <div id="content">
            <article ng-controller="mapController">
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
                <section class="section swatch-white" style="z-index:1;background:#e9e9e9">
                    <div class="container-fluid">
                        <main class="cd-main-content">
                                <div class="cd-tab-filter-wrapper">
                                    <div class="cd-tab-filter">
                                        <ul class="cd-filters">
                                            <li class="placeholder"> 
                                                <a data-type="all" href="#0">All</a> <!-- selected option on mobile -->
                                            </li> 
                                            <li class="filter"><a class="selected" href="#0" data-type="all">All</a></li>
                                            <li class="filter" data-filter=".color-1"><a href="#0" data-type="color-1">Color 1</a></li>
                                            <li class="filter" data-filter=".color-2"><a href="#0" data-type="color-2">Color 2</a></li>
                                        </ul> <!-- cd-filters -->
                                    </div> <!-- cd-tab-filter -->
                                </div> <!-- cd-tab-filter-wrapper -->

                                <section class="cd-gallery">
                                    <ul>
                                        <li class="mix color-1 check1 radio2 option3"><img src="img/img-1.jpg" alt="Image 1"></li>
                                        <li class="mix color-2 check2 radio2 option2"><img src="img/img-2.jpg" alt="Image 2"></li>
                                        <li class="mix color-1 check3 radio3 option1"><img src="img/img-3.jpg" alt="Image 3"></li>
                                        <li class="mix color-1 check3 radio2 option4"><img src="img/img-4.jpg" alt="Image 4"></li>
                                        <li class="mix color-1 check1 radio3 option2"><img src="img/img-5.jpg" alt="Image 5"></li>
                                        <li class="mix color-2 check2 radio3 option3"><img src="img/img-6.jpg" alt="Image 6"></li>
                                        <li class="mix color-2 check2 radio2 option1"><img src="img/img-7.jpg" alt="Image 7"></li>
                                        <li class="mix color-1 check1 radio3 option4"><img src="img/img-8.jpg" alt="Image 8"></li>
                                        <li class="mix color-2 check1 radio2 option3"><img src="img/img-9.jpg" alt="Image 9"></li>
                                        <li class="mix color-1 check3 radio2 option4"><img src="img/img-10.jpg" alt="Image 10"></li>
                                        <li class="mix color-1 check3 radio3 option2"><img src="img/img-11.jpg" alt="Image 11"></li>
                                        <li class="mix color-2 check1 radio3 option1"><img src="img/img-12.jpg" alt="Image 12"></li>
                                        <li class="gap"></li>
                                        <li class="gap"></li>
                                        <li class="gap"></li>
                                    </ul>
                                    <div class="cd-fail-message">No results found</div>
                                </section> <!-- cd-gallery -->

                                <div class="cd-filter">
                                    <form>
                                        <div class="cd-filter-block">
                                            <h4>Search</h4>
                                            
                                            <div class="cd-filter-content">
                                                <input type="search" placeholder="Try color-1...">
                                            </div> <!-- cd-filter-content -->
                                        </div> <!-- cd-filter-block -->

                                        <div class="cd-filter-block">
                                            <h4>Check boxes</h4>

                                            <ul class="cd-filter-content cd-filters list">
                                                <li>
                                                    <input class="filter" data-filter=".check1" type="checkbox" id="checkbox1">
                                                    <label class="checkbox-label" for="checkbox1">Option 1</label>
                                                </li>

                                                <li>
                                                    <input class="filter" data-filter=".check2" type="checkbox" id="checkbox2">
                                                    <label class="checkbox-label" for="checkbox2">Option 2</label>
                                                </li>

                                                <li>
                                                    <input class="filter" data-filter=".check3" type="checkbox" id="checkbox3">
                                                    <label class="checkbox-label" for="checkbox3">Option 3</label>
                                                </li>
                                            </ul> <!-- cd-filter-content -->
                                        </div> <!-- cd-filter-block -->

                                        <div class="cd-filter-block">
                                            <h4>Select</h4>
                                            
                                            <div class="cd-filter-content">
                                                <div class="cd-select cd-filters">
                                                    <select class="filter" name="selectThis" id="selectThis">
                                                        <option value="">Choose an option</option>
                                                        <option value=".option1">Option 1</option>
                                                        <option value=".option2">Option 2</option>
                                                        <option value=".option3">Option 3</option>
                                                        <option value=".option4">Option 4</option>
                                                    </select>
                                                </div> <!-- cd-select -->
                                            </div> <!-- cd-filter-content -->
                                        </div> <!-- cd-filter-block -->

                                        <div class="cd-filter-block">
                                            <h4>Radio buttons</h4>

                                            <ul class="cd-filter-content cd-filters list">
                                                <li>
                                                    <input class="filter" data-filter="" type="radio" name="radioButton" id="radio1" checked>
                                                    <label class="radio-label" for="radio1">All</label>
                                                </li>

                                                <li>
                                                    <input class="filter" data-filter=".radio2" type="radio" name="radioButton" id="radio2">
                                                    <label class="radio-label" for="radio2">Choice 2</label>
                                                </li>

                                                <li>
                                                    <input class="filter" data-filter=".radio3" type="radio" name="radioButton" id="radio3">
                                                    <label class="radio-label" for="radio3">Choice 3</label>
                                                </li>
                                            </ul> <!-- cd-filter-content -->
                                        </div> <!-- cd-filter-block -->
                                    </form>

                                    <a href="#0" class="cd-close">Close</a>
                                </div> <!-- cd-filter -->

                                <a href="#0" class="cd-filter-trigger">Filters</a>
                            </main> <!-- cd-main-content -->
                    </div>
                </section>
                @include('includes/advanced_search')
            </article>
        </div>
        @include('includes/footer')
    </body>
</html>