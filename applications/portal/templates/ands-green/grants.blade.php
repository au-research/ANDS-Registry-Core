@extends('layouts/single')
@section('content')
<article>

	<section class="section swatch-black section-text-shadow section-inner-shadow element-short-bottom" style="overflow:visible;z-index:9">
        @include('includes/banner-image')
        <div class="container">
            <div class="row">
                <div class="col-md-12 element-normal-top element-normal-bottom">
                    <header class="text-center element-small-bottom os-animation condensed animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                        <h1 class="bigger hairline bordered bordered-normal">Explore Research Grants and Projects</h1>
                        <p class="normal">
                            Search for Australian research grants and projects. This discovery service includes grant information from 
                            Australia's principal research funders as well as project descriptions from some institutions and agencies.
                            These descriptions can include connections to related datasets and publications.
                        </p>
                    </header>
                    @include('includes/search-bar')
                </div>
            </div>
        </div>
    </section>

	<section class="section swatch-white element-short-bottom">
	   <div class="container">
	       <div class="row">
          <div class="col-md-6">
            <header class="text-center element-normal-top element-short-bottom not-condensed os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                <h1 class="bigger hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;"> Browse Grants and Projects by Subjects </h1>
            </header>
            <ul>
              @foreach($highlevel as $item)
              <li><a href="{{base_url('search').'#!'.$item['query']}}" target="_self">{{$item['display']}}</a></li>
              @endforeach
            </ul>
          </div>
	        <div class="col-md-6">
            <header class="text-center element-normal-top element-short-bottom not-condensed os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                <h1 class="bigger hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;">About Exploring Research Grants and Projects</h1>
            </header>

            <p> Research Data Australia aggregates research grant information supplied by
                some  funders and research project information supplied by some of our
                research institutions.</p>
            <p>Grant descriptions are the responsibility of the funder who contributed the
                information. Some provide open access to their funding grants on their own
                web sites. Their own web site may provide detail absent from the
                description in RDA. Further information about each funder's grant data,
                including the currency of the information, can be found by following the
                'read more' link under their entry below.</p>
            <p> Research Project descriptions are the responsibility of the institution who
                contributed the information. RDA can contain both a description of the
                project and a description of the grant that funded it, in which case both
                descriptions will appear together in search results if they share the same
                grant identifier.</p>
          </div>
	       </div>
	   </div>
	</section>

	<section class="section swatch-white element-short-bottom">
       <div class="container">
           <div class="row">
               <div class="col-md-12">
                   <header class="text-center element-normal-top element-medium-bottom not-condensed os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                       <h1 class="bigger hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;"> Which funders contribute information about research grants they have awarded </h1>
                   </header>
               </div>
           </div>
           <div class="row ">
            <div class="portfolio-container element-medium-top element-medium-bottom">
                <div class="portfolio masonry isotope" data-padding="10" data-col-xs="1" data-col-sm="1" data-col-md="2" data-col-lg="2" data-layout="fitRows">
                  @foreach($contributors as $con)
                    <div class="masonry-item portfolio-item isotope-item" data-menu-order="1" data-title="{{ $con['title'] }}">
                        <div class="figure portfolio-os-animation element-no-top element-no-bottom text-center normalwidth fade-in animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s">
                            <div class="figure-caption text-center">
                                <h3 class="figure-caption-title bordered bordered-small bordered-link">
                                  <a href="{{ portal_url($con['slug'].'/'.$con['id']) }}" target="_self">{{ $con['title'] }}</a>
                                </h3>
                                @if(array_key_exists('list_description', $con))
                                <p>
                                  {{ strlen($con['list_description']) > 300 ? substr($con['list_description'],0,300)."..." : $con['list_description'] }}
                                </p>
                                @endif

                                  <a href="{{ portal_url($con['slug'].'/'.$con['id']) }}" target="_self">read more</a>

                            </div>
                        </div>
                    </div>
                  @endforeach
                </div>
            </div>
           </div>
       </div>
    </section>
</article>
@stop