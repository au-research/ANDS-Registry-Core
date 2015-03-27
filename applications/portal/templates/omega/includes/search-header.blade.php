<div class="container-fluid search-header">
	<div class="row swatch-white">
		<div class="col-md-2 col-lg-3">
			<p ng-hide="loading" ng-cloak ><b>[[result.response.numFound]]</b> results ([[result.responseHeader.QTime]] milliseconds)</p>
		</div>
		<div class="col-md-5 col-lg-4">
			<div class="animated fadeInDown">
				<span style="margin-right:10px">Records selected: [[selected.length]]</span>
				<a href="" class="btn btn-primary btn-sm btn-icon-right" ng-click="add_user_data('saved_record')">Save Records <span><i class="fa fa-bookmark-o"></i></span></a>
				<a href="" class="btn btn-primary btn-sm btn-icon-right" ng-click="add_user_data('export')" ng-if="filters.class=='collection'">Export <span><i class="fa fa-download"></i></span></a>
			</div>
		</div>
		<div class="col-md-5 col-lg-5 sort-box">
			<span class="btn-group">
				<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Sort by: <span ng-bind-html="filters.sort | getLabelFor:sort"></span><span class="caret"></span></button>
				<ul class="dropdown-menu" role="menu">
					<li ng-repeat="item in sort">
						<a ng-click="changeFilter('sort', item.value, true)" href="" ng-bind-html="item.label"></a>
					</li>
				</ul>
			</span>
			<span class="btn-group">
				<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Show: [[ filters.rows ]]<span class="caret"></span></button>
				<ul class="dropdown-menu" role="menu">
					<li ng-repeat="item in pp">
						<a ng-click="changeFilter('rows', item.value, true)" href="">[[ item.label ]]</a>
					</li>
				</ul>
			</span>
		</div>
	</div>
</div>