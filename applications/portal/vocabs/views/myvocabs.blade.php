@extends('layout/vocab_layout')
@section('content')
<article>
	<section class="section swatch-gray">
		<div class="container element-short-top">
			<div class="row">
				<div class="col-md-8">
					<div class="panel swatch-white">
						<div class="panel-heading">My Vocabs</div>
						<div class="panel-body">
							<a href="{{ portal_url('vocabs/add') }}" class="btn btn-block btn-primary"><i class="fa fa-plus"></i> Add a new Vocabulary</a>
							<hr>
							@if(sizeof($owned_vocabs) == 0)
								You don't own any vocabulary, start by adding a new one
							@else
								@foreach($owned_vocabs as $vocab)
									<li><a href="{{ portal_url('vocabs/edit/'.$vocab['slug']) }}">{{ $vocab['title'] }}</a></li>
								@endforeach
							@endif
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="panel swatch-white">
						<div class="panel-heading">Profile</div>
						<div class="panel-body">
							<h3>{{ $this->user->name() }}</h3>
							<a href="{{ portal_url('vocabs/logout') }}" class="btn btn-danger">Logout</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</article>
@stop