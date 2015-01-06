<div class="panel-body swatch-white">
	<span ng-show="loading"><i class="fa fa-refresh fa-spin"></i> Loading...</span>
	<small style="float:right" ng-hide="loading"><b>[[result.response.numFound]]</b> Results found ([[result.responseHeader.QTime]] milliseconds)</small>
	<div class="search-toolbar" ng-hide="loading">
		<div style="width:98px">
			<select name="" id="">
				<option value="15">Show 15</option>
				<option value="15">Show 50</option>
				<option value="15">Show 100</option>
			</select>
		</div>
	</div>
</div>

