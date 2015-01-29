var app = angular.module('app', ['ngRoute', 'ngSanitize', 'search_components', 'profile_components', 'uiGmapgoogle-maps', 'ui.utils', 'portal-filters'], function($interpolateProvider){
	$interpolateProvider.startSymbol('[[');
	$interpolateProvider.endSymbol(']]');
});

app.config(function($routeProvider, $locationProvider) {
    $locationProvider.hashPrefix('!');
});

app.config(function(uiGmapGoogleMapApiProvider) {
    uiGmapGoogleMapApiProvider.configure({
        //    key: 'your api key',
        v: '3.17',
        libraries: 'weather,geometry,visualization'
    });
});

app.controller('searchController', searchController);
app.controller('mainController', mainSearchController);