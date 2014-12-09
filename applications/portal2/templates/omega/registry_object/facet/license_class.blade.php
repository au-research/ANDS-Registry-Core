<div class="sidebar-widget widget_search os-animation animated fadeInDown">
	<h3 class="sidebar-header">License</h3>
	<ul class="list-unstyled">
		<li ng-repeat="licence in result.facets.license_class | limitTo:5">
			<input type="checkbox" ng-checked="filters['license_class']==licence.name" ng-click="toggleFilter('license_class', licence.name)">
			<a href="" ng-click="toggleFilter('license_class', licence.name)">[[licence.name]] ([[licence.value]])</a></li>
		<li><a href="" ng-click="advanced()">View More...</a></li>
	</ul>
</div>