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

	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="panel swatch-gray">
					<div class="panel-heading">PoolParty Integration</div>
					<div class="panel-body">
						<input type="text" class="form-control" ng-model="vocab.pool_party_id" placeholder="Pool Party ID">
						<p class="help-block">Insert PoolParty ID to pre-fill form. <a href="">Search for a PoolParty</a></p>
					</div>
				</div>
			</div>
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
							<input type="text" class="form-control" ng-model="vocab.licence" placeholder="Vocabulary Licence URI">
						</div>
						<div class="form-group">
							<label for="">Vocabulary Top Concepts</label>
							<ul class="listy">
								<li ng-repeat="concept in vocab.top_concept track by $index"> <input type="text" ng-model="concept"> <a href="" ng-click="vocab.top_concept.splice($index, 1)"><i class="fa fa-remove"></i></a></li> 
								<li><a href="" ng-click='vocab.top_concept.push("")'>Add New</a></li>
							</ul>
						</div>
						<div class="form-group">
							<label for="">Subjects</label>
							<ul>
								<li ng-repeat="subject in vocab.subjects">[[ subject.subject ]]</li>
							</ul>
						</div>
						<div class="form-group">
							<input type="text" class="form-control" ng-model="vocab.creation_date" placeholder="Vocabulary Creation Date">
						</div>
						<div class="form-group">
							<input type="text" class="form-control" ng-model="vocab.revision_cycle" placeholder="Revision Cycle">
						</div>
						<div class="form-group">
							<label for="">Languages</label>
							<ul>
								<li ng-repeat="lang in vocab.language"> <input type="text" ng-model="lang" typeahead="lang for lang in langs | filter:$viewValue | limitTo:8"> </li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				
				<div class="panel swatch-gray">
					<div class="panel-heading">Publishers</div>
					<div class="panel-body">
						<ul>
							<li ng-repeat="related in vocab.related_entity" ng-if="related.type=='publisher'"><a href="" ng-click="relatedmodal('edit', 'publisher', related)">[[ related.title ]]</a></li>
						</ul>
						<div class="form-group">
							<a href="" class="btn btn-primary" ng-click="relatedmodal('add', 'publisher')"><i class="fa fa-plus"></i> Add a related publisher</a>
						</div>
					</div>
				</div>

				<div class="panel swatch-gray">
					<div class="panel-heading">Versions</div>
					<div class="panel-body">
						<table class="table">
							<thead>
								<tr><th>Title</th><th>Status</th></tr>
							</thead>
							<tbody>
								<tr ng-repeat="version in vocab.versions">
									<td><a href="" ng-click="versionmodal('edit', version)">[[ version.title ]]</a></td>
									<td><span class="label" ng-class="{'deprecated': 'label-danger', 'current': 'label-success', 'superceded': 'label-info', 'depreciated': 'label-danger'}[version.status]">[[ version.status ]]</span></td>
								</tr>
							</tbody>
						</table>
						<a href="" class="btn btn-primary" ng-click="versionmodal('add')"><i class="fa fa-plus"></i> Add a version</a>
					</div>
				</div>

				<div class="panel swatch-gray">
					<div class="panel-heading">Related</div>
					<div class="panel-body">
						<h4>Related Vocabularies</h4>
						<ul>
							<li ng-repeat="related in vocab.related_entity" ng-if="related.type=='vocab'"><a href="" ng-click="relatedmodal('edit', related.type, related)">[[ related.title ]]</a></li>
						</ul>
						<h4>Related Tools</h4>
						<ul>
							<li ng-repeat="related in vocab.related_entity" ng-if="related.type=='tool'"><a href="" ng-click="relatedmodal('edit', related.type, related)">[[ related.title ]]</a></li>
						</ul>
						<h4>Related Services</h4>
						<ul>
							<li ng-repeat="related in vocab.related_entity" ng-if="related.type=='service'"><a href="" ng-click="relatedmodal('edit', related.type, related)">[[ related.title ]]</a></li>
						</ul>
						<div class="form-group">
							<span class="input-group-btn swatch-gray" style="background:#e9e9e9;">
								<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Add a related entity <span class="caret"></span></button>
								<ul class="dropdown-menu" role="menu">
									<li><a href="" ng-click="relatedmodal('add', 'vocab')">Related Vocabulary</a></li>
									<li><a href="" ng-click="relatedmodal('add', 'tool')">Related Tools</a></li>
									<li><a href="" ng-click="relatedmodal('add', 'service')">Related </a></li>
								</ul>
							</span>
						</div>
					</div>
				</div>
				
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="panel swatch-gray">
					<div class="panel-body">
						<a href="" class="btn btn-large btn-primary" ng-click="save()">Save</a>
						<div class="alert alert-danger element-short-top os-animation animated fadeInUp" data-os-animation="fadeInUp" ng-if="error_message">[[ error_message ]]</div>
						<div class="alert alert-success element-short-top os-animation animated fadeInUp" data-os-animation="fadeInUp" ng-if="success_message">[[ success_message ]]</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
@stop