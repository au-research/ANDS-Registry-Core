<div class="sidebar-widget widget_search">
	<h3 class="sidebar-header">Types</h3>
	<ul class="list-unstyled">
		<li ng-repeat="type in result.facets.type | limitTo:5"><input type="checkbox"> <a href="">[[type.name]] ([[type.value]])</a></li>
		<li><a href="">View More...</a></li>
	</ul>
</div>