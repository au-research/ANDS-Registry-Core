@extends('layout/vocab_layout')
@section('content')
<article>
	<section class="section swatch-white element-short-bottom">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<header class="text-center element-short-top element-no-bottom not-condensed os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                       <h1 class="bigger hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;"> Vocabulary Portal </h1>
                   </header>
				</div>
			</div>
			<div class="row">
				<p class="lead">
					Vocabulary Portal is the controlled vocabulary discovery service of the Australian National Data Service (ANDS). ANDS is supported by the Australian Government through the National Collaborative Research Infrastructure Strategy Program.	
				</p>
			</div>
		</div>
	</section>
	@include('includes/search-view')
</article>
@stop