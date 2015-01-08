<div class="panel-body swatch-white">
	<span ng-show="loading"><i class="fa fa-refresh fa-spin"></i> Loading...</span>
	<small style="float:right" ng-hide="loading"><b>[[result.response.numFound]]</b> Results found ([[result.responseHeader.QTime]] milliseconds)</small>
	<div class="search-toolbar" ng-hide="loading">
		<div style="width:auto;display:inline-block;">
			<select ng-options="item.value as item.label for item in pp" ng-model="filters.rows" ng-change="changeFilter('rows', filters.rows)"></select>
		</div>
		<div style="width:auto;display:inline-block;">
			<select ng-options="item.value as item.label for item in sort" ng-model="filters.sort" ng-change="changeFilter('sort', filters.sort)"></select>
		</div>
	</div>
</div>