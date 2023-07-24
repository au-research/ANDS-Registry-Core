<div ng-show="result.facets.group" class="panel panel-primary element-no-top element-no-bottom os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0.2s" style="-webkit-animation: 0.2s;" ng-cloak>
    <div class="panel-heading">
        <h3 class="panel-title">Spatial</h3>
    </div>
    <div class="panel-body swatch-white">
        <div class=" element-no-top element-no-bottom" data-os-animation="none" data-os-animation-delay="0s">
            @include('registry_object/facet/map')
            <a href="" class="btn btn-primary" ng-click="advanced('spatial')">View More</a>
        </div>
    </div>
</div>