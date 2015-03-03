@extends('layouts/single')
@section('content')
<article>
	<section class="section swatch-white element-normal-bottom">
       <div class="container-fluid">
           <div class="row">

               <div class="col-md-12 text-center not-condensed os-animation animated fadeInUp" style="-webkit-animation: 0s;padding-top:50px;padding-bottom:50px;">
                       <h1 class="bigger hairline bordered os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;">Find out more about us</h1>
               </div>
               <div class="col-md-12  swatch-gray">
               <div class="col-md-2 not-condensed os-animation animated fadeInUp swatch-gray"> </div>
               <div class="col-md-8 not-condensed os-animation animated fadeInUp swatch-gray" style="-webkit-animation: 0s;padding-top:30px;padding-bottom:50px">
                   <div class="text-center" style="-webkit-animation: 0s;">
                        <p class="strong hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;color:#000000">
                            <strong>Why make data discoverable?</strong></p>
                   </div>
                   <div style="padding-left:50px;padding-right:50px">
                       <p>Research is using and producing larger and more complex data than ever before.
                        This input and output is a valuable sset for research, public policy, industry and the general public.
                        Data is more valuable when it is easily discoverable, better described, more connected, more integrated and organised,
                        more accessible, more easily used for new purposes.
                        It allows new questions to be investigated, larger issues to be examined, and data landscapes to be explored.
                        </p>
                   </div>
               </div>
               <div class="col-md-2 swatch-gray">  </div>
               </div>
               <div  class="col-md-12">
                   <div class="col-md-2 not-condensed os-animation animated fadeInUp"> </div>
               <div class="col-md-8 not-condensed os-animation animated fadeInUp " style="-webkit-animation: 0s;padding-top:20px;padding-bottom:20px;text-decoration-color: #ffffff">

                   <div class="text-left" style="-webkit-animation: 0s;padding-left:50px;padding-right:50px;">
                       <div style="float:right;padding-left:20px">
                       <p><img src="{{asset_url('images/ands-logo-small.png', 'core')}}" style="height:80px;"/></p>
                       <p style="padding:10px"><img src="{{asset_url('images/NCRIS_PROVIDER_mono.jpg','core')}}" style="height:80px"/></p>
                       </div>
                       <p class="strong hairline bordered os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;color:#000000"><strong>A service built on sharing</strong></p>

                        <p>
                           The Australian national data Service (ANDS) has partnered with research institutions
                           and data producing agencies to provide a comprehensive window into the Australian
                           research data commons through this service. Research Data Australia ANDS is funded by the Australian
                           Government through the National Collaborative Research Infrastructure Strategy (NCRIS) and is leading the
                           creation of a cohesive national collection of research resources and a richer data environment.
                        </p>
                   </div>
               </div>
                   <div class="col-md-2 not-condensed os-animation animated fadeInUp"> </div>
               </div>
               <div class="col-md-12  swatch-gray">
                   <div class="col-md-2 not-condensed os-animation animated fadeInUp swatch-gray"> </div>
                <div class="col-md-8  swatch-gray">
               <div class="col-md-8  swatch-gray" style="float:left" >
                       @include('includes/about-subjects-list')
               </div>
               <div class="col-md-4  swatch-gray" style="float:right;padding-left:20px;padding-top:100px" >
                   <p class="strong hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;color:#000000"><strong>What will I find here?</strong></p>
                   <p>Research Data Australia can be used by everyone from policy-makers to teachers,but it is targeted to researchers
                       as users and creators of research data. <br />You can search here for data descriptions that are contributed by Australian
                       universities, research organisations and public sector agencies. Research Data Australia covers all subject areas of
                       interest to researchers - from the sciences to economics to humanities. Much of the data you can discover here is
                       immediately accessible online via our partners and free to use (subject to any licence conditions</p>
               </div>
                </div>
                   </div>
               </div>

               <div class="col-md-12">
                   <div class="col-md-2"></div>
                       <div class="col-md-8 not-condensed os-animation animated fadeInUp" style="-webkit-animation: 0s;padding-top:50px;padding-bottom:50px">
                   <div class="text-left" style="-webkit-animation: 0s;padding-left:50px;padding-right:50px;">
                    <ul>
                       <p class="strong hairline bordered os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;color:#000000"><strong>Why use Research data Australia?</strong></p>
                    </ul>
                       <p>



                       <ul  class="fa-ul features-list element-no-top element-no-bottom" >
                           <li class="element-no-top element-short-bottom os-animation animated fadeInLeft" ><div class="features-list-icon box-animate" style="background-color:#e9e9e9;">

                              <i class=" fa fa-lightbulb-o fa-4x" ></i> </div>
                               Research Data Australia brings together a broad range of data resources across almost all of the publicly-funded research and government organisations across Australia.

                           </li>
                           <li class="element-no-top element-short-bottom os-animation animated fadeInLeft" ><div class="features-list-icon box-animate" style="background-color:#e9e9e9;">

                                   <i class=" fa fa-lightbulb-o fa-4x" ></i> </div>
                              You can perform a single search in Research Data Australia to retrieve resources across a range of subjects and providers.
                               For example a search for the town of "Wagga Wagga" will return results for data from a number of fields of research:
                               Earth Sciences, Agriculture and Veterinary Sciences, Environmental Sciences, Built Environment and Design, Biological Sciences,
                               Technology, Studies in Human Society, Studies in Creative Arts, History and Archaeology, and Philosophy and Religious Studies.
                           </li>
                           <li class="element-no-top element-short-bottom os-animation animated fadeInLeft" ><div class="features-list-icon box-animate" style="background-color:#e9e9e9;">

                                   <i class=" fa fa-lightbulb-o fa-4x" ></i> </div>
                               Unlike using a generic search engine or discipline portal, searching in Research Data Australia provides an increased likelihood of related
                               discovery, as well as the ability to conduct cross-disciplinary research and address broader challenges over a larger pool of resources.
                           </li>
                           </ul>

                       </p>
                   </div>
                           </div></div>


        </div>
           <div class="col-md-12  swatch-gray">
               <div class="col-md-2 not-condensed os-animation animated fadeInUp swatch-gray"> </div>
               <div class="col-md-8 not-condensed os-animation animated fadeInUp swatch-gray" style="-webkit-animation: 0s;padding-top:30px;padding-bottom:50px">
                   <div class="text-center" style="-webkit-animation: 0s;">
                       <p class="strong hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;color:#000000"><strong>Get Started</strong></p>
                   </div>
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
                                   <p class="">Discover data in themed collections</p>
                               </div>
                           </a>
                       </div>
                       <div class="col-md-3 ">
                           <a href="{{portal_url('search')}}#!/class=service">
                               <div class="element-medium-top element-medium-bottom text-center os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay=".3s" style="-webkit-animation: 0.3s;">
                                   <div class="box box-round box-medium box-simple">
                                       <div class="box-dummy"></div>
                                       <div class="box-inner grid-overlay-0" style="background-color:#353b42;">
                                           <i class="fa fa-wrench icon-portal"></i>
                                       </div>
                                   </div>
                                   <p class="">Access data-related services and tools</p>
                               </div>
                           </a>
                       </div>
                       <div class="col-md-3 ">
                           <a href="{{portal_url('search')}}#!/access_rights=open">
                               <div class="element-medium-top element-medium-bottom text-center os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay=".6s" style="-webkit-animation: 0.6s;">
                                   <div class="box box-round box-medium box-simple">
                                       <div class="box-dummy"></div>
                                       <div class="box-inner grid-overlay-0" style="background-color:#353b42;">
                                           <i class="fa fa-unlock icon-portal"></i>
                                       </div>
                                   </div>
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
                                   <!-- <h3 class="normal bold bordered bordered-small "> Ultra flexible </h3> -->
                                   <p class="">Search for research grants and projects</p>
                               </div>
                           </a>
                       </div>
                   </div>
               </div>
           </div>
       </div>
           </div>

       </div>
    </section>
</article>
@stop