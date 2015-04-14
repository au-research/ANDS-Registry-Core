var app = angular.module('app', ['ngRoute', 'ui.bootstrap', 'ngSanitize', 'search_components', 'profile_components', 'uiGmapgoogle-maps', 'ui.utils', 'portal-filters', 'record_components'], function($interpolateProvider){
	$interpolateProvider.startSymbol('[[');
	$interpolateProvider.endSymbol(']]');
});

app.config(function($routeProvider, $locationProvider) {
	// $locatonProvider.html5Mode(false);
    $locationProvider.hashPrefix('!');
});

// Dangerous hack, don't use
// app.config( ['$provide', function ($provide){
//     $provide.decorator('$browser', ['$delegate', function ($delegate) {
//         $delegate.onUrlChange = function () {};
//         $delegate.url = function () { return ""};
//         return $delegate;
//     }]);
// }]);

app.config(function(uiGmapGoogleMapApiProvider) {
    uiGmapGoogleMapApiProvider.configure({
        //    key: 'your api key',
        v: '3.17',
        libraries: 'weather,geometry,visualization'
    });
});

app.controller('searchController', searchController);
app.controller('mainController', mainSearchController);