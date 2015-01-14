<div class="widget-box">
	<div class="widget-title">
		<h5><?php echo $title; ?></h5>
	</div>
	<div ui-sortable="sortableOptions" ng-model="page.<?php echo $region;?>" class="widget-content region">
		<div ng-repeat="c in page.<?php echo $region;?>">
			<div class="widget-box">
				<div class="widget-title">
					<h5>{{c.title}} <small>{{c.type}}</small></h5>
					<select ng-model="c.type">
						<option value="html">HTML Contents</option>
						<option value="gallery">Image Gallery</option>
						<option value="list_ro">List of Registry Objects</option>
						<option value="separator">Separator</option>
						<option value="search">Search Results</option>
						<option value="facet">Facet</option>
						<option value="relation">Relation</option>
					</select>
				</div>
				<div class="widget-content">
					<div ng-hide="c.editing">
						<div ng-bind-html="c.content" ng-show="c.type == 'html'"></div>
						<div ng-show="c.type=='gallery'">
							<a colorbox ng-repeat="img in c.gallery" href="{{img.src}}" rel="{{c.title}}"><img src="{{img.src}}" alt="" style="width:100px;" rel="{{c.title}}"></a>
							<br/><span class="label label-info">{{c.gallery_type}}</span>
						</div>
						<div ng-show="c.type=='list_ro'">
							<ul>
								<li ng-repeat="ro in c.list_ro">{{ro.key}}</li>
							</ul>
						</div>
						<div ng-show="c.type=='search'">
							<div ng-show="search_result[c.search.id]">
								<ul>
									<li ng-repeat="doc in search_result[c.search.id].data.result.docs"><a href="<?php echo portal_url();?>{{doc.slug}}" target="_blank">{{doc.display_title}}</a></li>
								</ul>

								<a target="_blank" href="<?php echo portal_url('search') ?>#!/{{search_result[c.search.id].filter_query}}">
									<span ng-show="c.search.view_search_text">{{c.search.view_search_text}} </span>
									<span ng-hide="c.search.view_search_text">View Full Search </span>
									({{search_result[c.search.id].data.result.numFound}} results)
								</a>
							</div>
						</div>

						<div ng-show="c.type=='facet'" ng-bind-html="search_result[c.facet.search_id].data.facet.facet_fields[c.facet.type] | facet_display"></div>

						<div ng-show="c.type=='relation'">
							<div ng-show="relationships[c.relation.key]" ng-bind-html="relationships[c.relation.key] | relationships_display:c.relation.type"></div>
						</div>

						<hr/>
						<div class="btn-group">
							<a href="" ng-click="edit(c)" class="btn"><i class="icon-pencil"></i> Edit</a>
							<a href="" data-toggle="dropdown" class="btn"><span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><a href="" ng-click="delete_blob('<?php echo $region; ?>', $index)"><i class="icon icon-trash"></i> Delete</a></li>
								<!-- <li>
									<a href="" ng-click="toggle_visible(c)">
										<span ng-show="c.hide"><i class="icon icon-eye-closed"></i> Hide</span>
										<span ng-hide="c.hide"><i class="icon icon-eye-closed"></i> Show</span>
									</a>
								</li> -->
							</ul>
						</div>
					</div>

					<div ng-show="c.editing">

						<form class="form">
							<label for="title">Title</label><input type="text" ng-model="c.title">
							<select ng-model="c.heading" ng-options="h.value as h.title for h in available_headings"></select>
							<hr/>
							<div ng-show="c.type == 'html'">
								<textarea ui-tinymce="tinymceOptions" ng-model="c.content"></textarea>
							</div>

							<div ng-show="c.type == 'gallery'">
								<select ng-model="c.gallery_type" ng-options="f.value as f.name for f in gallery_types"></select>
								<div ng-repeat="img in c.gallery">
									<div class="input-prepend input-append">
										<span class="add-on">Image Link</span>
										<input type="text" ng-model="img.src">
										<a href="" class="btn" ng-click="removeFromList(c.gallery, $index)"><i class="icon icon-remove"></i></a>
									</div>
								</div>
								<a href="" class="btn btn-primary" ng-click="addToList(c, c.gallery)"><i class="icon-white icon-plus"></i> Add Image</a>
							</div>

							<div ng-show="c.type == 'list_ro'">
								<div ng-repeat="ro in c.list_ro">
									<div class="input-prepend input-append">
										<span class="add-on">Key</span>
										<input type="text" ng-model="ro.key" ro-search>
										<a href="" class="btn" ng-click="removeFromList(c.list_ro, $index)"><i class="icon icon-remove"></i></a>
									</div>
								</div>
								<a href="" class="btn btn-primary" ng-click="addToList(c, c.list_ro)"><i class="icon-white icon-plus"></i> Add Registry Object</a>
							</div>

							<div ng-show="c.type == 'search'">

								<div class="form-horizontal">
									<div class="control-group">
										<label class="control-label" for="">Limit number of records to display</label>
										<div class="controls">
											<input type="number" min="0" max="30" ng-model="c.search.limit"/>
											</select>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label" for="">Style</label>
										<div class="controls">
											<select name="" id="" ng-model="c.search.style">
												<option value="tile">Tile</option>
												<option value="list">List</option>
											</select>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label" for="">View Full Search Text</label>
										<div class="controls">
											<input type="text" ng-model="c.search.view_search_text" placeholder="View Full Search"/>
										</div>
									</div>
									<div class="control-group">
										<div class="controls">
											<label class="checkbox">
												<input type="checkbox" ng-model="c.search.random"/> Randomize Result
											</label>
										</div>
									</div>

								</div>

								<form class="form-search" ng-submit="preview_search(c)">
									<div class="input-append">
										<input type="text" name="search-query" class="" ng-model="c.search.query" placeholder="Search Query" ui-keypress="{13:'preview_search(c)'}">
										<button type="submit" class="btn" ng-click="preview_search(c)">Preview Search</button>
										<a href="" class="btn" ng-click="addToList(c, c.search.fq)"><i class="icon icon-plus"></i> Add Filter</a>
									</div>
									
									<div class="input-prepend input-append" ng-repeat="f in c.search.fq">

										<div class="btn-group" style="display:inline-block;">
											<button class="btn dropdown-toggle" data-toggle="dropdown">{{f.name}} <span class="caret"></span></button>
											<ul class="dropdown-menu">
												<li ng-repeat="j in available_filters"><a href="" ng-click="setFilterType(f, j.value)">{{j.title}}</a></li>
											</ul>
										</div>
										<input id="{{f.id}}" type="text" ng-model="f.value" typeahead="c.value as c.label for c in suggest(f.name, f.value)" ui-keypress="{13:'preview_search(c)'}">
										<a href="" class="btn" ng-click="removeFromList(c.search.fq, $index)"><i class="icon icon-remove"></i></a>
										<div mapwidget ng-show="f.name=='spatial'"></div>
									</div>

								</form>
								<div ng-show="search_result[c.search.id]">
									<div ng-show="search_result[c.search.id].data.numFound > 0">
										<ul>
											<li ng-repeat="doc in search_result[c.search.id].data.result.docs">
												<a href="<?php echo portal_url();?>{{doc.slug}}" target="_blank">{{doc.display_title}}</a>
												<a href="" tip="Boost This Record" ng-click="addBoost(c, doc.key)"><i class="icon icon-arrow-up"></i></a>
											</li>
										</ul>
										<a target="_blank" href="<?php echo portal_url('search') ?>#!/{{search_result[c.search.id].filter_query}}">
											<span ng-show="c.search.view_search_text">{{c.search.view_search_text}} </span>
											<span ng-hide="c.search.view_search_text">View Full Search </span>
											({{search_result[c.search.id].data.result.numFound}} results)
										</a>
									</div>
									<div ng-show="search_result[c.search.id].data.numFound == 0">
										There are no search result!
									</div>
								</div>
							</div>

							<div ng-show="c.type == 'facet'">
								<form action="" class="form-inline">
									<div class="control-group">
										<label class="control-label">Facet for:</label>
										<div class="controls">
											<select ng-model="c.facet.search_id" ng-options="f.search_id as f.name for f in available_search"></select>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label">Facet On:</label>
										<div class="controls">
											<select ng-model="c.facet.type" ng-options="f.type as f.name for f in available_facets"></select>
										</div>
									</div>
								</form>
							</div>

							<div ng-show="c.type == 'relation'">
								<form action="">
									<div class="alert alert-info">Input the key to display related objects from</div>
									<div class="input-prepend input-append">
										<span class="add-on">Key</span>
										<input type="text" ng-model="c.relation.key" ro-search>
									</div>
									<div class="control-group">
										<label class="control-label">Relation:</label>
										<div class="controls">
											<select ng-model="c.relation.type" ng-options="f.type as f.name for f in available_relation_class" ng-change="preview_relation(c)"></select>
										</div>
									</div>
									<div ng-show="relationships[c.relation.key]" ng-bind-html="relationships[c.relation.key] | relationships_display:c.relation.type"></div>
								</form>
							</div>

						</form>
						<hr/>
						<a href="" ng-click="edit(c)" class="btn">Done</a>
					</div>
				</div>
			</div>
		</div>
		<hr/>
		<a href="" ng-click="addContent('<?php echo $region; ?>')" class="btn btn-primary"><i class="icon-white icon-plus"></i> Add Content</a>
	</div>
</div>