(function(){
    'use strict';

    angular.module('analytic_app')
        .controller('rdaCtrl', rdaCtrl);

    function rdaCtrl($scope, $log, $modal, analyticFactory, filterService, org) {
        var vm = this;

        //chart configuration
        vm.types = ['Line', 'Bar'];
        vm.chartType = vm.types[0];

        //filters configuration based on current org
        vm.org = org;
        vm.filters = filterService.getFilters();
        vm.filters['groups'] = vm.org.groups;

        $scope.$watch('vm.filters', function(data){
            if (data) vm.getRDASummaryData(vm.filters);
        }, true);

        vm.getRDASummaryData = function() {
            analyticFactory.summary(vm.filters).then(function(data){
                //parse date into data
                vm.rdaChartData = {
                    labels: [],
                    series: ['View', 'Search'],
                    data: [[],[]]
                };
                angular.forEach(data.dates, function (obj, index) {
                    vm.rdaChartData.labels.push(index);
                    vm.rdaChartData.data[0].push(obj['portal_view']);
                    if (obj['portal_search']) {
                        vm.rdaChartData.data[1].push(obj['portal_search'])
                    } else {
                        vm.rdaChartData.data[1].push(0);
                    }
                });

                //parse groups
                vm.viewGroupChartData = {labels: [], data: [] }
                vm.searchGroupChartData = {labels: [], data: [] }
                angular.forEach(data.group_event, function(obj, index){
                    vm.viewGroupChartData.labels.push(index);
                    vm.searchGroupChartData.labels.push(index);
                    vm.viewGroupChartData.data.push(obj['portal_view']);
                    vm.searchGroupChartData.data.push(obj['portal_search']);
                });

                //parse rostat
                if (data.aggs.rostat) vm.rostat = data.aggs.rostat;
                if (data.aggs.qstat) vm.qstat = data.aggs.qstat;
            });

            analyticFactory.allTimeStats(vm.filters).then(function(data){
                vm.alltime = data;
                //parse groups
                vm.viewGroupAllTimeChartData = {labels: [], data: [] }
                vm.searchGroupAllTimeChartData = {labels: [], data: [] }
                angular.forEach(data.group_event, function(obj, index){
                    vm.viewGroupAllTimeChartData.labels.push(index);
                    vm.searchGroupAllTimeChartData.labels.push(index);
                    vm.viewGroupAllTimeChartData.data.push(obj['portal_view']);
                    vm.searchGroupAllTimeChartData.data.push(obj['portal_search']);
                });
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



    }

})();