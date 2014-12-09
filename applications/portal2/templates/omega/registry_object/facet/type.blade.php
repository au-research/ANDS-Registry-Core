<div class="sidebar-widget widget_search os-animation animated fadeInDown">
	<h3 class="sidebar-header">Types</h3>
	<ul class="list-unstyled">
		<li ng-repeat="type in result.facets.type | limitTo:5">
			<input type="checkbox" ng-checked="filters['type']==type.name" ng-click="toggleFilter('type', type.name)">
			<a href="" ng-click="toggleFilter('type', type.name)">[[type.name]] ([[type.value]])</a></li>
		<li><a href="" ng-click="advanced()">View More...</a></li>
	</ul>
</div>