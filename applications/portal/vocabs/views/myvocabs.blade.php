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
							<a href="{{ portal_url('vocabs/add') }}" class="btn btn-block btn-primary"><i class="fa fa-plus"></i> Add a new Vocabulary from pool party</a>
                            <a href="{{ portal_url('vocabs/add?skip=true') }}" class="btn btn-block btn-primary"><i class="fa fa-plus"></i> Add a new Vocabulary</a>
							<hr>
							@if(sizeof($owned_vocabs) == 0)
								You don't own any vocabulary, start by adding a new one
							@else
								<h4>Published Vocabularies</h4>
								<table class="table">
									<thead>
										<tr><th>Vocabulary</th><th>Action</th></tr>
									</thead>
									<tbody>
										@foreach($owned_vocabs as $vocab)
											@if($vocab['status']=='published')
											<tr>
												<td><a href="{{ portal_url('vocabs/edit/'.$vocab['id']) }}">{{ $vocab['title'] }}</a></td>
												<td>
													<div class="btn-group">
														<a href="{{ portal_url($vocab['id']) }}" class="btn btn-primary"><i class="fa fa-search"></i> View</a>
														<a href="{{ portal_url('vocabs/edit/'.$vocab['id']) }}" class="btn btn-primary"><i class="fa fa-edit"></i> Edit</a>
														<a href="javascript:;" class="btn btn-primary deleteVocab" vocab_id="{{ $vocab['id'] }}"><i class="fa fa-trash"></i></a>
													</div>
												</td>
											</tr>
											@endif
										@endforeach
									</tbody>
								</table>

								<h4>Drafts</h4>
								<table class="table">
									<thead>
										<tr><th>Vocabulary</th><th>Action</th></tr>
									</thead>
									<tbody>
										@foreach($owned_vocabs as $vocab)
											@if($vocab['status']=='draft')
											<tr>
												<td><a href="{{ portal_url('vocabs/edit/'.$vocab['id']) }}">{{ $vocab['title'] }}</a></td>
												<td>
													<div class="btn-group">
														<a href="{{ portal_url('vocabs/edit/'.$vocab['id']) }}" class="btn btn-primary"><i class="fa fa-edit"></i> Edit</a>
														<a href="javascript:;" class="btn btn-primary deleteVocab" vocab_id="{{ $vocab['id'] }}"><i class="fa fa-trash"></i></a>
													</div>
												</td>
											</tr>
											@endif
										@endforeach
									</tbody>
								</table>
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