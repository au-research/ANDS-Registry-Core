<div class="sidebar-widget widget_search os-animation animated fadeInDown">
	<h3 class="sidebar-header">Contributors</h3>
	<ul class="list-unstyled">
		<li ng-repeat="group in result.facets.group | limitTo:5">
			<input type="checkbox" ng-checked="filters['group']==group.name" ng-click="toggleFilter('group', group.name)">
			<a href="" ng-click="toggleFilter('group', group.name)">
				[[group.name]] ([[group.value]])
			</a>
		</li>
		<li><a href="" ng-click="advanced()">View More...</a></li>
	</ul>
</div>