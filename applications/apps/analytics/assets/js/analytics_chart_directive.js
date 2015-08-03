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
                scope.$watch('cdata', function(data){
                    if (data) scope.chart = data;
                });
            }
        }
    }

    //deprecated, due for removal
    function chartDirective_old($log, statFactory, filterService) {
        return {
            templateUrl: apps_url + 'assets/analytics/templates/chartjs.html',
            scope: {
                // filters: '=filters',
                type: '=',
                ctype: '='
            },
            link: function (scope, elem, attr, ngModel) {
                scope.chart = {};
                //$log.debug(ngModel);

                scope.filters = filterService.getFilters();

                //$log.debug(scope.filters);
                scope.$watch('filters', function(data){
                    if (data) {
                        getData();
                    }
                }, true);

                function getData() {
                    statFactory.getStat(scope.ctype, scope.filters).then(function(data){
                        if (scope.ctype=='doi') {
                            scope.chart = {
                                labels: ["Missing DOI", "Has DOI"],
                                data: [data['missing_doi'], data['has_doi']]
                            }
                        } else if (scope.ctype=='tr') {
                            scope.chart = {
                                labels: [], data: []
                            };
                            angular.forEach(data, function(stat){
                                scope.chart.labels.push(stat.key+' Cited');
                                scope.chart.data.push(stat.doc_count);
                            });
                        } else if (scope.ctype=='doi_minted') {
                            scope.chart = {
                                labels: [], data: []
                            };
                            if (data && data.length > 0) {
                                angular.forEach(data, function(stat){
                                    scope.chart.labels.push(stat.activity);
                                    scope.chart.data.push(stat.count);
                                });
                            }
                        } else if (scope.ctype=='doi_client') {
                            scope.chart = {
                                labels: [], data: []
                            };
                            if (data && data.length != 0) {
                                scope.chart = {
                                    labels: ['URL', 'Broken URL'],
                                    data: [ data['url_num'], data['url_broken_num'] ]
                                };
                            }
                        }
                    });

                }

            }
        }
    }
})();