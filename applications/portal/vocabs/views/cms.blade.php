@extends('layout/vocab_layout')
@section('content')
<section ng-controller="addVocabsCtrl" class="section swatch-white">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<header class="section-text-shadow section-innder-shadow element-short-top element-short-bottom">
					<h1 class="hairline bordered-normal">Add a new Vocabulary</h1>
					[[status]]
				</header>
			</div>
		</div>
	</div>

	<div class="container">
		<div class="row">
			<div class="col-md-8">
				<div class="panel swatch-gray">
					<div class="panel-heading">Vocabulary Metadata</div>
					<div class="panel-body">
						<div class="form-group">
							<input type="text" class="form-control" ng-model="vocab.title" placeholder="Vocabulary Title">
						</div>
						<div class="form-group">
							<input type="text" class="form-control" ng-model="vocab.uri" placeholder="Vocabulary URI">
						</div>
						<div class="form-group">
							<textarea class="form-control" ng-model="vocab.description" placeholder="Vocabulary Description" rows="10"></textarea>
						</div>
						<div class="form-group">
							<input type="text" class="form-control" ng-model="vocab.licence_uri" placeholder="Vocabulary Licence URI">
						</div>
						<div class="form-group">
							<input type="text" class="form-control" ng-model="vocab.top_concept" placeholder="Vocabulary Top Concept">
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
					<div class="panel-heading">Vocabulary Logo</div>
					<div class="panel-body">
						<input type="text" class="form-control element-shorter-bottom" ng-model="vocab.title" placeholder="Vocabulary Logo URL">
					</div>
				</div>
				<div class="panel swatch-gray">
					<div class="panel-heading"></div>
					<div class="panel-body">
						<input type="text" class="form-control" ng-model="vocab.poolparty_id" placeholder="Pool Party ID">
					</div>
					<div class="panel-body">
						<input type="text" class="form-control" ng-model="vocab.publisher_id" placeholder="Publisher ID">
					</div>
				</div>
				<div class="panel swatch-gray">
					<div class="panel-heading">Related</div>
					<div class="panel-body">
						<div class="form-group">
							<a href="" class="btn btn-primary"><i class="fa fa-plus"></i> Add a related publisher</a>
						</div>
						<div class="form-group">
							<a href="" class="btn btn-primary"><i class="fa fa-plus"></i> Add a related vocabulary</a>
						</div>
					</div>
				</div>
				<div class="panel swatch-gray">
					<div class="panel-heading">Versions</div>
					<div class="panel-body">
						<ul>
							<li ng-repeat="version in vocab.versions">[[ version.title ]]</li>
						</ul>
						<a href="" class="btn btn-primary"><i class="fa fa-plus"></i> Add a version</a>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="panel swatch-gray">
					<div class="panel-body">
						<a href="" class="btn btn-large btn-primary">Save</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
@stop