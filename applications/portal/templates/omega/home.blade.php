@extends('layouts/single')
@section('content')
<article>
    <script type="text/javascript">
    var oxyThemeData = {
        navbarHeight: 90,
        navbarScrolled: 70,
        navbarScrolledPoint: 200,
        navbarScrolledSwatches:
        {
            up: 'swatch-black',
            down: 'swatch-white'
        },
        scrollFinishedMessage: 'No more items to load.',
        hoverMenu:
        {
            hoverActive: false,
            hoverDelay: 1,
            hoverFadeDelay: 200
        }
    };
    </script>
    <section class="section swatch-black section-text-shadow section-inner-shadow" style="overflow:visible;z-index:9">
        <div class="background-media skrollable skrollable-between" style="background-image: url(http://devl.ands.org.au/minh/assets/templates/omega/images/uploads/home-classic-1.jpg); background-attachment: fixed; background-size: cover; background-position: 50% 60%; background-repeat: no-repeat;" data-start="background-position:" data-70-top-bottom="background-position:">
       </div>
       <div class="background-overlay grid-overlay-30 " style="background-color: rgba(0,0,0,0.3);"></div>
        <div class="container">
            <div class="row">
                <div class="col-md-12 element-normal-top element-normal-bottom">
                    <header class="text-center element-normal-bottom os-animation condensed animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                        <h1 class="bigger hairline bordered bordered-normal">Find data for research.</h1>
                        <p class="normal">
                            Find, access, and re-use data for research - from over one hundred Australian research organisations, government agencies, and cultural institutions
                        </p>
                    </header>
                    @include('includes/search-bar')
                </div>
            </div>
        </div>
    </section>
    <section class="section swatch-white">
       <div class="container">
           <div class="row">
               <div class="col-md-12">
                   <header class="text-center element-normal-top element-medium-bottom not-condensed os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                       <h1 class="bigger hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;"> Browse By Subjects </h1>
                   </header>
               </div>
           </div>
           <div class="row ">
           </div>
       </div>
    </section>
    <section id="two" class="section swatch-white">
       <div class="container">
           <div id="services" class="row">
               <div class="col-md-12">
                   <header class="text-center element-tall-top element-no-bottom os-animation condensed animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                       <h1 class="bigger hairline bordered bordered-normal"> Explore </h1>
                   </header>
                   <div class="row " data-os-animation="" data-os-animation-delay="">
                       <div class="col-md-3 ">
                           <div class="element-medium-top element-medium-bottom text-center os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay=".0s" style="-webkit-animation: 0s;">
                               <div class="box box-round box-medium box-simple">
                                   <div class="box-dummy"></div>
                                   <div class="box-inner grid-overlay-0" style="background-color:#353b42;">
                                       <!-- <img class="" src="assets/images/icons/indicator-512w.png" alt="a clock"> -->
                                   </div>
                               </div>
                               <!-- <h3 class="normal bold bordered bordered-small "> Collections </h3> -->
                               <p class="">Discover data and services by theme in our <strong>collection guides</strong></p>
                           </div>
                       </div>
                       <div class="col-md-3 ">
                           <div class="element-medium-top element-medium-bottom text-center os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay=".3s" style="-webkit-animation: 0.3s;">
                               <div class="box box-round box-medium box-simple">
                                   <div class="box-dummy"></div>
                                   <div class="box-inner grid-overlay-0" style="background-color:#353b42;">
                                       <!-- <img class="" src="assets/images/icons/gift-512w.png" alt="a gift"> -->
                                   </div>
                               </div>
                               <!-- <h3 class="normal bold bordered bordered-small "> Services </h3> -->
                               <p class="">Access research data-related services ands tools</p>
                           </div>
                       </div>
                       <div class="col-md-3 ">
                           <div class="element-medium-top element-medium-bottom text-center os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay=".6s" style="-webkit-animation: 0.6s;">
                               <div class="box box-round box-medium box-simple">
                                   <div class="box-dummy"></div>
                                   <div class="box-inner grid-overlay-0" style="background-color:#353b42;">
                                       <!-- <img class="" src="assets/images/icons/lifebuoy-512w.png" alt="a support"> -->
                                   </div>
                               </div>
                               <!-- <h3 class="normal bold bordered bordered-small "> Activities </h3> -->
                               <p class="">Find open-data that is accessible and free to use</p>
                           </div>
                       </div>
                       <div class="col-md-3 ">
                           <div class="element-medium-top element-medium-bottom text-center os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay=".9s" style="-webkit-animation: 0.9s;">
                               <div class="box box-round box-medium box-simple">
                                   <div class="box-dummy"></div>
                                   <div class="box-inner grid-overlay-0" style="background-color:#353b42;">
                                       <!-- <img class="" src="assets/images/icons/map-512w.png" alt="a clock"> -->
                                   </div>
                               </div>
                               <!-- <h3 class="normal bold bordered bordered-small "> Ultra flexible </h3> -->
                               <p class="">Search for research grant information in our sister portal, Research Grant Australia</p>
                           </div>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   </section>
</article>
@stop