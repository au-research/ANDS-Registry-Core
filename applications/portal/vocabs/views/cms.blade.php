@extends('layout/vocab_layout')

@section('og-description')
@if(gettype($vocab) == "object" && isset($vocab->prop))
	<?php
		$clean_description = htmlspecialchars(substr(str_replace(array('"','[[',']]'), '', $vocab->prop['description']), 0, 200));
	?>
@endif
@if(isset($clean_description))
	<meta ng-non-bindable property="og:description" content="{{ $clean_description }}" />
@else
	<meta ng-non-bindable property="og:description" content="Find, access, and re-use vocabularies for research" />
@endif
@stop
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
						<a href="" class="btn btn-primary" ng-click="populate(project)">Use this PoolParty Project</a>
					<!--	<a href="" class="btn btn-link" ng-click="skip()">Skip</a>
						<p class="help-block">Skipping will start a blank Vocabulary</p> -->
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="container" ng-if="decide">
		<div class="alert alert-success element-short-top os-animation animated fadeInUp" data-os-animation="fadeInUp" ng-if="success_message">
			<ul> <li ng-repeat="msg in success_message" ng-bind-html="msg">[[ msg ]]</li> </ul>
		</div>
		<form name="form.cms" novalidate>
			<div class="row">
				<div class="col-md-8">
					<div class="panel swatch-gray">
						<!-- <div class="panel-heading">Vocabulary Metadata</div> -->
						<div class="panel-body">
							<div class="form-group" ng-class="{ 'has-error' : form.cms.title.$invalid }">
								<label for="">Vocabulary Title</label>
								<input type="text" required class="form-control" ng-model="vocab.title" name="title" placeholder="Vocabulary Title">
								<p ng-show="form.cms.title.$invalid" class="help-block">Vocabulary Title is required.</p>
							</div>
							<div class="form-group">
								<label for="">Vocabulary Acronym</label>
								<input type="text" class="form-control" ng-model="vocab.acronym" name="acronym" placeholder="Vocabulary Acronym">
							</div>
							<div class="form-group" ng-class="{ 'has-error' : form.cms.description.$invalid }">
								<label for="">Vocabulary Description</label>
								<textarea class="form-control" ng-model="vocab.description" placeholder="Vocabulary Description" rows="10" required name="description"></textarea>
								<p ng-show="form.cms.description.$invalid" class="help-block">Vocabulary Description is required.</p>
							</div>
							<div class="form-group">
								<label for="">Vocabulary Licence</label>
								<select name="" id="" class="form-control" placeholder="vocab Licence" ng-options="lic for lic in licence" ng-model="vocab.licence"></select>
							</div>

							<div class="form-group" ng-class="{ 'has-error' : form.cms.creation_date.$invalid }">
								<label for="">Vocabulary Creation Date</label>
								<p class="input-group">
									<input type="text" id="creation_date" class="form-control" name="creation_date" required ng-model="vocab.creation_date" ng-change="setCreationDate()" placeholder="Vocabulary Creation Date" datepicker-popup="dd-MM-yyyy" is-open="$parent.opened" >
									<span class="input-group-btn">
										<button type="button" class="btn btn-default" ng-click="open($event)"><i class="glyphicon glyphicon-calendar"></i></button>
									</span>
								</p>
								<p ng-show="form.cms.creation_date.$invalid" class="help-block">Vocabulary Creation Date is required.</p>
							</div>
							<div class="form-group">
								<label for="">Revision Cycle</label>
								<input type="text" class="form-control" ng-model="vocab.revision_cycle" placeholder="Revision Cycle">
							</div>
							<div class="form-group">
								<label for="">Note</label>
								<textarea class="form-control" ng-model="vocab.note" placeholder="Notes" rows="10"></textarea>
							</div>
							@if(null!=$this->user->affiliations())
							<div class="form-group">
								<label for="">Owner</label>
								<select name="owner" id="owner" class="form-control" placeholder="vocab Owner" ng-options="owner for owner in user_orgs" ng-model="vocab.owner"></select>
							</div>
							@endif
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


							<form action="" class="form swatch-gray col-md-8" ng-submit="addtolist('top_concept', newTopConcept)">
								<div class="input-group">
									<input type="text" class="form-control" placeholder="New Top Concept" ng-model="newTopConcept">
									<span class="input-group-btn">
										<button class="btn btn-primary" type="button" ng-click="addtolist('top_concept', newTopConcept)"><i class="fa fa-plus"></i> Add</button>
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


							<form action="" class="form swatch-gray col-md-8" ng-submit="addtolist('language', newValue.language)">
								<div class="input-group">
									<span class="caret caret-for-input-suggestion"></span>
									<input type="text" ng-model="newValue.language" class="form-control input-suggestion" placeholder="Language" typeahead="lang.value as lang.text for lang in langs | filter:$viewValue" typeahead-min-length="0" typeahead-on-select="addtolist('language', newValue.language)"/>
									<span class="input-group-btn">
										<button class="btn btn-primary" type="button" ng-click="addtolist('language', newValue.language)"><i class="fa fa-plus"></i> Add</button>
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

							<form action="" class="form swatch-gray col-md-8" ng-submit="addtolist('subjects', newValue.subject)">
								<div class="form-group">
									<label for="">Subject Label</label>
									<input type="text" class="form-control" placeholder="Subject Label" ng-model="newValue.subject.subject">
								</div>
								<div class="form-group">
									<label for="">Subject Source</label>
									<select name="" id="" class="form-control" placeholder="Subject Source" ng-options="subject_source for subject_source in subject_sources" ng-model="newValue.subject.subject_source"></select>
								</div>
								<button class="btn btn-primary" ng-submit="addtolist('subjects', newValue.subject)"><i class="fa fa-plus"></i> Add Subject</button>
							</form>
						</div>
					</div>

				</div>
				<div class="col-md-4">

					<div class="panel swatch-gray" ng-if="vocab.pool_party_id">
						<div class="panel-heading">PoolParty Project Info</div>
						<div class="panel-body">
							<dl>
								<dt>PoolParty Project ID</dt>
								<dd>[[ vocab.pool_party_id ]]</dd>
							</dl>
						</div>
					</div>

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
									<tr><th>Title</th> <th>Type</th> <th></th></tr>
								</thead>
								<tbody>
									<tr ng-repeat="related in vocab.related_entity track by $index">
										<td><a href="" ng-click="relatedmodal('edit', related.type, related)" tooltip="[[ related.relationship.join() ]]">[[ related.title ]]</a></td>
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
						<div class="panel-body" ng-if="status=='idle'">
							<a href="" class="btn btn-large btn-primary" ng-click="save('draft')" ng-disabled="form.cms.$invalid">Save to draft</a>
							<a href="" class="btn btn-large btn-primary" ng-click="save('published')" ng-disabled="form.cms.$invalid">Publish</a>
							@if($vocab && $vocab->prop['status']=='published')
							<a href="" class="btn btn-large btn-primary" ng-click="save('deprecated')">Deprecate</a>
							@endif
							<div class="alert alert-danger element-short-top os-animation animated fadeInUp" data-os-animation="fadeInUp" ng-if="error_message">[[ error_message ]]</div>
							<div class="alert alert-danger element-short-top os-animation animated fadeInUp" data-os-animation="fadeInUp" ng-show="form.cms.$invalid">There are validation errors in the form</div>
							<div class="alert alert-success element-short-top os-animation animated fadeInUp" data-os-animation="fadeInUp" ng-if="success_message">
								<ul>
									<li ng-repeat="msg in success_message" ng-bind-html="msg">[[ msg ]]</li>
								</ul>
							</div>
						</div>
						<div class="panel-body" ng-if="status=='saving'">
							<i class="fa fa-refresh fa-spin"></i> Saving...
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</section>

@stop
