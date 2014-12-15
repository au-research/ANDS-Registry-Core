<div class="sidebar-widget widget_search os-animation animated fadeInDown" ng-show="result.facets.type.length > 0">
	<h3 class="sidebar-header">Types</h3>
	<ul class="list-unstyled">
		<li ng-repeat="type in result.facets.type | limitTo:5">
			<input type="checkbox" ng-checked="isFacet('type', type.name)" ng-click="toggleFilter('type', type.name)">
			<a href="" ng-click="toggleFilter('type', type.name)">[[type.name]] ([[type.value]])</a></li>
		<li><a href="" ng-click="advanced('type')">View More...</a></li>
	</ul>
</div>