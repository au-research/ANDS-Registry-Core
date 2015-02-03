<div ng-show="result.facets.group" class="panel panel-primary element-no-top element-no-bottom os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0.2s" style="-webkit-animation: 0.2s;" ng-cloak>
    <div class="panel-heading">
        <h3 class="panel-title">Contributors</h3>
    </div>
    <div class="panel-body swatch-white">
        <div class=" element-no-top element-no-bottom" data-os-animation="none" data-os-animation-delay="0s">
            <ul class="listy">
        		<li ng-repeat="group in result.facets.group | limitTo:5">
        			<input type="checkbox" ng-checked="isFacet('group', group.name)" ng-click="toggleFilter('group', group.name, true)">
        			<a href="" ng-click="toggleFilter('group', group.name, true)" title="[[group.name]] ([[group.value]])">
        				[[group.name]] <small>([[group.value]])</small>
        			</a>
        		</li>
        		<li><a href="" ng-click="advanced('group')">View More...</a></li>
        	</ul>
        </div>
    </div>
</div>