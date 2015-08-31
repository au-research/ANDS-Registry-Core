(function(){
    'use strict';

    angular.module('analytic_app')
        .controller('reportCtrl', reportCtrl);

    function reportCtrl($scope, $log, $modal, analyticFactory, filterService, org) {
        var vm = this;

        //chart configuration
        vm.types = ['Line', 'Bar'];
        vm.chartType = vm.types[0];

        //filters configuration based on current org
        vm.org = org;
        vm.filters = filterService.getFilters();
        vm.filters['groups'] = vm.org.groups;
        vm.filters['doi_app_id'] = vm.org.doi_app_id;

        $scope.$watch('vm.filters', function(data){
            if (data) vm.getRDASummaryData();
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
                    if (obj['portal_view']) {
                        vm.viewGroupChartData.data.push(obj['portal_view']);
                    } else {
                        vm.viewGroupChartData.data.push(0);
                    }
                    if (obj['portal_search']) {
                        vm.searchGroupChartData.data.push(obj['portal_search']);
                    } else {
                        vm.searchGroupChartData.data.push(0);
                    }
                });

                //parse rostat
                if (data.aggs.rostat) vm.rostat = data.aggs.rostat;
                if (data.aggs.qstat) vm.qstat = data.aggs.qstat;
            });

            //all time stats
            analyticFactory.allTimeStats(vm.filters).then(function(data){
                vm.alltime = data;
                //parse groups
                vm.viewGroupAllTimeChartData = {labels: [], data: [] }
                vm.searchGroupAllTimeChartData = {labels: [], data: [] }
                angular.forEach(data.group_event, function(obj, index){
                    vm.viewGroupAllTimeChartData.labels.push(index);
                    vm.viewGroupAllTimeChartData.data.push(obj['portal_view']);
                    vm.searchGroupAllTimeChartData.labels.push(index);
                    vm.searchGroupAllTimeChartData.data.push(obj['portal_search']);
                });
            });

            //get cited
            analyticFactory.getStat('tr', vm.filters).then(function(data){
                vm.trChartData = {
                    labels: [], data: []
                };
                angular.forEach(data, function(stat){
                    vm.trChartData.labels.push(stat.key+' Cited');
                    vm.trChartData.data.push(stat.doc_count);
                });
            });

            //get doi breakdown
            analyticFactory.getStat('doi', vm.filters).then(function(data){
                vm.doiChartData = {
                    labels: ["Missing DOI", "Has DOI"],
                    data: [data['missing_doi'], data['has_doi']]
                }
            });

            //doi activity
            analyticFactory.getStat('doi_activity', vm.filters).then(function(data){
                vm.doiActivityChartData = {
                    labels:[], data:[]
                }
                angular.forEach(data, function(doi){
                    angular.forEach(doi, function(obj, index){
                        if (vm.doiActivityChartData.labels.indexOf(obj.activity) > -1) {
                            var index = vm.doiActivityChartData.labels.indexOf(obj.activity);
                            vm.doiActivityChartData.data[index]+=obj.count;
                        } else {
                            vm.doiActivityChartData.labels.push(obj.activity);
                            vm.doiActivityChartData.data.push(obj.count);
                        }
                    });
                })
            });

            //link check
            analyticFactory.getStat('doi_client', vm.filters).then(function(data){
                vm.brokenLinksByAppID = {
                    labels:[],data:[]
                }
                vm.linkCheckerReport = [];
                angular.forEach(data, function(obj, index){
                    vm.brokenLinksByAppID.labels.push('Broken Link for : '+obj.client_name);
                    vm.brokenLinksByAppID.data.push(obj.url_broken_num);
                    vm.linkCheckerReport.push(obj.linkchecker_report);
                });
            });

            //quality level
            analyticFactory.getStat('ro_ql', vm.filters).then(function(data){
                vm.QLChartData = {
                    labels:[], data:[]
                }
                angular.forEach(data, function(obj){
                    vm.QLChartData.labels.push('Quality Level '+obj.key);
                    vm.QLChartData.data.push(obj.doc_count);
                });
            });

            //class
            analyticFactory.getStat('ro_class', vm.filters).then(function(data){
                vm.ClassChartData = {
                    labels:[], data:[]
                }
                angular.forEach(data, function(obj){
                    vm.ClassChartData.labels.push(obj.key);
                    vm.ClassChartData.data.push(obj.doc_count);
                });
            });

            //group
            analyticFactory.getStat('ro_group', vm.filters).then(function(data){
                vm.GroupChartData = {
                    labels:[], data:[]
                }
                angular.forEach(data, function(obj){
                    vm.GroupChartData.labels.push(obj.key);
                    vm.GroupChartData.data.push(obj.doc_count);
                });
            });

            //group collection
            var tmpfilters = {};
            angular.copy(vm.filters, tmpfilters);
            tmpfilters['class'] = 'collection';
            analyticFactory.getStat('ro_group', tmpfilters).then(function(data){
                vm.GroupCollectionChartData = {
                    labels:[], data:[]
                }
                angular.forEach(data, function(obj){
                    vm.GroupCollectionChartData.labels.push(obj.key);
                    vm.GroupCollectionChartData.data.push(obj.doc_count);
                });
            });
        }

        vm.onClick = function (points, evt) {
            $log.debug(points, evt);
            if (points.length > 0) {
                var date = points[0].label;
                $log.debug('Showing date ' + date);

                var data = {
                    type: 'showdate',
                    value: date,
                    filters: vm.filters
                }
                vm.showDate(data);
            }

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