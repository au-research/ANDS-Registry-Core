<div class="sidebar-widget widget_search">
	<h3 class="sidebar-header">Contributors</h3>
	<ul class="list-unstyled">
		<li ng-repeat="group in result.facets.group | limitTo:5"><input type="checkbox"> <a href="">[[group.name]] ([[group.value]])</a></li>
		<li><a href="">View More...</a></li>
	</ul>
</div>