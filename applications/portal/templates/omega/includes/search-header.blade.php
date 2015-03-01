<div class="container-fluid">
	<div class="row swatch-white">
		<div class="col-md-3">
			<p ng-hide="loading" ng-cloak style="line-height:36px;margin:0;"><b>[[result.response.numFound]]</b> results ([[result.responseHeader.QTime]] milliseconds)</p>
		</div>
		<div class="col-md-6">
			<div class="animated fadeInDown">
				<a href="" class="btn btn-primary btn-sm btn-icon-right" ng-click="add_user_data('saved_record')">Add to Favourites <span><i class="fa fa-heart"></i></span></a>
				<!-- <a href="" class="btn btn-primary btn-sm btn-icon-right" ng-click="add_user_data('saved_record')">Export <span><i class="fa fa-download"></i></span></a> -->
				<a class="btn">[[selected.length]] selected record</a>
			</div>
		</div>
		<div class="col-md-3" style="text-align:right;line-height:36px;">
			<span class="btn-group">
				<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Sort by: [[ filters.sort ]]<span class="caret"></span></button>
				<ul class="dropdown-menu" role="menu">
					<li ng-repeat="item in sort">
						<a ng-click="changeFilter('sort', item.value, true)" href="">[[ item.label ]]</a>
					</li>
				</ul>
			</span>
			<span class="btn-group">
				<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Show: [[ filters.rows ]]<span class="caret"></span></button>
				<ul class="dropdown-menu" role="menu">
					<li ng-repeat="item in pp">
						<a ng-click="filters.rows=item.value" href="">[[ item.label ]]</a>
					</li>
				</ul>
			</span>
		</div>
	</div>
</div>