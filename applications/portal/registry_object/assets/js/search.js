var app = angular.module('app', ['ngRoute', 'ngSanitize', 'portal-filters', 'ui.bootstrap', 'ui.utils', 'profile_components', 'record_components', 'queryBuilder', 'lz-string', 'angular-loading-bar', 'uiGmapgoogle-maps']);

app.config(function($interpolateProvider, $locationProvider, $logProvider){
	$interpolateProvider.startSymbol('[[');
	$interpolateProvider.endSymbol(']]');

	$locationProvider.hashPrefix('!');

	$logProvider.debugEnabled(true);
});


app.config(function(uiGmapGoogleMapApiProvider) {
    uiGmapGoogleMapApiProvider.configure({
        //    key: 'your api key',
        v: '3.17',
        libraries: 'weather,drawing,geometry,visualization'
    });
});