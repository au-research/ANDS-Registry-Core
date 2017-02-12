<?php $this->load->view('header'); ?>
<div ng-app="bulk_tag_app">
	<?php if(!$this->user->isSuperAdmin()): ?>
	<?php foreach($dataSources as $d): ?>
	<div class="ds-restrict" ds-key="<?php echo $d['key'] ?>"></div>
	<?php endforeach; ?>
	<?php endif; ?>
	<div ng-view></div>
</div>

<div id="index_template" class="hide">
	<div class="content-header">
		<h1>Bulk Tagging Tool</h1>
	</div>
	<div id="breadcrumb" style="clear:both;">
		<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
		<a href="#/" class="current">Bulk Tagging Tool</a>
		<div class="pull-right">
			<span class="label"><i class="icon-question-sign icon-white"></i> <a target="_blank" style="color:white;" href="http://services.ands.org.au/documentation/BulkTagHelp/"> Help</a></span>
		</div>
	</div>
	<div class="container-fluid">
		
		<div class="row-fluid" ng-hide="search_result" style="margin-top:20px">
			<div class="span12">
				<div class="alert alert-info">
					Loading...
				</div>
			</div>
		</div>

		<div class="row-fluid" ng-show="search_result">
			<div class="span4">
				<div class="widget-box">
					<div class="widget-title">
						<form class="form-search" ng-submit="search()" style="margin:6px;">
							<div class="input-prepend">
								<button type="submit" class="btn">Search</button>
								<input type="text" class="input-medium search-query" placeholder="Keywords" ng-model="search_query" ui-keypress="{13:'search()'}">
							</div>
							<?php if(!$this->user->isSuperAdmin()): ?>
							<a href="" class="btn btn-link" ng-click="showHidden=!showHidden" style="float:right;">Show Hidden Filters</a>
							<?php endif; ?>
						</form>
					</div>
					<div class="widget-content">
						<div class="input-prepend input-append" ng-repeat="f in filters" ng-show="!f.disable || showHidden">
							<div class="btn-group" style="display:inline-block;">
								<button class="btn dropdown-toggle" data-toggle="dropdown" ng-disabled="f.disable">{{f.name}} <span class="caret"></span></button>
								<ul class="dropdown-menu">
									<li ng-repeat="j in available_filters"><a href="" ng-click="setFilterType(f, j.value)">{{j.title}}</a></li>
								</ul>
							</div>
							<input id="{{f.id}}" type="text" ng-model="f.value" typeahead="c.value as c.label for c in suggest(f.name, f.value)" ui-keypress="{13:'search()'}" ng-disabled="f.disable">
							<a href="" class="btn" ng-click="removeFromList(filters, $index)" ng-show="!f.disable"><i class="icon icon-remove"></i></a>
							<div mapwidget ng-show="f.name=='spatial'"></div>
						</div>

						<hr>
						<a class="btn btn-small" ng-click="addFilter()"><i class="icon-plus"></i> Add Filter</a>
						<hr>
						<div ng-show="hiddenDS" class="well">
							Your search is restricted to {{hiddenDS}} data sources. <a href="" class="btn btn-link" ng-click="showHidden=!showHidden">Show Hidden Filters</a>
						</div>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title">
						<h5>Facet</h5>
					</div>
					<div class="widget-content">
						<label for="">Facet Limit: </label>
						<input type="number" ng-model="show" style="width:30px">
						<h5>Organisations &amp; groups</h5>
						<ul><li ng-repeat="f in facet_result.group | limitTo:show"><a href="" ng-click="addFilter({name:'group', value:f.name})">{{f.name}} ({{f.value}})</a></li></ul>
						<h5>Class</h5>
						<ul><li ng-repeat="f in facet_result.class | limitTo:show"><a href="" ng-click="addFilter({name:'class', value:f.name})">{{f.name}} ({{f.value}})</a></li></ul>
						<h5>License Class</h5>
						<ul><li ng-repeat="f in facet_result.license_class | limitTo:show"><a href="" ng-click="addFilter({name:'license_class', value:f.name})">{{f.name}} ({{f.value}})</a></li></ul>
					</div>
				</div>
			</div>

			<div class="span8">
				<div class="widget-box">
					<div class="widget-title">
						<h5>
							<span ng-show="selected_ro.length==0">Displaying tags for all records contained in this search.</span>
							<span ng-show="selected_ro.length>0">Displaying tags for selected records ({{selected_ro.length}})</span>
						</h5>
					</div>
					<div class="widget-content">
						<div ng-show="loading_tags" class="alert alert-info">
							<img src="<?php echo asset_url('img/ajax-loader.gif','base');?>" alt="loading"> Loading all tags based on this search...
						</div>
						<div ng-show="selected_ro.length>0" class="alert alert-info">
							Adding or removing tags will only affect the selected records ({{selected_ro.length}})
							<button class="btn btn-info" ng-click="selected_ro=[]">Clear Selected</button>
						</div>
						<div ng-hide="loading_tags">
							<div class="btn-toolbar tags" ng-show="tags_result.data.length > 0">
								<div class="btn-group" ng-repeat="tag in tags_result.data">
									<button class="btn btn-small" ng-click="addFilter({name:'tag', value:tag.name})" ng-class="{'secret': 'btn-warning'}[tag.type]">{{tag.name}} <small class="muted" ng-show="tag.value">({{tag.value}})</small></button>
									<button class="btn btn-small btn-remove" ng-click="tagAction('remove', tag.name)" ng-class="{'secret': 'btn-warning'}[tag.type]"><i class="icon icon-trash" ng-class="{'secret': 'icon-white'}[tag.type]"></i></button>
								</div>
							</div>
							<div class="alert alert-info" ng-show="tags_result.data.length == 0">No tags found in this search</div>
							<hr>
							<form class="form tag_form" ng-submit="tagAction('add')" id="add_form">
								<div class="alert alert-info" ng-show="loading">Loading... Please wait</div>
								<div class="input-prepend input-append">
									<div class="btn-group" style="display:inline-block;">
										<button class="btn dropdown-toggle" data-toggle="dropdown">{{newTagType}} <span class="caret"></span></button>
										<ul class="dropdown-menu">
											<li><a href="" ng-click="newTagType='public'">public</a></li>
											<li><a href="" ng-click="newTagType='secret'">secret</a></li>
										</ul>
									</div>
									<input type="text" ng-model="tagToAdd" typeahead="c.value as c.label for c in suggest('tag', tagToAdd) | filter:$viewValue | limitTo:5"/>
									<button type="submit" class="btn" data-loading="Loading..."><i class="icon icon-plus"></i> Add Tag</button>
									<hr>
									<label for="">Choose a theme page: </label>
									<select ng-model="tagToAdd" ng-change="newTagType='secret'">
										<option value=""></option>
										<?php foreach($themepages as $t): ?>
										<?php if($t['secret_tag']!=''): ?>
										<option value="<?php echo $t['secret_tag'];?>"><?php echo $t['title']; ?></option>
										<?php endif; ?>
										<?php endforeach; ?>
									</select>
								</div>
								<div id="status_message"></div>
							</form>
						</div>
					</div>
				</div>
				<div class="widget-box">
					<div class="widget-title">
						<h5>{{search_result.data.numFound}} results</h5>
						<select name="" id="" ng-model="perPage">
							<option value="5">Show 5</option>
							<option value="10">Show 10</option>
							<option value="30">Show 30</option>
						</select>
					</div>
					<div class="widget-content ro_box nopadding dataTables_wrapper" style="border-bottom:0">
						<div class="ro_box" ng-show="search_result">
							<ul class="ro_list">
								<li ng-repeat="ro in search_result.data.result.docs" class="ro_item" ng-click="select(ro)" ng-class="ro.selected">
									<div class="ro_item_header">
										<div class="ro_title"><a href="<?php echo registry_url('registry_object/view/{{ro.id}}'); ?>" tip="<b>{{ro.display_title}}</b> - {{ro.key}}" target="_blank">{{ro.display_title}} </a></div>
										<i class="class_icon icon-class icon-{{ro.class}}" alt="" tip="{{ro.class}}"></i>
										
									</div>
									<div class="ro_content">
										<ul class="tags">
											<li ng-repeat="tag in ro.tag">{{tag}}<span class="hide"><i class="icon icon-remove"></i></span></li>
										</ul>
									</div>
								</li>
							</ul>
						</div>

					</div>
					<div class="widget-footer" style="padding:4px">
						<ul class="pagination alternate" style="margin:0;">
							<li ng-class="minpage"><a href="" ng-Click="page(currentPage - 1)">Prev</a></li>
							<li class="disabled">
								<a href="">Page {{currentPage}} / {{maxPage}}</a>
							</li>
							<li ng-class="maxpage"><a href="" ng-click="page(currentPage + 1)">Next</a></li>
						</ul>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>



<?php $this->load->view('footer'); ?>