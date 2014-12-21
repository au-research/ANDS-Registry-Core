<div class="panel panel-primary element-no-top element-no-bottom os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0.2s" style="-webkit-animation: 0.2s;">
    <div class="panel-heading">
        <h3 class="panel-title">Types</h3>
    </div>
    <div class="panel-body swatch-white">
        <div class="element-no-top element-no-bottom" data-os-animation="none" data-os-animation-delay="0s">
            <ul class="list-unstyled">
        		<li ng-repeat="type in result.facets.type | limitTo:5">
        			<input type="checkbox" ng-checked="isFacet('type', type.name)" ng-click="toggleFilter('type', type.name)">
        			<a href="" ng-click="toggleFilter('type', type.name)">[[type.name]] ([[type.value]])</a></li>
        		<li><a href="" ng-click="advanced('type')">View More...</a></li>
        	</ul>
        </div>
    </div>
</div>