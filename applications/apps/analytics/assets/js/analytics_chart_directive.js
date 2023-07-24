(function(){
    'use strict';
    angular.module('analytic_app')
        .directive('chart', chartDirective);


    function chartDirective($log, $modal, filterService) {
        return {
            templateUrl: apps_url + 'assets/analytics/templates/chartjs.html',
            scope: {
                cdata: '=',
                type: '='
            },
            link: function(scope, elem, attr, ngModel) {
                scope.chart = {};
                scope.haszero = false;
                scope.showzero = false;

                //click handler for clicking on chart
                scope.click = function(points, evt) {
                    var label = points[0].label;
                    scope.openModal(label);
                }

                scope.openModal = function(label) {
                    var type = '';
                    if (scope.type == 'doiChartData') {
                        if (label == 'Missing DOI') {
                            type = 'missing_doi';
                        } else if (label == 'Has DOI') {
                            type = 'has_doi';
                        }
                    } else if(scope.type == 'portal_cited') {
                        var value = label.split(' ')[0];

                    }

                    var data = {
                        type: type,
                        filters: filterService.getFilters()
                    }

                    $modal.open({
                        templateUrl:apps_url+'assets/analytics/templates/modalDetail.html',
                        controller: 'modalDetailCtrl as vm',
                        resolve : {
                            data: function() {
                                return data;
                            }
                        }
                    });
                }

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