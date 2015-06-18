@extends('layout/vocab_layout')
@section('content')
<article>
	<section class="section swatch-white element-short-bottom">
		<div class="container-fluid">
            <div class="row">
                <div  class="col-md-12 swatch-white">
                    <div class="col-md-2 not-condensed os-animation animated fadeInUp"> </div>
                    <div class="col-md-8 swatch-white not-condensed os-animation animated fadeInUp">

                        <div class="col-md-6 not-condensed os-animation animated fadeInUp">
                            <h1 class="hairline bordered bordered-normal os-animation animated fadeIn"> A service built for discovery and reuse</h1>
                            Research Vocabularies Australia helps you find, access, and reuse vocabularies for research. Many of the vocabularies you can discover here are immediately accessible, either directly through Research Vocabularies Australia or via partners and publishers, and are free to use (subject to licence conditions).</p>
                        </div>
                        <div class=" text-center col-md-6 not-condensed os-animation animated fadeInUp"><img src="{{asset_url('images/use_vocab.jpg', 'core')}}" /></div>
                    </div>
                    <div class="col-md-2 not-condensed os-animation animated fadeInUp"> </div>
                </div>
            </div>
            <div class="row">
                <div  class="col-md-12 swatch-gray">
                    <div class="col-md-2 not-condensed os-animation animated fadeInUp swatch-gray"> </div>
                    <div class="col-md-8 swatch-gray">
                        <h1 class="text-center hairline bordered bordered-normal os-animation animated fadeIn"> Explore the concepts in a vocabulary</h1>
                        <p>Research Vocabularies Australia includes a portal which allows users to search and browse controlled vocabularies hosted in the vocabulary editor or repository or have been described in the portal. Users can browse for vocabulary based on:
                           <ul>
                           <li> Subject</li>
                            <li> Publisher</li>
                            <li> Language</li>
                            <li> Format</li>
                            <li> Access type</li>
                            <li> Licence</li>
                        </ul>
                        </p>
                    </div>
                    <div class="col-md-2 not-condensed os-animation animated fadeInUp swatch-gray"> </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2 not-condensed os-animation animated fadeInUp"> </div>
                <div class="col-md-8 swatch-white not-condensed os-animation animated fadeInUp">
                    <h1  class="text-center hairline bordered bordered-normal os-animation animated fadeIn"> Consume a vocabulary in your own system</h1>
                    <p>
                        Users of Research Vocabularies Australia have several options for using an ANDS-hosted vocabulary in their own data centres, portals,
                        data generation tools, vocabulary services or other applications.
                        The Vocabulary Service API allows developers to integrate with the vocabulary service using HTTP and a
                        variety of data representations (including XML, JSON and RDF) and the vocabulary widget allows you to add
                        Data Classification capabilities to your data capture tools via the Research Vocabularies Australia.
                    </p>
                </div>
                <div class="col-md-2 not-condensed os-animation animated fadeInUp"> </div>
            </div>

        </div>
		</div>
	</section>
</article>
@stop