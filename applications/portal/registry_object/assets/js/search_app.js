(function () {
    'use strict';

    angular
        .module('app',
            ['ngRoute', 'ui.bootstrap', 'ngSanitize', 'search_components',
                'profile_components', 'uiGmapgoogle-maps', 'ui.utils', 'portal-filters', 'record_components'] )
        .config(configuration);


    function configuration($interpolateProvider, $routeProvider, $locationProvider, uiGmapGoogleMapApiProvider) {
        $interpolateProvider.startSymbol('[[');
        $interpolateProvider.endSymbol(']]');
        $locationProvider.hashPrefix('!');

        uiGmapGoogleMapApiProvider.configure({
            //    key: 'your api key',
            v: '3.17',
            libraries: 'weather,geometry,visualization'
        });
    }
})();