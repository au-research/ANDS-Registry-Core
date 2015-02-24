<ui-gmap-google-map class="map-canvas" center="map.center" zoom="map.zoom" draggable="true" options="map.options" events="map.events" bounds="map.bounds" ng-if="map" ng-cloak>
    <ui-gmap-markers models="centres" coords="'self'" icon="'icon'">
        <ui-gmap-windows>
            <div ng-non-bindable>[[title]]</div>
        </ui-gmap-windows>
    </ui-gmap-markers>
</ui-gmap-google-map>