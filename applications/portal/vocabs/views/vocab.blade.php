@extends('layout/vocab_layout')
@section('content')
<article>
	<section class="section swatch-white element-short-bottom">
		<div class="container">
			<div class="row">
				<div class="col-md-8">
					<header class="text-center element-short-top element-no-bottom not-condensed os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                       <h1 class="bigger hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;"> {{ $vocab->title }} </h1>
                   </header>
                    {{ $vocab }}
				</div>

                <div class="col-md-4">
                    some stuff for the side
                </div>
			</div>

		</div>
	</section>
</article>
@stop