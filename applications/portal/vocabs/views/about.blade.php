@extends('layout/vocab_layout')
@section('title')
About ANDS Vocabulary Services
@stop
@section('content')
<article>
	<section class="section swatch-white element-short-bottom">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<header class="text-center element-short-top element-no-bottom not-condensed os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                       <h1 class="bigger hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;"> About </h1>
                   </header>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8 col-md-offset-2 col-lg-offset-2 animated fadeInUp">
					<h3>A service built on sharing</h3>
					<p>
						Research Vocabularies Australia helps you find, access, and reuse vocabularies for research. Some vocabularies are hosted by ANDS and can be accessed directly through Research Vocabularies Australia.  Otherwise Research Vocabularies Australia provides a link to the vocabulary owner’s web page.	
					</p>
				</div>
				<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8 col-md-offset-2 col-lg-offset-2 animated fadeInUp">
					<h3>Vocabularies for the research community</h3>
					<p>
						Research Vocabularies Australia caters for researchers and those who support, describe and discover research, including vocabulary managers, ontologists, data managers and librarians.
					</p>
					<p>
						Through engagement with the research community, Research Vocabularies Australia will grow to cover a broad spectrum of research fields - across sciences, social sciences, arts and humanities. Many of the vocabularies you can discover here are immediately accessible, either directly through Research Vocabularies Australia or via partners and publishers, and are free to use (subject to licence conditions).
					</p>
					<p>
						Research Vocabularies Australia is one of a suite of vocabulary services offered by ANDS.  For more information, see the <a href="{{ portal_url('vocabs/page/contribute') }}">service overview page</a>.
					</p>
				</div>
			</div>
		</div>
	</section>
</article>
@stop