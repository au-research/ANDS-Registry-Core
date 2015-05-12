var app = angular.module('app', ['ngRoute', 'ngSanitize', 'ui.bootstrap', 'ui.utils', 'angular-loading-bar']);

app.config(function($interpolateProvider, $locationProvider, $logProvider){
	$interpolateProvider.startSymbol('[[');
	$interpolateProvider.endSymbol(']]');
	$locationProvider.hashPrefix('!');
	$logProvider.debugEnabled(true);
});