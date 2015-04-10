@extends('layouts/single')
@section('content')
<?php
$banner = isset($banner) ? $banner : asset_url('images/collection_banner.jpg', 'core');
?>
<article>
    <section class="section swatch-black section-text-shadow section-inner-shadow" style="overflow:visible;z-index:9">
        @include('includes/banner-image')
    <div class="container">
        <div class="row">
            <div class="col-md-12 element-higher-top element-short-bottom">
                <header class="text-center element-normal-bottom os-animation condensed animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                    <h1 class="bigger hairline bordered bordered-normal element-shorter-bottom">About Research Data Australia</h1>

                </header>
            </div>
            </div>
        </div>
        </section>
        <section class="section swatch-white">
        <div class="container-fluid" style="padding:0px">

                <div class="col-md-12  swatch-gray">
                    <div class="col-md-2 not-condensed os-animation animated fadeInUp swatch-gray"> </div>
                    <div class="col-md-8 not-condensed os-animation animated fadeInUp swatch-gray" style="-webkit-animation: 0s;padding-top:30px;padding-bottom:50px">
                        <div class="text-center" style="-webkit-animation: 0s;">
                            <p class="strong hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;color:#000000">
                                <strong>A service built on sharing</strong></p>
                        </div>
                        <div style="padding-left:50px;padding-right:50px">
                            <p>Research Data Australia helps you find, access, and reuse data for research from over one hundred
                                Australian research organisations, government agencies, and cultural institutions.
                                We do not store the data itself here but provide descriptions of, and links to, the data from our <u>data publishing partners</u>.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-2 swatch-gray">  </div>
                </div>
            </div>


            <div class="col-md-12">
                <div class="col-md-2 not-condensed os-animation animated fadeInUp"> </div>
                <div class="col-md-10  ">
                    <div class="col-md-4" style="float:left" >
                        @include('includes/about-subjects-list')
                    </div>
                    <div class="col-md-6 text-center" style="padding-left:20px;padding-top:100px" >
                        <p class="strong hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;color:#000000"><strong>Research data for researchers everywhere</strong></p>
                        <div class="text-left">
                        <p>Research Data Australia caters specifically for researchers but also has broader relevance to others including policy makers, educators and business people.</p>
                        <p>Research Data Australia covers a broad spectrum of research fields - across sciences, social sciences, arts and humanities. Much of the data you can discover
                            here is immediately accessible online via our partners and free to use (subject to any licence conditions).</p>
                            </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12  swatch-gray">
                <div class="col-md-2 not-condensed os-animation animated fadeInUp swatch-gray"> </div>
                <div class="col-md-8 not-condensed os-animation animated fadeInUp swatch-gray" style="-webkit-animation: 0s;padding-top:30px;padding-bottom:50px">
                    <div style="-webkit-animation: 0s;padding-left:50px">
                        <p class="strong hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;color:#000000">
                            <strong>More than just a search engine</strong></p>
                    </div>
                    <div style="padding-left:50px;padding-right:50px">
                        <p>A single search in Research Data Australia retrieves data resources across a wide range of subjects and providers, so that you can: </p>
                        <p>
                        <ul  class="fa-ul features-list element-no-bottom" >
                            <li class="element-no-top os-animation animated fadeInLeft">
                                <div class="features-list-icon box-animate" style="background-color:#e9e9e9;"><i class="fa fa-recycle fa-6x" ></i></div><div style="padding-top:20px">reuse existing data</div>
                            </li>
                            <li class="element-no-top os-animation animated fadeInLeft" >
                                <div class="features-list-icon box-animate" style="background-color:#e9e9e9;"><i class="fa fa-binoculars fa-4x" ></i></div><div style="padding-top:20px">explore beyond your discipline</div>
                            </li>
                            <li class="element-no-top os-animation animated fadeInLeft">
                                <div class="features-list-icon box-animate" style="background-color:#e9e9e9;"><i class="fa fa-puzzle-piece fa-4x" ></i></div><div style="padding-top:20px">assemble data resources to solve big problems</div>
                            </li>
                         </ul>
                        </p>
                        <p>
                            For example a search for the town "Wagga Wagga" will return results for data from a number of fields of research including:
                            Earth Sciences, Agriculture and Veterinary sciences, Environmental Sciences, Built Environment and Design, Biological Sciences,
                            Studies in Human Society, Studies in Creative Arts, History and Archaeology, and Philosophy and Religious Studies.
                        </p>
                    </div>
                </div>
                <div class="col-md-2 swatch-gray">  </div>
            </div>


        <div class="col-md-12" style="background-image: url({{$banner}}); background-size: cover; background-repeat: no-repeat;">
            <div class="col-md-2"> </div>
            <div class="col-md-8 not-condensed os-animation animated fadeInUp" style="-webkit-animation: 0s;padding-top:30px;padding-bottom:50px">
                <div class="col-md-6 text-center not-condensed os-animation animated fadeInUp">
                    <p class="bigger hairline bordered bordered-normal os-animation animated fadeIn" style="-webkit-animation: 0s;color:#ffffff">
                        {{number_format($collections)}}
                    </p> <h1 style="color:#ffffff">Datasets</h1> </div>
                <div class="col-md-6 text-center">
                    <p class="bigger hairline bordered bordered-normal os-animation animated fadeIn" style="-webkit-animation: 0s;color:#ffffff">
                        {{count($contributors)}}</p>
                    <h1 style="color:#ffffff">Contributors</h1> </div>
            </div>
            <div class="col-md-2"> </div>
        </div>

            <div class="col-md-12 swatch-gray" style="padding-bottom:-20px">
                <div class="col-md-2 not-condensed os-animation animated fadeInUp swatch-gray"> </div>
                <div class="col-md-8 not-condensed os-animation animated fadeInUp swatch-gray" style="-webkit-animation: 0s;padding-top:30px;padding-bottom:50px">
                    <div class="text-center" style="-webkit-animation: 0s;">
                        <p class="strong hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;color:#000000"><strong>Learn more</strong></p>
                    </div>
                    <div class="row " data-os-animation="" data-os-animation-delay="">
                        <div class="col-md-4 ">
                            <a href="http://ands.org.au/researchers/">
                                <div class="element-medium-top element-medium-bottom text-center os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay=".0s" style="-webkit-animation: 0s;">
                                    <div class="box box-round box-medium box-simple">
                                        <div class="box-dummy"></div>
                                        <div class="box-inner grid-overlay-0" style="background-color:#353b42;">
                                            <i class="fa fa-share-alt" style="color:#ffffff"></i>
                                        </div>
                                    </div>
                                    <p class="">Becoming a Contributor</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 ">
                            <a href="">
                                <div class="element-medium-top element-medium-bottom text-center os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay=".3s" style="-webkit-animation: 0.3s;">
                                    <div class="box box-round box-medium box-simple">
                                        <div class="box-dummy"></div>
                                        <div class="box-inner grid-overlay-0" style="background-color:#353b42;">
                                            <i class="fa fa-search" style="color:#ffffff"></i>
                                        </div>
                                    </div>
                                    <p class="">Searching for data</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 ">
                            <a href="http://www.ands.org.au/discovery/reuse.html">
                                <div class="element-medium-top element-medium-bottom text-center os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay=".6s" style="-webkit-animation: 0.6s;">
                                    <div class="box box-round box-medium box-simple">
                                        <div class="box-dummy"></div>
                                        <div class="box-inner grid-overlay-0" style="background-color:#353b42">
                                            <i class="fa fa-external-link" style="color:#ffffff"></i>
                                        </div>
                                    </div>
                                    <p class="">Accessing and reusing research data</p>
                                </div>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
    </section>
</article>
@stop