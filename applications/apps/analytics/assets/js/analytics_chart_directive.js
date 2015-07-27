(function(){
    'use strict';
    angular.module('analytic_app')
        .directive('chart', chartDirective)
    function chartDirective($log, $http, statFactory, filterService) {
        return {
            templateUrl: apps_url + 'assets/analytics/templates/chartjs.html',
            scope: {
                // filters: '=filters',
                type: '=',
                ctype: '='
            },
            link: function (scope, elem, attr, ngModel) {
                scope.chart = {};
                $log.debug(ngModel);

                scope.filters = filterService.getFilters();

                $log.debug(scope.filters);
                scope.$watch('filters', function(data){
                    if (data) {
                        getData();
                    }
                }, true);

                function getData() {
                    if (scope.ctype == 'doi') {
                        scope.chart = {
                            labels: ["Missing DOI", "Has DOI"],
                            data: []
                        }
                        statFactory.getStat(scope.ctype, scope.filters).then(function(data){
                            scope.chart.data = [
                                data.missing_doi,
                                data.has_doi
                            ];
                        });
                    } else if(scope.ctype=='tr') {
                        scope.chart = {
                            labels: [],
                            data: []
                        }
                        statFactory.getStat(scope.ctype, scope.filters).then(function(data){
                            angular.forEach(data, function(stat){
                                scope.chart.labels.push(stat.key+' Cited');
                                scope.chart.data.push(stat.doc_count);
                            });
                            $log.debug(scope.chart);
                        });
                    } else if (scope.ctype=='doi_minted') {
                        scope.chart = {
                            labels: [],
                            data: []
                        }
                        statFactory.getStat(scope.ctype, scope.filters).then(function(data){
                            angular.forEach(data, function(stat){
                                scope.chart.labels.push(stat.activity);
                                scope.chart.data.push(stat.count);
                            });
                            $log.debug(scope.chart);
                        });
                    }
                }

            }
        }
    }
})();