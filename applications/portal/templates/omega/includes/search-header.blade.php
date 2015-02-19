<div class="container-fluid">
	<div class="row swatch-white">
		<div class="col-md-3">
			<p ng-hide="loading" ng-cloak style="line-height:36px;margin:0;"><b>[[result.response.numFound]]</b> results ([[result.responseHeader.QTime]] milliseconds)</p>
		</div>
		<div class="col-md-6">
			<div class="animated fadeInDown">
				<a href="" class="btn btn-primary btn-sm btn-icon-right" ng-click="add_user_data('saved_record')">Add to Favourites <span><i class="fa fa-heart"></i></span></a>
				<a href="" class="btn btn-primary btn-sm btn-icon-right" ng-click="add_user_data('saved_record')">Export <span><i class="fa fa-download"></i></span></a>
				<a class="btn">[[selected.length]] selected record</a>
			</div>
		</div>
		<div class="col-md-3" style="text-align:right;line-height:36px;">
			Sort by: <select ng-options="item.value as item.label for item in sort" ng-model="filters.sort" ng-change="changeFilter('sort', filters.sort)"></select>
			<select ng-options="item.value as item.label for item in pp" ng-model="filters.rows" ng-change="changeFilter('rows', filters.rows)"></select>
		</div>
	</div>
</div>