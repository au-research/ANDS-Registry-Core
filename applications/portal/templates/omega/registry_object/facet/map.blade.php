<ui-gmap-google-map class="map-canvas" center="map.center" zoom="map.zoom" draggable="true" options="options" events="map.events" ng-if="map" ng-cloak>
    <ui-gmap-layer namespace="weather" type="WeatherLayer" show="showWeather"></ui-gmap-layer>
    <ui-gmap-markers models="centres" coords="'self'" icon="'icon'">
        <ui-gmap-windows>
            <div ng-non-bindable>[[title]]</div>
        </ui-gmap-windows>
    </ui-gmap-markers>
</ui-gmap-google-map>