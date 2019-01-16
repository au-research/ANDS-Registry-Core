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
@section('script')
  var vocab_resolving_services = {{json_encode(\ANDS\Util\config::get('vocab.vocab_resolving_services'))}};
  var subject_vocab_proxy = {{json_encode(\ANDS\Util\config::get('app.subject_vocab_proxy'))}};
@stop
@section('content')
<section ng-controller="addVocabsCtrl" class="section swatch-white">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<header class="section-text-shadow section-innder-shadow element-short-top element-short-bottom">
					<h1 class="hairline bordered-normal break" ng-non-bindable>
						@if($vocab)
						{{ htmlspecialchars($vocab->title) }}
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
			<!-- Because of custom handling of the Enter key for Top Concepts,
				do not use _any_ buttons with type="submit" in this form. -->
			<div class="row">
				<div class="col-md-8">
					<div class="panel swatch-gray">
						<!-- <div class="panel-heading">Vocabulary Metadata</div> -->
						<div class="panel-body">
							<div class="form-group" ng-class="{ 'has-error' : form.cms.title.$invalid }">
								<label for="">Vocabulary Title
									<span ng-bind-html="confluenceTip('VocabularyTitle')"></span>
								</label>
								<input type="text" required class="form-control" ng-model="vocab.title" name="title" placeholder="Vocabulary Title">
								<p ng-show="form.cms.title.$invalid" class="help-block">Vocabulary Title is required.</p>
							</div>
							<div class="form-group">
								<label for="">Vocabulary Acronym
									<span ng-bind-html="confluenceTip('VocabularyAcronym')"></span>
								</label>
								<input type="text" class="form-control" ng-model="vocab.acronym" name="acronym" placeholder="Vocabulary Acronym">
							</div>
							<div class="form-group" ng-class="{ 'has-error' : form.cms.description.$invalid }">
								<label for="">Vocabulary Description
									<span ng-bind-html="confluenceTip('VocabularyDescription')"></span>
								</label>
								<textarea class="form-control" ng-model="vocab.description" placeholder="Vocabulary Description" rows="10" required name="description"></textarea>
								<p ng-show="form.cms.description.$invalid" class="help-block">Vocabulary Description is required.</p>
							</div>
							<div class="form-group">
								<label for="">Vocabulary Licence
									<span ng-bind-html="confluenceTip('VocabularyLicence')"></span>
								</label>
								<select name="" id="" class="form-control caret-for-select" placeholder="vocab Licence" ng-options="lic for lic in licence" ng-model="vocab.licence"><option value="">No selection</option></select>
							</div>

							<div class="form-group" ng-class="{ 'has-error' : form.cms.creation_date.$invalid }">
								<label for="">Vocabulary Creation Date
									<span ng-bind-html="confluenceTip('VocabularyCreationDate')"></span>
								</label>
								<p class="input-group">
									<input type="text" id="creation_date" class="form-control" required name="creation_date" placeholder="Vocabulary Creation Date (supported formats: YYYY-MM-DD, YYYY-MM, YYYY)" ng-model="vocab.creation_date" datepicker-popup="yyyy-MM-dd" is-open="$parent.opened" >
									<span class="input-group-btn">
										<button type="button" class="btn btn-default" ng-click="open($event)"><i class="glyphicon glyphicon-calendar"></i></button>
									</span>
								</p>
								<p ng-show="form.cms.creation_date.$invalid" class="help-block">Vocabulary Creation Date is required. Supported formats: YYYY-MM-DD, YYYY-MM, YYYY.</p>
							</div>
							<div class="form-group">
								<label for="">Revision Cycle
									<span ng-bind-html="confluenceTip('RevisionCycle')"></span>
								</label>
								<input type="text" class="form-control" ng-model="vocab.revision_cycle" placeholder="Revision Cycle">
							</div>
							<div class="form-group">
								<label for="">Note
									<span ng-bind-html="confluenceTip('Note')"></span>
								</label>
								<textarea class="form-control" ng-model="vocab.note" placeholder="Notes" rows="10"></textarea>
							</div>
							@if(null!=$this->user->affiliations())
							<div class="form-group"  ng-class="{ 'has-error' : form.cms.owner.$invalid }">
								<label for="owner">Owner
									<span ng-bind-html="confluenceTip('Owner')"></span>
								</label>
     							<select name="owner" id="owner" required class="form-control caret-for-select" placeholder="vocab Owner" ng-options="owner.id as owner.name for owner in user_orgs_names" ng-model="vocab.owner" ng-if="user_orgs.length>1"></select>
                                <select name="owner" id="owner" required class="form-control" placeholder="vocab Owner"  ng-if="user_orgs.length==1 && !vocab.owner" ng-model="vocab.owner" ng-options="owner.id as owner.name for owner in user_orgs_names" ng-init="vocab.owner=user_orgs[0]"/> </select>
                                <select name="owner" id="owner" required class="form-control" placeholder="vocab Owner" ng-options="owner.id as owner.name for owner in user_orgs_names" ng-if="user_orgs.length==1 && vocab.owner.length > 0" ng-model="vocab.owner"/></select>
                                <p ng-show="form.cms.owner.$invalid" class="help-block">To give editing rights to others in your organisation, please select the appropriate organisational Owner.</p>
							</div>
							@endif
						</div>
					</div>

					<div class="panel swatch-gray">
						<div class="panel-heading">Top Concepts
							<span ng-bind-html="confluenceTip('TopConcepts')"></span>
						</div>
						<div class="panel-body">
							<div class="input-group" ng-repeat="concept in vocab.top_concept track by $index" top-concepts-enter>
								<input type="text" class="form-control" placeholder="New Top Concept" ng-model="vocab.top_concept[$index]">
								<span class="input-group-btn">
									<button class="btn btn-primary btn-primary-warning" type="button"
										ng-click="vocab.top_concept.splice($index, 1)" title="Remove this top concept"><i class="fa fa-remove"></i></button>
								</span>
							</div>

							<p></p>

							<button class="btn btn-primary" type="button" ng-click="addtolist('top_concept')"><i class="fa fa-plus"></i> Add Top Concept</button>

						</div>
					</div>

					<div class="panel swatch-gray">
						<div class="panel-heading">Languages
							<span ng-bind-html="confluenceTip('Languages')"></span>
						</div>
						<div class="panel-body">

							<!-- Show one language field. Doesn't use input-group. -->
							<div ng-repeat="ln in vocab.language track by $index" ng-show="vocab.language.length == 1">
								<select name="vlanguage" id="vLanguage" class="form-control caret-for-select"
									placeholder="Select a language" ng-options="lang.value as lang.text for lang in langs" ng-model="vocab.language[$index]"><option value="">Select a language</option></select>
							</div>

							<!-- Show more than one language field. Does use input-group. -->
							<div class="input-group" ng-repeat="ln in vocab.language track by $index" ng-show="vocab.language.length > 1">
								<select name="vlanguage" id="vLanguage" class="form-control caret-for-select"
									placeholder="Select a language" ng-options="lang.value as lang.text for lang in langs" ng-model="vocab.language[$index]"><option value="">Select a language</option></select>
								<span class="input-group-btn">
									<button class="btn btn-primary btn-primary-warning" type="button"
										ng-click="list_remove('language', $index)" title="Remove this language"><i class="fa fa-remove"></i></button>
								</span>
							</div>


							<p></p>
							<button class="btn btn-primary" type="button" ng-click="addtolist('language')"><i class="fa fa-plus"></i> Add Language</button>

							<div class="form-group has-error" ng-show="vocab.language === undefined || array_has_no_nonempty_strings(vocab.language)">
								<p class="help-block">At least one language must be provided.</p>
							</div>

						</div>

					</div>

					<div class="panel swatch-gray">
						<div class="panel-heading">Subjects
							<span ng-bind-html="confluenceTip('Subjects')"></span>
						</div>
						<div class="panel-body">
							<table class="table">
								<thead>
									<tr><th>Subject Source</th> <th>Subject Label</th><th></th></tr>
								</thead>

								<tr ng-repeat="subject in vocab.subjects track by $index"
                                    subject-directive
                                    id="[['subject_'+$index]]"
                                    index="$index"
                                    subject_type="vocab.subjects[$index].subject_source"
                                    subject_label="vocab.subjects[$index].subject_label"
                                    subject_iri="vocab.subjects[$index].subject_iri"
                                    subject_notation="vocab.subjects[$index].subject_notation"
                                    on-close="list_remove('subjects', $index)"
                                    >
								</tr>
							</table>

							<button id="add_subject_button" class="btn btn-primary" type="button" ng-click="addtolist('subjects')"><i class="fa fa-plus"></i> Add Subject</button>

							<div class="form-group has-error" ng-show="vocab.subjects === undefined || subjects_has_no_complete_anzsrc_for_elements()">
								<p class="help-block">At least one subject drawn from the "ANZSRC Field of Research" vocabulary must be provided. Select a value from the Subject Source dropdown and select a Subject Label.</p>
							</div>

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
										<td><a href="" ng-click="versionmodal('edit', $index)">[[ version.title ]] </a></td>
										<td><span class="label" ng-class="{'deprecated': 'label-danger', 'current': 'label-success', 'superseded': 'label-warning', 'depreciated': 'label-danger'}[version.status]">[[ version.status ]]</span></td>
										<td><a href="" ng-click="list_remove('versions', $index)" title="Remove this version"><i class="fa fa-remove"></i></a></td>
									</tr>
								</tbody>
							</table>
							<a href="" class="btn btn-primary" ng-click="versionmodal('add')"><i class="fa fa-plus"></i> Add a version</a>
						</div>
					</div>

					<div class="panel swatch-gray">
						<div class="panel-heading">Related
							<span ng-bind-html="confluenceTip('RelatedMetadata')"></span>
						</div>
						<div class="panel-body">
							<table class="table">
								<thead>
									<tr><th>Title</th> <th>Type</th> <th></th></tr>
								</thead>
								<tbody>
									<tr ng-repeat="related in vocab.related_entity track by $index">
										<td><a href="" ng-click="relatedmodal('edit', related.type, $index)" tooltip="[[ related.relationship.join() ]]">[[ related.title ]]</a></td>
										<td>[[ related.type ]]</td>
										<td><a href="" ng-click="list_remove('related_entity', $index)" title="Remove this related entity"><i class="fa fa-remove"></i></a></td>
									</tr>
								</tbody>
							</table>

							<div class="btn-group">
								<button class="btn btn-primary" type="button" ng-click="relatedmodal('add', 'publisher')"><i class="fa fa-plus"></i> Add a publisher</button>
								<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><span class="caret"></span></button>
								<ul class="dropdown-menu" role="menu">
									<li><a href="" ng-click="relatedmodal('add', 'vocabulary')">Related Vocabulary</a></li>
									<li><a href="" ng-click="relatedmodal('add', 'party')">Related Party</a></li>
									<li><a href="" ng-click="relatedmodal('add', 'service')">Related Service</a></li>
								</ul>
							</div>

							<div class="has-error" ng-show="vocab.related_entity === undefined || (vocab.related_entity | filter:getPublishers).length == 0">
								<p class="help-block">At least one publisher must be provided.</p>
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
							<div class="alert alert-danger element-short-top os-animation animated fadeInUp" data-os-animation="fadeInUp" ng-show="form.cms.$invalid">There are validation errors in the form.</div>
							<div class="alert alert-success element-short-top os-animation animated fadeInUp" data-os-animation="fadeInUp" ng-if="success_message">
								<ul>
									<li ng-repeat="msg in success_message" ng-bind-html="msg">[[ msg ]]</li>
								</ul>
							</div>
                            <a href="" class="btn btn-large btn-primary btn-discard" ng-click="save('discard')">Exit Without Saving</a>
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

<!-- Placeholder for help page imported from Confluence. -->
<div id="all_help" style="display: none;"></div>

@stop
