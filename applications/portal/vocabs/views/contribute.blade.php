@extends('layout/vocab_layout')
@section('content')
<article>
	<section class="section swatch-white">
		<div class="container-fluid">
			<!--<div class="row">
                <div  class="col-md-12 swatch-gray">
                <div class="col-md-2 not-condensed os-animation animated fadeInUp swatch-gray"> </div>
				<div class="col-md-8 swatch-gray">
					<header class="text-center not-condensed os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                       <h1 class="bigger hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;"> Publish a vocabulary </h1>
                   </header>
				</div>
                <div class="col-md-2 not-condensed os-animation animated fadeInUp swatch-gray"> </div>
                    </div>
			</div> -->
			<div class="row">
                <div  class="col-md-12 swatch-white">
                <div class="col-md-2 not-condensed os-animation animated fadeInUp"> </div>
                <div class="col-md-8 swatch-white not-condensed os-animation animated fadeInUp">

                    <div class="col-md-6 not-condensed os-animation animated fadeInUp">
                        <h1 class="hairline bordered bordered-normal os-animation animated fadeIn"> A service built to support vocabulary development</h1>
                        <p>ANDS offers several options for our partners to publish vocabularies in Research Vocabularies Australia.</p>
                    </div>
                    <div class=" text-center col-md-6 not-condensed os-animation animated fadeInUp"><img src="{{asset_url('images/vocab_process.jpg', 'core')}}" /></div>
                </div>
                <div class="col-md-2 not-condensed os-animation animated fadeInUp"> </div>
                    </div>
            </div>
            <div class="row">
                <div  class="col-md-12 swatch-gray">
                <div class="col-md-2 not-condensed os-animation animated fadeInUp swatch-gray"> </div>
                <div class="col-md-8 swatch-gray">
                   <h1 class="text-center hairline bordered bordered-normal os-animation animated fadeIn"> Create or manage a vocabulary</h1>
                   <p>Research Vocabularies Australia includes an editing tool which allows for the creation and management of vocabularies and the concepts they contain. The editor allows you and your colleagues to collaboratively manage your vocabulary, with optional workflow management. Vocabularies may be uploaded or created from scratch within the editor. Additional features of the editor include:
                    Capture information about your vocabulary and the concepts it contains
                    Browse through the concepts in your vocabulary via the built-in visualisation tool
                    Link concepts in your vocabulary to concepts in other vocabularies
                    Query your vocabulary via the built-in SPARQL endpoint</p>
                    </div>
                <div class="col-md-2 not-condensed os-animation animated fadeInUp swatch-gray"> </div>
                    </div>
            </div>
            <div class="row">
                <div class="col-md-2 not-condensed os-animation animated fadeInUp"> </div>
                <div class="col-md-8 swatch-white not-condensed os-animation animated fadeInUp">
                <h1  class="text-center hairline bordered bordered-normal os-animation animated fadeIn"> Publish and describe a vocabulary</h1>
                <p>
                    Research Vocabularies Australia includes a portal in which you can upload and describe your vocabulary. Vocabulary files may be uploaded in a variety of formats. Additional publishing features of the portal include:
                <ul>
                    <li>Upload vocabulary files in a variety of formats</li>
                    <li>Describe versions of your vocabulary, their statuses, and changes made between versions</li>
                    <li>Provide contact, access and licencing information about your vocabulary</li>
                    <li>Describe your vocabulary by subject and language</li>
                    <li>Describe individuals who have contributed to the vocabulary’s creation or management</li>
                    <li>Use the PoolParty Integration tool to quickly publish vocabularies managed in the ANDS vocabulary service editor</li>
                </ul>
                </p>
                    </div>
                <div class="col-md-2 not-condensed os-animation animated fadeInUp"> </div>
            </div>
            <div class="row">
                <div  class="col-md-12 swatch-gray">
                <div class="col-md-2 not-condensed os-animation animated fadeInUp swatch-gray"> </div>
                <div class="col-md-8 swatch-gray">
                    <h1 class="text-center hairline bordered bordered-normal os-animation animated fadeIn"> Store a vocabulary</h1>
               <p> Research Vocabularies Australia includes a repository in which vocabulary files and the information about those vocabularies may be stored. The repository allows for the flow of information between the editor and the portal.

                </p>
                        </div>
                <div class="col-md-2 not-condensed os-animation animated fadeInUp swatch-gray"> </div>
                </div>
                </div>
			</div>
		</div>
	</section>
</article>
@stop