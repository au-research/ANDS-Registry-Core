angular.module('test_suite_app', ['slugifier', 'ui.sortable', 'ui.tinymce', 'ngSanitize', 'ui.bootstrap', 'ui.utils']).
	factory('tests', function($http){
		return{
			list: function(){
				return promise = $http.get(apps_url+'test_suite/tests').then(function(response){ return response.data; });
			},
			do_test: function(test){
				return promise = $http.get(apps_url+'test_suite/do_test/' + test).then(function(response){ return response.data; });
			}
		}
	}).
	filter('bytes', function() {
		return function(bytes, precision) {
			if (isNaN(parseFloat(bytes)) || !isFinite(bytes)) return '-';
			if (typeof precision === 'undefined') precision = 1;
			var units = ['bytes', 'kB', 'MB', 'GB', 'TB', 'PB'],
				number = Math.floor(Math.log(bytes) / Math.log(1024));
			return (bytes / Math.pow(1024, Math.floor(number))).toFixed(precision) +  ' ' + units[number];
		}
	}).
	config(function($routeProvider){
		$routeProvider
			.when('/',{
				controller:index,
				template:$('#index_template').html()
			})
	});

function index($scope, tests) {
	$scope.tests = [];
	$scope.currentTest = {};
	tests.list().then(function(data) {
		$scope.tests = [];
		if (data.status=='OK') {
			angular.forEach(data.content, function(value){
				var test = {name:value, result:{}};
				$scope.tests.push(test);
			});
		}
	});

	$scope.do_test = function(test) {
		$scope.currentTest = test;
		$scope.currentTest.result = {};
		$scope.currentTest.result.status = 'Testing...';
		tests.do_test(test.name).then(function(data) {
			if(data.status=='OK'){
				test.result = data.content;
			} else {
				$scope.currentTest.result.status = 'Failed';
				$scope.currentTest.result.report = data;
			}
		});
	}
}