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
    <section class="section swatch-black search-section section-text-shadow section-inner-shadow element-shorter-bottom" style="overflow:visible;z-index:9">
        @include('includes/banner-image')
        <div class="container">
            <div class="row">
                <div class="col-md-12 element-higher-top element-short-bottom">
                    <header class="text-center element-normal-bottom os-animation condensed animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                        <h1 class="bigger hairline bordered bordered-normal element-shorter-bottom">Find data for research</h1>
                        <p class="normal col-md-8 col-md-offset-2">
                            Find, access, and re-use data for research - from over one hundred Australian research organisations, government agencies, and cultural institutions
                        </p>
                    </header>
                </div>
                <div class="col-md-12 element-higher-bottom">
                    @include('includes/search-bar')
                </div>
            </div>
        </div>
    </section>
    <section class="section swatch-white element-short-bottom">
       <div class="container">
           <div class="row">
               <div class="col-md-12">
                   <header class="text-center element-short-top element-no-bottom not-condensed os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                       <h1 class="bigger hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;"> Browse By Subjects </h1>
                   </header>
               </div>
           </div>
           <div class="row ">
            @include('includes/subjects-list')
           </div>
       </div>
    </section>
    <section class="section swatch-gray element-short-bottom">
       <div class="container">
           <div id="services" class="row">
               <div class="col-md-12">
                   <header class="text-center element-short-top element-no-bottom os-animation condensed animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                       <h1 class="bigger hairline bordered bordered-normal"> Explore </h1>
                   </header>
                   <div class="row " data-os-animation="" data-os-animation-delay="">
                       <div class="col-md-3 ">
                        <a href="{{portal_url('themes')}}">
                         <div class="element-medium-top element-medium-bottom text-center os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay=".0s" style="-webkit-animation: 0s;">
                             <div class="box box-round box-medium box-simple">
                                 <div class="box-dummy"></div>
                                 <div class="box-inner grid-overlay-0" style="background-color:#353b42;">
                                  <i class="fa fa-folder-open icon-portal"></i>
                                 </div>
                             </div>
                             <h3 class="normal bold bordered bordered-small ">Themed Collections</h3>
                             <p class="">Explore selected resources by theme</p>
                         </div>
                        </a>
                       </div>
                       <div class="col-md-3 ">
                        <a href="{{portal_url('theme/services')}}">
                         <div class="element-medium-top element-medium-bottom text-center os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay=".3s" style="-webkit-animation: 0.3s;">
                             <div class="box box-round box-medium box-simple">
                                 <div class="box-dummy"></div>
                                 <div class="box-inner grid-overlay-0" style="background-color:#353b42;">
                                     <i class="fa fa-wrench icon-portal"></i>
                                 </div>
                             </div>
                             <h3 class="normal bold bordered bordered-small ">Services and Tools</h3>
                             <p class="">Access data-related services and tools</p>
                         </div>
                        </a>
                       </div>
                       <div class="col-md-3 ">
                        <a href="{{portal_url('theme/open-data')}}">
                         <div class="element-medium-top element-medium-bottom text-center os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay=".6s" style="-webkit-animation: 0.6s;">
                             <div class="box box-round box-medium box-simple">
                                 <div class="box-dummy"></div>
                                 <div class="box-inner grid-overlay-0" style="background-color:#353b42;">
                                  <i class="fa fa-unlock icon-portal"></i>
                                 </div>
                             </div>
                             <h3 class="normal bold bordered bordered-small ">Open Data</h3>
                             <p class="">Find open data that is reusable</p>
                         </div>
                        </a>
                       </div>
                       <div class="col-md-3 ">
                        <a href="{{portal_url('grants')}}">
                         <div class="element-medium-top element-medium-bottom text-center os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay=".9s" style="-webkit-animation: 0.9s;">
                             <div class="box box-round box-medium box-simple">
                                 <div class="box-dummy"></div>
                                 <div class="box-inner grid-overlay-0" style="background-color:#353b42;">
                                  <i class="fa fa-flask icon-portal"></i>
                                 </div>
                             </div>
                             <h3 class="normal bold bordered bordered-small ">Grants and Projects</h3>
                             <p class="">Search for research grants and projects</p>
                         </div>
                        </a>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   </section>
    <section class="section swatch-white element-short-bottom">
       <div class="container">
           <div class="row">
               <div class="col-md-12">
                   <header class="text-center element-short-top element-medium-bottom not-condensed os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                       <h1 class="bigger hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;"> Who Contributes to Research Data Australia </h1>
                       <h3><a href="{{portal_url('contributors')}}">View all</a></h3>
                   </header>
               </div>
           </div>
           <div class="row ">
            @include('includes/contributors-list')
           </div>
       </div>
    </section>
    
</article>
@stop