@extends('layouts/single')
@section('content')
<article>

	<section class="section swatch-black section-text-shadow section-inner-shadow element-short-bottom" style="overflow:visible;z-index:9">
        <div class="background-media" style="background-image: url({{asset_url('images/activity_banner.jpg','core')}}); background-size: cover; background-position: 50% 60%; background-repeat: no-repeat;" >
       </div>
       <div class="background-overlay grid-overlay-30 " style="background-color: rgba(0,0,0,0.3);"></div>
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
	           <div class="col-md-12">
	               <header class="text-center element-normal-top element-no-bottom not-condensed os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
	                   <h1 class="bigger hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;"> Browse Grants and Projects by Subjects </h1>
	               </header>
	           </div>
	       </div>
	       <div class="row ">
	        @include('includes/subjects-list')
	       </div>
	   </div>
	</section>

	<section class="section swatch-white element-short-bottom">
       <div class="container">
           <div class="row">
               <div class="col-md-12">
                   <header class="text-center element-normal-top element-medium-bottom not-condensed os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                       <h1 class="bigger hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;">About Exploring Research Grants and Projects </h1>
                   </header>
               </div>
           </div>
           <div class="row ">
            <div class="col-md-12">
            	<p>Research Data Australia aggregates research grant information supplied by multiple funders and research project information supplied by some of our data contributors.</p>
            	<p>Grant descriptions are the responsibility of the <b>funder</b> who contributed the information. Some also provide open access to their history of funding grants through downloads in either PDF or Excel formats. Each funder has their own format and structure for describing grants and their downloads may have detail absent from the description in RDA. Further information about their grant data can be found by reading the "Terms of Use" document - click on the funder's logo below.</p>
            	<p>Research Project descriptions are the responsibility of the <b>institution</b> who contributed the information. They may share the same identifier with a grant record in which case both descriptions will appear together in search results.</p>
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
            @include('includes/contributors-list')
           </div>
       </div>
    </section>
</article>
@stop