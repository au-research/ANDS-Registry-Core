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

					@if($this->user->isLoggedIn())
						<h1>{{$this->user->name()}}</h1>
						<?php var_dump($this->session->all_userdata()) ?>
					@else
					<a href="https://test.ands.org.au/registry/auth/login#/?redirect={{ current_url() }}">Login via TEST registry</a>
					<a href="https://devl.ands.org.au/minh/registry/auth/login#/?redirect={{ current_url() }}">Login via DEVL registry</a>
					@endif

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