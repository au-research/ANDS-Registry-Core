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
							<input type="text" class="form-control" ng-model="vocab.top_concept" placeholder="Vocabulary Top Concept">
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
							<input type="text" class="form-control" ng-model="vocab.language" placeholder="Language">
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				
				<div class="panel swatch-gray">
					<div class="panel-heading">Publishers</div>
					<div class="panel-body">
						<ul>
							<li ng-repeat="related in vocab.related_entity" ng-if="related.type=='publisher'">[[ related.title ]]</li>
						</ul>
						<div class="form-group">
							<a href="" class="btn btn-primary"><i class="fa fa-plus"></i> Add a related publisher</a>
						</div>
					</div>
				</div>

				<div class="panel swatch-gray">
					<div class="panel-heading">Versions</div>
					<div class="panel-body">
						<ul>
							<li ng-repeat="version in vocab.versions"><a href="">[[ version.title ]]</a></li>
						</ul>
						<a href="" class="btn btn-primary" ng-click="additem('versions')"><i class="fa fa-plus"></i> Add a version</a>
					</div>
				</div>

				<div class="panel swatch-gray">
					<div class="panel-heading">Related</div>
					<div class="panel-body">
						<h4>Related Vocabularies</h4>
						<ul>
							<li ng-repeat="related in vocab.related_entity" ng-if="related.type=='vocab'"><a href="">[[ related.title ]]</a></li>
						</ul>
						<h4>Related Tools</h4>
						<ul>
							<li ng-repeat="related in vocab.related_entity" ng-if="related.type=='tool'"><a href="">[[ related.title ]]</a></li>
						</ul>
						<h4>Related Services</h4>
						<ul>
							<li ng-repeat="related in vocab.related_entity" ng-if="related.type=='service'"><a href="">[[ related.title ]]</a></li>
						</ul>
						<div class="form-group">
							<a href="" class="btn btn-primary"><i class="fa fa-plus"></i> Add a related item <span class="caret"></span></a>
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