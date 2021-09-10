@if(!isBot())
<div class="modal advanced-search-modal fade" id="advanced_search" role="dialog" aria-labelledby="Advanced Search" aria-hidden="true" style="z-index:9999">
	<div class="modal-dialog">
		<div class="modal-content">

			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel">Advanced Search</h4>
			</div>

			<div class="modal-body">
				<div class="container-fluid">
					<div class="row">
						<div class="col-xs-12 col-md-2" id="advaside">

							<nav class="navbar navbar-default" role="navigation">
								 <div class="navbar-header">
										<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#advmenu">
											 <span class="icon-bar"></span>
											<span class="icon-bar"></span>
											<span class="icon-bar"></span>
										</button>
										<a class="navbar-brand" href="#">Filters</a>
								 </div>
								 <div class="clearfix"></div>
								 <div class="collapse navbar-collapse" id="advmenu">
									<ul class="nav nav-pills nav-stacked">
										<li ng-repeat="field in advanced_fields" ng-class="{'active':field.active==true}">
											<a href="" ng-click="selectAdvancedField(field.name)">
												<span ng-bind-html="field.display"></span> <i class="fa fa-check" ng-show="sizeofField(field.name) > 0"></i>
											</a>
										</li>
									</ul>
								 </div>
							</nav>

						</div>
						<div class="col-xs-12 col-md-10 swatch-white" id="advbody">
							<div ng-show="isAdvancedSearchActive('terms')">
								<i class="fa fa-info" tip='
								<ul>
									<li>Use the ? symbol to perform a single character wildcard search. E.g. Organi?ations.</li>
									<li>Use the <star> symbol to perform multiple character wildcard search. E.g. Extend* </li>
									<li> Use quotes " " to perform an exact phrase search. E.g. "ice sheets"</li>
								</ul>
								'></i>
								<div ng-controller="QueryBuilderCtrl">
									<div class="alert alert-info">
										<strong>Query Construction</strong><br>
										<span ng-bind-html="output"></span>
									</div>
									<query-builder group="filter.group"></query-builder>
								</div>
							</div>

							<div ng-if="isAdvancedSearchActive(facet.name)" ng-repeat="facet in prefacets2">
								<ul class="list-unstyled" ng-if="facet.name!='subject'">
									<li ng-repeat="item in facet.value | orderObjectBy:'name'" ng-if="facet.name!='access_rights' && facet.name!='license_class'">
										<input type="checkbox" ng-checked="isPrefilterFacet(facet.name, item.name)" ng-click="togglePreFilter(facet.name, item.name, false)">
                                            <a href="" ng-click="togglePreFilter(facet.name, item.name, false)" ng-if="facet.name!='group' && facet.name!='administering_institution' && facet.name!='funders' && facet.name!='funding_scheme'">[[item.name | formatFacet ]] ([[item.value]])</a>
                                            <a href="" ng-click="togglePreFilter(facet.name, item.name, false)" ng-if="facet.name=='group' || facet.name=='administering_institution' || facet.name=='funders' || facet.name=='funding_scheme'">[[item.name]] ([[item.value]])</a>
										<span facetinfo infotype="facet.name" infovalue="item.name"></span>
									</li>
                                    <li ng-repeat="item in facet.value | sortObjectBy:'name'" ng-if="facet.name=='access_rights' || facet.name=='license_class'">
                                        <input type="checkbox" ng-checked="isPrefilterFacet(facet.name, item.name)" ng-click="togglePreFilter(facet.name, item.name, false)">
                                        <a href="" ng-click="togglePreFilter(facet.name, item.name, false)">[[item.name | formatFacet]] ([[item.value]])</a>
                                        <span facetinfo infotype="facet.name" infovalue="item.name"></span>
                                    </li>
								</ul>
							</div>

							<div ng-if="isAdvancedSearchActive('temporal')">
								<div class="alert alert-info" ng-if="earliest_year && latest_year">
									To filter your results by a time period enter a year range between [[ earliest_year ]] and [[ latest_year ]] inclusive. Open ranges can be specified by leaving one of the fields blank. Please note that adding a time period filter to your search will restrict your search to only those records in Research Data Australia which contain temporal information.
								</div>
								<div class="alert alert-info" ng-if="!earliest_year && !latest_year">
									Search results contain no time period information.
								</div>
								<label for="">From Year</label>
								<input type="text" class="form-control" ng-model="prefilters.year_from" placeholder="From Year. eg [[earliest_year]]">
								<label for="">To Year</label>
								<input type="text" class="form-control" ng-model="prefilters.year_to" placeholder="To Year. eg [[latest_year]]">
							</div>

							<div ng-if="isAdvancedSearchActive('date_range')">
								<h3>Commencement Date Range</h3>
								<select ng-model="prefilters.commence_from" ng-options="year_from as year_from for year_from in temporal_range" class="form-control">
									<option value="" style="display:none">From Year</option>
								</select>
								<select ng-model="prefilters.commence_to" ng-options="year_to as year_to for year_to in temporal_range | orderBy:year_to:true" class="form-control">
									<option value="" style="display:none">To Year</option>
								</select>
								<div class="alert alert-info">Please note that adding this filter will restrict your search to only those activity records in Research Data Australia which have a start date recorded</div>
								<h3>Completion Date Range</h3>
								<select ng-model="prefilters.completion_from" ng-options="year_from as year_from for year_from in temporal_range" class="form-control">
									<option value="" style="display:none">From Year</option>
								</select>
								<select ng-model="prefilters.completion_to" ng-options="year_to as year_to for year_to in temporal_range | orderBy:year_to:true" class="form-control">
									<option value="" style="display:none">To Year</option>
								</select>
								<div class="alert alert-info">Please note that adding this filter will restrict your search to only those activity records in Research Data Australia which have an end date recorded</div>
							</div>

							<div ng-if="isAdvancedSearchActive('funding_amount')">
								<h4>Funding Amount <i class="fa fa-info" tip="Funding amount should be entered in whole dollars with no comma or $ sign"></i></h4>
								<label for="">Funding From</label>
								<input type="text" ng-model="prefilters.funding_from" class="form-control" placeholder="Funding From"/>
								<label for="">Funding To</label>
								<input type="text" ng-model="prefilters.funding_to" class="form-control" placeholder="Funding To"/>
							</div>



							<div ng-if="isAdvancedSearchActive('subject')">
								<div>
									<div class="btn-group pull-left">
										<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
											Vocabulary [[ vocab | getLabelFor:vocab_choices ]] <span class="caret"></span>
										</button>
										<ul class="dropdown-menu" role="menu">
											<li ng-repeat="c in vocab_choices"><a href="" ng-click="setVocab(c.value)">[[c.value | getLabelFor:vocab_choices ]]</a></li>
										</ul>
										<button type="button" class="btn btn-link" ng-if="loading_subjects"><i class="fa fa-refresh fa-spin"></i></button>
										<button class="btn btn-link" ng-if="sizeofField('subject') > 0" ng-click="clearSubject()">Clear All <i class="fa fa-remove"></i></button>
									</div>

									<div class="clearfix"></div>
									<ul class="tree" ng-if="vocab_tree_tmp" ng-cloak>
										<li ng-repeat="item in vocab_tree_tmp | orderObjectBy:'prefLabel'">
                           					<input type="checkbox" ng-checked="isVocabSelected(item, prefilters)" ui-indeterminate="isVocabParentSelected(item)" ng-click="togglePreFilter(vocab, item.notation, false)">
											<a href="" ng-click="getSubTree(item)" ng-if="item.has_narrower">[[item.prefLabel | toTitleCase]] ([[ item.collectionNum ]])</a> 											<span ng-if="!item.has_narrower">[[item.prefLabel | toTitleCase]] ([[ item.collectionNum ]])</span>
											<ul ng-if="item.subtree && item.showsubtree">
												<li ng-repeat="item2 in item.subtree |orderObjectBy:'prefLabel'">
													<input type="checkbox" ng-checked="isVocabSelected(item2, prefilters)" ui-indeterminate="isVocabParentSelected(item2)" ng-click="togglePreFilter(vocab, item2.notation, false)">
													<a href="" ng-click="getSubTree(item2)" ng-if="item2.has_narrower">[[item2.prefLabel | toTitleCase]] ([[ item2.collectionNum ]])</a>
													<span ng-if="!item2.has_narrower">[[item2.prefLabel | toTitleCase]] ([[ item2.collectionNum ]])</span>
													<ul ng-if="item2.subtree && item2.showsubtree">
														<li ng-repeat="item3 in item2.subtree |orderObjectBy:'prefLabel'"">
															<input type="checkbox" ng-checked="isVocabSelected(item3, prefilters)" ui-indeterminate="isVocabParentSelected(item3)" ng-click="togglePreFilter(vocab, item3.notation, false)">
															<a href="" ng-click="getSubTree(item3)" ng-if="item3.has_narrower">[[item3.prefLabel | toTitleCase]] ([[ item3.collectionNum ]])</a>
															<span ng-if="!item3.has_narrower">[[item3.prefLabel | toTitleCase]] ([[ item3.collectionNum ]])</span>
														</li>
													</ul>
												</li>
											</ul>
										</li>
									</ul>

								</div>
							</div>

							<div ng-if="isAdvancedSearchActive('spatial')">
								<i class="fa fa-info" tip="
									To define a search region
									<ul>
										<li>Select the box tool.</li>
										<li>Click on the map and drag the mouse to draw a rectangle.</li>
										<li>Release the mouse to finish.</li>
									</ul>
								"></i>
								<a href="" ng-click="clearMap()" ng-if="searchBox">Clear Map</a>
								@include('registry_object/facet/map')
								<div class="alert alert-info">
									Note: Adding a Location filter will restrict your search to only records that have location information described.
								</div>
							</div>

							<div ng-if="isAdvancedSearchActive('review')">
								<div class="panel panel-primary" ng-cloak>
									<div class="panel-heading">Current Search</div>
									<!-- <div class="panel-body swatch-white">
											[[filters]]
									</div> -->
									<div class="panel-body swatch-white">
										<div ng-repeat="(name, value) in prefilters" ng-if="showFilter(name, 'advanced')">
										    <h4 ng-if="(name!='q' && value!='') || (name=='q' && !prefilters.cq) ">[[name | filter_name]]</h4>
										    <h4 ng-if="name=='q' && prefilters.cq">Search Terms</h4>
										    <ul class="listy no-bottom" ng-show="isArray(value) && (name!='anzsrc-for' && name!='anzsrc-seo' && name!='anzsrc-for-2020' && name!='anzsrc-seo-2020')">
										        <li ng-repeat="v in value track by $index">
										            <a href="" ng-click="togglePreFilter(name, v, true)">[[ v | truncate:30 ]]<small><i class="fa fa-remove" tip="Remove Item"></i></small> </a>
										        </li>
										    </ul>
										    <ul class="listy no-bottom" ng-show="isArray(value)===false && (name!='anzsrc-for' && name!='anzsrc-seo'  && name!='anzsrc-for-2020' && name!='anzsrc-seo-2020') && value!=''">
										        <li>
										            <a href="" ng-click="togglePreFilter(name, value, true)">
														<span ng-if="name!='related_party_one_id'">[[ value |  formatFacet | truncate:30 ]]</span>
										                <span ng-if="name=='related_party_one_id'" resolve-ro roid="value">[[value]]</span>
										                <small><i class="fa fa-remove" tip="Remove Item"></i></small>
										            </a>
										        </li>
										    </ul>
										    <div resolve ng-if="name=='anzsrc-for'" subjects="value" vocab="'anzsrc-for'" prefilter="true"></div>
											<div resolve ng-if="name=='anzsrc-for-2020'" subjects="value" vocab="'anzsrc-for-2020'" prefilter="true"></div>
										    <div resolve ng-if="name=='anzsrc-seo'" subjects="value" vocab="'anzsrc-seo'" prefilter="true"></div>
											<div resolve ng-if="name=='anzsrc-seo-2020'" subjects="value" vocab="'anzsrc-seo-2020'" prefilter="true"></div>
										</div>
										<div class="panel-body swatch-white">
											<a href="" class="btn btn-primary" ng-click="advancedSearch();"><i class="fa fa-search"></i> Search</a>
										</div>
									</div>
								</div>
								<hr>
								<div class="well">
									<b>[[ preresult.response.numFound ]]</b> result(s) found with these filters. Hit <a href="" ng-click="advancedSearch();">Search</a>
								</div>
							</div>

							<div ng-if="isAdvancedSearchActive('help')">
								@include('includes/help-adv-search')
							</div>

						</div>

					</div>
				</div>
			</div>
			<div class="modal-footer swatch-white">
                <div class=" pull-left" style="vertical-align:bottom;padding-right:5px;padding-top:5px"><h4> Search for </h4></div>
                <div class="btn-group pull-left">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <span classicon fclass="filters.class"></span> [[ prefilters.class | getLabelFor:class_choices ]] <span class="caret"></span>
                    </button>
					<ul class="dropdown-menu" role="menu">
						<li ng-repeat="c in class_choices"><a href="" ng-click="prefilters.class=c.value"><span classicon fclass="c.value"></span> [[c.value | getLabelFor:class_choices ]]</a></li>
					</ul>
				</div>
				<button type="button" class="btn btn-link" ng-click="advanced('close');">Cancel</button>
				<button type="button" class="btn btn-primary" ng-click="advancedSearch();">Search</button>
			</div>
		</div>
	</div>
</div>
@endif