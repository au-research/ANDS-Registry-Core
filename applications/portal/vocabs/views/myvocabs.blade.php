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
                            <div align="center">
                                <p class="small center"> <span class="yellow_exclamation"><i class="fa fa-exclamation" style="color:#fff"></i></span>
                                    Please review the <a href="https://documentation.ardc.edu.au/display/DOC/Research+Vocabularies+Australia+participant+agreement" target="_blank">Research Vocabularies Australia participant agreement</a> before you proceed.<br />
                                    If you have questions, please email <a href="mailto:services.ands.org.au">services.ands.org.au</a>.
                                </p>

                            </div>
                            @if($this->user->affiliations())
							<a href="{{ portal_url('vocabs/add') }}" class="btn btn-block btn-primary"><i class="fa fa-plus"></i> Add a new vocabulary from PoolParty</a>
                            @endif
                            <a href="{{ portal_url('vocabs/add#!/?skip=true') }}" class="btn btn-block btn-primary"><i class="fa fa-plus"></i> Add a new Vocabulary</a>
							<hr>
							@if(sizeof($owned_vocabs) == 0)
								You don't own any vocabularies, start by adding a new one
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
												<td style="width:90%"><div class="published_title"><a href="{{ portal_url('vocabs/edit/'.$vocab['id']) }}" ng-non-bindable>{{ htmlspecialchars($vocab['title']) }}</a></div></td>
												<td>
													<div class="btn-group" style="display:inline-flex">
														<a href="{{ portal_url($vocab['slug']) }}" class="btn btn-primary" style="float:none"><i class="fa fa-search"></i> View</a>
														<a href="{{ portal_url('vocabs/edit/'.$vocab['id']) }}" class="btn btn-primary" style="float:none"><i class="fa fa-edit"></i> Edit</a>
														<a href="javascript:;" class="btn btn-primary btn-primary-warning deleteVocab" style="float:none" vocab_id="{{ $vocab['id'] }}" title="Delete this vocabulary"><i class="fa fa-trash"></i></a>
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
												<td style="width:90%"><div class="draft_title"><a href="{{ portal_url('vocabs/edit/'.$vocab['id']) }}" ng-non-bindable>{{ htmlspecialchars($vocab['title']) }}</a></div></td>
                        <td>
													<div class="btn-group" style="display:inline-flex">
														<a href="{{ portal_url('vocabs/edit/'.$vocab['id']) }}" class="btn btn-primary" style="float:none"><i class="fa fa-edit"></i> Edit</a>
														<a href="javascript:;" class="btn btn-primary btn-primary-warning deleteVocab" style="float:none" vocab_id="{{ $vocab['id'] }}" title="Delete this vocabulary"><i class="fa fa-trash"></i></a>
													</div>
												</td>
											</tr>
											@endif
										@endforeach
									</tbody>
								</table>

								<h4>Deprecated</h4>
								<table class="table">
									<thead>
										<tr><th>Vocabulary</th><th>Action</th></tr>
									</thead>
									<tbody>
										@foreach($owned_vocabs as $vocab)
											@if($vocab['status']=='deprecated')
											<tr>
												<td style="width:90%"><div class="draft_title"><a href="{{ portal_url('vocabs/edit/'.$vocab['id']) }}" ng-non-bindable>{{ htmlspecialchars($vocab['title']) }}</a></div></td>
												<td>
													<div class="btn-group" style="display:inline-flex">
														<a href="{{ portal_url('vocabs/edit/'.$vocab['id']) }}" class="btn btn-primary" style="float:none"><i class="fa fa-edit"></i> Edit</a>
														<a href="javascript:;" class="btn btn-primary btn-primary-warning deleteVocab" style="float:none" vocab_id="{{ $vocab['id'] }}" title="Delete this vocabulary"><i class="fa fa-trash"></i></a>
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