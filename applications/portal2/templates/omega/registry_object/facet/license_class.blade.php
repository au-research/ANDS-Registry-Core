<div class="sidebar-widget widget_search os-animation animated fadeInDown" ng-show="result.facets.license_class.length > 0">
	<h3 class="sidebar-header">License</h3>
	<ul class="list-unstyled">
		<li ng-repeat="licence in result.facets.license_class | limitTo:5">
			<input type="checkbox" ng-checked="isFacet('license_class', licence.name)" ng-click="toggleFilter('license_class', licence.name)">
			<a href="" ng-click="toggleFilter('license_class', licence.name)">[[licence.name]] ([[licence.value]])</a></li>
		<li><a href="" ng-click="advanced('license_class')">View More...</a></li>
	</ul>
</div>