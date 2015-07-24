(function () {
    'use strict';

    angular.module("analytic_app", ['chart.js', 'daterangepicker', 'ui.bootstrap']);

    angular.module('analytic_app')
        .controller('mainCtrl', mainCtrl)

    function mainCtrl($scope, $log, $modal, analyticFactory, filterService) {
        var vm = this;
        vm.types = ['Line', 'Bar'];
        vm.chartType = vm.types[0];
        vm.filters = filterService.getFilters();
        $scope.$watch('vm.filters', function(data){
            if (data) {
                vm.getData();
            }
        }, true);

        vm.showDate = function(data) {
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

        vm.onClick = function (points, evt) {
            $log.debug(points, evt);
            var date = points[0].label;
            $log.debug('Showing date' + date);

            var data = {
                date:date,
                filters:vm.filters
            }
            vm.showDate(data);
        };

        vm.getData = function() {
            vm.loading = true;
            return analyticFactory.summary(vm.filters).then(function(data){
                vm.loading = false;
                vm.chartData = data;
            });
        }
        vm.getData();
    }

})();