@extends('layout/vocab_layout')
@section('content')
<article>
	<section class="section swatch-white element-short-bottom">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<header class="text-center element-short-top element-no-bottom not-condensed os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                       <h1 class="bigger hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;"> Login to MyVocabs </h1>
                   </header>
				</div>
			</div>
			<div class="row" id="main">
			    <div class="col-lg-6 col-lg-offset-3 col-xs-12 col-md-12">
			        

			        <a href="https://test.ands.org.au/registry/services/roles/authenticate/aaf_rapid?redirect={{ current_url() }}">Login via shib</a>

			        <div class="element-small-top">
			            <p>By logging into Research Data Australia, you will have access to additional features including the ability to save records and searches, and contribute to the Research Data Australia community by adding tags (keywords) to records.</p>
			            <p><small>Research Data Australia. <a href="{{ base_url('home/privacy') }}">Privacy Policy</a></small></p>
			        </div>
			    </div>
			</div>
		</div>
	</section>
</article>
@stop