@extends('layout/vocab_layout')
@section('content')

<section class="section swatch-dark-blue search-section section-text-shadow section-inner-shadow">

    <div id="banner-image" class="background-media" style="">
    </div>

    <div class="background-overlay grid-overlay-30 " style="background-color: rgba(0,0,0,0.4);"></div>
        <div class="container element-tall-top element-normal-bottom">
            <div class="row">

                <div class="col-md-6">
                    <form action="" ng-submit="search()" style="padding-top:55px">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search for a vocabulary or a concept" ng-model="filters.q" ng-debounce="500">
                            <span class="input-group-btn">
                                <button class="btn btn-primary" type="button" ng-click="search()"><i class="fa fa-search"></i> Search</button>
                            </span>
                        </div>
                    </form>
                    <div class="pull-right">
                    <a href="{{ portal_url('search') }}">Browse all vocabularies</a>
                    </div>
               </div>

                <div class="col-md-6">
                    <header class="animated fadeIn">
                        <h1 class="big">Research Vocabularies Australia</h1>
                        <p class="big hairline">
                            helps you find, access, and reuse vocabularies for research.
                        </p>
                    </header>
                </div>

            </div>
        </div>
    </div>

</section>
<section class="section swatch-white">
   <div class="container">
       <div id="services" class="row">
           <div class="col-md-12">
               <header class="text-center element-short-top element-no-bottom os-animation condensed animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                   <h1 class="big"> Get Involved </h1>
               </header>
               <div class="row " data-os-animation="" data-os-animation-delay="">
                   <div class="col-md-3 ">
                    <a href="{{ portal_url('vocabs/page/contribute') }}">
                     <div class="element-medium-top element-medium-bottom text-center os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay=".0s" style="-webkit-animation: 0s;">
                         <div class="box-square box-medium box-simple">
                             <div class="box-dummy"></div>
                             <div class="box-inner grid-overlay-0">
                              <i class="fa fa-cloud-upload icon-portal"></i>
                             </div>
                         </div>
                         <h3 class="normal bold bordered bordered-small ">Publish a vocabulary</h3>
                         <p class="">Upload, describe and publish your vocabularies to Research Vocabularies Australia</p>
                     </div>
                    </a>
                   </div>
                   <div class="col-md-3 ">
                    <a href="{{ portal_url('vocabs/page/use') }}">
                     <div class="element-medium-top element-medium-bottom text-center os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay=".3s" style="-webkit-animation: 0.3s;">
                         <div class="box-square box-medium box-simple">
                             <div class="box-dummy"></div>
                             <div class="box-inner grid-overlay-0">
                                 <i class="fa fa-language icon-portal"></i>
                             </div>
                         </div>
                         <h3 class="normal bold bordered bordered-small ">Use a vocabulary</h3>
                         <p class="">Understand how you can utilise Research Vocabulary Australia vocabularies</p>
                     </div>
                    </a>
                   </div>
                   <div class="col-md-3 ">
                    <a href="{{ portal_url('vocabs/page/widget_explorer') }}">
                     <div class="element-medium-top element-medium-bottom text-center os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay=".6s" style="-webkit-animation: 0.6s;">
                         <div class="box-square box-medium box-simple">
                             <div class="box-dummy"></div>
                             <div class="box-inner grid-overlay-0">
                              <i class="fa fa-cogs icon-portal"></i>
                             </div>
                         </div>
                         <h3 class="normal bold bordered bordered-small ">Explore widgetable vocabularies</h3>
                         <p class="">Discover vocabularies that can be readily used in your system using our vocabulary widget</p>
                     </div>
                    </a>
                   </div>
                   <div class="col-md-3 ">
                    <a href="{{ portal_url('vocabs/page/feedback') }}">
                     <div class="element-medium-top element-medium-bottom text-center os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay=".9s" style="-webkit-animation: 0.9s;">
                         <div class="box-square box-medium box-simple">
                             <div class="box-dummy"></div>
                             <div class="box-inner grid-overlay-0">
                              <i class="fa fa-comments-o icon-portal"></i>
                             </div>
                         </div>
                         <h3 class="normal bold bordered bordered-small ">Provide feedback</h3>
                         <p class="">Help Research Vocabularies Australia to grow into a comprehensive vocabulary portal</p>
                     </div>
                    </a>
                   </div>
               </div>
           </div>
       </div>
   </div>
</section>

@stop
