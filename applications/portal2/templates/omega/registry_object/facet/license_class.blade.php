<div class="sidebar-widget widget_search">
	<h3 class="sidebar-header">License</h3>
	<ul class="list-unstyled">
		<li ng-repeat="licence in result.facets.license_class | limitTo:5"><input type="checkbox"> <a href="">[[licence.name]] ([[licence.value]])</a></li>
		<li><a href="">View More...</a></li>
	</ul>
</div>