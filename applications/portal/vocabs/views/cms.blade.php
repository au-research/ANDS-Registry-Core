@extends('layout/vocab_layout')

@section('content')
<section ng-controller="addVocabsCtrl" class="section swatch-white">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<header class="section-text-shadow section-innder-shadow element-short-top element-short-bottom">
					<h1 class="hairline bordered-normal">
						@if($vocab)
							{{ $vocab->title }}
						@else
							Add a new Vocabulary
						@endif
					</h1>
				</header>
			</div>
		</div>
	</div>

	@if($vocab)
		<input type="hidden" type="text" value="{{ $vocab->id }}" id="vocab_id"/>
		<input type="hidden" type="text" value="{{ $vocab->slug }}" id="vocab_slug"/>
	@endif

	<div class="container" ng-if="!decide">
		<div class="row">
			<div class="col-md-12">
				<div class="panel swatch-gray">
					<div class="panel-heading">PoolParty Integration</div>
					<div class="panel-body">
						<div class="form-group">
							<label for="">PoolParty Search</label>
							<input type="text" class="form-control" placeholder="PoolParty ID" ng-model="project" typeahead="project as project.title for project in projects | filter:projectSearch($viewValue) | limitTo:8" typeahead-min-length="0">
							<p class="help-block">Search for a PoolParty Project to pre-fill form</p>
						</div>
						<div ng-if="project">
							<dl class="dl-horizontal">
								<dt>Title</dt> <dd>[[ project.title ]]</dd>
								<dt>PoolParty ID</dt> <dd>[[ project.id ]]</dd>
							</dl>
						</div>
					</div>
					<div class="panel-footer">
						<a href="" class="btn btn-primary" ng-click="populate(project)">Use this PoolParty</a>
						<a href="" class="btn btn-link" ng-click="skip()">Skip</a>
						<p class="help-block">Skipping will start a blank Vocabulary</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="container" ng-if="decide">
		<div class="row">
			<div class="col-md-8"> 
				<div class="panel swatch-gray">
					<!-- <div class="panel-heading">Vocabulary Metadata</div> -->
					<div class="panel-body">
						<div class="form-group">
							<label for="">Vocabulary Title</label>
							<input type="text" class="form-control" ng-model="vocab.title" placeholder="Vocabulary Title">
						</div>
						<div class="form-group">
							<label for="">Vocabulary URI</label>
							<input type="text" class="form-control" ng-model="vocab.vocab_uri" placeholder="Vocabulary URI">
						</div>
						<div class="form-group">
							<label for="">Vocabulary Description</label>
							<textarea class="form-control" ng-model="vocab.description" placeholder="Vocabulary Description" rows="10"></textarea>
						</div>
						<div class="form-group">
							<label for="">Vocabulary Licence</label>
                            <select name="" id="" class="form-control" placeholder="vocab Licence" ng-options="lic for lic in licence" ng-model="vocab.licence"></select>
						</div>
						
						<div class="form-group">
							<label for="">Vocabulary Creation Date</label>
							<p class="input-group">
								<input type="text" class="form-control" ng-model="vocab.creation_date" placeholder="Vocabulary Creation Date" datepicker-popup="dd-MM-yyyy" is-open="opened" >
								<span class="input-group-btn">
									<button type="button" class="btn btn-default" ng-click="open($event)"><i class="glyphicon glyphicon-calendar"></i></button>
								</span>
							</p>
						</div>
						<div class="form-group">
							<label for="">Revision Cycle</label>
							<input type="text" class="form-control" ng-model="vocab.revision_cycle" placeholder="Revision Cycle">
						</div>
						<div class="form-group">
							<label for="">Note</label>
							<textarea class="form-control" ng-model="vocab.note" placeholder="Vocabulary Description" rows="10"></textarea>
						</div>
                        @if(null!=$this->user->affiliations())
                            <div class="form-group">
                                <label for="">Owner</label>
                                <select name="" id="" class="form-control" placeholder="vocab Owner" ng-options="owner for owner in user_orgs" ng-model="vocab.owner"></select>
                            </div>
                        @else
                           <input class="form-control" type="hidden" name="owner" value="" ng-model="vocab.owner"/>
                        @endif


                        <input name="user_owner" value="dsgsdgdsgfdsg" type="hidden" class="form-control" placeholder="vocab Owner" />

					</div>
				</div>

				<div class="panel swatch-gray">
					<div class="panel-heading">Top Concepts</div>
					<div class="panel-body">
						<table class="table">
							<thead>
								<tr><th>Top Concept</th> <th></th></tr>
							</thead>
							<tbody>
								<tr ng-repeat="concept in vocab.top_concept track by $index">
									<td>[[ concept ]]</td>
									<td><a href="" ng-click="vocab.top_concept.splice($index, 1)"><i class="fa fa-remove"></i></a></td>
								</tr>
							</tbody>
						</table>
						
						<form action="" class="form swatch-gray col-md-8" ng-submit="addtolist(vocab.top_concept, newTopConcept)">
							<div class="input-group">
								<input type="text" class="form-control" placeholder="New Top Concept" ng-model="newTopConcept">
								<span class="input-group-btn">
									<button class="btn btn-primary" type="button" ng-click="addtolist(vocab.top_concept, newTopConcept)"><i class="fa fa-plus"></i> Add</button>
								</span>
							</div>
						</form>
					</div>
				</div>

				<div class="panel swatch-gray">
					<div class="panel-heading">Languages</div>
					<div class="panel-body">
						<table class="table">
							<thead>
								<tr><th>Language</th> <th></th></tr>
							</thead>
							<tbody>
								<tr ng-repeat="ln in vocab.language  track by $index ">
									<td >[[ ln | languageFilter:langs ]]</td>
									<td><a href="" ng-click="vocab.language.splice($index, 1)"><i class="fa fa-remove"></i></a></td>
								</tr>
							</tbody>
						</table>
						
						<form action="" class="form swatch-gray col-md-8" ng-submit="addtolist(vocab.language, newLanguage)">
							<div class="input-group">
                                <select class="form-control" placeholder="Language" ng-options="lang.value as lang.text for lang in langs" ng-model="newLanguage"></select>
								<span class="input-group-btn">
									<button class="btn btn-primary" type="button" ng-click="addtolist(vocab.language, newLanguage)"><i class="fa fa-plus"></i> Add</button>
								</span>
							</div>
						</form>
					</div>

				</div>

				<div class="panel swatch-gray">
					<div class="panel-heading">Subjects</div>
					<div class="panel-body">
						<table class="table">
							<thead>
								<tr><th>Subject Label</th><th>Source</th> <th></th></tr>
							</thead>
							<tbody>
								<tr ng-repeat="subject in vocab.subjects track by $index">
									<td>[[ subject.subject ]]</td>
									<td>[[ subject.subject_source ]]</td>
									<td><a href="" ng-click="list_remove('subjects', $index)"><i class="fa fa-remove"></i></a></td>
								</tr>
							</tbody>
						</table>

						<form action="" class="form swatch-gray col-md-8" ng-submit="addtolist(vocab.subjects, newSubject)">
							<div class="form-group">
								<label for="">Subject Label</label>
								<input type="text" class="form-control" placeholder="Subject Label" ng-model="newSubject.subject">
							</div>
							<div class="form-group">
								<label for="">Subject Source</label>
								<input type="text" class="form-control" placeholder="Subject Source" ng-model="newSubject.subject_source">
							</div>
							<button class="btn btn-primary" ng-submit="addtolist(vocab.language, newSubject)"><i class="fa fa-plus"></i> Add Subject</button>
						</form>
					</div>
				</div>

			</div>
			<div class="col-md-4">

				
				
			<!--	<div class="panel swatch-gray">
					<div class="panel-heading">Publishers</div>
					<div class="panel-body">
						<table class="table">
							<thead>
								<tr><th>Publisher</th> <th></th></tr>
							</thead>
							<tbody>
								<tr ng-repeat="related in vocab.related_entity track by $index" ng-if="related.type=='party' && related.relationship=='publishedBy'">
									<td><a href="" ng-click="relatedmodal('edit', 'publisher', related)">[[ related.title ]]</a></td>
									<td><a href="" ng-click="list_remove('related_entity', $index)"><i class="fa fa-remove"></i></a></td>
								</tr>
							</tbody>
						</table>
						
						<div class="form-group">
							<a href="" class="btn btn-primary" ng-click="relatedmodal('add', 'publisher')"><i class="fa fa-plus"></i> Add a related publisher</a>
						</div>
					</div>
				</div> -->

				<div class="panel swatch-gray">
					<div class="panel-heading">Versions</div>
					<div class="panel-body">
						<table class="table">
							<thead>
								<tr><th>Title</th><th>Status</th><th></th></tr>
							</thead>
							<tbody>
								<tr ng-repeat="version in vocab.versions track by $index">
									<td><a href="" ng-click="versionmodal('edit', version)">[[ version.title ]] </a></td>
									<td><span class="label" ng-class="{'deprecated': 'label-danger', 'current': 'label-success', 'superseded': 'label-warning', 'depreciated': 'label-danger'}[version.status]">[[ version.status ]]</span></td>
									<td><a href="" ng-click="list_remove('versions', $index)"><i class="fa fa-remove"></i></a></td>
								</tr>
							</tbody>
						</table>
						<a href="" class="btn btn-primary" ng-click="versionmodal('add')"><i class="fa fa-plus"></i> Add a version</a>
					</div>
				</div>

				<div class="panel swatch-gray">
					<div class="panel-heading">Related</div>
					<div class="panel-body">
						<table class="table">
							<thead>
								<tr><th>Related</th> <th>Type</th> <th></th></tr>
							</thead>
							<tbody>
								<tr ng-repeat="related in vocab.related_entity track by $index">
									<td><a href="" ng-click="relatedmodal('edit', related.type, related)">[[ related.title ]]</a></td>
									<td>[[ related.type ]]</td>
									<td><a href="" ng-click="list_remove('related_entity', $index)"><i class="fa fa-remove"></i></a></td>
								</tr>
							</tbody>
						</table>

						<div class="btn-group">
							<button class="btn btn-primary" ng-click="relatedmodal('add', 'publisher')"><i class="fa fa-plus"></i> Add a publisher</button>
							<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><span class="caret"></span></button>
							<ul class="dropdown-menu" role="menu">
								<li><a href="" ng-click="relatedmodal('add', 'vocabulary')">Related Vocabulary</a></li>
								<li><a href="" ng-click="relatedmodal('add', 'party')">Related Party</a></li>
								<li><a href="" ng-click="relatedmodal('add', 'service')">Related Service</a></li>
							</ul>
						</div>
						
						
					</div>
				</div>
				
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="panel swatch-gray">
					<div class="panel-body">

						<a href="" class="btn btn-large btn-primary" ng-click="save('draft')">Save to draft</a>

                        @if(null!=$this->user->affiliations())
                        <a href="" class="btn btn-large btn-primary" ng-click="save('published')">Publish</a>
                        @else
                        <a href="" class="btn btn-large btn-primary" ng-click="save('requested')">Submit for assessment</a>
                        @endif
                        @if($vocab && $vocab->prop['status']=='published')
                        <a href="" class="btn btn-large btn-primary" ng-click="save('deprecated')">Deprecate</a>
                        @endif
						<div class="alert alert-danger element-short-top os-animation animated fadeInUp" data-os-animation="fadeInUp" ng-if="error_message">[[ error_message ]]</div>
						<div class="alert alert-success element-short-top os-animation animated fadeInUp" data-os-animation="fadeInUp" ng-if="success_message">[[ success_message ]]</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
@stop