<div ng-show="result.facets.license_class.length > 0" class="panel panel-primary element-no-top element-no-bottom os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0.2s" style="-webkit-animation: 0.2s;" ng-cloak>
    <div class="panel-heading">
        <h3 class="panel-title">License</h3>
    </div>
    <div class="panel-body swatch-white">
        <div class="element-no-top element-no-bottom" data-os-animation="none" data-os-animation-delay="0s">
            <ul class="listy">
        		<li ng-repeat="licence in result.facets.license_class | limitTo:5">
        			<input type="checkbox" ng-checked="isFacet('license_class', licence.name)" ng-click="toggleFilter('license_class', licence.name, true)">
        			<a href="" ng-click="toggleFilter('license_class', licence.name, true)">[[licence.name]] <small>([[licence.value]])</small></a></li>
        		<li><a href="" ng-click="advanced('license_class')">View More...</a></li>
        	</ul>
        </div>
    </div>
</div>