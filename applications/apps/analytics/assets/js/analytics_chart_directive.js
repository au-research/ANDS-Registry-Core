(function(){
    'use strict';
    angular.module('analytic_app')
        .directive('chart', chartDirective);


    function chartDirective($log) {
        return {
            templateUrl: apps_url + 'assets/analytics/templates/chartjs.html',
            scope: {
                cdata: '='
            },
            link: function(scope, elem, attr, ngModel) {
                scope.chart = {};
                scope.haszero = false;
                scope.showzero = false;
                scope.$watch('cdata', function(data){
                    if (data) {
                        scope.chart = data;
                        if (scope.chart.data) {
                            if (scope.chart.data.length < 2) {
                                scope.showzero = true;
                            }
                            angular.forEach(scope.chart.data, function(){
                                if (this == 0) scope.haszero = true;
                            });
                        }
                    }
                });
            }
        }
    }

})();