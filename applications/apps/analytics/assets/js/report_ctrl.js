(function(){
    'use strict';

    angular.module('analytic_app')
        .controller('reportCtrl', reportCtrl);

    function reportCtrl($scope, $log, $modal, analyticFactory, filterService, org) {
        var vm = this;

        //chart configuration
        vm.types = ['Line', 'Bar'];
        vm.chartType = vm.types[1];

        //filters configuration based on current org
        vm.org = org;
        vm.filters = filterService.getFilters();

        vm.filters['groups'] = vm.org.groups;
        // console.log( vm.org );
        vm.filters['record_owner'] = org.role_id;

        if (vm.org.name=='Masterview') {
            vm.filters['Masterview'] = true;
        }


        vm.all_time_views = [
            {id:'popular_records', label:'Popular Record(s)'},
            {id:'popular_search', label:'Popular Search Term(s)'},
            {id:'popular_data', label:'Popular Data accessed'},
            {id:'view_by_group', label:'View Breakdown By Group'},
            {id:'search_by_group', label:'Search Breakdown By Group'},
            {id:'tr_cited', label:'Thomson Reuter cited'}
        ]
        vm.all_time_view = vm.all_time_views[0];

        var dsids = [];
        angular.forEach(vm.org.data_sources, function(ds){
            dsids.push(ds.data_source_id);
        });
        vm.filters['data_sources'] = dsids;
        vm.filters['doi_app_id'] = vm.org.doi_app_id;

        vm.classNames = ['collection', 'party', 'service', 'activity'];
        vm.toggleSelection = filterService.toggleSelection;

        filterService.registerAvailableFilters(vm.org.groups, "groups");
        filterService.registerAvailableFilters(vm.org.data_sources, "data_sources");
        vm.availableFilters = filterService.getAvailableFilters();
        //$log.debug(vm.availableFilters);

        $scope.$watch('vm.filters', function(data){
            if (data) vm.getRDASummaryData();
        }, true);

        vm.getRDASummaryData = function() {
            analyticFactory.summary(vm.filters).then(function(data){

                vm.rdaChartData = {data: {}};

                if (data.dates.length == 0) {
                    //no data, set existing data dates to 0
                    angular.forEach(vm.rdaChartData.data, function(obj){
                        for (var i=0; i< obj.length ;i++) {
                            obj[i] = 0;
                        }
                    });
                } else {
                    //some data, set some data to graph
                    vm.rdaChartData = {
                        labels: [],
                        series: ['View', 'Search', 'Accessed'],
                        data: [[],[],[]]
                    };

                    angular.forEach(data.dates, function (obj, index) {
                        vm.rdaChartData.labels.push(index);
                        vm.rdaChartData.data[0].push(obj['portal_view']);
                        if (obj['portal_search']) {
                            vm.rdaChartData.data[1].push(obj['portal_search'])
                        }else {
                            vm.rdaChartData.data[1].push(0);
                        }
                        if (obj['portal_accessed']) {
                            vm.rdaChartData.data[2].push(obj['portal_accessed'])
                        }else {
                            vm.rdaChartData.data[2].push(0);
                        }
                    });
                }

                //$log.debug(data);
                //$log.debug(vm.rdaChartData);

                //parse groups
                vm.viewGroupChartData = {labels: [], data: [] }
                vm.searchGroupChartData = {labels: [], data: [] }
                vm.accessedGroupChartData = {labels: [], data: [] }
                angular.forEach(data.group_event, function(obj, index){
                    vm.viewGroupChartData.labels.push(index);
                    vm.searchGroupChartData.labels.push(index);
                    vm.accessedGroupChartData.labels.push(index);
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
                    if (obj['portal_accessed']) {
                        vm.accessedGroupChartData.data.push(obj['portal_accessed']);
                    } else {
                        vm.accessedGroupChartData.data.push(0);
                    }
                });

                //parse rostat
                if (data.aggs.rostat) vm.rostat = data.aggs.rostat;
                if (data.aggs.viewedstat) vm.viewedstat = data.aggs.viewedstat;
                if (data.aggs.qstat) vm.qstat = data.aggs.qstat;
                if (data.aggs.accessedstat) vm.accessedstat = data.aggs.accessedstat;

            });

            //all time stats
            analyticFactory.allTimeStats(vm.filters).then(function(data){
                vm.alltime = data;
                //parse groups
                vm.viewGroupAllTimeChartData = {labels: [], data: [] }
                vm.searchGroupAllTimeChartData = {labels: [], data: [] }
                vm.accessedGroupAllTimeChartData = {labels: [], data: [] }
                angular.forEach(data.group_event, function(obj, index){
                    vm.viewGroupAllTimeChartData.labels.push(index);
                    vm.viewGroupAllTimeChartData.data.push(obj['portal_view']);
                    vm.searchGroupAllTimeChartData.labels.push(index);
                    vm.searchGroupAllTimeChartData.data.push(obj['portal_search']);
                    vm.accessedGroupAllTimeChartData.labels.push(index);
                    vm.accessedGroupAllTimeChartData.data.push(obj['accessed']);
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
         /*   analyticFactory.getStat('doi', vm.filters).then(function(data){
                vm.doiChartData = {
                    labels: ["Missing DOI", "ANDS DOI", "Non-ANDS DOI"],
                    data: [data['missing_doi'], data['has_ands_doi'], data['has_non_ands_doi']],
                }
            }); */

            //doi activity
        /*    analyticFactory.getStat('doi_activity', vm.filters).then(function(data){
                vm.doiActivityChartData = {
                    labels:[], data:[]
                }
				//$log.debug(data)
					if(data.display){
						vm.doiUser = true
					}else{
						vm.doiUser = false
					}
                angular.forEach(data, function(doi){
                    angular.forEach(doi, function(obj, index){
                        if (vm.doiActivityChartData.labels.indexOf(obj.activity) > -1) {
                            var index = vm.doiActivityChartData.labels.indexOf(obj.activity);
                            vm.doiActivityChartData.data[index]+=obj.count;
                        } else {
                            if(obj.activity=='MINT'){obj.activity='Automatically Minted'}
                            if(obj.activity=='M_MINT'){obj.activity='Manually Minted'}
                            vm.doiActivityChartData.labels.push(obj.activity);
                            vm.doiActivityChartData.data.push(obj.count);
                        }
                    });
                })
            }); */

            //link check
         /*   analyticFactory.getStat('doi_client', vm.filters).then(function(data){
                vm.brokenLinksByAppID = {
                    labels:[],data:[]
                }
                vm.linkCheckerReport = [];
                angular.forEach(data, function(obj, index){
                    vm.brokenLinksByAppID.labels.push('Broken Link for : '+obj.client_name);
                    vm.brokenLinksByAppID.data.push(obj.url_broken_num);
                    vm.linkCheckerReport.push(obj.linkchecker_report);
                });
            }); */

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
 			//access rights
            analyticFactory.getStat('ro_ar', vm.filters).then(function(data){
                vm.ARChartData = {
                    labels:[], data:[]
                }
                angular.forEach(data, function(obj){
                    vm.ARChartData.labels.push('Access rights: '+obj.key);
                    vm.ARChartData.data.push(obj.doc_count);
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
            tmpfilters['class'] = ['collection'];
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
            //$log.debug(points, evt);
            if (points.length > 0) {
                var date = points[0].label;
                //$log.debug('Showing date ' + date);

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