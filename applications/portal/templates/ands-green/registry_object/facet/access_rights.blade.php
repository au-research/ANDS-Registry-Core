<div ng-show="result.facets.group" class="panel panel-primary element-no-top element-no-bottom os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0.2s" style="-webkit-animation: 0.2s;" ng-cloak>
    <div class="panel-heading">
        <h3 class="panel-title">Access Rights</h3>
    </div>
    <div class="panel-body swatch-white">
        <div class=" element-no-top element-no-bottom" data-os-animation="none" data-os-animation-delay="0s">
            <ul class="listy">
        		<li ng-repeat="access_right in result.facets.access_rights | limitTo:5">
        			<input type="checkbox" ng-checked="isFacet('access_right', access_right.name)" ng-click="toggleFilter('access_right', access_right.name, true)">
        			<a href="" ng-click="toggleFilter('access_right', access_right.name, true)" title="[[access_right.name]] ([[access_right.value]])">
        				[[access_right.name]] <small>([[access_right.value]])</small>
        			</a>
        		</li>
        		<li><a href="" ng-click="advanced('access_right')">View More...</a></li>
        	</ul>
        </div>
    </div>
</div>